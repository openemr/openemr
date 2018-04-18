# Callback Validator

`Zend\Validator\Callback` allows you to provide a callback with which to
validate a given value.

## Supported options

The following options are supported for `Zend\Validator\Callback`:

- `callback`: Sets the callback which will be called for the validation.
- `options`: Sets the additional options which will be given to the validator
  and/or callback.

## Basic usage

The simplest use case is to pass a function as a callback. Consider the
following function:

```php
function myMethod($value)
{
    // some validation
    return true;
}
```

To use it within `Zend\Validator\Callback`, pass it to the constructor

```php
$valid = new Zend\Validator\Callback('myMethod');
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

## Usage with closures

The `Callback` validator supports any PHP callable, including PHP
[closures](http://php.net/functions.anonymous).

```php
$valid = new Zend\Validator\Callback(function($value) {
    // some validation
    return true;
});

if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

## Usage with class-based callbacks

Of course it's also possible to use a class method as callback. Consider the
following class definition:

```php
class MyClass
{
    public function myMethod($value)
    {
        // some validation
        return true;
    }
}
```

To use it with the `Callback` validator, pass a callable using an instance of
the class:

```php
$valid = new Zend\Validator\Callback([new MyClass, 'myMethod']);
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

You may also define a static method as a callback. Consider the following class
definition and validator usage:

```php
class MyClass
{
    public static function test($value)
    {
        // some validation
        return true;
    }
}

$valid = new Zend\Validator\Callback(MyClass::class, 'test']);
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

Finally, you may define the magic method `__invoke()` in your class. If you do
so, you can provide a class instance itself as the callback:

```php
class MyClass
{
    public function __invoke($value)
    {
        // some validation
        return true;
    }
}

$valid = new Zend\Validator\Callback(new MyClass());
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

## Adding options

`Zend\Validator\Callback` also allows the usage of options which are provided as
additional arguments to the callback.

Consider the following class and method definition:

```php
class MyClass
{
    public static function myMethod($value, $option)
    {
        // some validation
        return true;
    }

    /**
     * Or, to use with contextual validation
     */
    public static function myMethod($value, $context, $option)
    {
        // some validation
        return true;
    }

}
```

There are two ways to inform the validator of additional options: pass them in
the constructor, or pass them to the `setOptions()` method.

To pass them to the constructor, you would need to pass an array containing two
keys, `callback` and `callbackOptions`:

```php
$valid = new Zend\Validator\Callback([
    'callback'        => [MyClass::class, 'myMethod'],
    'callbackOptions' => $options,
]);

if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

Otherwise, you may pass them to the validator after instantiation:

```php
$valid = new Zend\Validator\Callback([MyClass::class, 'myMethod']);
$valid->setOptions($options);

if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

When there are additional values given to `isValid()`, then these values will be
passed as an additional argument:

```php
$valid = new Zend\Validator\Callback([MyClass::class, 'myMethod']);
$valid->setOptions($options);

if ($valid->isValid($input, $context)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

When making the call to the callback, the value to be validated will always be
passed as the first argument to the callback followed by all other values given
to `isValid()`; all other options will follow it. The amount and type of options
which can be used is not limited.
