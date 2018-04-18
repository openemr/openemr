# Storage Capabilities

Storage capabilities describe how a storage adapter works, and which features it
supports.

To get capabilities of a storage adapter, you can use the method
`getCapabilities()`, but only the storage adapter and its plugins have
permissions to change them.

Because capabilities are mutable, you can subscribe to the "change" event to get
notifications; see the examples for details.

If you are writing your own plugin or adapter, you can also change capabilities
because you have access to the marker object and can create your own marker to
instantiate a new instance of `Zend\Cache\Storage\Capabilities`.

## Available Methods

```php
namespace Zend\Cache\Storage;

use ArrayObject;
use stdClass;
use Zend\Cache\Exception;
use Zend\EventManager\EventsCapableInterface;

class Capabilities
{
    /**
     * Constructor
     *
     * @param StorageInterface  $storage
     * @param stdClass          $marker
     * @param array             $capabilities
     * @param null|Capabilities $baseCapabilities
     */
    public function __construct(
        StorageInterface $storage,
        stdClass $marker,
        array $capabilities = [],
        Capabilities $baseCapabilities = null
    );

    /**
     * Get the storage adapter
     *
     * @return StorageInterface
     */
    public function getAdapter();

    /**
     * Get supported datatypes
     *
     * @return array
     */
    public function getSupportedDatatypes();

    /**
     * Set supported datatypes
     *
     * @param  stdClass $marker
     * @param  array $datatypes
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setSupportedDatatypes(stdClass $marker, array $datatypes);

    /**
     * Get supported metadata
     *
     * @return array
     */
    public function getSupportedMetadata();

    /**
     * Set supported metadata
     *
     * @param  stdClass $marker
     * @param  string[] $metadata
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setSupportedMetadata(stdClass $marker, array $metadata);

    /**
     * Get minimum supported time-to-live
     *
     * @return int 0 means items never expire
     */
    public function getMinTtl();

    /**
     * Set minimum supported time-to-live
     *
     * @param  stdClass $marker
     * @param  int $minTtl
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setMinTtl(stdClass $marker, $minTtl);

    /**
     * Get maximum supported time-to-live
     *
     * @return int 0 means infinite
     */
    public function getMaxTtl();

    /**
     * Set maximum supported time-to-live
     *
     * @param  stdClass $marker
     * @param  int $maxTtl
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setMaxTtl(stdClass $marker, $maxTtl);

    /**
     * Is the time-to-live handled static (on write)
     * or dynamic (on read)
     *
     * @return bool
     */
    public function getStaticTtl();

    /**
     * Set if the time-to-live handled static (on write) or dynamic (on read)
     *
     * @param  stdClass $marker
     * @param  bool $flag
     * @return Capabilities Fluent interface
     */
    public function setStaticTtl(stdClass $marker, $flag);

    /**
     * Get time-to-live precision
     *
     * @return float
     */
    public function getTtlPrecision();

    /**
     * Set time-to-live precision
     *
     * @param  stdClass $marker
     * @param  float $ttlPrecision
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setTtlPrecision(stdClass $marker, $ttlPrecision);

    /**
     * Get use request time
     *
     * @return bool
     */
    public function getUseRequestTime();

    /**
     * Set use request time
     *
     * @param  stdClass $marker
     * @param  bool $flag
     * @return Capabilities Fluent interface
     */
    public function setUseRequestTime(stdClass $marker, $flag);

    /**
     * Get if expired items are readable
     *
     * @return bool
     * @deprecated This capability has been deprecated and will be removed in the future.
     *             Please use getStaticTtl() instead
     */
    public function getExpiredRead();

    /**
     * Set if expired items are readable
     *
     * @param  stdClass $marker
     * @param  bool $flag
     * @return Capabilities Fluent interface
     * @deprecated This capability has been deprecated and will be removed in the future.
     *             Please use setStaticTtl() instead
     */
    public function setExpiredRead(stdClass $marker, $flag);

    /**
     * Get maximum key lenth
     *
     * @return int -1 means unknown, 0 means infinite
     */
    public function getMaxKeyLength();

    /**
     * Set maximum key length
     *
     * @param  stdClass $marker
     * @param  int $maxKeyLength
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setMaxKeyLength(stdClass $marker, $maxKeyLength);

    /**
     * Get if namespace support is implemented as prefix
     *
     * @return bool
     */
    public function getNamespaceIsPrefix();

    /**
     * Set if namespace support is implemented as prefix
     *
     * @param  stdClass $marker
     * @param  bool $flag
     * @return Capabilities Fluent interface
     */
    public function setNamespaceIsPrefix(stdClass $marker, $flag);

    /**
     * Get namespace separator if namespace is implemented as prefix
     *
     * @return string
     */
    public function getNamespaceSeparator();

    /**
     * Set the namespace separator if namespace is implemented as prefix
     *
     * @param  stdClass $marker
     * @param  string $separator
     * @return Capabilities Fluent interface
     */
    public function setNamespaceSeparator(stdClass $marker, $separator);
}
```

## Examples

### Get storage capabilities and do specific stuff based on them

```php
use Zend\Cache\StorageFactory;

$cache = StorageFactory::adapterFactory('filesystem');
$supportedDatatypes = $cache->getCapabilities()->getSupportedDatatypes();

// now you can run specific stuff in base of supported feature
if ($supportedDatatypes['object']) {
    $cache->set($key, $object);
} else {
    $cache->set($key, serialize($object));
}
```

### Listen to the change event

```php
use Zend\Cache\StorageFactory;

$cache = StorageFactory::adapterFactory('filesystem', [
    'no_atime' => false,
]);

// Catching capability changes
$cache->getEventManager()->attach('capability', function($event) {
    echo count($event->getParams()) . ' capabilities changed';
});

// change option which changes capabilities
$cache->getOptions()->setNoATime(true);
```
