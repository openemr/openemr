# Hash

`Zend\Validator\File\Hash` allows you to validate if a given file's hashed
contents matches the supplied hash(es) and algorithm(s).

> ### Requires the hash extension
>
> This validator requires the PHP [Hash extension](http://php.net/hash). A list
> of supported hash algorithms can be found with the
> [hash\_algos() function](http://php.net/hash_algos).

## Supported Options

The following set of options are supported:

- `hash`: String hash or array of hashes against which to test.
- `algorithm`: String hashing algorithm to use; defaults to `crc32`

## Basic Usage

```php
use Zend\Validator\File\Hash;

// Does file have the given hash?
$validator = new Hash('3b3652f', 'crc32');

// Or, check file against multiple hashes
$validator = new Hash(['3b3652f', 'e612b69'], 'crc32');

// Or use options notation:
$validator = new Hash([
    'hash' => ['3b3652f', 'e612b69'],
    'algorithm' => 'crc32',
]);

// Perform validation with file path
if ($validator->isValid('./myfile.txt')) {
   // file is valid
}
```

## Public Methods

### getHash

```php
getHash() : array
```

Returns an array containing the set of hashes against which to validate.

### addHash

```php
addHash(string|array $options) : void
```

Add one or more hashes against which to validate.

### setHash

```php
setHash(string|array $options) : void
```

Overwrite the current set of hashes with those provided to the method.
