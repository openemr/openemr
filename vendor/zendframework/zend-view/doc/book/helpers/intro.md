# View Helpers

In your view scripts, you'll perform certain complex functions over and over:
e.g., formatting a date, generating form elements, or displaying action links.
You can use helper, or plugin, classes to perform these behaviors for you.

A helper is a class that implements `Zend\View\Helper\HelperInterface`, which
defines two methods, `setView()`, which accepts a
`Zend\View\Renderer\RendererInterface` instance/implementation, and `getView()`,
used to retrieve that instance.  `Zend\View\Renderer\PhpRenderer` composes a
*plugin manager*, allowing you to retrieve helpers, and also provides some
method overloading capabilities that allow proxying method calls to helpers.

> ### Callable helpers
>
> Starting in version 2.7.0, if your helper does not need access to the view,
> you can also use any PHP callable as a helper, including arbitrary objects
> that implement `__invoke()`.

As an example, let's say we have a helper class named
`MyModule\View\Helper\LowerCase`, which we register in our plugin manager with
the name `lowercase`. We can retrieve it in one of the following ways:

```php
// $view is a PhpRenderer instance

// Via the plugin manager:
$pluginManager = $view->getHelperPluginManager();
$helper        = $pluginManager->get('lowercase');

// Retrieve the helper instance, via the method "plugin",
// which proxies to the plugin manager:
$helper = $view->plugin('lowercase');

// If the helper does not define __invoke(), the following also retrieves it:
$helper = $view->lowercase();

// If the helper DOES define __invoke, you can call the helper
// as if it is a method:
$filtered = $view->lowercase('some value');
```

The last two examples demonstrate how the `PhpRenderer` uses method overloading
to retrieve and/or invoke helpers directly, offering a convenience API for end
users.

A large number of helpers are provided by default with zend-view.  You can also
register helpers by adding them to the plugin manager.

## Included Helpers

Zend Framework comes with an initial set of helper classes. In particular, there
are helpers for creating route-based URLs and HTML lists, as well as declaring
variables. Additionally, there are a rich set of helpers for providing values
for, and rendering, the various HTML `<head>` tags, such as `HeadTitle`,
`HeadLink`, and `HeadScript`. The currently shipped helpers include:

- [Asset](asset.md)
- [BasePath](base-path.md)
- [Cycle](cycle.md)
- [Doctype](doctype.md)
- [FlashMessenger](flash-messenger.md)
- [Gravatar](gravatar.md)
- [HeadLink](head-link.md)
- [HeadMeta](head-meta.md)
- [HeadScript](head-script.md)
- [HeadStyle](head-style.md)
- [HeadTitle](head-title.md)
- [HtmlList](html-list.md)
- [HTML Object Plugins](html-object.md)
- [Identity](identity.md)
- [InlineScript](inline-script.md)
- [JSON](json.md)
- [Partial](partial.md)
- [Placeholder](placeholder.md)
- [Url](url.md)

> ### Help document!
>
> Not all helpers are documented! Some that could use documentation include the
> various escaper helpers, the layout helper, and the `serverUrl` helper. Click
> the "GitHub" octocat link in the top navbar to go to the repository and start
> writing documentation!

> ### i18n helpers
>
> View helpers related to **Internationalization** are documented in the
> [I18n View Helpers](http://zendframework.github.io/zend-i18n/view-helpers/)
> documentation.

> ### Form helpers
>
> View helpers related to **form** are documented in the
> [Form View Helpers](https://zendframework.github.io/zend-form/helper/intro/)
> documentation.

> ### Navigation helpers
>
> View helpers related to **navigation** are documented in the
> [Navigation View Helpers](https://zendframework.github.io/zend-navigation/helpers/intro/)
> documentation.

> ### Pagination helpers
>
> View helpers related to **paginator** are documented in the
> [Paginator Usage](https://zendframework.github.io/zend-paginator/usage/#rendering-pages-with-view-scripts)
> documentation.

> ### Custom helpers
>
> For documentation on writing **custom view helpers** see the
> [Advanced usage](advanced-usage.md) chapter.
