<h1 align="center">SiteBuilder</h1>

## About SiteBuilder

SiteBuilder is a page management and HTML auto-generation framework for PHP written by Alpin Gencer.

## Installation

## Framework Rules and Restrictions

### 1. Project structure

* 'SiteBuilder' directory
* 'Content' directory
    * 'Content/hierarchy.json' hierarchy file
    * Directories for each subsite
    * 'shared' directory for common pages in all subsites
* 'sitebuilder.json' configuration file in the root directory
* 'p' GET parameter
* Autoload: Namespace structure matches directory structure

### 2. Server system

* PHP sessions must be enabled

### 3. Client system

* Cookies must be enabled
* JavaScript must be enabled (for some modules)
* Target browsers: Latest versions of Firefox and Chrome

## Wiki

## License

The SiteBuilder framework is open-sourced software licensed under
the [GNU Lesser General Public License](LICENSE.md).
