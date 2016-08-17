FPDI - Free PDF Document Importer
=================================

[![Latest Stable Version](https://poser.pugx.org/setasign/fpdi/v/stable.svg)](https://packagist.org/packages/setasign/fpdi) [![Total Downloads](https://poser.pugx.org/setasign/fpdi/downloads.svg)](https://packagist.org/packages/setasign/fpdi) [![Latest Unstable Version](https://poser.pugx.org/setasign/fpdi/v/unstable.svg)](https://packagist.org/packages/setasign/fpdi) [![License](https://poser.pugx.org/setasign/fpdi/license.svg)](https://packagist.org/packages/setasign/fpdi)

A clone of [FPDI](https://www.setasign.com/fpdi) for GitHub/[Composer](https://packagist.org/packages/setasign/fpdi).

FPDI is a collection of PHP classes facilitating developers to read pages from existing PDF documents and use them as templates in FPDF, which was developed by Olivier Plathey. Apart from a copy of FPDF, FPDI does not require any special PHP extensions.

## Installation with [Composer](https://packagist.org/packages/setasign/fpdi)

FPDI is an add-on for [FPDF](http://fpdf.org/). Additionally FPDI can be used with [TCPDF](http://www.tcpdf.org/).
For completion we added a [FPDF repository](https://github.com/Setasign/FPDF) which simply clones the offical releases.

This package comes without any dependency configuration in the composer.json file. It's up to you to load the desired package as described below.

A basic installation via Composer could be done this way:

```bash
$ composer require setasign/fpdi:1.6.1
```

or you can include the following in your composer.json file:

```json
{
    "require": {
        "setasign/fpdi": "1.6.1"
    }
}
```

### Evaluate Dependencies Automatically

To load dependencies automatically we prepared kind of metadata packages. To use FPDI with FPDF use [this](https://github.com/Setasign/FPDI-FPDF) package:

```json
{
    "require": {
        "setasign/fpdi-fpdf": "1.6.1"
    }
}
```

For TCPDF use [this](https://github.com/Setasign/FPDI-TCPDF):

```json
{
    "require": {
        "setasign/fpdi-tcpdf": "1.6.1"
    }
}
```

### Manual Dependencies

To support both FPDF and TCPDF its up to you to load the preferred package before the classes of FPDI are loaded. By default FPDI will extend FPDF. If the TCPDF class exists, a new FPDF class will be created which will extend TCPDF while FPDI will extend this.

To use FPDI with FPDF include following in your composer.json file:

```json
{
    "require": {
        "setasign/fpdf": "1.8",
        "setasign/fpdi": "1.6.1"
    }
}
```

If you are using TCPDF, your have to update your composer.json respectively to:

```json
{
    "require": {
        "tecnickcom/tcpdf": "6.2.12",
        "setasign/fpdi": "1.6.1"
    }
}
```

Additionally you have to trigger composers autoloader for the TCPDF class before you are initiating FPDI:

```php
class_exists('TCPDF', true); // trigger Composers autoloader to load the TCPDF class
$pdf = new FPDI();
```

