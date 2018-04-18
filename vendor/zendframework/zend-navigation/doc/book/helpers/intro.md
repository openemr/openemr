# View Helpers

The navigation helpers are used for rendering navigational elements from
[`Zend\Navigation\Navigation`](../containers.md) instances.

There are 5 built-in helpers:

- [Breadcrumbs](breadcrumbs.md), used for rendering the path to the currently
  active page.
- [Links](links.md), used for rendering navigational head links (e.g.
  `<link rel="next" href="..." />`).
- [Menu](menu.md), used for rendering menus.
- [Sitemap](sitemap.md), used for rendering sitemaps conforming to the
  [Sitemaps XML format](http://www.sitemaps.org/protocol.php).
- [Navigation](navigation.md), used for proxying calls to other navigational
  helpers.

All built-in helpers extend `Zend\View\Helper\Navigation\AbstractHelper`, which
adds integration with
[zend-acl](https://zendframework.github.io/zend-permissions-acl/) and
[zend-i18n](https://zendframework.github.io/zend-i18n/). The abstract class
implements the interface `Zend\View\Helper\Navigation\HelperInterface`, which
defines the following methods:

Method signature                                                      | Description
--------------------------------------------------------------------- | -----------
`getContainer() : null|AbstractContainer`                             | Retrieve the current navigation container, if any.
`hasContainer() : bool`                                               | Is any navigation container currently registered?
`setContainer(AbstractContainer $container) : self`                   | Set a navigation container.
`getTranslator() : null|Zend\I18n\Translator\TranslatorInterface`     | Retrieve the current translator instance, if any.
`setTranslator(Zend\I18n\Translator\TranslatorInterface`) : self`     | Set a translator instance to use with labels.
`hasTranslator() | bool`                                              | Is a translator instance present?
`isTranslatorEnabled() : bool`                                        | Should translation occur? To be `true`, both the flag enabling translation must be set, and a translator instance present.
`setTranslatorEnabled(bool $flag) : self`                             | Set the flag indicating whether or not translation should occur.
`getAcl() : null|Zend\Permissions\Acl\AclInterface`                   | Retrieve the current ACL instance, if any.
`setAcl(Zend\Permissions\Acl\AclInterface $acl) : self`               | Set an ACL instance.
`hasAcl() : bool`                                                     | Whether or not an ACL instance is present.
`getRole() : null|string|Zend\Permissions\Acl\Role\RoleInterface`     | Retrieve the current ACL role instance, if any.
`setRole(string|Zend\Permissions\Acl\Role\RoleInterface $acl) : self` | Set an ACL role instance.
`hasRole() : bool`                                                    | Whether or not an ACL role instance is present.
`getUseAcl() : bool`                                                  | Whether or not to use ACLs; both the flag must be enabled and an ACL instance present.
`setUseAcl(bool $flag) : self`                                        | Set the flag indicating whether or not to use ACLs.
`__toString()`                                                        | Cast the helper to a string value; relies on `render()`.
`render()`                                                            | Render the helper to a string.

In addition to the method stubs from the interface, the abstract class also
implements the following methods:

Method signature                                                             | Description
---------------------------------------------------------------------------- | -----------
`getIndent() : string`                                                       | Retrieve the indentation string to use; default is 4 spaces.
`setIndent(string|int $indent) : self`                                       | Set the indentation to use. In the case of an integer, this indicates the number of spaces. Indentation can be specified for all but the `Sitemap` helper.
`getMinDepth() : int`                                                        | Retrieve the minimum depth a page must have to be included in output
`setMinDepth(null|int $depth) : self`                                        | Set the minimum depth a page must have to be included in output; `null` means no minimum.
`getMaxDepth() : int`                                                        | Retrieve the maximum depth a page must have to be included in output
`setMaxDepth(null|int $depth) : self`                                        | Set the maximum depth a page must have to be included in output; `null` means no maximum.
`getRenderInvisible() : bool`                                                | Retrieve the flag indicating whether or not to render items marked as invisible; defaults to `false`.
`setRenderInvisible(bool $flag) : self`                                      | Set the flag indicating whether or not to render items marked as invisible.
`__call() : mixed`                                                           | Proxy method calls to the registered container; this allows you to use the helper as if it were a navigation container. See [the example below](#proxying-calls-to-the-navigation-container).
`findActive(/* ... */) : array`                                              | Find the deepest active page in the container, using the arguments `AbstractContainer $container, int $minDepth = null, int $maxDepth = -1)`. If depths are not given, the method will use the values retrieved from `getMinDepth()` and `getMaxDepth()`. The deepest active page must be between `$minDepth` and `$maxDepth` inclusively. Returns an array containing the found page instance (key `page`) and the depth (key `depth`) at which the page was found.
`htmlify(AbstractPage $page) : string`                                       | Renders an HTML `a` element based on the give page.
`accept(AbstractPage $page, bool $recursive = true) : bool`                  | Determine if a page should be accepted when iterating containers. This method checks for page visibility and verifies that the helper's role is allowed access to the page's resource and privilege.
`static setDefaultAcl(Zend\Permissions\Acl\AclInterface $acl) : void`        | Set a default ACL instance to use with all navigation helpers.
`static setDefaultRole(Zend\Permissions\Acl\Role\RoleInterface $acl) : void` | Set a default ACL role instance to use with all navigation helpers.

If a container is not explicitly set, the helper will create an empty
`Zend\Navigation\Navigation` container when calling `$helper->getContainer()`.

### Proxying calls to the navigation container

Navigation view helpers use the magic method `__call()` to proxy method calls to
the navigation container that is registered in the view helper.

```php
$this->navigation()->addPage([
    'type' => 'uri',
    'label' => 'New page',
]);
```

The call above will add a page to the container in the `Navigation` helper.

## Translation of labels and titles

The navigation helpers support translation of page labels and titles. You can
set a translator of type `Zend\I18n\Translator\TranslatorInterface` in the
helper using `$helper->setTranslator($translator)`.

If you want to disable translation, use `$helper->setTranslatorEnabled(false)`.

The [proxy helper](navigation.md) will inject its own translator to the helper
it proxies to if the proxied helper doesn't already have a translator.

> ### Sitemaps do not use translation
>
> There is no translation in the sitemap helper, since there are no page labels
> or titles involved in an XML sitemap.

## Integration with ACL

All navigational view helpers support ACLs.  An object implementing
`Zend\Permissions\Acl\AclInterface` can be assigned to a helper instance with
`$helper->setAcl($acl)`, and role with `$helper->setRole('member')` or
`$helper->setRole(new Zend\Permissions\Acl\Role\GenericRole('member'))`. If an
ACL is used in the helper, the role in the helper must be allowed by the ACL to
access a page's `resource` and/or have the page's `privilege` for the page to be
included when rendering.

If a page is not accepted by ACL, any descendant page will also be excluded from
rendering.

The [proxy helper](navigation.md) will inject its own ACL and role to the helper
it proxies to if the proxied helper doesn't already have any.

The examples below all show how ACL affects rendering.

## Navigation setup used in examples

This example shows the setup of a navigation container for a fictional software company.

Notes on the setup:

- The domain for the site is `www.example.com`.
- Interesting page properties are marked with a comment.
- Unless otherwise is stated in other examples, the user is requesting the URL
  `http://www.example.com/products/server/faq/`, which translates to the page
  labeled `FAQ` under "Foo Server".
- The assumed ACL and router setup is shown below the container setup.

```php
use Zend\Navigation\Navigation;

/*
 * Navigation container

 * Each element in the array will be passed to
 * Zend\Navigation\Page\AbstractPage::factory() when constructing
 * the navigation container below.
 */
$pages = [
    [
        'label'      => 'Home',
        'title'      => 'Go Home',
        'module'     => 'default',
        'controller' => 'index',
        'action'     => 'index',
        'order'      => -100, // make sure home is the first page
    ],
    [
        'label'      => 'Special offer this week only!',
        'module'     => 'store',
        'controller' => 'offer',
        'action'     => 'amazing',
        'visible'    => false, // not visible
    ],
    [
        'label'      => 'Products',
        'module'     => 'products',
        'controller' => 'index',
        'action'     => 'index',
        'pages'      => [
            [
                'label'      => 'Foo Server',
                'module'     => 'products',
                'controller' => 'server',
                'action'     => 'index',
                'pages'      => [
                    [
                        'label'      => 'FAQ',
                        'module'     => 'products',
                        'controller' => 'server',
                        'action'     => 'faq',
                        'rel'        => [
                            'canonical' => 'http://www.example.com/?page=faq',
                            'alternate' => [
                                'module'     => 'products',
                                'controller' => 'server',
                                'action'     => 'faq',
                                'params'     => ['format' => 'xml'],
                            ],
                        ],
                    ],
                    [
                        'label'      => 'Editions',
                        'module'     => 'products',
                        'controller' => 'server',
                        'action'     => 'editions',
                    ],
                    [
                        'label'      => 'System Requirements',
                        'module'     => 'products',
                        'controller' => 'server',
                        'action'     => 'requirements',
                    ],
                ],
            ],
            [
                'label'      => 'Foo Studio',
                'module'     => 'products',
                'controller' => 'studio',
                'action'     => 'index',
                'pages'      => [
                    [
                        'label'      => 'Customer Stories',
                        'module'     => 'products',
                        'controller' => 'studio',
                        'action'     => 'customers',
                    ],
                    [
                        'label'      => 'Support',
                        'module'     => 'products',
                        'controller' => 'studio',
                        'action'     => 'support',
                    ],
                ],
            ],
        ],
    ],
    [
        'label'      => 'Company',
        'title'      => 'About us',
        'module'     => 'company',
        'controller' => 'about',
        'action'     => 'index',
        'pages'      => [
            [
                'label'      => 'Investor Relations',
                'module'     => 'company',
                'controller' => 'about',
                'action'     => 'investors',
            ],
            [
                'label'      => 'News',
                'class'      => 'rss', // class
                'module'     => 'company',
                'controller' => 'news',
                'action'     => 'index',
                'pages'      => [
                    [
                        'label'      => 'Press Releases',
                        'module'     => 'company',
                        'controller' => 'news',
                        'action'     => 'press',
                    ],
                    [
                        'label'      => 'Archive',
                        'route'      => 'archive', // route
                        'module'     => 'company',
                        'controller' => 'news',
                        'action'     => 'archive',
                    ],
                ],
            ],
        ],
    ],
    [
        'label'      => 'Community',
        'module'     => 'community',
        'controller' => 'index',
        'action'     => 'index',
        'pages'      => [
            [
                'label'      => 'My Account',
                'module'     => 'community',
                'controller' => 'account',
                'action'     => 'index',
                'resource'   => 'mvc:community.account', // resource
            ],
            [
                'label' => 'Forums',
                'uri'   => 'http://forums.example.com/',
                'class' => 'external', // class,
            ],
        ],
    ],
    [
        'label'      => 'Administration',
        'module'     => 'admin',
        'controller' => 'index',
        'action'     => 'index',
        'resource'   => 'mvc:admin', // resource
        'pages'      => [
            [
                'label'      => 'Write new article',
                'module'     => 'admin',
                'controller' => 'post',
                'action'     => 'write',
            ],
        ],
    ],
];

// Create container from array
$container = new Navigation($pages);

// Store the container in the proxy helper:
$view->plugin('navigation')->setContainer($container);

// ...or simply:
$view->navigation($container);
```

In addition to the container above, the following setup is assumed:

```php
<?php
// module/MyModule/config/module.config.php

return [
    /* ... */
    'router' [
        'routes' => [
            'archive' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/archive/:year',
                    'defaults' => [
                        'module'     => 'company',
                        'controller' => 'news',
                        'action'     => 'archive',
                        'year'       => (int) date('Y') - 1,
                    ],
                    'constraints' => [
                        'year' => '\d+',
                    ],
                ],
            ],
            /* You can have other routes here... */
        ],
    ],
    /* ... */
];
```

```php
<?php
// module/MyModule/Module.php

namespace MyModule;

use Zend\View\HelperPluginManager;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Permissions\Acl\Resource\GenericResource;

class Module
{
    /* ... */
    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                // This will overwrite the native navigation helper
                'navigation' => function(HelperPluginManager $pm) {
                    // Setup ACL:
                    $acl = new Acl();
                    $acl->addRole(new GenericRole('member'));
                    $acl->addRole(new GenericRole('admin'));
                    $acl->addResource(new GenericResource('mvc:admin'));
                    $acl->addResource(new GenericResource('mvc:community.account'));
                    $acl->allow('member', 'mvc:community.account');
                    $acl->allow('admin', null);

                    // Get an instance of the proxy helper
                    $navigation = $pm->get('Zend\View\Helper\Navigation');

                    // Store ACL and role in the proxy helper:
                    $navigation->setAcl($acl);
                    $navigation->setRole('member');

                    // Return the new navigation helper instance
                    return $navigation;
                }
            ]
        ];
    }
    /* ... */
}
```
