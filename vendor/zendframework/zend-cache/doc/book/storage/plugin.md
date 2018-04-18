# Storage Plugins

Cache storage plugins are objects that provide additional functionality to or
influence behavior of a storage adapter.

The plugins listen to events the adapter triggers, and can:

- change the arguments provided to the method triggering the event (via `*.post` events)
- skip and directly return a result (by calling `stopPropagation`)
- change the result (by calling `setResult` on the provided `Zend\Cache\Storage\PostEvent`)
- catch exceptions (by reacting to `Zend\Cache\Storage\ExceptionEvent`)

## Quick Start

Storage plugins can either be created from
`Zend\Cache\StorageFactory::pluginFactory()`, or by instantiating one of the
`Zend\Cache\Storage\Plugin\*` classes.

To make life easier, `Zend\Cache\StorageFactory::factory()` can create both the
requested adapter and all specified plugins at once.

```php
use Zend\Cache\StorageFactory;

// All at once:
$cache = StorageFactory::factory([
    'adapter' => 'filesystem',
    'plugins' => ['serializer'],
]);

// Alternately, via discrete factory methods:
$cache  = StorageFactory::adapterFactory('filesystem');
$plugin = StorageFactory::pluginFactory('serializer');
$cache->addPlugin($plugin);

// Or manually:
$cache  = new Zend\Cache\Storage\Adapter\Filesystem();
$plugin = new Zend\Cache\Storage\Plugin\Serializer();
$cache->addPlugin($plugin);
```

## The ClearExpiredByFactor Plugin

`Zend\Cache\Storage\Plugin\ClearExpiredByFactor` calls the storage method
`clearExpired()` randomly (by factor) after every call of `setItem()`,
`setItems()`, `addItem()`, and `addItems()`.

### Plugin specific options

Name | Data Type | Default Value | Description
---- | --------- | ------------- | -----------
`clearing_factor` | `integer` | `0` | The automatic clearing factor.

> ### Adapter must implement ClearExpiredInterface
>
> The storage adapter must implement `Zend\Cache\Storage\ClearExpiredInterface`
> to work with this plugin.

## The ExceptionHandler Plugin

`Zend\Cache\Storage\Plugin\ExceptionHandler` catches all exceptions thrown on
reading from or writing to the cache, and sends the exception to a defined callback function.
You may also configure the plugin to re-throw exceptions.

### Plugin specific options

Name | Data Type | Default Value | Description
---- | --------- | ------------- | -----------
`exception_callback` | `callable|null` | null | Callback to invoke on exception; receives the exception as the sole argument.
`throw_exceptions` | `boolean` | `true` | Re-throw caught exceptions.

## The IgnoreUserAbort Plugin

`Zend\Cache\Storage\Plugin\IgnoreUserAbort` ignores user-invoked script
termination when, allowing cache write operations to complete first.

### Plugin specific options

Name | Data Type | Default Value | Description
---- | --------- | ------------- | -----------
`exit_on_abort` | `boolean` | `true` | Terminate script execution on user abort.

## The OptimizeByFactor Plugin

`Zend\Cache\Storage\Plugin\OptimizeByFactor` calls the storage method `optimize()`
randomly (by factor) after removing items from the cache.

### Plugin specific options

Name | Data Type | Default Value | Description
---- | --------- | ------------- | -----------
`optimizing_factor` | `integer` | `0` | The automatic optimization factor.

> ### Adapter must implement OptimizableInterface
>
> The storage adapter must implement `Zend\Cache\Storage\OptimizableInterface`
> to work with this plugin.

## The Serializer Plugin

`Zend\Cache\Storage\Plugin\Serializer` will serialize data when writing to
cache, and deserialize when reading. This allows storing datatypes not supported
by the underlying storage adapter.

### Plugin specific options

Name | Data Type | Default Value | Description
---- | --------- | ------------- | -----------
`serializer` | `null|string|Zend\Serializer\Adapter\AdapterInterface` | `null` | The serializer to use; see below.
`serializer_options` | `array` | `[]` | Array of options to use when instantiating the specified serializer.

The `serializer` value has two special cases:

- When `null`, the default serializer is used (JSON).
- When a `string`, the value will be pulled via
  `Zend\Serializer\AdapterPluginManager`, with the provided
  `serializer_options`.

## Available Methods

The following methods are available to all `Zend\Cache\Storage\Plugin\PluginInterface` implementations:

```php
namespace Zend\Cache\Storage\Plugin;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

interface PluginInterface extends ListenerAggregateInterface
{
    /**
     * Set options
     *
     * @param  PluginOptions $options
     * @return PluginInterface
     */
    public function setOptions(PluginOptions $options);

    /**
     * Get options
     *
     * @return PluginOptions
     */
    public function getOptions();

    /**
     * Attach listeners; inherited from ListenerAggregateInterface.
     *
     * @param EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events);

    /**
     * Detach listeners; inherited from ListenerAggregateInterface.
     *
     * @param EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events);
}
```

## Examples

### Basic plugin implementation

```php
use Zend\Cache\Storage\Event;
use Zend\Cache\Storage\Plugin\AbstractPlugin;
use Zend\EventManager\EventManagerInterface;

class MyPlugin extends AbstractPlugin
{
    protected $handles = [];

    /**
     * Attach to all events this plugin is interested in.
     */
    public function attach(EventManagerInterface $events)
    {
        $this->handles[] = $events->attach('getItem.pre', array($this, 'onGetItemPre'));
        $this->handles[] = $events->attach('getItem.post', array($this, 'onGetItemPost'));
    }

    /**
     * Detach all handlers this plugin previously attached.
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->handles as $handle) {
           $events->detach($handle);
        }
        $this->handles = [];
    }

    public function onGetItemPre(Event $event)
    {
        $params = $event->getParams();
        echo sprintf("Method 'getItem' with key '%s' started\n", $params['key']);
    }

    public function onGetItemPost(Event $event)
    {
        $params = $event->getParams();
        echo sprintf("Method 'getItem' with key '%s' finished\n", $params['key']);
    }
}

// After defining this plugin, we can instantiate and add it to an adapter
// instance:
$plugin = new MyPlugin();
$cache->addPlugin($plugin);

// Now when calling getItem(), our plugin should print the expected output:
$cache->getItem('cache-key');
// Method 'getItem' with key 'cache-key' started
// Method 'getItem' with key 'cache-key' finished
```
