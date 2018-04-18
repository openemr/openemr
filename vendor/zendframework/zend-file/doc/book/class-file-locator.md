# ClassFileLocator

`Zend\File\ClassFileLocator` is a PHP [FilterIterator](http://php.net/FilterIterator)
for use with locating files containing PHP classes, interfaces, abstracts, or
traits. As such, it should be used in conjunction with a
[DirectoryIterator](http://php.net/DirectoryIterator) or
[RecursiveDirectoryIterator](http://php.net/RecursiveDirectoryIterator).

Use cases include building class maps for autoloading.

## Usage

The `ClassFileLocator` constructor can take one of:

- a string representing a directory location; if valid, this will be used to
  seed a `RecursiveDirectoryIterator` instance.
- a `DirectoryIterator` instance.
- a `RecursiveDirectoryIterator` instance.

In each case, once constructed, iteration will result in a list of files
containing PHP clases, interfaces, abstracts, or traits.

Instead of returning standard [SplFileInfo](http://php.net/SplFileInfo)
instances, the `ClassFileLocator` is configured to cast to
`Zend\File\PhpClassFile` instances, which extend `SplFileInfo`, and provide the
following additional methods:

- `getClasses()`: returns an array of all classes, abstract classes, interfaces,
  and traits defined in the file; all names are fully qualified. 
- `getNamespaces()`: returns an array of namespaces defined in the file.

> ### Tokenization
>
> The `ClassFileLocator` uses the [tokenizer](http://php.net/tokenizer)
> extension in order to locate items of interest; as such, its operations
> will not execute PHP files it finds.

## Example

The following will spit out a PHP file that returns a class map for the `src/`
directory in which it is run:

```php
<?php
use Zend\File\ClassFileLocator;

$path = realpath(getcwd() . '/src');

$locator = new ClassFileLocator($path);
$map = [];

foreach ($locator as $file) {
    $filename = str_replace($path . '/', '', $file->getRealPath());
    foreach ($file->getClasses() as $class) {
        $map[$class] = $filename;
    }
}

printf("<?php\nreturn %s;", var_export($map, true));
```
