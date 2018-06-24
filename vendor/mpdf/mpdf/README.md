mPDF is a PHP library which generates PDF files from UTF-8 encoded HTML.

It is based on [FPDF](http://www.fpdf.org/) and [HTML2FPDF](http://html2fpdf.sourceforge.net/)
(see [CREDITS](CREDITS.txt)), with a number of enhancements. mPDF was written by Ian Back and is released
under the [GNU GPL v2 licence](LICENSE.txt).

[![Build Status](https://travis-ci.org/mpdf/mpdf.svg?branch=development)](https://travis-ci.org/mpdf/mpdf)

> Note: If you are viewing this file on mPDF Github repository homepage or on Packagist, please note that
> the default repository branch is `development` which can differ from the last stable release.

Requirements
============

**mPDF 7.0** requires PHP `^5.6 || ~7.0.0 || ~7.1.0 || ~7.2.0`. PHP `mbstring` and `gd` extensions have to be loaded.

Additional extensions may be required for some advanced features such as `zlib` for compression of output and
embedded resources such as fonts, `bcmath` for generating barcodes or `xml` for character set conversion
and SVG handling.

mPDF has some problems with fetching external HTTP resources with single threaded servers such as `php -S`. A proper
server such as nginx (php-fpm) or Apache is recommended.

Support us
==========

Consider supporting development of mPDF with a donation of any value. [Donation button][1] can be found on the
[main page of the documentation][1].

Installation
============

Official installation method is via composer and its packagist package [mpdf/mpdf](https://packagist.org/packages/mpdf/mpdf).

```
$ composer require mpdf/mpdf
```

Usage
=====

The simplest usage (since version 7.0) of the library would be as follows:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML('<h1>Hello world!</h1>');
$mpdf->Output();

```

This will output the PDF inline to the browser as `application/pdf` Content-type.

Setup & Configuration
=====================

All [configuration directives](https://mpdf.github.io/reference/mpdf-variables/overview.html) can
be set by the `$config` parameter of the constructor.

It is recommended to set one's own temporary directory via `tempDir` configuration variable.
The directory must have write permissions (mode `775` is recommended) for users using mPDF
(typically `cli`, `webserver`, `fpm`).

**Warning:** mPDF will clean up old temporary files in the temporary directory. Choose a path dedicated to mPDF only.


```php
<?php

$mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp']);

```

By default, the temporary directory will be inside vendor directory and will have correct permissions from
`post_install` composer script.

For more information about custom temporary directory see the note on
[Folder for temporary files](https://mpdf.github.io/installation-setup/folders-for-temporary-files.html)
in the section on Installation & Setup in the [manual][1].

If you have problems, please read the section on
[troubleshooting](https://mpdf.github.io/troubleshooting/known-issues.html) in the manual.

Online manual
=============

Online manual is available at https://mpdf.github.io/.

For general questions or troubleshooting please use the [mpdf tag](https://stackoverflow.com/questions/tagged/mpdf) at Stack Overflow (and not the project's issue tracker).

Contributing
============

Please read before submitting issues and pull requests the [CONTRIBUTING.md](https://github.com/mpdf/mpdf/blob/development/.github/CONTRIBUTING.md) file.

Unit Testing
============

Unit testing for mPDF is done using [PHPUnit](https://phpunit.de/).

To get started, run `composer install` from the command line while in the mPDF root directory
(you'll need [composer installed first](https://getcomposer.org/download/)).

To execute tests, run `vendor/bin/phpunit` from the command line while in the mPDF root directory.

Any assistance writing unit tests for mPDF is greatly appreciated. If you'd like to help, please
note that any PHP file located in the `/tests/` directory will be autoloaded when unit testing.

[1]: https://mpdf.github.io
