# Sitemap Validators

The following validators conform to the
[Sitemap XML protocol](http://www.sitemaps.org/protocol.php).

## Supported options

There are no additional supported options for any of the `Sitemap` validators.

## Changefreq

`Zend\Validator\Sitemap\Changefreq` validates whether a string is valid for
using as a 'changefreq' element in a Sitemap XML document. Valid values are:
'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', or 'never'.

Returns `true` if and only if the value is a string and is equal to one of the
frequencies specified above.

## Sitemap\\Lastmod

`Zend\Validator\Sitemap\Lastmod` validates whether a string is valid for using
as a 'lastmod' element in a Sitemap XML document. The lastmod element should
contain a W3C date string, optionally discarding information about time.

Returns `true` if and only if the given value is a string and is valid according
to the protocol.

```php
$validator = new Zend\Validator\Sitemap\Lastmod();

$validator->isValid('1999-11-11T22:23:52-02:00'); // true
$validator->isValid('2008-05-12T00:42:52+02:00'); // true
$validator->isValid('1999-11-11'); // true
$validator->isValid('2008-05-12'); // true

$validator->isValid('1999-11-11t22:23:52-02:00'); // false
$validator->isValid('2008-05-12T00:42:60+02:00'); // false
$validator->isValid('1999-13-11'); // false
$validator->isValid('2008-05-32'); // false
$validator->isValid('yesterday'); // false
```

## Loc

`Zend\Validator\Sitemap\Loc` validates whether a string is valid for using as a
'loc' element in a Sitemap XML document. This uses
[Zend\\Uri\\Uri::isValid()](https://zendframework.github.io/zend-uri/usage/#validating-the-uri)
internally.

## Priority

`Zend\Validator\Sitemap\Priority` validates whether a value is valid for using
as a 'priority' element in a Sitemap XML document. The value should be a decimal
between 0.0 and 1.0. This validator accepts both numeric values and string
values.

```php
$validator = new Zend\Validator\Sitemap\Priority();

$validator->isValid('0.1'); // true
$validator->isValid('0.789'); // true
$validator->isValid(0.8); // true
$validator->isValid(1.0); // true

$validator->isValid('1.1'); // false
$validator->isValid('-0.4'); // false
$validator->isValid(1.00001); // false
$validator->isValid(0xFF); // false
$validator->isValid('foo'); // false
```
