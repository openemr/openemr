# ClassCache

The `ClassCache` pattern is an extension to the
[`CallbackCache`](callback-cache.md) pattern. It has the same methods, but
instead generates the callbacks for any public static method invoked on the
class being cached, and caches static properties.

## Quick Start

```php
use Zend\Cache\PatternFactory;

$classCache = PatternFactory::factory('class', [
    'class'   => 'MyClass',
    'storage' => 'apc',
]);
```

## Configuration Options

Option | Data Type | Default Value | Description
------ | --------- | ------------- | -----------
`storage` | `string | array | Zend\Cache\Storage\StorageInterface` | none | Adapter used for reading and writing cached data.
`class` | `string` | none | Name of the class for which to cache method output.
`cache_output` | `boolean` | `true` | Whether or not to cache method output.
`cache_by_default` | `boolean` | `true` | Cache all method calls by default.
`class_cache_methods` | `array` | `[]` | List of methods to cache (if `cache_by_default` is disabled).
`class_non_cache_methods` | `array` | `[]` | List of methods to omit from caching (if `cache_by_default` is enabled).

## Available Methods

In addition to the methods defined in `PatternInterface`, this implementation
exposes the following methods.

```php
namespace Zend\Cache\Pattern;

use Zend\Cache;
use Zend\Cache\Exception;

class ClassCache extends CallbackCache
{
    /**
     * Call and cache a class method
     *
     * @param  string $method  Method name to call
     * @param  array  $args    Method arguments
     * @return mixed
     * @throws Exception\RuntimeException
     * @throws \Exception
     */
    public function call($method, array $args = []);

    /**
     * Intercept method overloading; proxies to call().
     *
     * @param  string $method  Method name to call
     * @param  array  $args    Method arguments
     * @return mixed
     * @throws Exception\RuntimeException
     * @throws \Exception
     */
    public function __call($method, array $args)
    {
        return $this->call($method, $args);
    }

    /**
     * Generate a unique key in base of a key representing the callback part
     * and a key representing the arguments part.
     *
     * @param  string     $method  The method
     * @param  array      $args    Callback arguments
     * @return string
     * @throws Exception\RuntimeException
     */
    public function generateKey($method, array $args = []);

    /**
     * Property overloading: set a static property.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     * @see   http://php.net/manual/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __set($name, $value)
    {
        $class = $this->getOptions()->getClass();
        $class::$name = $value;
    }

    /**
     * Property overloading: get a static property.
     *
     * @param  string $name
     * @return mixed
     * @see    http://php.net/manual/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __get($name)
    {
        $class = $this->getOptions()->getClass();
        return $class::$name;
    }

    /**
     * Property overloading: does the named static property exist?
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        $class = $this->getOptions()->getClass();
        return isset($class::$name);
    }

    /**
     * Property overloading: unset a static property.
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $class = $this->getOptions()->getClass();
        unset($class::$name);
    }
}
```

## Examples

**Caching of import feeds**

```php
$cachedFeedReader = Zend\Cache\PatternFactory::factory('class', [
    'class'   => 'Zend\Feed\Reader\Reader',
    'storage' => 'apc',

    // The feed reader doesn't output anything,
    // so the output doesn't need to be caught and cached:
    'cache_output' => false,
]);

$feed = $cachedFeedReader->call("import", array('http://www.planet-php.net/rdf/'));

// OR
$feed = $cachedFeedReader->import('http://www.planet-php.net/rdf/');
```
