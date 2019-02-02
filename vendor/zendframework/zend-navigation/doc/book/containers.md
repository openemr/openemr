# Containers

Containers have methods for adding, retrieving, deleting, and iterating pages.
Containers implement the [SPL](http://php.net/spl) interfaces
`RecursiveIterator` and `Countable`, meaning that a container can be iterated
using the SPL `RecursiveIteratorIterator` class.

## Creating containers

`Zend\Navigation\AbstractContainer` can not be instantiated directly. Use
`Zend\Navigation\Navigation` if you want to instantiate a container.

`Zend\Navigation\Navigation` can be constructed entirely empty, or take an array
or a `Traversable` object with pages to put in the container. Each page provided
via options will eventually be passed to the `addPage()` method of the container
class, which means that each element in the options can be also be an array,
Traversable object, or a `Zend\Navigation\Page\AbstractPage` instance.

### Creating a container using an array

```php
use Zend\Navigation\Navigation;

/*
 * Create a container from an array
 *
 * Each element in the array will be passed to
 * Zend\Navigation\Page\AbstractPage::factory() when constructing.
 */
$container = new Navigation([
    [
        'label' => 'Page 1',
        'id' => 'home-link',
        'uri' => '/',
    ],
    [
        'label' => 'Zend',
        'uri' => 'http://www.zend-project.com/',
        'order' => 100,
    ],
    [
        'label' => 'Page 2',
        'controller' => 'page2',
        'pages' => [
            [
                'label' => 'Page 2.1',
                'action' => 'page2_1',
                'controller' => 'page2',
                'class' => 'special-one',
                'title' => 'This element has a special class',
                'active' => true,
            ],
            [
                'label' => 'Page 2.2',
                'action' => 'page2_2',
                'controller' => 'page2',
                'class' => 'special-two',
                'title' => 'This element has a special class too',
            ],
        ],
    ],
    [
        'label' => 'Page 2 with params',
        'action' => 'index',
        'controller' => 'page2',
        // specify a param or two,
        'params' => [
            'format' => 'json',
            'foo' => 'bar',
        ]
    ],
    [
        'label' => 'Page 2 with params and a route',
        'action' => 'index',
        'controller' => 'page2',

        // specify a route name and a param for the route
        'route' => 'nav-route-example',
        'params' => [
            'format' => 'json',
        ],
    ],
    [
        'label' => 'Page 3',
        'action' => 'index',
        'controller' => 'index',
        'module' => 'mymodule',
        'reset_params' => false,
    ],
    [
        'label' => 'Page 4',
        'uri' => '#',
        'pages' => [
            [
                'label' => 'Page 4.1',
                'uri' => '/page4',
                'title' => 'Page 4 using uri',
                'pages' => [
                    [
                        'label' => 'Page 4.1.1',
                        'title' => 'Page 4 using mvc params',
                        'action' => 'index',
                        'controller' => 'page4',
                        // let's say this page is active
                        'active' => '1',
                    ]
                ],
            ],
        ],
    ],
    [
        'label' => 'Page 0?',
        'uri' => '/setting/the/order/option',

        // setting order to -1 should make it appear first
        'order' => -1,
    ],
    [
        'label' => 'Page 5',
        'uri' => '/',

        // this page should not be visible
        'visible' => false,
        'pages' => [
            [
                'label' => 'Page 5.1',
                'uri' => '#',
                'pages' => [
                    [
                        'label' => 'Page 5.1.1',
                        'uri' => '#',
                        'pages' => [
                            [
                                'label' => 'Page 5.1.2',
                                'uri' => '#',

                                // let's say this page is active
                                'active' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    [
        'label' => 'ACL page 1 (guest)',
        'uri' => '#acl-guest',
        'resource' => 'nav-guest',
        'pages' => [
            [
                'label' => 'ACL page 1.1 (foo)',
                'uri' => '#acl-foo',
                'resource' => 'nav-foo',
            ],
            [
                'label' => 'ACL page 1.2 (bar)',
                'uri' => '#acl-bar',
                'resource' => 'nav-bar',
            ],
            [
                'label' => 'ACL page 1.3 (baz)',
                'uri' => '#acl-baz',
                'resource' => 'nav-baz',
            ],
            [
                'label' => 'ACL page 1.4 (bat)',
                'uri' => '#acl-bat',
                'resource' => 'nav-bat',
            ],
        ],
    ],
    [
        'label' => 'ACL page 2 (member)',
        'uri' => '#acl-member',
        'resource' => 'nav-member',
    ],
    [
        'label' => 'ACL page 3 (admin',
        'uri' => '#acl-admin',
        'resource' => 'nav-admin',
        'pages' => [
            [
                'label' => 'ACL page 3.1 (nothing)',
                'uri' => '#acl-nada',
            ],
        ],
    ],
    [
        'label' => 'Zend Framework',
        'route' => 'zf-route',
    ],
]);
```

## Adding pages

Adding pages to a container can be done with the methods `addPage()`,
`addPages()`, or `setPages()`.  See examples below for explanation.

```php
use Zend\Config\Config;
use Zend\Navigation\Navigation;
use Zend\Navigation\Page\AbstractPage;

// create container
$container = new Navigation();

// add page by giving a page instance
$container->addPage(AbstractPage::factory([
    'uri' => 'http://www.example.com/',
]]);

// add page by giving an array
$container->addPage([
    'uri' => 'http://www.example.com/',
]);

// add page by giving a Traversable object; in this case, a zend-config
// instance.
$container->addPage(Config([
    'uri' => 'http://www.example.com/',
]));

$pages = [
    [
        'label'  => 'Save',
        'action' => 'save',
    ],
    [
        'label' =>  'Delete',
        'action' => 'delete',
    ],
];

// add two pages
$container->addPages($pages);

// remove existing pages and add the given pages
$container->setPages($pages);
```

## Removing pages

Removing pages can be done with `removePage()` or `removePages()`.
`removePage()` accepts an instance of a page or an integer. Integer arguments
correspond to the `order` a page has. `removePages()` will remove all pages in
the container.

```php
use Zend\Navigation\Navigation;

$container = new Navigation([
    [
        'label'  => 'Page 1',
        'action' => 'page1',
    ],
    [
        'label'  => 'Page 2',
        'action' => 'page2',
        'order'  => 200,
    ],
    [
        'label'  => 'Page 3',
        'action' => 'page3',
    ],
]);

// remove page by implicit page order
$container->removePage(0);      // removes Page 1

// remove page by instance
$page3 = $container->findOneByAction('page3');
$container->removePage($page3); // removes Page 3

// remove page by explicit page order
$container->removePage(200);    // removes Page 2

// remove all pages
$container->removePages();      // removes all pages
```

### Remove a page recursively

Removing a page recursively can be done with the second parameter of 
the `removePage()` method, which expects a `boolean` value.
 
```php
use Zend\Navigation\Navigation;

$container = new Navigation(
    [
        [
            'label' => 'Page 1',
            'route' => 'page1',
            'pages' => [
                [
                    'label' => 'Page 1.1',
                    'route' => 'page1/page1-1',
                    'pages' => [
                        [
                            'label' => 'Page 1.1.1',
                            'route' => 'page1/page1-1/page1-1-1',
                        ],
                    ],
                ],
            ],
        ],
    ]
);
 
// Removes Page 1.1.1
$container->removePage(
    $container->findOneBy('route', 'page1/page1-1/page1-1-1'),
    true
);
```

## Finding pages

Containers have three finder methods for retrieving pages. Each recursively
searches the container testing for properties with values that match the one
provided.

- `findOneBy($property, $value) : AbstractPage|null`: Returns the first page
  found matching the criteria, or `null` if none was found.
- `findAllBy($property, $value) : AbstractPage[]`: Returns an array of all
  page instances matching the criteria.
- `findBy($property, $value, $all = false) AbstractPage|AbstractPage[]|null`:
  calls on one of the previous methods based on the value of `$all`.

The finder methods can also be used magically by appending the property name to
`findBy`, `findOneBy`, or `findAllBy`. As an example, `findOneByLabel('Home')`
will return the first matching page with label 'Home'.
    
Other combinations include `findByLabel(...)`, `findOneByTitle(...)`,
`findAllByController(...)`, etc. Finder methods also work on custom properties,
such as `findByFoo('bar')`.

```php
use Zend\Navigation\Navigation;

$container = new Navigation([
    [
        'label' => 'Page 1',
        'uri'   => 'page-1',
        'foo'   => 'bar',
        'pages' => [
            [
                'label' => 'Page 1.1',
                'uri'   => 'page-1.1',
                'foo'   => 'bar',
            ],
            [
                'label' => 'Page 1.2',
                'uri'   => 'page-1.2',
                'class' => 'my-class',
            ],
            [
                'type'   => 'uri',
                'label'  => 'Page 1.3',
                'uri'    => 'page-1.3',
                'action' => 'about',
            ],
        ],
    ],
    [
        'label'      => 'Page 2',
        'id'         => 'page_2_and_3',
        'class'      => 'my-class',
        'module'     => 'page2',
        'controller' => 'index',
        'action'     => 'page1',
    ],
    [
        'label'      => 'Page 3',
        'id'         => 'page_2_and_3',
        'module'     => 'page3',
        'controller' => 'index',
    ],
]);

// The 'id' is not required to be unique, but be aware that
// having two pages with the same id will render the same id attribute
// in menus and breadcrumbs.

// Returns "Page 2":
$found = $container->findBy('id', 'page_2_and_3');

// Returns "Page 2":
$found = $container->findOneBy('id', 'page_2_and_3');

// Returns "Page 2" AND "Page 3":
$found = $container->findBy('id', 'page_2_and_3', true);

// Returns "Page 2":
$found = $container->findById('page_2_and_3');

// Returns "Page 2":
$found = $container->findOneById('page_2_and_3');

// Returns "Page 2" AND "Page 3":
$found = $container->findAllById('page_2_and_3');

// Find all pages matching the CSS class "my-class":
// Returns "Page 1.2" and "Page 2":
$found = $container->findAllBy('class', 'my-class');
$found = $container->findAllByClass('my-class');

// Find first page matching CSS class "my-class":
// Returns "Page 1.2":
$found = $container->findOneByClass('my-class');

// Find all pages matching the CSS class "non-existent":
// Returns an empty array.
$found = $container->findAllByClass('non-existent');

// Find first page matching the CSS class "non-existent":
// Returns null.
$found = $container->findOneByClass('non-existent');

// Find all pages with custom property 'foo' = 'bar':
// Returns "Page 1" and "Page 1.1":
$found = $container->findAllBy('foo', 'bar');

// To achieve the same magically, 'foo' must be in lowercase.
// This is because 'foo' is a custom property, and thus the
// property name is not normalized to 'Foo':
$found = $container->findAllByfoo('bar');

// Find all with controller = 'index':
// Returns "Page 2" and "Page 3":
$found = $container->findAllByController('index');
```

## Iterating containers

`Zend\Navigation\AbstractContainer` implements `RecursiveIterator`.  iterate a
container recursively, use the `RecursiveIteratorIterator` class.

```php
use RecursiveIteratorIterator;
use Zend\Navigation\Navigation;

/*
 * Create a container from an array
 */
$container = new Navigation([
    [
        'label' => 'Page 1',
        'uri'   => '#',
    ],
    [
        'label' => 'Page 2',
        'uri'   => '#',
        'pages' => [
            [
                'label' => 'Page 2.1',
                'uri'   => '#',
            ],
            [
                'label' => 'Page 2.2',
                'uri'   => '#',
            ],
        ],
    ],
    [
        'label' => 'Page 3',
        'uri'   => '#',
    ],
]);

// Iterate flat using regular foreach:
// Output: Page 1, Page 2, Page 3
foreach ($container as $page) {
    echo $page->label;
}

// Iterate recursively using RecursiveIteratorIterator
$it = new RecursiveIteratorIterator(
    $container,
    RecursiveIteratorIterator::SELF_FIRST
);

// Output: Page 1, Page 2, Page 2.1, Page 2.2, Page 3
foreach ($it as $page) {
    echo $page->label;
}
```

## Other operations

### hasPage

```php
hasPage(AbstractPage $page) : bool
```

Check if the container has the given page.

### hasPages

```php
hasPages() : bool
```

Checks if there are any pages in the container, and is equivalent to
`count($container) > 0`.

### toArray

```php
toArray() : array
```

Converts the container and the pages in it to a (nested) array. This can be useful
for serializing and debugging.

```php
use Zend\Navigation\Navigation;

$container = new Navigation([
    [
        'label' => 'Page 1',
        'uri'   => '#',
    ],
    [
        'label' => 'Page 2',
        'uri'   => '#',
        'pages' => [
            [
                'label' => 'Page 2.1',
                'uri'   => '#',
            ],
            [
                'label' => 'Page 2.2',
               'uri'   => '#',
            ],
        ],
    ],
]);

var_dump($container->toArray());

/* Output:
array(2) {
  [0]=> array(15) {
    ["label"]=> string(6) "Page 1"
    ["id"]=> NULL
    ["class"]=> NULL
    ["title"]=> NULL
    ["target"]=> NULL
    ["rel"]=> array(0) {
    }
    ["rev"]=> array(0) {
    }
    ["order"]=> NULL
    ["resource"]=> NULL
    ["privilege"]=> NULL
    ["active"]=> bool(false)
    ["visible"]=> bool(true)
    ["type"]=> string(23) "Zend\Navigation\Page\Uri"
    ["pages"]=> array(0) {
    }
    ["uri"]=> string(1) "#"
  }
  [1]=> array(15) {
    ["label"]=> string(6) "Page 2"
    ["id"]=> NULL
    ["class"]=> NULL
    ["title"]=> NULL
    ["target"]=> NULL
    ["rel"]=> array(0) {
    }
    ["rev"]=> array(0) {
    }
    ["order"]=> NULL
    ["resource"]=> NULL
    ["privilege"]=> NULL
    ["active"]=> bool(false)
    ["visible"]=> bool(true)
    ["type"]=> string(23) "Zend\Navigation\Page\Uri"
    ["pages"]=> array(2) {
      [0]=> array(15) {
        ["label"]=> string(8) "Page 2.1"
        ["id"]=> NULL
        ["class"]=> NULL
        ["title"]=> NULL
        ["target"]=> NULL
        ["rel"]=> array(0) {
        }
        ["rev"]=> array(0) {
        }
        ["order"]=> NULL
        ["resource"]=> NULL
        ["privilege"]=> NULL
        ["active"]=> bool(false)
        ["visible"]=> bool(true)
        ["type"]=> string(23) "Zend\Navigation\Page\Uri"
        ["pages"]=> array(0) {
        }
        ["uri"]=> string(1) "#"
      }
      [1]=>
      array(15) {
        ["label"]=> string(8) "Page 2.2"
        ["id"]=> NULL
        ["class"]=> NULL
        ["title"]=> NULL
        ["target"]=> NULL
        ["rel"]=> array(0) {
        }
        ["rev"]=> array(0) {
        }
        ["order"]=> NULL
        ["resource"]=> NULL
        ["privilege"]=> NULL
        ["active"]=> bool(false)
        ["visible"]=> bool(true)
        ["type"]=> string(23) "Zend\Navigation\Page\Uri"
        ["pages"]=> array(0) {
        }
        ["uri"]=> string(1) "#"
      }
    }
    ["uri"]=> string(1) "#"
  }
}
*/
```
