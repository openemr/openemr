# Regex Validator

This validator allows you to validate if a given string conforms a defined
regular expression.

## Supported options

The following options are supported for `Zend\Validator\Regex`:

- `pattern`: Sets the regular expression pattern for this validator.

## Usage

Validation with regular expressions allows complex validations
without writing a custom validator.

```php
$validator = new Zend\Validator\Regex(['pattern' => '/^Test/']);

$validator->isValid("Test"); // returns true
$validator->isValid("Testing"); // returns true
$validator->isValid("Pest"); // returns false
```

The pattern uses the same syntax as `preg_match()`. For details about regular
expressions take a look into [PHP's manual about PCRE pattern
syntax](http://php.net/reference.pcre.pattern.syntax).

## Pattern handling

It is also possible to set a different pattern afterwards by using
`setPattern()` and to get the actual set pattern with `getPattern()`.

```php
$validator = new Zend\Validator\Regex(['pattern' => '/^Test/']);
$validator->setPattern('ing$/');

$validator->isValid("Test"); // returns false
$validator->isValid("Testing"); // returns true
$validator->isValid("Pest"); // returns false
```
