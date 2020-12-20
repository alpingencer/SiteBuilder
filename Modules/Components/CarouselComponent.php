<?php

namespace SiteBuilder\Modules\Components;

use SiteBuilder\Core\CM\Component;
use SiteBuilder\Core\CM\Dependencies\CSSDependency;
use SiteBuilder\Core\CM\Dependencies\JSDependency;

/**
 * A quick and easy carousel of images using Flickity
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Components
 */
class CarouselComponent extends Component {
	/**
	 * An array containing the source strings of the added images
	 *
	 * @var array
	 */
	private $imgSources;
	/**
	 * An array containing the alternative texts to display, if the image sources are not found
	 *
	 * @var array
	 */
	private $altTexts;
	/**
	 * An associative array containing the configuration parameters to be passed to the flickity
	 * carousel.
	 * Please refer to Flickity's documentation for further details.
	 *
	 * @var array
	 */
	private $flickityOptions;

	/**
	 * Returns an instance of CarouselComponent
	 *
	 * @return CarouselComponent The initialized instance
	 */
	public static function init(): CarouselComponent {
		return new self();
	}

	/**
	 * Constructor for the CarouselComponent.
	 * To get an instance of this class, use CarouselComponent::init()
	 */
	private function __construct() {
		parent::__construct();

		$this->clearImgs();
		$this->clearFlickityOptions();
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getDependencies()
	 */
	public function getDependencies(): array {
		$sitebuilderDependencies = array(
				CSSDependency::init('flickity-carousel.css')
		);
		$flickityDependencies = array(
				JSDependency::init('External/Flickity/flickity.pkgd.min.js'),
				CSSDependency::init('External/Flickity/flickity.min.css'),
				JSDependency::init('External/Flickity/fullscreen.js'),
				CSSDependency::init('External/Flickity/fullscreen.css'),
				JSDependency::init('External/Flickity/bg-lazyload.js'),
				JSDependency::init('External/Flickity/hash.js')
		);
		return array_merge($flickityDependencies, $sitebuilderDependencies);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getContent()
	 */
	public function getContent(): string {
		// Set id
		if(empty($this->getHTMLID())) {
			$id = '';
		} else {
			$id = ' id="' . $this->getHTMLID() . '"';
		}

		// Set classes
		$classes = 'sitebuilder-carousel';
		if(!empty($this->getHTMLClasses())) {
			$classes .= ' ' . $this->getHTMLClasses();
		}

		$html = '<div' . $id . ' class="' . $classes . '" data-flickity=\'' . json_encode($this->flickityOptions) . '\'>';

		foreach($this->imgSources as $index => $imgSource) {
			if(empty($this->altTexts[$index])) {
				$altText = 'Image ' . ($index + 1);
			} else {
				$altText = $this->altTexts[$index];
			}

			$html .= '<img class="sitebuilder-carousel--cell" alt="' . $altText . '" src="' . $imgSource . '">';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Adds an image to the carousel
	 *
	 * @param string $imgSource The source of the image
	 * @param string $altText The HTML alt text, for when the source is not found
	 * @return self Returns itself for chaining other functions
	 */
	public function addImg(string $imgSource, string $altText = ''): self {
		array_push($this->imgSources, $imgSource);
		array_push($this->altTexts, $altText);
		return $this;
	}

	/**
	 * Clears all added images from this carousel
	 */
	private function clearImgs(): void {
		$this->imgSources = array();
		$this->altTexts = array();
	}

	/**
	 * Getter for the image sources
	 *
	 * @return array
	 * @see CarouselComponent::$imgSources
	 */
	public function getImgSources(): array {
		return $this->imgSources;
	}

	/**
	 * Getter for the alternative texts
	 *
	 * @return array
	 * @see CarouselComponent::$altTexts
	 */
	public function getAltTexts(): array {
		return $this->altTexts;
	}

	/**
	 * Get the value of a given flickity configuration parameter
	 *
	 * @param string $option The parameter to fetch
	 * @return mixed The value of the parameter
	 */
	public function getFlickityOption(string $option) {
		return $this->flickityOptions[$option];
	}

	/**
	 * Getter for all flickity configuration parameters
	 *
	 * @return array
	 * @see CarouselComponent::$flickityOptions
	 */
	public function getAllFlickityOptions(): array {
		return $this->flickityOptions;
	}

	/**
	 * Set the value of a given flickity configuration parameter
	 *
	 * @param string $option The parameter to set
	 * @param mixed $value The value to set to
	 * @return self Returns itself for chaining other functions
	 */
	public function setFlickityOption(string $option, $value): self {
		$this->flickityOptions[$option] = $value;
		return $this;
	}

	/**
	 * Resets the flickity configuration parameters to the SiteBuilder default values
	 */
	private function clearFlickityOptions(): void {
		$this->flickityOptions = [
				"freeScroll" => false,
				"wrapAround" => true,
				"autoPlay" => 5000,
				"pauseAutoPlayOnHover" => false,
				"fullscreen" => true,
				"imagesLoaded" => true,
				"setGallerySize" => false
		];
	}

}

