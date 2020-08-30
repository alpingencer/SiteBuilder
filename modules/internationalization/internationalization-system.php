<?php

namespace SiteBuilder\Internationalization;

use SiteBuilder\SiteBuilderFamily;
use SiteBuilder\SiteBuilderPage;
use SiteBuilder\SiteBuilderSystem;
use SiteBuilder\Database\Database;
use SiteBuilder\Database\DatabaseComponent;
use DateTime;
use IntlDateFormatter;

class InternationalizationSystem extends SiteBuilderSystem {

	public static function replaceContentWithTokens(string &$content, InternationalizationComponent $component, Database $database): void {
		// Replace TOKEN([token]) by token ID or tag
		// [token] must contain only word characters (a-z, A-Z, 0-9, _) or -
		$contentRegex = "[\w-]*";
		$content = preg_replace_callback("/TOKEN\( *(?'identifier'" . $contentRegex . ") *\)/", function ($match) use ($component, $database) {
			return InternationalizationSystem::getToken($match['identifier'], $component, $database);
		}, $content);
	}

	public static function getToken(string $identifier, InternationalizationComponent $component, Database $database): string {
		$query = 'SELECT ' . $component->getLangColumnName() . ' FROM ' . $component->getTokenTableName();
		$query .= ' WHERE ' . $component->getIdentifierColumnName() . '="' . $identifier . '"';
		$token = $database->getVal($query);

		// Return on error
		if(is_null($token)) {
			return 'Error while fetching token. ' . $component->getIdentifierColumnName() . ': "' . $identifier . '"';
		}

		// Replace tokens within tokens recursively
		InternationalizationSystem::replaceContentWithTokens($token, $component, $database);

		return $token;
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

	public function __construct(int $priority = 0) {
		parent::__construct(SiteBuilderFamily::newInstance()->requireAll(InternationalizationComponent::class), $priority);
	}

	public function proccess(SiteBuilderPage $page): void {
		$internationalizationComponent = $page->getComponent(InternationalizationComponent::class);
		if($page->hasComponent(DatabaseComponent::class)) {
			$database = $page->getComponent(DatabaseComponent::class);
		}

		// Get tokens
		if(isset($database)) {
			InternationalizationSystem::replaceContentWithTokens($page->head, $internationalizationComponent, $database);
			InternationalizationSystem::replaceContentWithTokens($page->body, $internationalizationComponent, $database);
		}

		// Get dates
		InternationalizationSystem::replaceContentWithDates($page->head, $internationalizationComponent);
		InternationalizationSystem::replaceContentWithDates($page->body, $internationalizationComponent);
	}

}
