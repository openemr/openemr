# FilesSize

`Zend\Validator\File\FilesSize` allows validating the total size of all file
uploads in aggregate, allowing specifying a minimum upload size and/or a maximum
upload size.

Only use this validator if you will be expecting multiple file uploads in a
single payload, and want to ensure the aggregate size falls within a specific
range.

## Supported Options

`Zend\Validator\File\FilesSize` supports the following options:

- `min`: The minimum aggregate size of all file uploads. May be specified as an
  integer or using SI units. `null` indicates no minimum size is required.
- `max`: The maximum aggregate size of all file uploads. May be specified as an
  integer or using SI units. `null` indicates no maximum size is required.
- `useByteString`: A flag indicating whether sizes should be reported as
  integers or using SI units when reporting validation errors.

See the [Size validator](size.md#supported-options) for details on supported SI
units.

## Basic Usage

```php
use Zend\Validator\File\FilesSize;

$validator = new FilesSize([
    'min' => '1kB`,  // minimum of 1kB
    'max' => `10MB', // maximum of 10MB
]);

if ($validator->isValid($_FILES)) {
    // > 1kB, < 10MB in aggregate
}
```
