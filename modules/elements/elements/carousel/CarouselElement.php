<?php

namespace SiteBuilder\Elements;

class CarouselElement extends Element {
	private $imgSources, $altTexts;
	private $flickityOptions;

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		parent::__construct();
		$this->imgSources = array();
		$this->altTexts = array();
		$this->flickityOptions = array(
				"freeScroll" => false,
				"wrapAround" => true,
				"autoPlay" => 5000,
				"pauseAutoPlayOnHover" => false,
				"fullscreen" => true,
				"imagesLoaded" => true,
				"setGallerySize" => false
		);
	}

	public function getDependencies(): array {
		$sitebuilderDependencies = array(
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'elements/carousel/carousel.css')
		);
		$flickityDependencies = array(
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'flickity/flickity.pkgd.min.js'),
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'flickity/flickity.min.css'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'flickity/fullscreen.js'),
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'flickity/fullscreen.css'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'flickity/bg-lazyload.js'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'flickity/hash.js')
		);
		return array_merge($flickityDependencies, $sitebuilderDependencies);
	}

	public function getContent(): string {
		$html = '<div class="sitebuilder-carousel" data-flickity=\'' . json_encode($this->flickityOptions) . '\'>';

		foreach($this->imgSources as $index => $imgSource) {
			if(empty($this->altTexts[$index])) {
				$altText = 'Image ' . ($index + 1);
			} else {
				$altText = $this->altTexts[$index];
			}

			$html .= '<img class="sitebuilder-carousel-cell" alt="' . $altText . '" src="' . $imgSource . '">';
		}

		$html .= '</div>';

		return $html;
	}

	public function addImg(string $imgSource, string $altText = ''): self {
		array_push($this->imgSources, $imgSource);
		array_push($this->altTexts, $altText);
		return $this;
	}

	public function getImgSources(): array {
		return $this->imgSources;
	}

	public function getAltTexts(): array {
		return $this->altTexts;
	}

	public function setFlickityOption(string $option, $value): self {
		$this->flickityOptions[$option] = $value;
		return $this;
	}

	public function getFlickityOption(string $option) {
		return $this->flickityOptions['option'];
	}

	public function getAllFlickityOptions(): array {
		return $this->flickityOptions;
	}

}
