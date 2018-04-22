# Introduction

zend-serialzier provides an adapter-based interface for serializing and
deserializing PHP types to and from different representations.

For more information what a serializer is read the wikipedia page of
[Serialization](http://en.wikipedia.org/wiki/Serialization).

## Quick Start

Serializing adapters can either be created from the provided
`Zend\Serializer\Serializer::factory` method, or by instantiating one of the
`Zend\Serializer\Adapter\*` classes.

```php
use Zend\Serializer\Adapter;
use Zend\Serializer\Exception;
use Zend\Serializer\Serializer;

// Via factory:
$serializer = Serializer::factory(Adapter\PhpSerialize::class);

// Alternately:
$serializer = new Adapter\PhpSerialize();

// Now $serializer is an instance of Zend\Serializer\Adapter\AdapterInterface,
// specifically Zend\Serializer\Adapter\PhpSerialize

try {
    $serialized = $serializer->serialize($data);
    // now $serialized is a string

    $unserialized = $serializer->unserialize($serialized);
    // now $data == $unserialized
} catch (Exception\ExceptionInterface $e) {
    echo $e;
}
```

The method `serialize()` generates a storable string. To regenerate this
serialized data, call the method `unserialize()`.

Any time an error is encountered serializing or unserializing, the adapter will
throw a `Zend\Serializer\Exception\ExceptionInterface`.

Because an application often uses only one serializer internally, it is possible
to define and use a default serializer. That serializer will be used by default
by other components like `Zend\Cache\Storage\Plugin\Serializer`.

To define and use the default serializer, use the static serialization methods
of the basic `Zend\Serializer\Serializer`:

```php
use Zend\Serializer\Adapter;
use Zend\Serializer\Exception;
use Zend\Serializer\Serializer;

Serializer::setDefaultAdapter(Adapter\PhpSerialize::class);

try {
    $serialized = Serializer::serialize($data);
    // now $serialized is a string

    $unserialized = Serializer::unserialize($serialized);
    // now $data == $unserialized
} catch (Exception\ExceptionInterface $e) {
    echo $e;
}
```

## Basic configuration Options

To configure a serializer adapter, you can optionally use an instance of
`Zend\Serializer\Adapter\AdapterOptions`, an instance of one of the adapter
specific options class, an `array`, or a `Traversable` object. The adapter
will convert it into the adapter specific options class instance (if present) or
into the basic `Zend\Serializer\Adapter\AdapterOptions` class instance.

Options can be passed as the second argument to the provided
`Zend\Serializer\Serializer::factory` and `::setDefaultAdapter` methods, via the
adapter's `setOptions` method, or as constructor arguments when directly
instantiating an adapter.

## Available Methods

Each serializer implements the interface `Zend\Serializer\Adapter\AdapterInterface`.

This interface defines the following methods:

Method signature | Description
---------------- | -----------
`serialize(mixed $value) : string` | Generates a storable representation of a value.
`unserialize(string $value) : mixed` | Creates a PHP value from a stored representation.

The base class `Zend\Serializer\Serializer` is used to instantiate the
adapters, to configure the factory, and as a proxy for serialization operations.

It defines the following static methods, where the following references map to
classes/interfaces as follows:

- `AdapterPluginManager`: `Zend\Serializer\AdapterPluginManager`
- `AdapterInterface`: `Zend\Serializer\Adapter\AdapterInterface`
- `AdapterOptions`: `Zend\Serializer\Adapter\AdapterOptions`

Method signature | Description
---------------- | -----------
`factory(/* ... */) : AdapterInterface` | Create a serializer adapter instance. Arguments are: `string|AdapterInterface $adapterName, AdapterOptions|array|Traversable $adapterOptions = null`.
`setAdapterPluginManager(AdapterPluginManager $adapters) : void` | Change the adapter plugin manager.
`getAdapterPluginManager() : AdapterPluginManager` | Get the adapter plugin manager.
`resetAdapterPluginManager() : void` | Resets the internal adapter plugin manager.
`setDefaultAdapter(string|AdapterInterface $adapter /* ... */) : void` | Change the default adapter. Full argument list: `string|AdapterInterface $adapter, AdapterOptions|array|Traversable $adapterOptions = null`.
`getDefaultAdapter() : AdapterInterface` | Get the default adapter.
`serialize(mixed $data /* ... */) : string` | Generates a storable representation of a value using the default adapter. Optionally, provide a  different adapter via the second argument. Full argument list: `mixed $value, string|AdapterInterface $adapter = null, AdapterOptions|array|Traversable $adapterOptions = null`.
`unserialize(string $value /* ... */) : mixed` | Creates a PHP value from a stored representation using the default adapter. Optionally, provide a different adapter via the second argument. Full argument list: `string $value, string|AdapterInterface|null $adapter = null, AdapterOptions|array|Traversable $adapterOptions = null`
