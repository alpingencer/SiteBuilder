<p align="center">
    <img alt="The Eufony Logo" src="https://raw.githubusercontent.com/eufony/eufony/v3/Eufony.png">
</p>

Eufony is a page management and HTML auto-generation framework for PHP written by Alpin Gencer.

## Installation

* Eufony v3.0 Lithium comes with Composer support. You can find the Packagist
  page [here](https://packagist.org/packages/eufony/eufony).

## Framework Rules and Restrictions

### 1. Project structure

* '/public': Anything publicly accessible via an HTTP request (the server document root)
    * '/public/assets': Any CSS, JS, font, image, etc. assets that a page needs
* '/routes': Webpage content files
    * '/routes/routes.json': The webpage hierarchy file
    * One directory for each subsite

### 2. Server system

* PHP sessions must be enabled
* All HTTP requests must be rerouted to a common PHP file (typically '/public/index.php')

### 3. Client system

* Cookies must be enabled

## Wiki

As it is still very early days for Eufony, the wiki has not been set up yet.

## License

The Eufony framework is open-sourced software licensed under
the [GNU Lesser General Public License](LICENSE.md).
