# UploadFile

`Zend\Validator\File\UploadFile` checks whether a single file has been uploaded
via a form `POST` and will return descriptive messages for any upload errors.

# Basic Usage

```php
use Zend\Http\PhpEnvironment\Request;
use Zend\Validator\File\UploadFile;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['error'] == 0

$validator = new UploadFile();
if ($validator->isValid($files['my-upload'])) {
    // file is valid
}
```

## Usage with zend-inputfilter

When using zend-inputfilter's [FileInput](https://zendframework.github.io/zend-inputfilter/file-input/),
this validator will be automatically prepended to the validator chain.
