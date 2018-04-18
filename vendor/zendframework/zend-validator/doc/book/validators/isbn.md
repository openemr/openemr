# Isbn Validator

`Zend\Validator\Isbn` allows you to validate an ISBN-10 or ISBN-13 value.

## Supported options

The following options are supported for `Zend\Validator\Isbn`:

- `separator`: Defines the allowed separator for the ISBN number. It defaults to
  an empty string.
- `type`: Defines the allowed ISBN types. It defaults to
  `Zend\Validator\Isbn::AUTO`. For details, take a look at the section on
  [explicit types](#setting-an-explicit-isbn-validation-type).

## Basic usage

A basic example of usage is below:

```php
$validator = new Zend\Validator\Isbn();

if ($validator->isValid($isbn)) {
    // isbn is valid
} else {
    // isbn is not valid
}
```

This will validate any ISBN-10 and ISBN-13 without separator.

## Setting an explicit ISBN validation type

An example of an ISBN type restriction follows:

```php
use Zend\Validator\Isbn;

$validator = new Isbn();
$validator->setType(Isbn::ISBN13);

// OR
$validator = new Isbn([ 'type' => Isbn::ISBN13]);

if ($validator->isValid($isbn)) {
    // this is a valid ISBN-13 value
} else {
    // this is an invalid ISBN-13 value
}
```

The above will validate only ISBN-13 values.

Valid types include:

-   `Zend\Validator\Isbn::AUTO` (default)
-   `Zend\Validator\Isbn::ISBN10`
-   `Zend\Validator\Isbn::ISBN13`

## Specifying a separator restriction

An example of separator restriction:

```php
$validator = new Zend\Validator\Isbn();
$validator->setSeparator('-');

// OR
$validator = new Zend\Validator\Isbn(['separator' => '-']);

if ($validator->isValid($isbn)) {
    // this is a valid ISBN with separator
} else {
    // this is an invalid ISBN with separator
}
```

> ### Values without separators
>
> This will return `false` if `$isbn` doesn't contain a separator **or** if it's
> an invalid *ISBN* value.

Valid separators include:

- `` (empty) (default)
- `-` (hyphen)
- ` ` (space)
