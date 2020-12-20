<?php

namespace SiteBuilder\Core;

use SiteBuilder\Core\CM\ContentManager;
use SiteBuilder\Core\MM\ModuleManager;
use SiteBuilder\Core\WM\WebsiteManager;
use ErrorException;

/**
 * The Core class orchestrates all three managers (WebsiteManager, ModuleManager, and
 * ContentManager) an provides a convenient way to initialize, configure, and run them.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core
 * @see WebsiteManager
 * @see ModuleManager
 * @see ContentManager
 */
class Core {
	/**
	 * Static instance field for Singleton code design in PHP
	 *
	 * @var ContentManager
	 */
	private static $instance;
	/**
	 * Wether the core was run previously
	 *
	 * @var bool
	 */
	private $isRun;
	/**
	 * The WebsiteManager instance
	 *
	 * @var WebsiteManager
	 */
	private $wm;
	/**
	 * The ModuleManager instance
	 *
	 * @var ModuleManager
	 */
	private $mm;
	/**
	 * The ContentManager instance
	 *
	 * @var ContentManager
	 */
	private $cm;

	/**
	 * Returns an instance of Core
	 *
	 * @param array $config The configuration parameters to use
	 * @return Core The initialized instance
	 */
	public static function init(array $config = []): Core {
		if(isset(Core::$instance)) {
			throw new ErrorException("An instance of Core has already been initialized!");
		}

		Core::$instance = new self($config);
		return Core::$instance;
	}

	/**
	 * Constructor for the Core.
	 * To get an instance of this class, use Core::init().
	 * The constructor also sets the superglobal '__SiteBuilder_Core' to easily get this instance.
	 *
	 * @param array $config The configuration parameters to use
	 * @see Core::init()
	 */
	private function __construct(array $config) {
		$GLOBALS['__SiteBuilder_Core'] = &$this;

		$this->setIsRun(false);
		$this->setWebsiteManager($config);
		$this->setModuleManager($config);
		$this->setContentManager($config);

		// Check if 'autoGenerateTitle' configuration parameter is set to false
		// If no, generate <title> tag from page hierarchy 'title' attributes
		if(($config['autoGenerateTitle'] ?? true) && $this->wm->getHierarchy()->isPageDefined($this->wm->getCurrentPagePath())) {
			$pageTitle = $this->wm->getHierarchy()->getPageAttribute($this->wm->getCurrentPagePath(), 'title');
			$websiteTitle = $this->wm->getHierarchy()->getGlobalAttribute('title');
			$this->cm->page()->head .= "<title>$pageTitle - $websiteTitle</title>";
		}
	}

	/**
	 * Run the core, so that the individual managers are run.
	 * Please note that this method must be called in order for the framework to work.
	 * The managers are run in the following order:
	 * <ol>
	 * <li>The website manager is run.</li>
	 * <li>The first and second stages of the module manager are run.</li>
	 * <li>The content manager is run.</li>
	 * <li>The last stage of the module manager is run.</li>
	 * <li>The content manager sends its output to the browser.</li>
	 * </ol>
	 *
	 * @see WebsiteManager::run()
	 * @see ModuleManager::run()
	 * @see ContentManager::run()
	 */
	public function run(): void {
		// Check if the core has already been run
		// If yes, trigger warning and return: Cannot run core multiple times
		if($this->isRun) {
			trigger_error("The core has already been run!", E_USER_WARNING);
			return;
		}

		// Set is run
		$this->setIsRun(true);

		// Check if the website manager has already been run
		// If yes, trigger notice: Skipping running it a second time
		if(!$this->wm->isRun()) {
			$this->wm->run();
		} else {
			trigger_error("The website manager has already been run! Skipping it...", E_USER_NOTICE);
		}

		$this->mm->runEarly();
		$this->mm->run();

		// Check if the content manager has already been run
		// If yes, trigger notice: Skipping running it a second time
		if(!$this->cm->isRun()) {
			$this->cm->run();
		} else {
			trigger_error("The content manager has already been run! Skipping it...", E_USER_NOTICE);
		}

		$this->mm->runLate();

		$this->cm->outputToBrowser();
	}

	/**
	 * Getter for wether the core was run previously
	 *
	 * @return bool
	 * @see Core::$isRun
	 */
	public function isRun(): bool {
		return $this->isRun;
	}

	/**
	 * Setter for wether the core was run previously
	 *
	 * @param bool $isRun
	 * @see Core::$isRun
	 */
	private function setIsRun(bool $isRun): void {
		$this->isRun = $isRun;
	}

	/**
	 * Getter for the website manager.
	 * For a convenience function with a shorter name, see Core::wm()
	 *
	 * @return WebsiteManager
	 * @see Core::wm()
	 * @see Core::$wm
	 */
	public function getWebsiteManager(): WebsiteManager {
		return $this->wm;
	}

	/**
	 * Getter for the website manager.
	 * This is a convenience function for Core::getWebsiteManager()
	 *
	 * @return WebsiteManager
	 * @see Core::getWebsiteManager()
	 * @see Core::$wm
	 */
	public function wm(): WebsiteManager {
		return $this->getWebsiteManager();
	}

	/**
	 * Setter for the website manager
	 *
	 * @param array $config The configuration parameters to use
	 * @see Core::$wm
	 */
	private function setWebsiteManager(array $config): void {
		$this->wm = WebsiteManager::init($config);
	}

	/**
	 * Getter for the module manager.
	 * For a convenience function with a shorter name, see Core::mm()
	 *
	 * @return ModuleManager
	 * @see Core::mm(
	 * @see Core::$mm)
	 */
	public function getModuleManager(): ModuleManager {
		return $this->mm;
	}

	/**
	 * Getter for the module manager.
	 * This is a convenience function for Core::getModuleManager()
	 *
	 * @return ModuleManager
	 * @see Core::getModuleManager()
	 * @see Core::$mm
	 */
	public function mm(): ModuleManager {
		return $this->getModuleManager();
	}

	/**
	 * Setter for the module manager
	 *
	 * @param array $config The configuration parameters to use
	 * @see Core::$mm
	 */
	private function setModuleManager(array $config): void {
		$this->mm = ModuleManager::init($config);
	}

	/**
	 * Getter for the content manager.
	 * For a convenience function with a shorter name, see Core::cm()
	 *
	 * @return ContentManager
	 * @see Core::cm()
	 * @see Core::$cm
	 */
	public function getContentManager(): ContentManager {
		return $this->cm;
	}

	/**
	 * Getter for the content manager.
	 * This is a convenience function for Core::getContentManager()
	 *
	 * @return ContentManager
	 * @see Core::getContentManager()
	 * @see Core::$cm
	 */
	public function cm(): ContentManager {
		return $this->getContentManager();
	}

	/**
	 * Setter for the content manager
	 *
	 * @param array $config The configuration parameters to use
	 * @see Core::$cm
	 */
	private function setContentManager(array $config): void {
		$this->cm = ContentManager::init($config);
	}

}

