<?php

namespace SiteBuilder\Modules\Security;

use SiteBuilder\Core\CM\Component;

class AuthenticationComponent extends Component {
	private $loginInnerHTML;
	private $logoutInnerHTML;
	private $isLoginOrLogout;
	private $dependencies;

	public static function init(string $loginInnerHTML, string $logoutInnerHTML, array $dependencies = []): AuthenticationComponent {
		return new self($loginInnerHTML, $logoutInnerHTML, $dependencies);
	}

	protected function __construct(string $loginInnerHTML, string $logoutInnerHTML, array $dependencies) {
		parent::__construct();
		$this->setLoginInnerHTML($loginInnerHTML);
		$this->setLogoutInnerHTML($logoutInnerHTML);
		$this->setIsLoginOrLogout(true);
		$this->setDependencies($dependencies);
	}

	public function getDependencies(): array {
		return $this->dependencies;
	}

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

	public function getLoginInnerHTML(): string {
		return $this->loginInnerHTML;
	}

	private function setLoginInnerHTML(string $loginInnerHTML): self {
		$this->loginInnerHTML = $loginInnerHTML;
		return $this;
	}

	public function getLogoutInnerHTML(): string {
		return $this->logoutInnerHTML;
	}

	private function setLogoutInnerHTML(string $logoutInnerHTML): self {
		$this->logoutInnerHTML = $logoutInnerHTML;
		return $this;
	}

	public function isLoginOrLogout(): bool {
		return $this->isLoginOrLogout;
	}

	public function setIsLoginOrLogout(bool $isLoginOrLogout): self {
		$this->isLoginOrLogout = $isLoginOrLogout;
		return $this;
	}

	private function setDependencies(array $dependencies): self {
		$this->dependencies = $dependencies;
		return $this;
	}

}

