# ImageSize

`Zend\Validator\File\ImageSize` checks the size of image files. Minimum and/or
maximum dimensions can be set to validate against.

## Supported Options

The following set of options are supported:

- `minWidth`: Set the minimum image width as an integer; `null` (the default)
  indicates no minimum.
- `minHeight`: Set the minimum image height as an integer; `null` (the default)
  indicates no minimum.
- `maxWidth`: Set the maximum image width as an integer; `null` (the default)
  indicates no maximum.
- `maxHeight`: Set the maximum image height as an integer; `null` (the default)
  indicates no maximum.

## Basic Usage

```php
use Zend\Validator\File\ImageSize;

// Is image size between 320x200 (min) and 640x480 (max)?
$validator = new ImageSize(320, 200, 640, 480);

// ...or with array notation
$validator = new ImageSize([
    'minWidth' => 320,
    'minHeight' => 200,
    'maxWidth' => 640,
    'maxHeight' => 480,
]);

// Is image size equal to or larger than 320x200?
$validator = new ImageSize([
    'minWidth' => 320,
    'minHeight' => 200,
]);

// Is image size equal to or smaller than 640x480?
$validator = new ImageSize([
    'maxWidth' => 640,
    'maxHeight' => 480,
]);

// Perform validation with file path
if ($validator->isValid('./myfile.jpg')) {
    // file is valid
}
```

## Public Methods

### getImageMin

```php
getImageMin() : array
```

Returns the minimum valid dimensions as an array with the keys `width` and
`height`.

### getImageMax

```php
getImageMax() : array
```

Returns the maximum valid dimensions as an array with the keys `width` and
`height`.
