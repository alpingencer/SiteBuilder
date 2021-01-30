<p align="center">
    <img alt="The SiteBuilder Logo" src="SiteBuilder.png">
    <h1 align="center">SiteBuilder</h1>
</p>

## About SiteBuilder

SiteBuilder is a page management and HTML auto-generation framework for PHP written by Alpin Gencer.
Currently, the framework is going through some major changes, including adding Composer support,
which is why the "master" branch is empty. It is recommended to wait for SiteBuilder v3.0.0 Lithium
to be released. However, if you insist on getting started early, check out the installation guide
below.

## Installation

Currently, there are two major versions of SiteBuilder meant for use: v2.0 Helium and v3.0 Lithium.
As v3.0 is the first version of SiteBuilder to add Composer support, v2.0 only has a manual
installation process.

### Installing SiteBuilder v2.0 Helium

In order to use v2.0, clone the "v2" branch under the "SiteBuilder" directory in your server
document root. Afterwards, you can include the file "SiteBuilder/SiteBuilder.inc" and get started.

### Installing SiteBuilder v3.0 Lithium

As SiteBuilder v3.0 Lithium adds Composer support, all you need to do is require the "v3.x-dev"
branch in the package "sitebuilder/sitebuilder". Composer will automatically fetch the repository
from Packagist for you and copy it to the "vendor" directory. After including the Composer
autoloader script, you're all set!

## Wiki

As it is still very early days for SiteBuilder, the wiki has not been set up yet.

## License

The SiteBuilder framework is open-sourced software licensed under
the [GNU Lesser General Public License](LICENSE.md).
