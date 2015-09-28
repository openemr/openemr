DOMPDFModule
============

Master: [![Build Status](https://secure.travis-ci.org/raykolbe/DOMPDFModule.png?branch=master)](http://travis-ci.org/raykolbe/DOMPDFModule)

The DOMPDF module integrates the DOMPDF library with Zend Framework 2 with minimal
effort on the consumer's end.

## Requirements
  - [Zend Framework 2](http://www.github.com/zendframework/zf2)

## Installation
Installation of DOMPDFModule uses PHP Composer. For more information about
PHP Composer, please visit the official [PHP Composer site](http://getcomposer.org/).

#### Installation steps

  1. `cd my/project/directory`
  2. create a `composer.json` file with following contents:

     ```json
     {
         "require": {
             "dino/dompdf-module": "dev-master"
         }
     }
     ```
  3. install PHP Composer via `curl -s http://getcomposer.org/installer | php` (on windows, download
     http://getcomposer.org/installer and execute it with PHP)
  4. run `php composer.phar install`
  5. open `my/project/directory/config/application.config.php` and add the following key to your `modules`: 

     ```php
     'DOMPDFModule',
     ```
#### Configuration options
You can override options via the `dompdf_module` key in your local or global config files. See DOMPDFModule/config/module.config.php for config options.

## Usage

```php
<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use DOMPDFModule\View\Model\PdfModel;

class ReportController extends AbstractActionController
{
    public function monthlyReportPdfAction()
    {
        $pdf = new PdfModel();
        $pdf->setOption('filename', 'monthly-report'); // Triggers PDF download, automatically appends ".pdf"
        $pdf->setOption('paperSize', 'a4'); // Defaults to "8x11"
        $pdf->setOption('paperOrientation', 'landscape'); // Defaults to "portrait"
        
        // To set view variables
        $pdf->setVariables(array(
          'message' => 'Hello'
        ));
        
        return $pdf;
    }
}
```

## To-do
  - Add command line support.
