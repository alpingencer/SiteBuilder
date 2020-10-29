<?php

namespace SiteBuilder\Modules\Translation;

use SiteBuilder\Core\MM\Module;
use DateTime;
use ErrorException;
use IntlDateFormatter;

/**
 * The TranslationModule is resposible for the internationalization of your website.
 * It can handle the translation of tokens and dates into different languages.
 * In order to use this module, initiate it using the ModuleManager, giving it a 'controller'
 * configuration parameter to set how the translation of tokens should happen, for example from a
 * simple file or from a full-blown database.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Translation
 * @see TranslationController
 * @see FileTranslationController
 * @see DatabaseTranslationController
 */
class TranslationModule extends Module {
	/**
	 * The controller responsible for the translation of tokens
	 *
	 * @var TranslationController
	 */
	private $controller;
	/**
	 * The language to translate into
	 *
	 * @var string
	 */
	private $lang;

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\MM\Module::init()
	 */
	public function init(array $config): void {
		// Check if content manager has been initialized
		// If no, throw error: The translation module depends on the content manager
		if(!isset($GLOBALS['__SiteBuilder_ContentManager'])) {
			throw new ErrorException("TranslationModule cannot be used if a ContentManager has not been initialized!");
		}

		$cm = $GLOBALS['__SiteBuilder_ContentManager'];

		// Check if required configuration parameter 'controller' has been set
		// If no, throw error: A TranslationController must be passed to the module
		if(!isset($config['controller'])) {
			throw new ErrorException("The required configuration parameter 'controller' has not been set!");
		}

		if(!isset($config['lang'])) {
			if(empty($cm->page()->getLang())) {
				$config['lang'] = 'en';
			} else {
				$config['lang'] = $cm->page()->getLang();
			}
		}

		$this->setController($config['controller']);
		$this->setLang($config['lang']);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\MM\Module::runLate()
	 */
	public function runLate(): void {
		$cm = $GLOBALS['__SiteBuilder_ContentManager'];

		$this->replaceContentWithTokens($cm->page()->head);
		$this->replaceContentWithTokens($cm->page()->body);
		$this->replaceContentWithDates($cm->page()->head);
		$this->replaceContentWithDates($cm->page()->body);
	}

	/**
	 * Replaces tokens within a given string with their respective translations recursively
	 *
	 * @param string $content The string to process
	 */
	private function replaceContentWithTokens(string &$content): void {
		// Replace TOKEN([token]) by token ID or tag
		// [token] must contain only word characters (a-z, A-Z, 0-9, _) or -
		$content = preg_replace_callback("/TOKEN\( *(?'id'[\w-]*) *\)/", function ($match) {
			// Get translation
			$translation = $this->controller->translate($match['id'], $this->lang);

			// Replace tokens in tokens recursively
			$this->replaceContentWithTokens($translation);
			return $translation;
		}, $content);
	}

	/**
	 * Replace dates within a given string with their respective translations
	 *
	 * @param string $content The string to process
	 */
	private function replaceContentWithDates(string &$content): void {
		// Replace DATE([year]-[month]-[day]) with long date
		// [year], [month], and [day] must contain only digits
		$dateRegex = "\d*";
		$content = preg_replace_callback("/DATE\( *(?'year'" . $dateRegex . ")-(?'month'" . $dateRegex . ")-(?'day'" . $dateRegex . ") *\)/", function ($match) {
			$dateTime = new DateTime();
			$dateTime->setDate($match['year'], $match['month'], $match['day']);
			$formatter = new IntlDateFormatter($this->lang, IntlDateFormatter::LONG, IntlDateFormatter::NONE);
			return $formatter->format($dateTime);
		}, $content);
	}

	public function getController(): TranslationController {
		return $this->controller;
	}

	private function setController(TranslationController $controller): self {
		$this->controller = $controller;
		return $this;
	}

	public function getLang(): string {
		return $this->lang;
	}

	private function setLang(string $lang): self {
		$this->lang = $lang;
		return $this;
	}

}

