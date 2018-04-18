# StringLength Validator

This validator allows you to validate if a given string is between a defined
length.

> Supports only string validation
>
> `Zend\Validator\StringLength` supports only the validation of strings.
> Integers, floats, dates or objects can not be validated with this validator.

## Supported options

The following options are supported for `Zend\Validator\StringLength`:

- `encoding`: Sets the `ICONV` encoding to use with the string.
- `min`: Sets the minimum allowed length for a string.
- `max`: Sets the maximum allowed length for a string.
- `length`: Holds the actual length of the string.

## Default behaviour

By default, this validator checks if a value is between `min` and `max` using a
default `min` value of `0` and default `max` value of `NULL` (meaning unlimited).

As such, without any options, the validator only checks that the input is a
string.

## Limiting the maximum string length

To limit the maximum allowed length of a string you need to set the `max`
property. It accepts an integer value as input.

```php
$validator = new Zend\Validator\StringLength(['max' => 6]);

$validator->isValid("Test"); // returns true
$validator->isValid("Testing"); // returns false
```

You can set the maximum allowed length after instantiation by using the
`setMax()` method; `getMax()` retrieves the value.

```php
$validator = new Zend\Validator\StringLength();
$validator->setMax(6);

$validator->isValid("Test"); // returns true
$validator->isValid("Testing"); // returns false
```

## Limiting the minimum string length

To limit the minimal required string length, set the `min`
property using an integer value:

```php
$validator = new Zend\Validator\StringLength(['min' => 5]);

$validator->isValid("Test"); // returns false
$validator->isValid("Testing"); // returns true
```

You can set the value after instantiation using the `setMin()`
method; `getMin()` retrieves the value.

```php
$validator = new Zend\Validator\StringLength();
$validator->setMin(5);

$validator->isValid("Test"); // returns false
$validator->isValid("Testing"); // returns true
```

## Limiting both minimum and maximum string length

Sometimes you will need to set both a minimum and a maximum string length;
as an example, in a username input, you may want to limit the name to a maximum
of 30 characters, but require at least three charcters:

```php
$validator = new Zend\Validator\StringLength(['min' => 3, 'max' => 30]);

$validator->isValid("."); // returns false
$validator->isValid("Test"); // returns true
$validator->isValid("Testing"); // returns true
```

> ### Setting a maximum lower  than the minimum
>
> When you try to set a lower maximum value than the specified minimum value, or
> a higher minimum value as the actual maximum value, the validator will raise
> an exception.

## Encoding of values

Strings are always using a encoding. Even when you don't set the encoding
explicitly, PHP uses one. When your application is using a different encoding
than PHP itself, you should set an encoding manually.

You can set an encoding at instantiation with the `encoding` option, or by using
the `setEncoding()` method. We assume that your installation uses ISO and your
application it set to ISO. In this case you will see the below behaviour.

```php
$validator = new Zend\Validator\StringLength(['min' => 6]);
$validator->isValid("Ärger"); // returns false

$validator->setEncoding("UTF-8");
$validator->isValid("Ärger"); // returns true

$validator2 = new Zend\Validator\StringLength([
    'min' => 6,
    'encoding' => 'UTF-8',
]);
$validator2->isValid("Ärger"); // returns true
```

When your installation and your application are using different encodings, then
you should always set an encoding manually.

## Validation Messages
Using the setMessage() method you can set another message to be returned in case of the specified failure.

```php
$validator = new Zend\Validator\StringLength(['min' => 3, 'max' => 30]);
$validator->setMessage('Youre string is too long. You typed '%length%' chars.', Zend\Validator\StringLength::TOO_LONG);
```
