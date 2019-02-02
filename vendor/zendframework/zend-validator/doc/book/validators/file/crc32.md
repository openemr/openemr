# Crc32

`Zend\Validator\File\Crc32` allows you to validate if a given file's hashed
contents matches the supplied crc32 hash(es). It is subclassed from the [Hash
validator](hash.md) to provide a validator that only supports the `crc32`
algorithm.

> ### Requires the hash extension
>
> This validator requires the PHP [Hash extension](http://php.net/hash) with the
> `crc32` algorithm.

## Supported Options

The following options are supported:

- `hash`: Single string hash to test the file against, or array of filename/hash
  pairs.

## Usage Examples

```php
// Does file have the given hash?
$validator = new \Zend\Validator\File\Crc32('3b3652f');

// Or, check file against multiple hashes
$validator = new \Zend\Validator\File\Crc32(['3b3652f', 'e612b69']);

// Perform validation with file path
if ($validator->isValid('./myfile.txt')) {
    // file is valid
}
```

## Public Methods

### getCrc32

```php
getCrc32() : array
```

Returns an array of all currently registered hashes to test against.

### addCrc32

```php
addCrc32(string|array $options) : void
```

Add a single hash to test against, or a set of filename/hash pairs to test
against.

### setCrc32

```php
setCrc32(string|array $options): void
```

Overwrite the current list of registered hashes with the one(s) provided.
