silverstripe-autotoc
====================
[![License](https://poser.pugx.org/entidi/silverstripe-autotoc/license)](https://packagist.org/packages/entidi/silverstripe-autotoc)
[![Build Status](https://travis-ci.org/ntd/silverstripe-autotoc.svg?branch=master)](https://travis-ci.org/ntd/silverstripe-autotoc)
[![Code Quality](https://scrutinizer-ci.com/g/ntd/silverstripe-autotoc/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ntd/silverstripe-autotoc/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/entidi/silverstripe-autotoc/v/stable)](https://packagist.org/packages/entidi/silverstripe-autotoc)

Automatically generate the table of contents from the *Content* of a
page or, more generally, from any HTML field.

Installation
------------

If you use [composer](https://getcomposer.org/), you could just run the
following command:

    composer require entidi/silverstripe-autotoc

To manually install it you should unpack or copy `silverstripe-autotoc`
into your SilverStripe root directory, rename it to `autotoc` and do a
`?flush`.

This module can be used without the CMS.

Testing
-------

Part of this project (the _Tocifier_ class) is intentionally decoupled
from SilverStripe so it can be tested without pulling in all the
framework.

From the module root directory you can trigger the testing by calling
`phpunit` (that must be previously installed on your system):

    phpunit --bootstrap tests/Bootstrap.php tests/

Other documentation
-------------------

* [Usage](docs/en/usage.md)
* [AutoTOC format](docs/en/format.md)
* [Contributing](CONTRIBUTING.md)
* [BSD license](LICENSE.md)
* [ChangeLog](CHANGELOG.md)
* [Support](docs/en/support.md)
