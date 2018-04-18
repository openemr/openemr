# Advanced usage

## Custom data source adapters

At some point you may run across a data type that is not covered by the packaged
adapters. In this case, you will need to write your own.

To do so, you must implement `Zend\Paginator\Adapter\AdapterInterface`. There
are two methods required to do this:

- `count() : int`
- `getItems(int $offset, int $itemCountPerPage) | array`

Additionally, you'll typically implement a constructor that takes your data
source as a parameter.

If you've ever used the SPL interface [Countable](http://php.net/Countable),
you're familiar with `count()`. As used with zend-paginator, this is the total
number of items in the data collection; `Zend\Paginator\Paginator::countAllItems`
proxies to this method.

When retrieving items for the current page, `Zend\Paginator\Paginator` calls on
your adapter's `getItems()` method, providing it with an offset and the number
of items to display per page; your job is to return the appropriate slice of
data. For an array, that would be:

```php
return array_slice($this->array, $offset, $itemCountPerPage);
```

Take a look at the packaged adapters for ideas of how you might go about
implementing your own.

## Custom scrolling styles

Creating your own scrolling style requires that you implement
`Zend\Paginator\ScrollingStyle\ScrollingStyleInterface`, which defines a single
method:

```php
getPages(Paginator $paginator, int $pageRange = null) : array
```

This method should calculate a lower and upper bound for page numbers within the
range of so-called "local" pages (that is, pages that are nearby the current
page).

Unless it extends another scrolling style (see
`Zend\Paginator\ScrollingStyle\Elastic` for an example), your custom scrolling
style will inevitably end with something similar to the following line of code:

```php
return $paginator->getPagesInRange($lowerBound, $upperBound);
```

There's nothing special about this call; it's merely a convenience method to
check the validity of the lower and upper bound and return an array with the range
to the paginator.

When you're ready to use your new scrolling style, you'll need to notif
`Zend\Paginator\Paginator`:

```php
use My\Paginator\ScrollingStyle;
use Zend\Paginator\Paginator;
use Zend\ServiceManager\Factory\InvokableFactory;

$manager = Paginator::getScrollingStyleManager();
$manager->setAlias('my-style', ScrollingStyle::class);
$manager->setFactory(ScrollingStyle::class, InvokableFactory::class);
```

## Caching features

`Zend\Paginator\Paginator` can be told to cache the data it has already used,
preventing the adapter from fetching on next request. To tell
paginator to automatically cache the adapter's data, pass a pre-configured
[zend-cache adapter](https://zendframework.github.io/zend-cache/storage/adapter/)
to the static `setCache()` method:

```php
use Zend\Cache\StorageFactory;
use Zend\Paginator\Paginator;

$cache = StorageFactory::adapterFactory('filesystem', [
    'cache_dir' => '/tmp',
    'ttl'       => 3600,
    'plugins'   => [ 'serializer' ],
]);
Paginator::setCache($cache);
```

As long as the `Paginator` class has been seeded with a cache storage object,
the data any instance generates will be cached. If you want to disable caching, call
`setCacheEnabled()` with a boolean `false` on a concrete instance:

```php
use Zend\Paginator\Paginator;

// $cache is a Zend\Cache\Storage\StorageInterface instance
Paginator::setCache($cache);

// ... later on the script:
$paginator->setCacheEnabled(false);
// cache is now disabled for this instance.
```

When a cache is set, data are automatically stored in it and pulled out from it.
It then can be useful to empty the cache manually. You can get this done by
calling `clearPageItemCache($pageNumber)`. If you don't pass any parameter, the
whole cache will be empty. You can optionally pass a parameter representing the
page number to empty in the cache:

```php
use Zend\Paginator\Paginator;

// $cache is a Zend\Cache\Storage\StorageInterface instance
Paginator::setCache($cache);

// $paginator is a fully configured Paginator instance:
$items = $paginator->getCurrentItems();

$page3Items = $paginator->getItemsByPage(3);
// page 3 is now in cache

// clear the cache of the results for page 3
$paginator->clearPageItemCache(3);

// clear all the cache data
$paginator->clearPageItemCache();
```

Changing the item count per page will empty the whole cache as it would have
become invalid:

```php
use Zend\Paginator\Paginator;

// $cache is a Zend\Cache\Storage\StorageInterface instance
Paginator::setCache($cache);

// Fetch some items from an instance:
$items = $paginator->getCurrentItems();

// Changing item count flushes the cache:
$paginator->setItemCountPerPage(2);
```

It is also possible to see the data in cache and ask for it directly.
`getPageItemCache()` can be used for that:

```php
use Zend\Paginator\Paginator;

// $cache is a Zend\Cache\Storage\StorageInterface instance
Paginator::setCache($cache);

// Set the item count:
$paginator->setItemCountPerPage(3);

// Fetch some items:
$items = $paginator->getCurrentItems();
$otherItems = $paginator->getItemsPerPage(4);

// See the cached items as a two-dimensional array:
var_dump($paginator->getPageItemCache());
```
