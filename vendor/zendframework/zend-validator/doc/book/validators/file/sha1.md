# Sha1

`Zend\Validator\File\Sha1` allows you to validate if a given file's hashed
contents matches the supplied sha1 hash(es). It is subclassed from the
[Hash validator](hash.md) to provide a validator that only supports the `sha1`
algorithm.

> ### Requires the hash extension
>
> This validator requires the PHP [Hash extension](http://php.net/hash) with the
> `sha1` algorithm.

## Supported Options

The following set of options are supported:

- `hash`: String hash or array of hashes against which to validate.

## Basic Usage

```php
use Zend\Validator\File\Sha1;

// Does file have the given hash?
$validator = new Sha1('3b3652f336522365223');

// Or check file against multiple hashes:
$validator = new Sha1([
    '3b3652f336522365223',
    'eb3365f3365ddc65365',
]);

// Or using options notation:
$validator = new Sha1(['hash' => [
    '3b3652f336522365223',
    'eb3365f3365ddc65365',
]]);

// Perform validation with file path
if ($validator->isValid('./myfile.txt')) {
    // file is valid
}
```

## Public Methods

### getSha1

```php
getSha1() : array
```

Returns an array of sha1 hashes against which to validate.

### addSha1

```php
addSha1(string|array $options) : void
```

Add one or more hashes to validate against.

### setSha1

```php
setSha1(string|array $options) : void
```

Overwrite any previously set hashes with those specified.
