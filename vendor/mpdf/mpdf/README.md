mPDF is a PHP class which generates PDF files from UTF-8 encoded HTML. It is based on [FPDF](http://www.fpdf.org/)
and [HTML2FPDF](http://html2fpdf.sourceforge.net/) (see [CREDITS](CREDITS.txt)), with a number of enhancements.
mPDF was written by Ian Back and is released under the [GNU GPL v2 licence](LICENSE.txt).

[![Build Status](https://travis-ci.org/mpdf/mpdf.svg?branch=development)](https://travis-ci.org/mpdf/mpdf)

Installation
============

Preferred installation method is via composer and its packagist package [mpdf/mpdf](https://packagist.org/packages/mpdf/mpdf).

Manual installation
-------------------

   * Download the [.zip release file](https://github.com/mpdf/mpdf/releases) and unzip it
   * Create a folder e.g. /mpdf on your server
   * Upload all of the files to the server, maintaining the folders as they are
   * Ensure that you have write permissions set (CHMOD 6xx or 7xx) for the following folders:

     /ttfontdata/ - used to cache font data; improves performance a lot

     /tmp/ - used for some images and ProgressBar

     /graph_cache/ - if you are using [JpGraph](http://jpgraph.net) in conjunction with mPDF

To test the installation, point your browser to the basic example file:

    [path_to_mpdf_folder]/mpdf/examples/example01_basic.php

If you wish to define a different folder for temporary files rather than /tmp/ see the note on
[Folder for temporary files](https://mpdf.github.io/installation-setup/folders-for-temporary-files.html)
in the section on Installation & Setup in the [manual](https://mpdf.github.io/).

If you have problems, please read the section on [troubleshooting](https://mpdf.github.io/troubleshooting/known-issues.html) in the manual.

Online manual
=============

Online manual is available at https://mpdf.github.io/.

Unit Testing
============

Unit testing for mPDF is done using [PHPUnit](https://phpunit.de/).

To get started, run `composer install` from the command line while in the mPDF root directory
(you'll need [composer installed first](https://getcomposer.org/download/)).

To execute tests, run `vendor/bin/phpunit` from the command line while in the mPDF root directory.

Any assistance writing unit tests for mPDF is greatly appreciated. If you'd like to help, please
note that any PHP file located in the `/tests/` directory will be autoloaded when unit testing.
