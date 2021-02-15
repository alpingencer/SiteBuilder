<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Exception;

use ErrorException;
use Eufony\Core\Content\ContentManager;
use Eufony\Core\FrameworkManager;
use Eufony\Core\Website\WebsiteManager;
use Eufony\Utils\Classes\JsonDecoder;
use Eufony\Utils\Classes\Normalizer;
use Eufony\Utils\Exceptions\IOException;
use Eufony\Utils\Exceptions\PageHierarchyException;
use Eufony\Utils\Traits\ManagedObject;
use Eufony\Utils\Traits\Singleton;
use Throwable;

final class ExceptionManager {
	public const CONFIG_REDIRECT = 'exception.redirect';

	use ManagedObject;
	use Singleton;

	private array $errorPages;

	public function __construct(array $config) {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		$this->redirectOnException($config[ExceptionManager::CONFIG_REDIRECT] ?? true);
	}

	public function redirectOnException(bool $redirect): void {
		$this->assertCallerIsManager();

		if($redirect) {
			set_exception_handler(
				function(Throwable $e) {
					// Log exception
					error_log("Uncaught $e", message_type: 4);

					if(FrameworkManager::instance()->ready()) {
						// Redirect to error page
						$this->showErrorPage(500);
					}
				}
			);
		} else {
			restore_exception_handler();
		}
	}

	/**
	 * @param int         $error_code
	 * @param string|null $error_page
	 *
	 * @return string|self
	 * @throws PageHierarchyException|IOException|ErrorException
	 */
	public function errorPage(int $error_code, string $error_page = null): string|self {
		if($error_page === null) {
			// Check if error page path is defined for the given error code
			// If no, check if the Eufony default path for error pages is defined in the hierarchy
			// If also no, throw error: No error page path defined
			if(!isset($this->errorPages[$error_code])) {
				$error_page = "error/$error_code";

				try {
					$this->errorPage($error_code, $error_page);
				} catch(PageHierarchyException | IOException) {
					throw new ErrorException("Failed while getting error page path: Undefined error page for error code '$error_code'");
				}
			}

			return $this->errorPages[$error_code];
		} else {
			$error_page = Normalizer::filePath($error_page);
			$website_manager = WebsiteManager::instance();

			// Check if error page is in hierarchy
			// If no, throw error: Cannot use undefined error page
			try {
				$website_manager->hierarchy()->page($error_page);
			} catch(PageHierarchyException) {
				throw new PageHierarchyException("Failed while setting error page path: Undefined error page '$error_page' in the hierarchy");
			}

			// Check if error page has a content file
			// If no, throw error: Cannot use error page without its content file
			try {
				$website_manager->contentFile($error_page);
			} catch(IOException) {
				throw new IOException("Failed while setting error page path: Undefined content file for the error page path '$error_page'");
			}

			$this->errorPages[$error_code] = $error_page;
			return $this;
		}
	}

	public function errorPages(): array {
		return $this->errorPages;
	}

	public function showErrorPage(int $error_code, int ...$alternatives): void {
		// Check each error code in order to see if its error page is defined
		// If yes, redirect to it
		// If no, show default error page
		foreach(array($error_code, ...$alternatives) as $current_error_code) {
			try {
				WebsiteManager::instance()->redirect($this->errorPage($current_error_code));
			} catch(ErrorException | IOException) {
				continue;
			}
		}

		$this->showDefaultErrorPage($error_code);
	}

	public function showDefaultErrorPage(int $error_code): void {
		http_response_code($error_code);
		$error_pages = JsonDecoder::read('file://' . __DIR__ . '/default-error-pages.json');

		if(isset($error_pages[$error_code])) {
			$error_name = $error_pages[$error_code]['name'];
			$error_message = $error_pages[$error_code]['message'];
		} else {
			$error_name = 'Unknown Error';
			$error_message = 'An unknown error has occurred';
		}

		$content_manager = ContentManager::instance();
		$content_manager->clear();

		$content_manager->lang('en');
		$content_manager->head()->content = "<title>$error_code $error_name</title>";
		$content_manager->body()->content = "<!-- Eufony Default Page --><h1>$error_code $error_name</h1><p>$error_message</p>";

		$content_manager->output();
		die();
	}

}
