<h1 align="center">SiteBuilder</h1>

## About SiteBuilder

SiteBuilder is a page management and HTML auto-generation framework for PHP written by Alpin Gencer.

## Installation

## Framework Rules and Restrictions

### 1. Project structure

* Server document root is exactly 1 directory under the application root
* 'sitebuilder.json' configuration file in the application root
* Directories:
    * 'public': Anything publicly accessible via an HTTP request
    * 'src': PHP scripts, custom classes, etc.
        * 'src/content': Webpage content files
          * One directory for each subsite
          * 'shared' directory for common pages in all subsites
        * 'src/hierarchy.json': The webpage hierarchy file
* 'p' GET parameter for defining the current webpage

### 2. Server system

* PHP sessions must be enabled
* SiteBuilder must be built via Composer

### 3. Client system

* Cookies must be enabled
* JavaScript must be enabled (for some modules)
* Target browsers: Latest versions of Firefox and Chrome

## Wiki

## License

The SiteBuilder framework is open-sourced software licensed under
the [GNU Lesser General Public License](LICENSE.md).
