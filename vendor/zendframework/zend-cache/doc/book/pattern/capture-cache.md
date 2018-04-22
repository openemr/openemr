# CaptureCache

The `CaptureCache` pattern is useful for generating static resources to return
via HTTP request. When used in such a fashion, the web server needs to be
configured to run a PHP script generating the requested resource so that
subsequent requests for the same resource can be shipped without calling PHP
again.

This pattern comes with basic logic for managing generated resources.

## Quick Start

For use with an Apache 404 handler:

```apacheconf
# .htdocs
ErrorDocument 404 /index.php
```

```php
// index.php
use Zend\Cache\PatternFactory;
$capture = Zend\Cache\PatternFactory::factory('capture', [
    'public_dir' => __DIR__,
]);

// Start capturing all output, excluding headers, and write to the public
// directory:
$capture->start();

// Don't forget to change the HTTP response code
header('Status: 200', true, 200);

// do stuff to dynamically generate output
```

## Configuration Options

Option | Data Type | Default Value | Description
------ | --------- | ------------- | -----------
`public_dir` | `string` | none | Location of the public web root directory in which to write output.
`index_filename` | `string` | "index.html" | The name of the index file if only a directory was requested.
`file_locking` | `bool` | `true` | Whether or not to lock output files when writing.
`file_permission` | `int | bool` | `0600` (`false` on Windows) | Default permissions for generated output files.
`dir_permission` | `int | bool` | `0700` (`false` on Windows) | Default permissions for generated output directories.
`umask` | `int` | `bool` | `false` | Whether or not to umask generated output files / directories.

## Available Methods

In addition to the methods exposed in `PatternInterface`, this implementation
exposes the following methods.

```php
namespace Zend\Cache\Pattern;

use Zend\Cache\Exception;
use Zend\Stdlib\ErrorHandler;

class CaptureCache extends AbstractPattern
{
    /**
     * Start the cache.
     *
     * @param  string $pageId  Page identifier
     * @return void
     */
    public function start($pageId = null);

    /**
     * Write a page to the requested path.
     *
     * @param string      $content
     * @param null|string $pageId
     * @throws Exception\LogicException
     */
    public function set($content, $pageId = null);

    /**
     * Retrieve a generated page from the cache.
     *
     * @param  null|string $pageId
     * @return string|null
     * @throws Exception\LogicException
     * @throws Exception\RuntimeException
     */
    public function get($pageId = null);

    /**
     * Check if a cache exists for the given page.
     *
     * @param  null|string $pageId
     * @throws Exception\LogicException
     * @return bool
     */
    public function has($pageId = null);

    /**
     * Remove a page from the cache.
     *
     * @param  null|string $pageId
     * @throws Exception\LogicException
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function remove($pageId = null);

    /**
     * Clear cached pages that match the specified glob pattern.
     *
     * @param string $pattern
     * @throws Exception\LogicException
     */
    public function clearByGlob($pattern = '**');

    /**
     * Returns the generated file name.
     *
     * @param null|string $pageId
     * @return string
     */
    public function getFilename($pageId = null);
}
```

## Examples

### Scaling images in the web root

Using the following Apache 404 configuration:

```apacheconf
# .htdocs
ErrorDocument 404 /index.php
```

Use the following script:

```php
// index.php
$captureCache = Zend\Cache\PatternFactory::factory('capture', [
    'public_dir' => __DIR__,
]);

// TODO
```
