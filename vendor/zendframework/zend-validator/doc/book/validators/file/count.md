# Count

`Zend\Validator\File\Count` allows you to validate that the number of files
uploaded matches criteria, including a minimum number of files and/or a maximum
number of files.

## Supported Options

The following options are supported:

- `min`: The minimum number of uploaded files acceptable; `null` is equivalent
  to `0`, indicating no minimum.
- `max`: The maximum number of uploaded files acceptable; `null` is equivalent
  to no maximum.

## Basic Usage

```php
$validator = new Zend\Validator\File\Count([
    'min' => 1,
    'max' => 5,
]);

// Setting to the $_FILES superglobal; could also use the zend-http
// request's `getFiles()` or PSR-7 ServerRequest's `getUploadedFiles()`.
$files = $_FILES;

if ($validator->isValid($files)) {
    // Received between 1 and 5 files!
}
```
