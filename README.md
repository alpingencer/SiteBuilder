<p align="center">
    <img alt="The SiteBuilder Logo" src="https://raw.githubusercontent.com/alpingencer/sitebuilder/v3/SiteBuilder.png">
</p>

SiteBuilder is a page management and HTML auto-generation framework for PHP written by Alpin Gencer.

## Installation

* SiteBuilder v3.0 Lithium comes with Composer support. You can find the Packagist
  page [here](https://packagist.org/packages/sitebuilder/sitebuilder).

## Framework Rules and Restrictions

### 1. Project structure

* Server document root is exactly 1 directory under the application root
* Directories:
    * 'public': Anything publicly accessible via an HTTP request
        * 'public/assets': Any CSS, JS, font, image, etc. assets that a page needs
            * 'public/assets/sitebuilder': A symlink to 'vendor/sitebuilder/sitebuilder/assets' for
              SiteBuilder's own asset dependencies
    * 'src': PHP scripts, custom classes, etc.
        * 'src/content': Webpage content files
            * One directory for each subsite
            * 'shared' directory for common pages in all subsites
        * 'src/content/hierarchy.json': The webpage hierarchy file
* 'p' GET parameter for defining the current webpage

### 2. Server system

* PHP sessions must be enabled
* SiteBuilder must be installed via Composer
* Server document root must be set correctly (see project structure requirements)
* The following php.ini settings must be set:
    * zend.assertions=1
    * assert.active=1
    * assert.exception=1

### 3. Client system

* Cookies must be enabled
* JavaScript must be enabled
* Target browsers: Latest versions of Firefox and Chrome

## Wiki

As it is still very early days for SiteBuilder, the wiki has not been set up yet.

## License

The SiteBuilder framework is open-sourced software licensed under
the [GNU Lesser General Public License](LICENSE.md).
