<?

defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Library_PageRequestView extends RequestView {

	protected $c; // page

	public function getPageObject() {
		return $this->c;
	}

	/** 
	 * Begin the render
	 */
	public function start($page) {
		$this->c = $page;
		parent::start($page->getCollectionPath());
	}

	public function getScopeItems() {
		$items = parent::getScopeItems();
		$items['c'] = $this->c;
		return $items;
	}

	public function inc($file, $args = array()) {
		extract($args);
		extract($this->getScopeItems());
		$env = Environment::get();
		include($env->getPath(DIRNAME_THEMES . '/' . $this->themeHandle . '/' . $file, $this->themePkgHandle));
	}

	protected function setupController() {
		if (!isset($this->controller)) {
			$this->controller = Loader::controller($this->c);
			$this->controller->setupAndRun();
		}
	}

	protected function loadRequestViewThemeObject() {
		$theme = $this->c->getCollectionThemeObject();
		if (is_object($theme)) {
			$this->themeHandle = $theme->getThemeHandle();
		}
		parent::loadRequestViewThemeObject();
	}

	public function setupRender() {
		$this->loadRequestViewThemeObject();
		$env = Environment::get();

		if ($this->c->getCollectionTypeID() == 0 && $this->c->getCollectionFilename()) {
			$cFilename = trim($this->c->getCollectionFilename(), '/');
			// if we have this exact template in the theme, we use that as the outer wrapper and we don't do an inner content file
			$r = $env->getRecord(DIRNAME_THEMES . '/' . $this->themeHandle . '/' . $cFilename);
			if ($r->exists()) {
				$this->setViewTemplate($r->file);
			} else {
				$this->setViewTemplate($env->getPath(DIRNAME_THEMES . '/' . $this->themeHandle . '/' . FILENAME_THEMES_VIEW, $this->themePkgHandle));
				$this->setInnerContentFile($env->getPath(DIRNAME_PAGES . '/' . $cFilename, $this->themePkgHandle));
			}
		} else {
			$pt = PageTemplate::getByID($this->c->getPageTemplateID());
			$rec = $env->getRecord(DIRNAME_PAGE_TYPES . '/' . $this->c->getCollectionTypeHandle() . '.php', $this->themePkgHandle);
			if ($rec->exists()) {
				$this->setInnerContentFile($env->getPath(DIRNAME_PAGES . '/' . $cFilename, $this->themePkgHandle));
				$this->setViewTemplate($env->getPath(DIRNAME_THEMES . '/' . $this->themeHandle . '/' . FILENAME_THEMES_VIEW, $this->themePkgHandle));
			} else {
				$rec = $env->getRecord(DIRNAME_THEMES . '/' . $this->themeHandle . '/' . $pt->getPageTemplateHandle() . '.php', $this->themePkgHandle);
				if ($rec->exists()) {
					$this->setViewTemplate($env->getPath(DIRNAME_THEMES . '/' . $this->themeHandle . '/' . $pt->getPageTemplateHandle() . '.php', $this->themePkgHandle));
				} else {
					$this->setViewTemplate($env->getPath(DIRNAME_THEMES . '/' . $this->themeHandle . '/' . FILENAME_THEMES_DEFAULT, $this->themePkgHandle));
				}
			}
		}

	}

	public function deliverRender($contents) {
		$contents = parent::deliverRender($contents);
		// do full page caching
		print $contents;
	}

	/** 
	 * @deprecated
	 */
	public function getCollectionObject() {return $this->getPageObject();}
	

}