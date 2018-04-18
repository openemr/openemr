# Explode Validator

`Zend\Validator\Explode` executes a validator for each item exploded from an
array.

## Supported options

The following options are supported for `Zend\Validator\Explode`:

- `valueDelimiter`: Defines the delimiter used to explode values from an array.
  It defaults to `,`. If the given value is an array, this option isn't used.
- `validator`: Sets the validator that will be executed on each exploded item.
  This may be a validator instance, or a validator service name.

## Basic usage

To validate if every item in an array is in a specified haystack:

```php
$inArrayValidator = new Zend\Validator\InArray([
    'haystack' => [1, 2, 3, 4, 5, 6],
]);

$explodeValidator = new Zend\Validator\Explode([
    'validator' => $inArrayValidator
]);

$explodeValidator->isValid([1, 4, 6]);    // returns true
$explodeValidator->isValid([1, 4, 6, 8]); // returns false
```

## Exploding strings

To validate if every e-mail in a string is contained in a list of names:

```php
$inEmailListValidator = new Zend\Validator\InArray([
    'haystack' => ['joseph@test.com', 'mark@test.com', 'lucia@test.com'],
]);

$explodeValidator = new Zend\Validator\Explode([
    'validator' => $inEmailListValidator,
    'valueDelimiter' => ','
]);

$explodeValidator->isValid('joseph@test.com,mark@test.com'); // returns true
$explodeValidator->isValid('lucia@test.com,maria@test.com');  // returns false
```
