<?php

namespace SiteBuilder\Modules\Security;

use SiteBuilder\Core\CM\Component;

/**
 * The AuthenticationComponent specifies the HTML to be displayed when a user is trying to login or
 * logout.
 * If there are any CSS or JS dependencies, these can be specified as well.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Security
 */
class AuthenticationComponent extends Component {
	/**
	 * The HTML string to output when a user is logging in.
	 * Please note that in order for the login request to be processed, the
	 * '__SiteBuilder_LoginRequest' POST variable must be set
	 *
	 * @var string
	 */
	private $loginInnerHTML;
	/**
	 * The HTML string to output when a user is logging out
	 * Please note that in order for the logout request to be processed, the
	 * '__SiteBuilder_LogoutRequest' POST variable must be set
	 *
	 * @var string
	 */
	private $logoutInnerHTML;
	/**
	 * Wether the login or logout HTML should be displayed
	 *
	 * @var bool
	 */
	private $isLoginOrLogout;
	/**
	 * An array of dependencies the HTML might require
	 *
	 * @var array
	 */
	private $dependencies;

	/**
	 * Returns an instance of AuthenticationComponent
	 *
	 * @param string $loginInnerHTML The login HTML string
	 * @param string $logoutInnerHTML The logout HTML string
	 * @param array $dependencies An array of CSS and JS dependencies that are potentially required
	 * @return AuthenticationComponent The initialized instance
	 */
	public static function init(string $loginInnerHTML, string $logoutInnerHTML, array $dependencies = []): AuthenticationComponent {
		return new self($loginInnerHTML, $logoutInnerHTML, $dependencies);
	}

	/**
	 * Constructor for the AuthenticationComponent.
	 * To get an instance of this class, use AuthenticationComponent::init()
	 *
	 * @param string $loginInnerHTML The login HTML string
	 * @param string $logoutInnerHTML The logout HTML string
	 * @param array $dependencies An array of CSS and JS dependencies that are potentially required
	 * @see AuthenticationComponent::init()
	 */
	protected function __construct(string $loginInnerHTML, string $logoutInnerHTML, array $dependencies) {
		parent::__construct();
		$this->setLoginInnerHTML($loginInnerHTML);
		$this->setLogoutInnerHTML($logoutInnerHTML);
		$this->setIsLoginOrLogout(true);
		$this->setDependencies($dependencies);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getDependencies()
	 */
	public function getDependencies(): array {
		return $this->dependencies;
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getContent()
	 */
	public function getContent(): string {
		$id = empty($this->getHTMLID()) ? '' : ' id="' . $this->getHTMLID() . '"';
		$class = empty($this->getHTMLClasses()) ? '' : ' class="' . $this->getHTMLClasses() . '"';

		$html = '<form' . $id . $class . ' method="POST" enctype="multipart/form-data">';

		if($this->isLoginOrLogout) {
			$html .= $this->loginInnerHTML;
		} else {
			$html .= $this->logoutInnerHTML;
		}

		$html .= '</form>';
		return $html;
	}

	/**
	 * Getter for the login HTML
	 *
	 * @return string
	 * @see AuthenticationComponent::$loginInnerHTML
	 */
	public function getLoginInnerHTML(): string {
		return $this->loginInnerHTML;
	}

	/**
	 * Setter for the login HTML
	 *
	 * @param string $loginInnerHTML
	 * @see AuthenticationComponent::$loginInnerHTML
	 */
	private function setLoginInnerHTML(string $loginInnerHTML): void {
		$this->loginInnerHTML = $loginInnerHTML;
	}

	/**
	 * Getter for the logout HTML
	 *
	 * @return string
	 * @see AuthenticationComponent::$logoutInnerHTML
	 */
	public function getLogoutInnerHTML(): string {
		return $this->logoutInnerHTML;
	}

	/**
	 * Setter for the logout HTML
	 *
	 * @param string $logoutInnerHTML
	 * @see AuthenticationComponent::$logoutInnerHTML
	 */
	private function setLogoutInnerHTML(string $logoutInnerHTML): void {
		$this->logoutInnerHTML = $logoutInnerHTML;
	}

	/**
	 * Getter for wether the login or logout HTML should be displayed
	 *
	 * @return bool
	 * @see AuthenticationComponent::$isLoginOrLogout
	 */
	public function isLoginOrLogout(): bool {
		return $this->isLoginOrLogout;
	}

	/**
	 * Setter for wether the login or logout HTML should be displayed
	 *
	 * @param bool $isLoginOrLogout
	 * @return self Returns itself for chaining other functions
	 * @see AuthenticationComponent::$isLoginOrLogout
	 */
	public function setIsLoginOrLogout(bool $isLoginOrLogout): self {
		$this->isLoginOrLogout = $isLoginOrLogout;
		return $this;
	}

	/**
	 * Setter for the CSS and JS dependencies of the component
	 *
	 * @param array $dependencies
	 * @see AuthenticationComponent::$dependencies
	 */
	private function setDependencies(array $dependencies): void {
		$this->dependencies = $dependencies;
	}

}

