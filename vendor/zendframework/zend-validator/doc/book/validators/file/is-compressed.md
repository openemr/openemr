# IsCompressed

`Zend\Validator\File\IsCompressed` checks if a file is a compressed archive,
such as zip or gzip. This validator is based on the
[MimeType validator](mime-type.md), and supports the same methods and options.

The default list of [compressed file MIME types](https://github.com/zendframework/zend-validator/blob/master/src/File/IsCompressed.php#L45)
can be found in the source code.

Please refer to the [MimeType validator](mime-type.md) for options and public
methods.

## Basic Usage

```php
$validator = new \Zend\Validator\File\IsCompressed();

if ($validator->isValid('./myfile.zip')) {
    // file is valid
}
```
