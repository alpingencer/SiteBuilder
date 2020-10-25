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

		$this->setWebsiteManager($config);
		$this->setModuleManager($config);
		$this->setContentManager($config);
	}

	/**
	 * Run the core, so that the individual managers are run.
	 * Please note that this method must be called in order for the framework to work.
	 *
	 * @see WebsiteManager::run()
	 * @see ModuleManager::run()
	 * @see ContentManager::run()
	 */
	public function run(): void {
		$this->wm->run();
		$this->mm->run();
		$this->cm->run();
	}

	/**
	 * Getter for the website manager.
	 * For a convenience function with a shorter name, see Core::wm()
	 *
	 * @return WebsiteManager
	 * @see Core::wm()
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
	 */
	public function wm(): WebsiteManager {
		return $this->getWebsiteManager();
	}

	/**
	 * Setter for the website manager
	 *
	 * @param array $config The configuration parameters to use
	 * @return self Returns itself for chaining other functions
	 */
	private function setWebsiteManager(array $config): self {
		$this->wm = WebsiteManager::init($config);
		return $this;
	}

	/**
	 * Getter for the module manager.
	 * For a convenience function with a shorter name, see Core::mm()
	 *
	 * @return ModuleManager
	 * @see Core::mm()
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
	 */
	public function mm(): ModuleManager {
		return $this->getModuleManager();
	}

	/**
	 * Setter for the module manager
	 *
	 * @param array $config The configuration parameters to use
	 * @return self Returns itself for chaining other functions
	 */
	private function setModuleManager(array $config): self {
		$this->mm = ModuleManager::init($config);
		return $this;
	}

	/**
	 * Getter for the content manager.
	 * For a convenience function with a shorter name, see Core::cm()
	 *
	 * @return ContentManager
	 * @see Core::cm()
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
	 */
	public function cm(): ContentManager {
		return $this->getContentManager();
	}

	/**
	 * Setter for the content manager
	 *
	 * @param array $config The configuration parameters to use
	 * @return self Returns itself for chaining other functions
	 */
	private function setContentManager(array $config): self {
		$this->cm = ContentManager::init($config);
		return $this;
	}

}

