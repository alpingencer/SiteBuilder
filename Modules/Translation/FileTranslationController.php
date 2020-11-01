<?php

namespace SiteBuilder\Modules\Translation;

/**
 * A FileTranslationController translates tokens using a PHP associative array, loaded from a file
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Translation
 */
class FileTranslationController extends TranslationController {
	/**
	 * The associative array of translations.
	 * The first level of the array represents the token IDs, while the second level represents the
	 * language.
	 *
	 * @var array
	 */
	private $translations;

	/**
	 * Returns an instance of FileTranslationController
	 *
	 * @param array $translations The translations associative array
	 * @return FileTranslationController The initialized instance
	 * @see FileTranslationController::loadFromJSON()
	 */
	public static function init(array $translations): FileTranslationController {
		return new self($translations);
	}

	/**
	 * Loads the translations from a given JSON file and initializes a FileTranslationController
	 *
	 * @param string $file The JSON file to read from
	 * @return FileTranslationController The initialized instance
	 */
	public static function loadFromJSON(string $file): FileTranslationController {
		return FileTranslationController::init(json_decode(file_get_contents($file), true));
	}

	/**
	 * Constructor for the FileTranslationController.
	 * To get an instance of this class, use FileTranslationController::init()
	 *
	 * @param array $translations
	 * @see FileTranslationController::init()
	 */
	private function __construct(array $translations) {
		$this->setTranslation($translations);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Translation\TranslationController::translate()
	 */
	public function translate(string $id, string $lang): string {
		// Check if ID and language are defined in the given translations file
		// If no, return error: Translation not defined!
		if(!isset($this->translations[$id]) || !isset($this->translations[$id][$lang])) {
			$message = "Error while translating ID '$id' into language '$lang'!";
			trigger_error($message, E_USER_WARNING);
			return $message;
		}

		return $this->translations[$id][$lang];
	}

	/**
	 * Getter for the translations associative array
	 *
	 * @return array
	 */
	public function getTranslations(): array {
		return $this->translations;
	}

	/**
	 * Setter for the translations associative array
	 *
	 * @param array $translations
	 */
	private function setTranslation(array $translations): void {
		$this->translations = $translations;
	}

}

