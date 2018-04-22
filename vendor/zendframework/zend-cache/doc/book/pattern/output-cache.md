# OutputCache

The `OutputCache` pattern caches output between calls to `start()` and `end()`.

## Quick Start

```php
use Zend\Cache\PatternFactory;

$outputCache = PatternFactory::factory('output', [
    'storage' => 'apc'
]);
```
## Configuration Options

Option | Data Type | Default Value | Description
------ | --------- | ------------- | -----------
`storage` | `string | array | Zend\Cache\Storage\StorageInterface` | none | Adapter used for reading and writing cached data.

## Available Methods

In addition to the methods defined in `PatternInterface`, this implementation
defines the following methods.

```php
namespace Zend\Cache\Pattern;

use Zend\Cache\Exception;

class OutputCache extends AbstractPattern
{
    /**
     * If there is a cached item with the given key, display its data, and
     * return true. Otherwise, start buffering output until end() is called, or
     * the script ends.
     *
     * @param  string  $key Key
     * @throws Exception\MissingKeyException if key is missing
     * @return bool
     */
    public function start($key);

    /**
     * Stop buffering output, write buffered data to the cache using the key
     * provided to start(), and display the buffer.
     *
     * @throws Exception\RuntimeException if output cache not started or buffering not active
     * @return bool TRUE on success, FALSE on failure writing to cache
     */
    public function end();
}
```

## Examples

### Caching simple view scripts

```php
$outputCache = Zend\Cache\PatternFactory::factory('output', [
    'storage' => 'apc',
]);

$outputCache->start('mySimpleViewScript');
include '/path/to/view/script.phtml';
$outputCache->end();
```
