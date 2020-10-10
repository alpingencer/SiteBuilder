<?php

namespace SiteBuilder\Internationalization;

use SiteBuilder\Component;

class InternationalizationComponent extends Component {
	private $tokenTableName;
	private $langColumnName, $idColumnName;
	private $locales;

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		$this->locales = array();
		$this->setTokenTableName('');
		$this->setLangColumnName('');
		$this->setIDColumnName('ID');
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

	public function setIDColumnName(string $idColumnName): self {
		$this->idColumnName = $idColumnName;
		return $this;
	}

	public function getIDColumnName(): string {
		return $this->idColumnName;
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
