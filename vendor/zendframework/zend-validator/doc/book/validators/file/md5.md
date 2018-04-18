# Md5

`Zend\Validator\File\Md5` allows you to validate if a given file's hashed
contents matches the supplied md5 hash(es). It is subclassed from the
[Hash validator](hash.md) to provide a validator that supports only the MD5
algorithm.

> ### Requires the hash extension
>
> This validator requires the PHP [Hash extension](http://php.net/hash) PHP with
> the `md5` algorithm.

## Supported Options

The following set of options are supported:

- `hash`: String hash or array of hashes against which to validate.

## Basic Usage

```php
use Zend\Validator\File\Md5;

// Does file have the given hash?
$validator = new Md5('3b3652f336522365223');

// Or, check file against multiple hashes
$validator = new Md5([
    '3b3652f336522365223', 'eb3365f3365ddc65365'
]);

// Or use options notation:
$validator = new Md5(['hash' => [
    '3b3652f336522365223', 'eb3365f3365ddc65365'
]]);


// Perform validation with file path
if ($validator->isValid('./myfile.txt')) {
    // file is valid
}
```

## Public Methods

### getMd5

```php
getMd5() : array
```

Returns an array of MD5 hashes against which to validate.

### addMd5

```php
addMd5(string|array $options) : void
```

Add one or more hashes to validate against.

### setMd5

```php
setMd5(string|array $options) : void
```

Overwrite any previously set hashes with those specified.
