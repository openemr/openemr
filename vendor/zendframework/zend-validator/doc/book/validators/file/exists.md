# Exists

`Zend\Validator\File\Exists` checks for the existence of files in specified
directories.

This validator is inversely related to the [NotExists validator](not-exists.md).

## Supported Options

The following set of options are supported:

- `directory`: Array of directories, or comma-delimited string  of directories.

## Usage Examples

```php
use Zend\Validator\File\Exists;

// Only allow files that exist in ~both~ directories
$validator = new Exists('/tmp,/var/tmp');

// ...or with array notation
$validator = new Exists(['/tmp', '/var/tmp']);

// Perform validation
if ($validator->isValid('/tmp/myfile.txt')) {
    // file is valid
}
```

> ### Checks against all directories
>
> This validator checks whether the specified file exists in **all** of the
> given directories; validation will fail if the file does not exist in one
> or more of them.
