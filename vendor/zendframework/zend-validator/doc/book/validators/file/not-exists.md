# NotExists

`Zend\Validator\File\NotExists` checks for the existence of files in specified
directories.

This validator is inversely related to the [Exists validator](exists.md).

## Supported Options

The following set of options are supported:

- `directory`: Array of directories or comma-delimited string of directories
  against which to validate.

## Basic Usage

```php
use Zend\Validator\File\NotExists;

// Only allow files that do not exist in ~either~ directories
$validator = new NotExists('/tmp,/var/tmp');

// ... or with array notation:
$validator = new NotExists(['/tmp', '/var/tmp']);

// ... or using options notation:
$validator = new NotExists(['directory' => [
    '/tmp',
    '/var/tmp',
]]);

// Perform validation
if ($validator->isValid('/home/myfile.txt')) {
    // file is valid
}
```

> ### Checks against all directories
>
> This validator checks whether the specified file does not exist in **any** of
> the given directories; validation will fail if the file exists in one (or
> more) of the given directories.
