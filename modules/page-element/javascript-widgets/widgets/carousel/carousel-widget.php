<?php

namespace SiteBuilder\PageElement;

class CarouselWidget extends JavascriptWidget {
	private $imgSources, $altTexts;
	private $flickityOptions;

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		$dependencies = array(
				/* flickity */
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'javascript-widgets/external-resources/flickity/flickity.pkgd.min.js'),
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'javascript-widgets/external-resources/flickity/flickity.min.css'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'javascript-widgets/external-resources/flickity/fullscreen.js'),
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'javascript-widgets/external-resources/flickity/fullscreen.css'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'javascript-widgets/external-resources/flickity/bg-lazyload.js'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'javascript-widgets/external-resources/flickity/hash.js'),

				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'javascript-widgets/widgets/carousel/carousel.css')
		);
		parent::__construct($dependencies);

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
