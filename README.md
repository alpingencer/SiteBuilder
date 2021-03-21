<p align="center">
    <img alt="The Eufony Logo" width="128" src="https://raw.githubusercontent.com/eufony/eufony/master/Eufony.svg">
</p>

Eufony is a page management and HTML auto-generation framework for PHP written by Alpin Gencer.
Currently, the framework is going through some major changes, including adding Composer support,
which is why the "master" branch is empty. It is recommended to wait for Eufony v3.0.0 Lithium to be
released. However, if you insist on getting started early, check out the installation guide below.

## Installation

Currently, there are two major versions of Eufony meant for use: v2.0 Helium and v3.0 Lithium. As
v3.0 is the first version of Eufony to add Composer support, v2.0 only has a manual installation
process.

### Installing Eufony v2.0 Helium

In order to use v2.0, clone the "v2" branch under the "SiteBuilder" directory in your server
document root. Please note that the name discrepancy comes from the name change that occurred during
the development of SiteBuilder v3.0. Afterwards, you can include the file
"SiteBuilder/SiteBuilder.inc" and get started.

### Installing Eufony v3.0 Lithium

As Eufony v3.0 Lithium adds Composer support, all you need to do is require the "v3.x-dev"
branch in the package "eufony/eufony". Composer will automatically fetch the repository from
Packagist for you and copy it to the "vendor" directory. After including the Composer autoloader
script, you're all set!

## Wiki

As it is still very early days for Eufony, the wiki has not been set up yet.

## License

The Eufony framework is open-sourced software licensed under
the [GNU Lesser General Public License](LICENSE.md).
