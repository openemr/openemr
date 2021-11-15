<h1 align="center">
    <a href="http://plugins.krajee.com" title="Krajee Plugins" target="_blank">
        <img src="http://kartik-v.github.io/bootstrap-fileinput-samples/samples/krajee-logo-b.png" alt="Krajee Logo"/>
    </a>
    <br>
    php-date-formatter
    <hr>
    <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DTP3NZQ6G2AYU"
       title="Donate via Paypal" target="_blank">
        <img src="http://kartik-v.github.io/bootstrap-fileinput-samples/samples/donate.png" alt="Donate"/>
    </a>
</h1>

[![Bower version](https://badge.fury.io/bo/php-date-formatter.svg)](http://badge.fury.io/bo/php-date-formatter)
[![Stable Version](https://poser.pugx.org/kartik-v/php-date-formatter/v/stable)](https://packagist.org/packages/kartik-v/php-date-formatter)
[![Unstable Version](https://poser.pugx.org/kartik-v/php-date-formatter/v/unstable)](https://packagist.org/packages/kartik-v/php-date-formatter)
[![License](https://poser.pugx.org/kartik-v/php-date-formatter/license)](https://packagist.org/packages/kartik-v/php-date-formatter)
[![Packagist Downloads](https://poser.pugx.org/kartik-v/php-date-formatter/downloads)](https://packagist.org/packages/kartik-v/php-date-formatter)
[![Monthly Downloads](https://poser.pugx.org/kartik-v/php-date-formatter/d/monthly)](https://packagist.org/packages/kartik-v/php-date-formatter)

A Javascript datetime library that allows you to manipulate date/times using PHP date-time formats in javascript. This library was built with an intention 
to read and write date/timestamps to the database easily when working with PHP server code. Use cases for this library would involve reading and saving a 
timestamp to database in one format, but displaying it on client or html forms in another format. Maintaining a consistent PHP Date time format for both 
server side and client side validation should help in building extensible applications with various PHP frameworks easily.

This library is a standalone javascript library and does not depend on other libraries or plugins like jQuery.

The latest release of the library is v1.3.6. Check the [CHANGE LOG](https://github.com/kartik-v/php-date-formatter/blob/master/CHANGE.md) for details.

## Features

- Parse date/time strings or a Date object, and convert it into Javascript Date Object by passing any of the [PHP DateTime formats](http://php.net/manual/en/function.date.php).
- Automatically guess date/time strings, even if it does not exactly match the format, and convert it into Javascript Date Object.
- Read date/time strings or a Date object, and format it as per a [PHP DateTime format](http://php.net/manual/en/function.date.php).
- With release v1.3.2 the library has been converted to use pure javacript code without dependency on jQuery or other third party JS library.

## Documentation and Demo

View the [library documentation](http://plugins.krajee.com/php-date-formatter) and
[library demos](http://plugins.krajee.com/php-date-formatter/demo) at Krajee JQuery plugins.

## Installation

### Using Bower
You can use the `bower` package manager to install. Run:

    bower install php-date-formatter

### Using Composer
You can use the `composer` package manager to install. Either run:

    $ php composer.phar require kartik-v/php-date-formatter "@dev"

or add:

    "kartik-v/php-date-formatter": "@dev"

to your composer.json file

### Manual Install

You can also manually install the plugin easily to your project. Just download the source
[ZIP](https://github.com/kartik-v/php-date-formatter/zipball/master) or
[TAR ball](https://github.com/kartik-v/php-date-formatter/tarball/master) and extract the
plugin assets (css and js folders) into your project.

## Usage

**Step 1** Load the following assets in your header.

```html
<script src="path/to/js/php-date-formatter.min.js" type="text/javascript"></script>
```

**Step 2** You can now access the library using the `DateFormatter` object. For example, you can convert any date string to javascript date object for a specific PHP date format.

```js
var fmt = new DateFormatter();
var date1 = fmt.parseDate('23-Sep-2013 09:24:12', 'd-M-Y H:i:s');
var date2 = fmt.formatDate(date1, 'd-F-Y h:i:s A');
```

## License

**php-date-formatter** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.