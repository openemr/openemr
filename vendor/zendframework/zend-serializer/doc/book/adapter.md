# Adapters

zend-serializer adapters handle serialization to and deserialization from
specific representations.

Each adapter has its own strengths. In some cases, not every PHP datatype (e.g.,
objects) can be converted to a string representation. In most such cases, the
type will be converted to a similar type that is serializable.

As an example, PHP objects will often be cast to arrays. If this fails, a
`Zend\Serializer\Exception\ExceptionInterface` will be thrown.

## The PhpSerialize Adapter

The `Zend\Serializer\Adapter\PhpSerialize` adapter uses the built-in
[serialize()](http://php.net/serialize)/[unserialize()](http://php.net/unserialize)
functions, and is a good default adapter choice.

There are no configurable options for this adapter.

## The IgBinary Adapter

[Igbinary](http://pecl.php.net/package/igbinary) was originally released by
Sulake Dynamoid Oy and since 2011-03-14 moved to [PECL](http://pecl.php.net) and
maintained by Pierre Joye. It's a drop-in replacement for the standard PHP
serializer. Instead of using a costly textual representation, igbinary stores
PHP data structures in a compact binary form. Savings are significant when using
memcached or similar memory based storages for serialized data.

You need the igbinary PHP extension installed on your system in order to use
this adapter.

There are no configurable options for this adapter.

## The Wddx Adapter

[WDDX](http://wikipedia.org/wiki/WDDX) (Web Distributed Data eXchange) is a
programming-language-, platform-, and transport-neutral data interchange
mechanism for passing data between different environments and different
computers.

The adapter uses the [wddx](http://php.net/wddx) PHP functions. Please read the
PHP manual to determine how you may enable them in your installation.

Additionally, the [SimpleXML](http://php.net/simplexml) extension is used to
check if a returned `NULL` value from `wddx_unserialize()` is based on a
serialized `NULL` or on invalid data.

Available options include:

Option  | Data Type | Default Value | Description
------- | --------- | ------------- | -----------
comment | `string`  |               | An optional comment that appears in the packet header.

## The Json Adapter

The [JSON](http://wikipedia.org/wiki/JavaScript_Object_Notation) adapter provides a bridge to the
[zend-json](https://zendframework.github.io/zend-json) component.

Available options include:

Option                    | Data Type                | Default Value
------------------------- | ------------------------ | -------------
`cycle_check`             | `boolean`                | `false`
`object_decode_type`      | `Zend\Json\Json::TYPE_*` | `Zend\Json\Json::TYPE_ARRAY`
`enable_json_expr_finder` | `boolean`                | `false`

## The PythonPickle Adapter

This adapter converts PHP types to a [Python Pickle](http://docs.python.org/library/pickle.html)
string representation. With it, you can read the serialized data with Python and
read Pickled data from Python with PHP.

This adapter requires the [zend-math](https://zendframework.github.io/zend-math/) component:

```bash
$ composer require zendframework/zend-math
```

Available options include:

Option   | Data Type           | Default Value | Description
---------|---------------------|---------------|------------
protocol | `integer` (0/1/2/3) | 0             | The Pickle protocol version used on serialize

### Datatype merging (PHP to Python Pickle)

PHP Type     | Python Pickle Type
------------ | ------------------
`NULL`       | None
`boolean`    | `boolean`
`integer`    | `integer`
`float`      | `float`
`string`     | `string`
`array` list | `list`
`array` map  | `dictionary`
`object`     | `dictionary`

### Datatype merging (Python Pickle to PHP)

Python Pickle Type | PHP Type
-------------------|---------
`None`             | `NULL`
`boolean`          | `boolean`
`integer`          | `integer`
`long`             | `integer`, `float`, `string`, or `Zend\Serializer\Exception\ExceptionInterface`
`float`            | `float`
`string`           | `string`
`bytes`            | `string`
`unicode string`   | `string` UTF-8
`list`             | `array` list
`tuple`            | `array` list
`dictionary`       | `array` map
All other types    | `Zend\Serializer\Exception\ExceptionInterface`

## The PhpCode Adapter

The `Zend\Serializer\Adapter\PhpCode` adapter generates a parsable PHP code
representation using [var_export()](http://php.net/var_export). To restore,
the data will be executed using [eval](http://php.net/eval).

There are no configuration options for this adapter.

> ### Warning: Unserializing objects
>
> Objects will be serialized using the
> [__set_state](http://php.net/language.oop5.magic#language.oop5.magic.set-state) magic
> method. If the class doesn't implement this method, a fatal error will occur
> during execution.

> ### Warning: Uses eval()
>
> The `PhpCode` adapter utilizes `eval()` to unserialize. This introduces both a
> performance and potential security issue as a new process will be executed.
> Typically, you should use the `PhpSerialize` adapter unless you require
> human-readability of the serialized data.
