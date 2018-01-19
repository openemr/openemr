# ROS PHP Pdf creation class

[![Latest Stable Version](https://poser.pugx.org/rospdf/pdf-php/v/stable)](https://packagist.org/packages/rospdf/pdf-php) [![Total Downloads](https://poser.pugx.org/rospdf/pdf-php/downloads)](https://packagist.org/packages/rospdf/pdf-php) [![Latest Unstable Version](https://poser.pugx.org/rospdf/pdf-php/v/unstable)](https://packagist.org/packages/rospdf/pdf-php) [![License](https://poser.pugx.org/rospdf/pdf-php/license)](https://packagist.org/packages/rospdf/pdf-php) [![Build Status](https://travis-ci.org/rospdf/pdf-php.svg?branch=master)](https://travis-ci.org/rospdf/pdf-php) 

![ros.jpg](https://raw.githubusercontent.com/rospdf/pdf-php/master/ros.jpg "R&OS PHP Pdf creation class")

This is the offical GIT clone from the R&OS PHP Pdf class previously stored on [sourceforge.net/projects/pdf-php](https://sourceforge.net/projects/pdf-php/). Development will take place here now.

The R&OS Pdf class is used to generate PDF Documents using PHP without installing any additional modules or extensions
It comes with a base class called "Cpdf.php" plus a helper class "Cezpdf.php" to generate tables, add backgrounds and provide paging.

<div align="center"> <a href="https://github.com/rospdf/pdf-php/blob/master/readme.pdf">DOCUMENTATION</a> : <a href="http://pdf-php.sf.net/">WEBSITE</a></div>

## Features
- Quick and easy to use
- Support for extension classes
- Unicode and ANSI formated text
- Custom TTF fonts and font subsetting (version >= 0.11.8)
- Auto page and line breaks
- Text alignments (left, right, center, justified)
- Linked XObjects
- Internal and external links
- Compression by using gzcompress
- Encryption 40bit, 128bit since PDF 1.4
- Image support for JPEG, PNG and GIF (partly)
- Template support

## Installation

### Manual Download

Open the [RELEASE](https://github.com/rospdf/pdf-php/releases) page and pick the latest version to download.

Extract the archive into your project directory

### Clone via git

You can also use git to install it using:

    git clone https://github.com/rospdf/pdf-php.git
    git checkout <latest-version>
	

### Installation via composer

This library is also available on the dependecy manager `composer` - https://packagist.org/packages/rospdf/pdf-php

Please follow the steps [here](https://getcomposer.org/download/) to install. Once this is done one simple command will get all dependencies for the package `rospdf/pdf-php`

	./composer.phar require rospdf/pdf-php

For more details on how to use `composer` please refer to the documentation - https://getcomposer.org/doc/

### Example

```php
<?php

include 'src/Cezpdf.php'; // Or use 'vendor/autoload.php' when installed through composer

// Initialize a ROS PDF class object using DIN-A4, with background color gray
$pdf = new Cezpdf('a4','portrait','color',array(0.8,0.8,0.8));
// Set pdf Bleedbox
$pdf->ezSetMargins(20,20,20,20);
// Use one of the pdf core fonts
$mainFont = 'Times-Roman';
// Select the font
$pdf->selectFont($mainFont);
// Define the font size
$size=12;
// Modified to use the local file if it can
$pdf->openHere('Fit');

// Output some colored text by using text directives and justify it to the right of the document
$pdf->ezText("PDF with some <c:color:1,0,0>blue</c:color> <c:color:0,1,0>red</c:color> and <c:color:0,0,1>green</c:color> colours", $size, array('justification'=>'right'));
// Output the pdf as stream, but uncompress
$pdf->ezStream(array('compress'=>0));
?>
```

## Contributors

[ole1986](http://github.com/ole1986) is lead developer. 

See the full list of [contributors](https://github.com/rospdf/pdf-php/graphs/contributors).
