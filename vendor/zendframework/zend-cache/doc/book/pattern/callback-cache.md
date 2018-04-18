# CallbackCache


The callback cache pattern caches the results of arbitrary PHP callables.

## Quick Start

```php
use Zend\Cache\PatternFactory;
use Zend\Cache\Pattern\PatternOptions;

// Via the factory:
$callbackCache = PatternFactory::factory('callback', [
    'storage'      => 'apc',
    'cache_output' => true,
]);

// Or the equivalent manual instantiation:
$callbackCache = new \Zend\Cache\Pattern\CallbackCache();
$callbackCache->setOptions(new PatternOptions([
    'storage'      => 'apc',
    'cache_output' => true,
]));
```

## Configuration Options

Option | Data Type | Default Value | Description
------ | --------- | ------------- | -----------
`storage` | `string | array | Zend\Cache\Storage\StorageInterface` | none | Adapter used for reading and writing cached data.
`cache_output` | `boolean` | `true` | Whether or not to cache callback output.

## Available Methods

In addition to the methods defined in the `PatternInterface`, this
implementation provides the following methods.

```php
namespace Zend\Cache\Pattern;

use Zend\Cache\Exception;
use Zend\Stdlib\ErrorHandler;

class CallbackCache extends AbstractPattern
{
    /**
     * Call the specified callback or get the result from cache
     *
     * @param  callable   $callback  A valid callback
     * @param  array      $args      Callback arguments
     * @return mixed Result
     * @throws Exception\RuntimeException if invalid cached data
     * @throws \Exception
     */
    public function call($callback, array $args = []);

    /**
     * Intercept method overloading; proxies to call()
     *
     * @param  string $function  Function name to call
     * @param  array  $args      Function arguments
     * @return mixed
     * @throws Exception\RuntimeException
     * @throws \Exception
     */
    public function __call($function, array $args);

    /**
     * Generate a unique key in base of a key representing the callback part
     * and a key representing the arguments part.
     *
     * @param  callable   $callback  A valid callback
     * @param  array      $args      Callback arguments
     * @return string
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function generateKey($callback, array $args = []);
}
```

## Examples

### Instantiating the callback cache pattern

```php
use Zend\Cache\PatternFactory;

$callbackCache = PatternFactory::factory('callback', [
    'storage' => 'apc'
]);
```
