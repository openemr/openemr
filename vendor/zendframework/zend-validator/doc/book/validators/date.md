# Date Validator

`Zend\Validator\Date` allows you to validate if a given value contains a date.

## Supported options

The following options are supported for `Zend\Validator\Date`:

- `format`: Sets the format which is used to write the date.
- `locale`: Sets the locale which will be used to validate date values.

## Default date validation

The easiest way to validate a date is by using the default date format,
`Y-m-d`.

```php
$validator = new Zend\Validator\Date();

$validator->isValid('2000-10-10');   // returns true
$validator->isValid('10.10.2000'); // returns false
```

## Specifying a date format

`Zend\Validator\Date` also supports custom date formats. When you want to
validate such a date, use the `format` option. This option accepts any format
allowed by the PHP [DateTime::createFromFormat()](http://php.net/manual/en/datetime.createfromformat.php#refsect1-datetime.createfromformat-parameters) method.

```php
$validator = new Zend\Validator\Date(['format' => 'Y']);

$validator->isValid('2010'); // returns true
$validator->isValid('May');  // returns false
```
