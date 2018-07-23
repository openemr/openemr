# Snappy

[![Build Status](https://travis-ci.org/KnpLabs/snappy.svg?branch=master)](https://travis-ci.org/KnpLabs/snappy)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/KnpLabs/Gaufrette/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/KnpLabs/Gaufrette/?branch=master)
[![StyleCI](https://styleci.io/repos/723980/shield?branch=master)](https://styleci.io/repos/723980)

Snappy is a PHP library allowing thumbnail, snapshot or PDF generation from a url or a html page.
It uses the excellent webkit-based [wkhtmltopdf and wkhtmltoimage](http://wkhtmltopdf.org/)
available on OSX, linux, windows.

You will have to download wkhtmltopdf `0.12.x` in order to use Snappy.

Please, check [FAQ](doc/faq.md) before opening a new issue. Snappy is a tiny wrapper around wkhtmltox, so lots of issues are already answered, resolved or wkhtmltox ones.

[![Build Status](https://secure.travis-ci.org/KnpLabs/snappy.png?branch=master)](http://travis-ci.org/KnpLabs/snappy)

Following integrations are available:
* [`knplabs/knp-snappy-bundle`](https://github.com/KnpLabs/KnpSnappyBundle), for Symfony
* [`barryvdh/laravel-snappy`](https://github.com/barryvdh/laravel-snappy), for Laravel

## Current maintainer(s)

* [NiR-](https://github.com/NiR-)

## Installation using [Composer](http://getcomposer.org/)

```bash
$ composer require knplabs/knp-snappy
```

## Usage

### Initialization
```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Knp\Snappy\Pdf;

$snappy = new Pdf('/usr/local/bin/wkhtmltopdf');

// or you can do it in two steps
$snappy = new Pdf();
$snappy->setBinary('/usr/local/bin/wkhtmltopdf');
```

### Display the pdf in the browser

```php
$snappy = new Pdf('/usr/local/bin/wkhtmltopdf');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="file.pdf"');
echo $snappy->getOutput('http://www.github.com');
```

### Merge multiple urls into one pdf
```php
$snappy = new Pdf('/usr/local/bin/wkhtmltopdf');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="file.pdf"');
echo $snappy->getOutput(array('http://www.github.com','http://www.knplabs.com','http://www.php.net'));
```

### Generate local pdf file 
```php
$snappy = new Pdf('/usr/local/bin/wkhtmltopdf');
$snappy->generateFromHtml('<h1>Bill</h1><p>You owe me money, dude.</p>', '/tmp/bill-123.pdf');
```

### Pass options to snappy
```php
// Type wkhtmltopdf -H to see the list of options
$snappy = new Pdf('/usr/local/bin/wkhtmltopdf');
$snappy->setOption('disable-javascript', true);
$snappy->setOption('no-background', true);
$snappy->setOption('allow', array('/path1', '/path2'));
$snappy->setOption('cookie', array('key' => 'value', 'key2' => 'value2'));
$snappy->setOption('post', array('key' => 'value'));
$snappy->setOption('cover', 'pathToCover.html');
// .. or pass a cover as html
$snappy->setOption('cover', '<h1>Bill cover</h1>');
$snappy->setOption('toc', true);
$snappy->setOption('cache-dir', '/path/to/cache/dir');
```

## wkhtmltopdf binary as composer dependencies

If you want to download wkhtmltopdf and wkhtmltoimage with composer you add to `composer.json`:

```bash
$ composer require h4cc/wkhtmltopdf-i386 0.12.x
$ composer require h4cc/wkhtmltoimage-i386 0.12.x
```

or this if you are in 64 bit based system:

```bash
$ composer require h4cc/wkhtmltopdf-amd64 0.12.x
$ composer require h4cc/wkhtmltoimage-amd64 0.12.x
```

And then you can use it

```php
<?php

use Knp\Snappy\Pdf;

$myProjectDirectory = '/path/to/my/project';

$snappy = new Pdf($myProjectDirectory . '/vendor/h4cc/wkhtmltopdf-i386/bin/wkhtmltopdf-i386');

// or

$snappy = new Pdf($myProjectDirectory . '/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64');
```

*N.B.* These static binaries are extracted from  [Debian7 packages](https://github.com/h4cc/wkhtmltopdf-amd64/issues/13#issuecomment-150948179), so it might not be compatible with non-debian based linux distros 
## Some use cases

If you want to generate table of contents and you want to use custom XSL stylesheet, do the following:

```php
<?php
$snappy = new Pdf('/path/to/binary');

$snappy->setOption('toc', true);
$snappy->setOption('xsl-style-sheet', 'http://path/to/stylesheet.xsl') //or local file;

$snappy->generateFromHtml('<p>Some content</p>', 'test.pdf');
```

## Bugs & Support

If you found a bug please fill a detailed issue with all the following points.  
If you need some help, please at least provide a complete reproducer so we could help you based on facts rather than assumptions.

* OS and its version
* Wkhtmltopdf, its version and how you installed it
* A complete reproducer with relevant php and html/css/js code

If your reproducer is big, please try to shrink it. It will help everyone to narrow the bug.

## Credits

Snappy has been originally developed by the [KnpLabs](http://knplabs.com) team.
