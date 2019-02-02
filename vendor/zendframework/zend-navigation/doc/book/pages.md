# Pages

zend-navigation ships with two page types:

- [MVC pages](#mvc-pages), using the class `Zend\Navigation\Page\Mvc`
- [URI pages](#uri-pages), using the class `Zend\Navigation\Page\Uri`

MVC pages link to on-site web pages, and are defined using MVC parameters
(`action`, `controller`, `route`, `params`). URI pages are defined by a single
property `uri`, which give you the full flexibility to link off-site pages or do
other things with the generated links (e.g. a URI that turns into `<a href="#">foo<a>`).

## Common page features

All page classes must extend `Zend\Navigation\Page\AbstractPage`, and will thus
share a common set of features and properties. Most notably, they share the
options in the table below and the same initialization process.

Option keys are mapped to `set*()` methods. This means that the option `order` maps to the method
`setOrder()`, and `reset_params` maps to the method `setResetParams()`. If there is no setter
method for the option, it will be set as a custom property of the page.

Read more on extending `Zend\Navigation\Page\AbstractPage` in the section
["Creating custom page types"](#creating-custom-page-types).

### Common page options

Key      | Type                                                          | Default | Description
-------- | ------------------------------------------------------------- | ------- | -----------
label    | `string`                                                      | `NULL`  | A page label, such as 'Home' or 'Blog'.
fragment | `string|null`                                                 | `NULL`  | A fragment identifier (anchor identifier) pointing to an anchor within a resource that is subordinate to another, primary resource. The fragment identifier introduced by a hash mark "#". Example: ``http://www.example.org/foo.html#bar`` (*bar* is the fragment identifier)
id       | `string|integer`                                              | `NULL`  | An *id* tag/attribute that may be used when rendering the page, typically in an anchor element.
class    | `string`                                                      | `NULL`  | A *CSS* class that may be used when rendering the page, typically in an anchor element.
title    | `string`                                                      | `NULL`  | A short page description, typically for using as the title attribute in an anchor.
target   | `string`                                                      | `NULL`  | Specifies a target that may be used for the page, typically in an anchor element.
rel      | `array`                                                       | `[]`    | Specifies forward relations for the page. Each element in the array is a key-value pair, where the key designates the relation/link type, and the value is a pointer to the linked page. An example of a key-value pair is ``'alternate' => 'format/plain.html'``. To allow full flexibility, there are no restrictions on relation values. The value does not have to be a string. Read more about ``rel`` and ``rev`` in the section on the Links helper.
rev      | `array`                                                       | `[]`    | Specifies reverse relations for the page. Works exactly like rel.
order    | `string|integer|null`                                         | `NULL`  | Works like order for elements in ``Zend\Form``. If specified, the page will be iterated in a specific order, meaning you can force a page to be iterated before others by setting the order attribute to a low number, e.g. -100. If a String is given, it must parse to a valid int. If ``NULL`` is given, it will be reset, meaning the order in which the page was added to the container will be used.
resource | `string|Zend\Permissions\Acl\Resource\ResourceInterface|null` | `NULL`  | ACL resource to associate with the page. Read more in the section on ACL integration in view helpers.
privilege| `string|null`                                                 | `NULL`  | ACL privilege to associate with the page. Read more in the section on ACL integration in view helpers.
active   | `boolean`                                                     | `FALSE` | Whether the page should be considered active for the current request. If active is FALSE or not given, MVC pages will check its properties against the request object upon calling ``$page->isActive()``.
visible  | `boolean`                                                     | `TRUE`  | Whether page should be visible for the user, or just be a part of the structure. Invisible pages are skipped by view helpers.
pages    | `array|Travsersable|null`                                     | `NULL`  | Child pages of the page. This could be an array or `Traversable` object containing either page options that can be passed to the `factory()` method, `AbstractPage` instances, or a mixture of both.

> #### Custom properties
>
> All pages support setting and retrieval of custom properties by use of the
> magic methods `__set($name, $value)`, `__get($name)`, `__isset($name)` and
> `__unset($name)`. Custom properties may have any value, and will be included
> in the array that is returned from `$page->toArray()`, which means that pages
> can be serialized/deserialized successfully even if the pages contains
> properties that are not native in the page class.
>
> Both native and custom properties can be set using `$page->set($name, $value)`
> and retrieved using `$page->get($name)`, or by using magic methods.

> The following example demonstrates custom properties:
> 
> ```php
> $page = new Zend\Navigation\Page\Mvc();
> $page->foo     = 'bar';
> $page->meaning = 42;
> 
> echo $page->foo;
> 
> if ($page->meaning != 42) {
>     // action should be taken
> }
> ```

## MVC pages

MVC pages are defined using MVC parameters known from the
[zend-mvc](https://zendframework.github.com/zend-mvc/) component. An MVC page
will use `Zend\Router\RouteStackInterface` internally in the `getHref()` method
to generate `href` attributes, and the `isActive()` method will compare the
`Zend\Router\RouteMatch` params with the page's params to determine if the page
is active.

> ### useRouteMatch flag
>
> Starting in version 2.2.0, if you want to re-use any matched route parameters
> when generating a link, you can do so via the `useRouteMatch` flag. This is
> particularly useful when creating segment routes that include the currently
> selected language or locale as an initial segment, as it ensures the links
> generated all include the matched value.

### MVC page options

Key          | Type                              | Default | Description
------------ | --------------------------------- | ------- | -----------
action       | `string`                          | `NULL`  | Action name to use when generating `href` to the page.
controller   | `string`                          | `NULL`  | Controller name to use when generating `href` to the page.
params       | `array`                           | `[]`    | User params to use when generating `href` to the page.
route        | `string`                          | `NULL`  | Route name to use when generating `href` to the page.
routeMatch   | `Zend\Router\RouteMatch`          | `NULL`  | `RouteInterface` matches used for routing parameters and testing validity.
useRouteMatch| `boolean`                         | `FALSE` | If true, then the `getHref()` method will use the `routeMatch` parameters to assemble the URI.
router       | `Zend\Router\RouteStackInterface` | `NULL`  | Router for assembling URLs.
query        | `array`                           | `[]`    | Query string arguments to use when generating `href` to page.

> ### URIs are relative to base URL
>
> The URI returned is relative to the `baseUrl` in `Zend\Router\Http\TreeRouteStack`.
> In the examples, the `baseUrl` is '/' for simplicity.

### getHref() generates the page URI

This example demonstrates that MVC pages use `Zend\Router\RouteStackInterface`
internally to generate URIs when calling `$page->getHref()`.

```php
use Zend\Navigation\Page;
use Zend\Router\Http\Segment;
use Zend\Router\Http\TreeRouteStack;

// Create route
$route = Segment::factory([
   'route'       => '/[:controller[/:action][/:id]]',
   'constraints' => [
      'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
      'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
      'id'         => '[0-9]+',
   ],
   [
      'controller' => 'Album\Controller\Album',
      'action'     => 'index',
   ]
]);
$router = new TreeRouteStack();
$router->addRoute('default', $route);

// getHref() returns /album/add
$page = new Page\Mvc([
    'action'     => 'add',
    'controller' => 'album',
]);

// getHref() returns /album/edit/1337
$page = new Page\Mvc([
    'action'     => 'edit',
    'controller' => 'album',
    'params'     => ['id' => 1337],
]);

 // getHref() returns /album/1337?format=json
$page = new Page\Mvc([
    'action'     => 'edit',
    'controller' => 'album',
    'params'     => ['id' => 1337],
    'query'      => ['format' => 'json'],
]);
```

### isActive() determines if page is active

This example demonstrates that MVC pages determine whether they are active by
using the params found in the route match object.

```php
use Zend\Navigation\Page;

/**
 * Dispatched request:
 * - controller: album
 * - action:     index
 */
$page1 = new Page\Mvc([
    'action'     => 'index',
    'controller' => 'album',
]);

$page2 = new Page\Mvc([
    'action'     => 'edit',
    'controller' => 'album',
]);

$page1->isActive(); // returns true
$page2->isActive(); // returns false

/**
 * Dispatched request:
 * - controller: album
 * - action:     edit
 * - id:         1337
 */
$page = new Page\Mvc([
    'action'     => 'edit',
    'controller' => 'album',
    'params'     => ['id' => 1337],
]);

// returns true, because request has the same controller and action
$page->isActive();

/**
 * Dispatched request:
 * - controller: album
 * - action:     edit
 */
$page = new Page\Mvc([
    'action'     => 'edit',
    'controller' => 'album',
    'params'     => ['id' => null],
]);

// returns false, because page requires the id param to be set in the request
$page->isActive(); // returns false
```

### Using routes

Routes can be used with MVC pages. If a page has a route, this route will be
used in `getHref()` to generate the URL for the page.

> #### Default parameters are not necessary
>
> Note that when using the `route` property in a page, you do not need to
> specify the default params that the route defines (controller, action, etc.).

```php
use Zend\Navigation\Page;
use Zend\Router\Http\Segment;
use Zend\Router\Http\TreeRouteStack;

// the following route is added to the ZF router
$route = Segment::factory([
   'route'       => '/a/:id',
   'constraints' => [
      'id' => '[0-9]+',
   ],
   [
      'controller' => 'Album\Controller\Album',
      'action'     => 'show',
   ]
]);
$router = new TreeRouteStack();
$router->addRoute('albumShow', $route);

// a page is created with a 'route' option
$page = new Page\Mvc([
    'label'      => 'Show album',
    'route'      => 'albumShow',
    'params'     => ['id' => 42]
]);

// returns: /a/42
$page->getHref();
```

## URI Pages

Pages of type `Zend\Navigation\Page\Uri` can be used to link to pages on other
domains or sites, or to implement custom logic for the page. In addition to the
common page options, a URI page takes only one additional option, a `uri`. The
`uri` will be returned when calling `$page->getHref()`, and may be a `string` or
`null`.

> ### No auto-determination of active status
>
> `Zend\Navigation\Page\Uri` will not try to determine whether it should be
> active when calling `$page->isActive()`; it merely returns what currently is
> set. In order to make a URI page active, you must manually call
> `$page->setActive()` or specify the `active` as a page option during
> instantiation.

### URI page options

Key | Type     | Default | Description
--- | -------- | ------- | -----------
uri | `string` | `NULL`  | URI to page. This can be any string or `NULL`.

## Creating custom page types

When extending `Zend\Navigation\Page\AbstractPage`, there is usually no need to
override the constructor or the `setOptions()` method. The page constructor
takes a single parameter, an `array` or a `Traversable` object, which is then
passed to `setOptions()`. That method will in turn call the appropriate `set*()`
methods based on the options provided, which in turn maps the option to native
or custom properties. If the option `internal_id` is given, the method will
first look for a method named `setInternalId()`, and pass the option to this
method if it exists. If the method does not exist, the option will be set as a
custom property of the page, and be accessible via `$internalId =
$page->internal_id;` or `$internalId = $page->get('internal_id');`.

### Basic custom page example

The only thing a custom page class needs to implement is the `getHref()` method.

```php
namespace My;

use Zend\Navigation\Page\AbstractPage;

class Page extends AbstractPage
{
    public function getHref()
    {
        return 'something-completely-different';
    }
}
```

### A custom page with properties

When adding properties to an extended page, there is no need to override/modify
`setOptions()`.

```php
namespace My\Navigation;

use Zend\Navigation\Page\AbstractPage;

class Page extends AbstractPage
{
    protected $foo;
    protected $fooBar;

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function setFooBar($fooBar)
    {
        $this->fooBar = $fooBar;
    }

    public function getFooBar()
    {
        return $this->fooBar;
    }

    public function getHref()
    {
        return sprintf('%s/%s', $this->foo, $this->fooBar);
    }
}

// Instantiation:
$page = new Page([
    'label'   => 'Property names are mapped to setters',
    'foo'     => 'bar',
    'foo_bar' => 'baz',
]);
```

## Creating pages using the page factory

All pages (also custom classes), can be created using the page factory,
`Zend\Navigation\Page\AbstractPage::factory()`. The factory accepts either an
array or `Traversable` set of options.  Each key in the options corresponds to a
page option, as seen earlier.  If the option `uri` is given and no MVC options
are provided (e.g., `action`, `controller`, `route`), a URI page will be
created. If any of the MVC options are given, an MVC page will be created.

If `type` is given, the factory will assume the value to be the name of the
class that should be created. If the value is `mvc` or `uri`, an MVC or URI page
will be created, respectively.

### Creating an MVC page using the page factory

```php
use Zend\Navigation\Page\AbstractPage;

// MVC page, as "action" is defined
$page = AbstractPage::factory([
    'label'  => 'My MVC page',
    'action' => 'index',
]);

// MVC page, as "action" and "controller" are defined
$page = AbstractPage::factory([
    'label'      => 'Search blog',
    'action'     => 'index',
    'controller' => 'search',
]);

// MVC page, as "route" is defined
$page = AbstractPage::factory([
    'label' => 'Home',
    'route' => 'home',
]);

// MVC page, as "type" is "mvc"
$page = AbstractPage::factory([
    'type'   => 'mvc',
    'label'  => 'My MVC page',
]);
```

### Creating a URI page using the page factory

```php
use Zend\Navigation\Page\AbstractPage;

// URI page, as "uri" is present, with now MVC options
$page = AbstractPage::factory([
    'label' => 'My URI page',
    'uri'   => 'http://www.example.com/',
]);

// URI page, as "uri" is present, with now MVC options
$page = AbstractPage::factory([
    'label'  => 'Search',
    'uri'    => 'http://www.example.com/search',
    'active' => true,
]);

// URI page, as "uri" is present, with now MVC options
$page = AbstractPage::factory([
    'label' => 'My URI page',
    'uri'   => '#',
]);

// URI page, as "type" is "uri"
$page = AbstractPage::factory([
    'type'  => 'uri',
    'label' => 'My URI page',
]);
```

### Creating a custom page type using the page factory

To create a custom page type using the factory, use the option `type` to specify
a class name to instantiate.

```php
namespace My\Navigation;

use Zend\Navigation\Page\AbstractPage;

class Page extends AbstractPage
{
    protected $fooBar = 'ok';

    public function setFooBar($fooBar)
    {
        $this->fooBar = $fooBar;
    }
}

// Creates Page instance, as "type" refers to its class.
$page = AbstractPage::factory([
    'type'    => Page::class,
    'label'   => 'My custom page',
    'foo_bar' => 'foo bar',
]);
```
