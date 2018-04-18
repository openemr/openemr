# Upload

`Zend\Validator\File\Upload` validates that a file upload operation was
successful.

## Supported Options

`Zend\Validator\File\Upload` supports the following options:

- `files`: array of file uploads. This is generally the `$_FILES` array, but
  should be normalized per the details in [PSR-7](http://www.php-fig.org/psr/psr-7/#1-6-uploaded-files)
  (which is also how [the zend-http Request](https://zendframework.github.io/zend-http)
  normalizes the array).

## Basic Usage

```php
use Zend\Validator\File\Upload;

// Using zend-http's request:
$validator = new Upload($request->getFiles());

// Or using options notation:
$validator = new Upload(['files' => $request->getFiles()]);

// Validate:
if ($validator->isValid('foo')) {
    // "foo" file upload was successful
}
```
