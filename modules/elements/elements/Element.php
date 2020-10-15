<?php

namespace SiteBuilder\Elements;

use SiteBuilder\Component;

abstract class Element extends Component {
	private $htmlID;
	private $htmlClasses;

	public function __construct() {
		$this->clearHTMLID();
		$this->clearHTMLClasses();
	}

	public abstract function getDependencies(): array;

	public abstract function getContent(): string;

	public function setHTMLID(string $htmlID) {
		$this->htmlID = $htmlID;
		return $this;
	}

	public function clearHTMLID() {
		$this->setHTMLID('');
		return $this;
	}

	public function getHTMLID(): string {
		return $this->htmlID;
	}

	public function setHTMLClasses(string $htmlClasses) {
		$this->htmlClasses = trim($htmlClasses);
		return $this;
	}

	public function clearHTMLClasses() {
		$this->setHTMLClasses('');
		return $this;
	}

	public function addHTMLClasses(string $htmlClasses) {
		$htmlClasses = trim($htmlClasses);

		if(empty($this->htmlClasses)) {
			$this->setHTMLClasses($htmlClasses);
		} else {
			$this->setHTMLClasses($this->htmlClasses . ' ' . $htmlClasses);
		}

		return $this;
	}

	public function getHTMLClasses(): string {
		return $this->htmlClasses;
	}

}
