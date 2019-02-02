# WordCount

`Zend\Validator\File\WordCount` validates that the number of words within a file
match the specified criteria.

## Supported Options

The following set of options are supported:

- `min`: the minimum number of words required; `null` indicates no minimum.
- `max`: the maximum number of words required; `null` indicates no maximum.

## Basic Usage

```php
use Zend\Validator\File\WordCount;

// Limit the amount of words to a maximum of 2000:
$validator = new WordCount(2000);

// Limit the amount of words to between 100 and 5000:
$validator = new WordCount(100, 5000);

// ... or use options notation:
$validator = new WordCount([
    'min' => 1000,
    'max' => 5000,
]);

// Perform validation with file path
if ($validator->isValid('./myfile.txt')) {
    // file is valid
}
```
