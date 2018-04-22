# Memory Objects

## Movable

"Movable" memory objects are ones that may be swapped into the cache backend and
unloaded from memory when not in active use.

Create movable memory objects using the `create([$data])` method of the memory
manager:

```php
$memObject = $memoryManager->create($data);
```

Such objects will be retrieved from the cache and/or memor when accessed again.

## Locked

"Locked" memory objects will never be swapped to cache or unloaded from memory.

Create locked memory objects using the `createLocked([$data])` method of the
memory manager:

```php
$memObject = $memoryManager->createLocked($data);
```

Locked objects implement the same interface as movable objects
(`Zend\Memory\Container\Interface`), and can be used interchangably with movable
objects. Use them when you have performance considerations that dictate keeping
the information in memory. Access to locked objects is faster, because the
memory manager doesn't need to track changes for these objects.

The locked objects class (`Zend\Memory\Container\Locked`) guarantees virtually
the same performance as working with a string variable. The overhead is a single
dereference to get the class property.

## Memory container 'value' property

Use the memory container (movable or locked) `value` property to operate with
memory object data:

```php
$memObject = $memoryManager->create($data);

echo $memObject->value;

$memObject->value = $newValue;

$memObject->value[$index] = '_';

echo ord($memObject->value[$index1]);

$memObject->value = substr($memObject->value, $start, $length);
```

An alternative way to access memory object data is to use the
[getRef()](#getref-method); method.

## Memory container interface

Each memory container type provides the following methods:

### getRef() method

```php
&getRef() : mixed
```

The `getRef()` method returns a reference to the object value.

Movable objects are loaded from the cache at this moment if the object is not
already in memory. If the object is loaded from the cache, this might cause
swapping of other objects if the memory limit would be exceeded by having all
the managed objects in memory.

Tracking changes to data needs additional resources. The `getRef()` method
returns a reference to the string value used to store the data, which is changed
directly by user application. Use the `getRef()` method for value data
processing where you want to ensure the data changes without necessarily
interacting directly with the memory container:

```php
$memObject = $memoryManager->create($data);

$value = &$memObject->getRef();

for ($count = 0; $count < strlen($value); $count++) {
    $char = $value[$count];
    // ...
}
```

### touch() method

```php
touch() : void
```

The `touch()` method should be used in conjunction with `getRef()`. It signals
that object value has been changed:

```php
$memObject = $memoryManager->create($data);
...

$value = &$memObject->getRef();

for ($count = 0; $count < strlen($value); $count++) {
    // ...
    if ($condition) {
        $value[$count] = $char;
    }
    // ...
}

$memObject->touch();
```

### lock() method

```php
lock() : void
```

The `lock()` methods locks the object in memory. It should be used to prevent
swapping of the object.  Normally, this is not necessary, because the memory
manager uses an intelligent algorithm to choose candidates for swapping. But if
you know that at a specific point in the code an object should not be swapped,
you may lock it.

Locking objects in memory also guarantees that the reference returned by the
`getRef()` method is valid until you unlock the object:

```php
$memObject1 = $memoryManager->create($data1);
$memObject2 = $memoryManager->create($data2);
...

$memObject1->lock();
$memObject2->lock();

$value1 = &$memObject1->getRef();
$value2 = &$memObject2->getRef();

for ($count = 0; $count < strlen($value2); $count++) {
    $value1 .= $value2[$count];
}

$memObject1->touch();
$memObject1->unlock();
$memObject2->unlock();
```

### unlock() method

```php
unlock() : void
```

The `unlock()` method unlocks object when it's no longer necessary to be locked.
See the example above.

### isLocked() method

```php
isLocked() : bool
```

The `isLocked()` method can be used to check if object is locked. It returns
`true` if the object is locked, or `false` if it is not locked. This is always
`true` for "locked" objects, and may be either `true` or `false` for "movable"
objects.
