# NotEmpty Validator

This validator allows you to validate if a given value is not empty. This is
often useful when working with form elements or other user input, where you can
use it to ensure required elements have values associated with them.

## Supported options

The following options are supported for `Zend\Validator\NotEmpty`:

- `type`: Sets the type of validation which will be processed; for details, see
  the section on [specifying empty behavior](#specifying-empty-behavior).

## Default behaviour

By default, this validator works differently than you would expect when you've
worked with PHP's `empty()` operator. In particular, this validator will
evaluate both the integer `0` and string `'0'` as empty.

```php
$valid = new Zend\Validator\NotEmpty();
$value  = '';
$result = $valid->isValid($value);
// returns false
```

## Specifying empty behavior

Some projects have differing opinions of what is considered an "empty" value: a
string with only whitespace might be considered empty, or `0` may be
considered non-empty (particularly for boolean sequences). To accommodate
differing needs, `Zend\Validator\NotEmpty` allows you to configure which types
should be validated as empty and which not.

The following types can be handled:

- `boolean`: Returns `false` when the boolean value is `false`.
- `integer`: Returns `false` when an integer `0` value is given. By default,
  this validation is not activate and returns `true` for any integer values.
- `float`: Returns `false` when a float `0.0` value is given. By default, this
  validation is not activate and returns `true` on any float values.
- `string`: Returns `false` when an empty string `''` is given.
- `zero`: Returns `false` when the single character zero (`'0'`) is given.
- `empty_array`: Returns `false` when an empty `array` is given.
- `null`: Returns `false` when a `null` value is given.
- `php`: Returns `false` on wherever PHP's `empty()` would return `true`.
- `space`: Returns `false` when an string is given which contains only
  whitespace.
- `object`: Returns `true`. `false` will be returned when `object` is not
  allowed but an object is given.
- `object_string`: Returns `false` when an object is given and its
  `__toString()` method returns an empty string.
- `object_count`: Returns `false` when an object is given, it implements
  `Countable`, and its count is 0.
- `all`: Returns `false` on all above types.

All other given values will return `true` per default.

There are several ways to select which of the above types are validated. You can
give one or multiple types and add them, you can provide an array, you can use
constants, or you can provide a textual string. See the following examples:

```php
use Zend\Validator\NotEmpty;

// Returns false on 0
$validator = new NotEmpty(NotEmpty::INTEGER);

// Returns false on 0 or '0'
$validator = new NotEmpty( NotEmpty::INTEGER | NotEmpty::ZERO);

// Returns false on 0 or '0'
$validator = new NotEmpty([ NotEmpty::INTEGER, NotEmpty::ZERO ]);

// Returns false on 0 or '0'
$validator = new NotEmpty(['integer', 'zero']);
```

You can also provide an instance of `Traversable` to set the desired types. To
set types after instantiation, use the `setType()` method.
