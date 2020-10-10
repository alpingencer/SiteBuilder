<?php

namespace SiteBuilder\Internationalization;

use SiteBuilder\Family;
use SiteBuilder\Page;
use SiteBuilder\System;
use SiteBuilder\Database\DatabaseInterface;
use SiteBuilder\Database\DatabaseComponent;
use DateTime;
use IntlDateFormatter;

class InternationalizationSystem extends System {

	public static function getToken(string $id, InternationalizationComponent $component, DatabaseInterface $database): string {
		$token = $database->getVal($component->getTokenTableName(), $id, $component->getLangColumnName(), $component->getIDColumnName());

		// Return on error
		if(empty($token)) {
			$message = "Error while fetching token " . $component->getIDColumnName() . "='$id'.";
			trigger_error($message, E_USER_WARNING);
			return $message;
		}

		// Replace tokens within tokens recursively
		static::replaceContentWithTokens($token, $component, $database);

		return $token;
	}

	public static function replaceContentWithTokens(string &$content, InternationalizationComponent $component, DatabaseInterface $database): void {
		// Replace TOKEN([token]) by token ID or tag
		// [token] must contain only word characters (a-z, A-Z, 0-9, _) or -
		$contentRegex = "[\w-]*";
		$content = preg_replace_callback("/TOKEN\( *(?'id'" . $contentRegex . ") *\)/", function ($match) use ($component, $database) {
			return static::getToken($match['id'], $component, $database);
		}, $content);
	}

	public static function replaceContentWithDates(string &$content, InternationalizationComponent $component) {
		// Replace DATE([year]-[month]-[day]) with long date
		// [year], [month], and [day] must contain only digits
		$dateRegex = "\d*";
		$content = preg_replace_callback("/DATE\( *(?'year'" . $dateRegex . ")-(?'month'" . $dateRegex . ")-(?'day'" . $dateRegex . ") *\)/", function ($match) use ($component) {
			$dateTime = new DateTime();
			$dateTime->setDate($match['year'], $match['month'], $match['day']);
			$formatter = new IntlDateFormatter($component->getLocale(LC_TIME), IntlDateFormatter::LONG, IntlDateFormatter::NONE);
			return $formatter->format($dateTime);
		}, $content);
	}

	public function __construct() {
		parent::__construct(Family::newInstance()->requireAll(InternationalizationComponent::class));
	}

	public function process(Page $page): void {
		$internationalizationComponent = $page->getComponentByClass(InternationalizationComponent::class);
		if($page->hasComponentsByClass(DatabaseComponent::class)) {
			$database = $page->getComponentByClass(DatabaseComponent::class);
		}

		// Get tokens
		if(isset($database)) {
			static::replaceContentWithTokens($page->head, $internationalizationComponent, $database);
			static::replaceContentWithTokens($page->body, $internationalizationComponent, $database);
		}

		// Get dates
		static::replaceContentWithDates($page->head, $internationalizationComponent);
		static::replaceContentWithDates($page->body, $internationalizationComponent);
	}

}
