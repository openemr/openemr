# Between Validator

`Zend\Validator\Between` allows you to validate if a given value is between two
other values.

> ### Only supports number validation
>
> `Zend\Validator\Between` supports only the validation of numbers. Strings or
> dates can not be validated with this validator.

## Supported options

The following options are supported for `Zend\Validator\Between`:

- `inclusive`: Defines if the validation is inclusive of the minimum and maximum
  border values, or exclusive. It defaults to `true`.
- `max`: Sets the maximum border for the validation.
- `min`: Sets the minimum border for the validation.

## Default behaviour

Per default, this validator checks if a value is between `min` and `max` where
both border values are allowed as value.

```php
$valid  = new Zend\Validator\Between(['min' => 0, 'max' => 10]);
$value  = 10;
$result = $valid->isValid($value);
// returns true
```

In the above example, the result is `true` due to the reason that the default
search is inclusive of the border values. This means in our case that any value
from '0' to '10' is allowed; values like '-1' and '11' will return `false`.

## Excluding border values

Sometimes it is useful to validate a value by excluding the border values. See
the following example:

```php
$valid  = new Zend\Validator\Between([
    'min' => 0,
    'max' => 10,
    'inclusive' => false,
]);
$value  = 10;
$result = $valid->isValid($value);
// returns false
```

The example above is almost identical to our first example, but we now exclue
the border values; as such, the values '0' and '10' are no longer allowed and
will return `false`.
