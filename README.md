<p align="center">
    <img alt="The Eufony Logo" width="128" src="https://raw.githubusercontent.com/eufony/eufony/master/Eufony.svg">
</p>

Eufony is a page management and HTML auto-generation framework for PHP written by Alpin Gencer.

## Installation

* Eufony v3.0 Lithium comes with Composer support. You can find the Packagist
  page [here](https://packagist.org/packages/eufony/eufony).

## Project Structure

* `config` Configuration files for the webserver (keep any sensitive information out of VCS!)
    * `constants.php`
    * `.env`
    * `.env.*`
* `public` Publicly accessible files via an HTTP request (the server document root)
* `routes` Webpage content files
    * `routes.json` The webpage hierarchy file
* `src` User-defined classes and functions, following the PSR-4 standard
* `storage`
    * `chmod o+rw storage`
* `vendor`

## Wiki

As it is still very early days for Eufony, the wiki has not been set up yet.

## License

The Eufony framework is open-sourced software licensed under the [GNU Lesser General Public License](LICENSE.md).
