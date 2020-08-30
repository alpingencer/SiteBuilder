<?php

namespace SiteBuilder\Internationalization;

use SiteBuilder\SiteBuilderComponent;

class InternationalizationComponent extends SiteBuilderComponent {
	private $tokenTableName;
	private $langColumnName, $identifierColumnName;
	private $locales;

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		$this->locales = array();
		$this->tokenTableName = '';
		$this->langColumnName = '';
		$this->identifierColumnName = 'IDENTIFIER';
	}

	public function setTokenTableName(string $tableName): self {
		$this->tokenTableName = $tableName;
		return $this;
	}

	public function getTokenTableName(): string {
		return $this->tokenTableName;
	}

	public function setLangColumnName(string $langColumnName): self {
		$this->langColumnName = $langColumnName;
		return $this;
	}

	public function getLangColumnName(): string {
		return $this->langColumnName;
	}

	public function setIdentifierColumnName(string $identifierColumnName): self {
		$this->identifierColumnName = $identifierColumnName;
		return $this;
	}

	public function getIdentifierColumnName(): string {
		return $this->identifierColumnName;
	}

	public function setLocale(int $localeType, string $locale): self {
		$this->locales[$localeType] = $locale;
		return $this;
	}

	public function getLocale(int $localeType): string {
		if(isset($this->locales[$localeType])) {
			return $this->locales[$localeType];
		} else if(isset($this->locales[LC_ALL])) {
			return $this->locales[LC_ALL];
		} else {
			return 'en';
		}
	}

}
