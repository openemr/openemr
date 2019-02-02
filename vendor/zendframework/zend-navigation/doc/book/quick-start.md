# Quick Start

The fastest way to get up and running with zend-navigation is:

- Enable the zend-navigation `DefaultNavigationFactory`.
- Define navigation container configuration under the top-level `navigation` key
  in your application configuration.
- Render your container using a navigation view helper within your view scripts.

```php
<?php
// your configuration file, e.g. config/autoload/global.php
return [
    // ...

    'navigation' => [
        'default' => [
            [
                'label' => 'Home',
                'route' => 'home',
            ],
            [
                'label' => 'Page #1',
                'route' => 'page-1',
                'pages' => [
                    [
                        'label' => 'Child #1',
                        'route' => 'page-1-child',
                    ],
                ],
            ],
            [
                'label' => 'Page #2',
                'route' => 'page-2',
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'navigation' => Zend\Navigation\Service\DefaultNavigationFactory::class,
        ],
    ],
    // ...
];
```

```php
<!-- in your layout -->
<!-- ... -->

<body>
    <?= $this->navigation('default')->menu() ?>
</body>
<!-- ... -->
```

## Using multiple navigations

If you want to use more than one navigation, you can register the abstract factory
`Zend\Navigation\Service\NavigationAbstractServiceFactory` with the
[service manager](https://github.com/zendframework/zend-servicemanager).

Once the service factory is registered, you can create as many navigation
definitions as you wish, and the factory will create navigation containers
automatically. This factory can also be used for the `default` container.

```php
<?php
// your configuration file, e.g. config/autoload/global.php
return [
    // ...

    'navigation' => [

        // navigation with name default
        'default' => [
            [
                'label' => 'Home',
                'route' => 'home',
            ],
            [
                'label' => 'Page #1',
                'route' => 'page-1',
                'pages' => [
                    [
                        'label' => 'Child #1',
                        'route' => 'page-1-child',
                    ],
                ],
            ],
            [
                'label' => 'Page #2',
                'route' => 'page-2',
            ],
        ],

        // navigation with name special
        'special' => [
            [
                'label' => 'Special',
                'route' => 'special',
            ],
            [
                'label' => 'Special Page #2',
                'route' => 'special-2',
            ],
        ],

        // navigation with name sitemap
        'sitemap' => [
            [
                'label' => 'Sitemap',
                'route' => 'sitemap',
            ],
            [
                'label' => 'Sitemap Page #2',
                'route' => 'sitemap-2',
            ],
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            Zend\Navigation\Service\NavigationAbstractServiceFactory::class,
        ],
    ],

    // ...
];
```

> ### Container names have a prefix
>
> There is one important point to know when using
> `NavigationAbstractServiceFactory`: The name of the service in your view must
> start with `Zend\Navigation\` followed by the name of the configuration key.
> This helps ensure that no naming collisions occur with other services.

The following example demonstrates rendering the navigation menus for the named
`default`, `special` and `sitemap` containers.

```php
<!-- in your layout -->
<!-- ... -->

<body>
    <?= $this->navigation('Zend\Navigation\Default')->menu() ?>

    <?= $this->navigation('Zend\Navigation\Special')->menu() ?>

    <?= $this->navigation('Zend\Navigation\Sitemap')->menu() ?>
</body>
<!-- ... -->
```
