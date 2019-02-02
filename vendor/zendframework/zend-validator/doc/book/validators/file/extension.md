# Extension

`Zend\Validator\File\Extension` checks the extension of files. It will assert
`true` when a given file matches any of the defined extensions.

This validator is inversely related to the
[ExcludeExtension validator](exclude-extension.md).

## Supported Options

The following set of options are supported:

- `extension`: Array of extensions, or comma-delimited string of extensions,
  against which to test.
- `case`: Boolean indicating whether or not extensions should match case
  sensitively; defaults to `false` (case-insensitive).

## Usage Examples

```php
use Zend\Validator\File\Extension;

// Allow files with 'php' or 'exe' extensions
$validator = new Extension('php,exe');

// ...or with array notation
$validator = new Extension(['php', 'exe']);

// Test with case-sensitivity on
$validator = new Extension(['php', 'exe'], true);

// Using an options array:
$validator = new Extension([
    'extension' => ['php', 'exe'],
    'case' => true,
]);

// Perform validation
if ($validator->isValid('./myfile.php')) {
    // file is valid
}
```

## Public Methods

### addExtension

```php
addExtension(string|array $options) : void
```

Add one or more extensions as a comma-separated list, or as an array.
