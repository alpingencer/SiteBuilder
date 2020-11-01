<?php

namespace SiteBuilder\Core\CM;


/**
 * A StaticHTMLComponent is the most basic form of Component there is.
 * It is used to define the HTML to be output directly, meaning it will not get processed in any
 * further way.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\CM
 * @see Component
 */
class StaticHTMLComponent extends Component {
	/**
	 * The HTML string to be output directly to the page body
	 *
	 * @var string
	 */
	private $html;

	/**
	 * Returns an instance of StaticHTMLComponent
	 *
	 * @param string $html The HTML string of this component
	 * @return StaticHTMLComponent The initialized instance
	 */
	public static function init(string $html): StaticHTMLComponent {
		return new self($html);
	}

	/**
	 * Constructor for the StaticHTMLComponent.
	 * To get an instance of this class, use StaticHTMLComponent::init()
	 *
	 * @param string $html The HTML string of this component
	 * @see StaticHTMLComponent::init()
	 */
	private function __construct(string $html) {
		$this->html = $html;
	}

	/**
	 * {@inheritdoc}
	 * @see Component::getDependencies()
	 */
	public function getDependencies(): array {
		return array();
	}

	/**
	 * {@inheritdoc}
	 * @see Component::getContent()
	 */
	public function getContent(): string {
		return $this->getHTML();
	}

	/**
	 * Getter for the HTML string
	 *
	 * @return string
	 * @see StaticHTMLComponent::$html
	 */
	public function getHTML(): string {
		return $this->html;
	}

}

