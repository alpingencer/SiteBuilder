<p align="center">
    <img alt="The SiteBuilder Logo" src="SiteBuilder.png">
    <h1 align="center">SiteBuilder</h1>
</p>

## About SiteBuilder

SiteBuilder is a page management and HTML auto-generation framework for PHP written by Alpin Gencer.

## Installation

SiteBuilder v3.0 Lithium comes with Composer support. As such, installation is very quick and
simple. All you need to do is require the "v3.x-dev" branch in the package
"sitebuilder/sitebuilder". Composer will automatically fetch the repository from Packagist for you
and copy it to the "vendor" directory. After including the Composer autoloader script, you're all
set!

## Framework Rules and Restrictions

### 1. Project structure

* Server document root is exactly 1 directory under the application root
* 'sitebuilder.json' configuration file in the application root
* Directories:
    * 'public': Anything publicly accessible via an HTTP request
        * 'public/assets': Any CSS, JS, font, image, etc. assets that a page needs
            * 'public/assets/sitebuilder': A symlink to 'vendor/sitebuilder/sitebuilder/assets' for
              SiteBuilder's own asset dependencies
    * 'src': PHP scripts, custom classes, etc.
        * 'src/content': Webpage content files
            * One directory for each subsite
            * 'shared' directory for common pages in all subsites
        * 'src/hierarchy.json': The webpage hierarchy file
* 'p' GET parameter for defining the current webpage

### 2. Server system

* PHP sessions must be enabled
* SiteBuilder must be built via Composer
* Server document root must be set correctly (see project structure requirements)

### 3. Client system

* Cookies must be enabled
* JavaScript must be enabled (for some modules)
* Target browsers: Latest versions of Firefox and Chrome

## Wiki

As it is still very early days for SiteBuilder, the wiki has not been set up yet.

## License

The SiteBuilder framework is open-sourced software licensed under
the [GNU Lesser General Public License](LICENSE.md).
