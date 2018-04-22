# LessThan Validator

`Zend\Validator\LessThan` allows you to validate if a given value is less than a
maximum value.

> Supports only number validation
>
> `Zend\Validator\LessThan` supports only the validation of numbers. Strings or
> dates can not be validated with this validator.

## Supported options

The following options are supported for `Zend\Validator\LessThan`:

- `inclusive`: Defines if the validation is inclusive the maximum value or
  exclusive. It defaults to `false`.
- `max`: Sets the maximum allowed value.

## Basic usage

To validate if a given value is less than a defined maximum:

```php
$valid  = new Zend\Validator\LessThan(['max' => 10]);
$value  = 12;
$return = $valid->isValid($value);
// returns false
```

The above example returns `true` for all values lower than 10.

## Inclusive validation

Sometimes it is useful to validate a value by including the maximum value:

```php
$valid  = new Zend\Validator\LessThan([
    'max' => 10,
    'inclusive' => true,
]);
$value  = 10;
$result = $valid->isValid($value);
// returns true
```

The example is identical to our first example, with the exception that we've
specified that the maximum is inclusive. Now the value '10' is allowed and will
return `true`.
