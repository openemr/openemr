# GreaterThan Validator

`Zend\Validator\GreaterThan` allows you to validate if a given value is greater
than a minimum border value.

> ### Only supports numbers
>
> `Zend\Validator\GreaterThan` supports only the validation of numbers. Strings
> or dates can not be validated with this validator.

## Supported options

The following options are supported for `Zend\Validator\GreaterThan`:

- `inclusive`: Defines if the validation is inclusive of the minimum value,
  or exclusive. It defaults to `false`.
- `min`: Sets the minimum allowed value.

## Basic usage

To validate if a given value is greater than a defined minimum:

```php
$valid  = new Zend\Validator\GreaterThan(['min' => 10]);
$value  = 8;
$return = $valid->isValid($value);
// returns false
```

The above example returns `true` for all values which are greater than 10.

## Inclusive validation

Sometimes it is useful to validate a value by including the minimum value.

```php
$valid  = new Zend\Validator\GreaterThan([
    'min' => 10,
    'inclusive' => true,
]);
$value  = 10;
$result = $valid->isValid($value);
// returns true
```

The example is identical to our first example, with the exception that we
included the minimum value. Now the value '10' is allowed and will return
`true`.
