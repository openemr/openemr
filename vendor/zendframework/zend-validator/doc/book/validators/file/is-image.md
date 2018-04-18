# IsImage

`Zend\Validator\File\IsImage` checks if a file is an image, such as jpg or png.
This validator is based on the [MimeType validator](mime-type.md) and supports
the same methods and options.

The default list of [image file MIME types](https://github.com/zendframework/zend-validator/blob/master/src/File/IsImage.php#L44)
can be found in the source code.

Please refer to the [MimeType validator](mime-type.md) for options and public
methods.

## Basic Usage

```php
$validator = new Zend\Validator\File\IsImage();

if ($validator->isValid('./myfile.jpg')) {
    // file is valid
}
```
