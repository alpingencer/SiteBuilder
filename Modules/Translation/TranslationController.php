<?php

namespace SiteBuilder\Modules\Translation;

/**
 * A TranslationController is responsible for translating a token with a given ID into a given
 * language using the translate() method.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Translation
 * @see FileTranslationController
 * @see DatabaseTranslationController
 */
abstract class TranslationController {

	/**
	 * Translate the given token ID into the given language
	 *
	 * @param int $id The ID to search for
	 * @param string $lang The language to get
	 * @return string The translated string
	 */
	public abstract function translate(int $id, string $lang): string;

}

