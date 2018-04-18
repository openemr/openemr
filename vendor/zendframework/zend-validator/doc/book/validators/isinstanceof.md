# IsInstanceOf Validator

`Zend\Validator\IsInstanceOf` allows you to validate whether a given object is
an instance of a specific class or interface.

## Supported options

The following options are supported for `Zend\Validator\IsInstanceOf`:

- `className`: Defines the fully-qualified class name which objects must be an
  instance of.

## Basic usage

```php
$validator = new Zend\Validator\IsInstanceOf([
    'className' => 'Zend\Validator\Digits'
]);
$object = new Zend\Validator\Digits();

if ($validator->isValid($object)) {
    // $object is an instance of Zend\Validator\Digits
} else {
    // false. You can use $validator->getMessages() to retrieve error messages
}
```

If a string argument is passed to the constructor of
`Zend\Validator\IsInstanceOf`, then that value will be used as the class name:

```php
use Zend\Validator\Digits;
use Zend\Validator\IsInstanceOf;

$validator = new IsInstanceOf(Digits::class);
$object = new Digits();

if ($validator->isValid($object)) {
    // $object is an instance of Zend\Validator\Digits
} else {
    // false. You can use $validator->getMessages() to retrieve error messages
}
```
