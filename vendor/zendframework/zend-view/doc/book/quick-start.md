# Quick Start

zend-view provides the "View" layer of Zend Framework 2's MVC system. It is a
multi-tiered system allowing a variety of mechanisms for extension,
substitution, and more.

The components of the view layer are as follows:

- **Variables Containers** hold variables and callbacks that you wish to
  represent in the view.  Often-times, a Variables Container will also provide
  mechanisms for context-specific escaping of variables and more.
- **View Models** hold Variables Containers, specify the template to use (if
  any), and optionally provide rendering options (more on that below). View
  Models may be nested in order to represent complex structures.
- **Renderers** take View Models and provide a representation of them to return.
  zend-view ships with three renderers by default: a `PhpRenderer` which
  utilizes PHP templates in order to generate markup, a `JsonRenderer`, and a
  `FeedRenderer` for generating RSS and Atom feeds.
- **Resolvers** utilize Resolver Strategies to resolve a template name to a
  resource a Renderer may consume. As an example, a Resolver may take the name
  "blog/entry" and resolve it to a PHP view script.
- The **View** consists of strategies that map the current Request to a
  Renderer, and strategies for injecting the result of rendering to the
  Response.
- **Rendering Strategies** listen to the `Zend\View\ViewEvent::EVENT_RENDERER`
  event of the View and decide which Renderer should be selected based on the
  Request or other criteria.
- **Response Strategies** are used to inject the Response object with the
  results of rendering. That may also include taking actions such as setting
  Content-Type headers.

Additionally, zend-mvc integrates with zend-view via a number of event listeners
in the `Zend\Mvc\View` namespace.

This section of the manual is designed to show you typical usage patterns of the
view layer when using it with [zend-mvc](https://zendframework.github.io/zend-mvc/).
The assumption is that you are using the [service manager](https://zendframework.github.io/zend-servicemanager/)
and the default MVC view strategies.

## Configuration

The default configuration will typically work out-of-the-box. However, you will
still need to select Resolver Strategies and configure them, as well as
potentially indicate alternate template names for things like the site layout,
404 (not found) pages, and error pages. The code snippets below can be added to
your configuration to accomplish this. We recommend adding it to a
site-specific module, such as the "Application" module from the framework's
[ZendSkeletonApplication](https://github.com/zendframework/ZendSkeletonApplication),
or to one of your autoloaded configurations within the `config/autoload/`
directory.

```php
return [
    'view_manager' => [
        // The TemplateMapResolver allows you to directly map template names
        // to specific templates. The following map would provide locations
        // for a home page template ("application/index/index"), as well as for
        // the layout ("layout/layout"), error pages ("error/index"), and
        // 404 page ("error/404"), resolving them to view scripts.
        'template_map' => [
            'application/index/index' => __DIR__ .  '/../view/application/index/index.phtml',
            'site/layout'             => __DIR__ . '/../view/layout/layout.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
        ],

        // The TemplatePathStack takes an array of directories. Directories
        // are then searched in LIFO order (it's a stack) for the requested
        // view script. This is a nice solution for rapid application
        // development, but potentially introduces performance expense in
        // production due to the number of static calls necessary.
        //
        // The following adds an entry pointing to the view directory
        // of the current module. Make sure your keys differ between modules
        // to ensure that they are not overwritten -- or simply omit the key!
        'template_path_stack' => [
            'application' => __DIR__ . '/../view',
        ],

        // This will be used as the default suffix for template scripts
        // resolving, it defaults to 'phtml'.
        'default_template_suffix' => 'php',

        // Set the template name for the site's layout.
        //
        // By default, the MVC's default Rendering Strategy uses the
        // template name "layout/layout" for the site's layout.
        // Here, we tell it to use the "site/layout" template,
        // which we mapped via the TemplateMapResolver above.
        'layout' => 'site/layout',

        // By default, the MVC registers an "exception strategy", which is
        // triggered when a requested action raises an exception; it creates
        // a custom view model that wraps the exception, and selects a
        // template. We'll set it to "error/index".
        //
        // Additionally, we'll tell it that we want to display an exception
        // stack trace; you'll likely want to disable this by default.
        'display_exceptions' => true,
        'exception_template' => 'error/index',

       // Another strategy the MVC registers by default is a "route not
       // found" strategy. Basically, this gets triggered if (a) no route
       // matches the current request, (b) the controller specified in the
       // route match cannot be found in the service locator, (c) the controller
       // specified in the route match does not implement the DispatchableInterface
       // interface, or (d) if a response from a controller sets the
       // response status to 404.
       //
       // The default template used in such situations is "error", just
       // like the exception strategy. Here, we tell it to use the "error/404"
       // template (which we mapped via the TemplateMapResolver, above).
       //
       // You can opt in to inject the reason for a 404 situation; see the
       // various `Application\:\:ERROR_*`_ constants for a list of values.
       // Additionally, a number of 404 situations derive from exceptions
       // raised during routing or dispatching. You can opt-in to display
       // these.
       'display_not_found_reason' => true,
       'not_found_template'       => 'error/404',
    ],
];
```

## Controllers and View Models

`Zend\View\View` consumes `ViewModel`s, passing them to the selected renderer.
Where do you create these, though?

The most explicit way is to create them in your controllers and return them.

```php
namespace Foo\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class BazBatController extends AbstractActionController
{
    public function doSomethingCrazyAction()
    {
        $view = new ViewModel([
            'message' => 'Hello world',
        ]);
        $view->setTemplate('foo/baz-bat/do-something-crazy');
        return $view;
    }
}
```

This sets a "message" variable in the View Model, and sets the template name
"foo/baz-bat/do-something-crazy". The View Model is then returned.

In most cases, you'll likely have a template name based on the module namespace,
controller, and action. Considering that, and if you're simply passing some
variables, could this be made simpler?  Definitely.

The MVC registers a couple of listeners for controllers to automate this. The
first will look to see if you returned an associative array from your
controller; if so, it will create a View Model and make this associative array
the Variables Container; this View Model then replaces the
[MvcEvent](http://zendframework.github.io/zend-mvc/mvc-event/)'s result. It will
also look to see if you returned nothing or `null`; if so, it will create a View
Model without any variables attached; this View Model also replaces the
`MvcEvent`'s result.

The second listener checks to see if the `MvcEvent` result is a View Model, and,
if so, if it has a template associated with it. If not, it will inspect the
controller matched during routing to determine the module namespace and the
controller class name, and, if available, it's "action" parameter in order to
create a template name. This will be `module/controller/action`, all normalized
to lowercase, dash-separated words.

As an example, the controller `Foo\Controller\BazBatController` with action
"doSomethingCrazyAction", would be mapped to the template
`foo/baz-bat/do-something-crazy`. As you can see, the words "Controller" and
"Action" are omitted.

In practice, that means our previous example could be re-written as follows:

```php
namespace Foo\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class BazBatController extends AbstractActionController
{
    public function doSomethingCrazyAction()
    {
        return [
            'message' => 'Hello world',
        ];
    }
}
```

The above method will likely work for the majority of use cases. When you need
to specify a different template, explicitly create and return a View Model and
specify the template manually, as in the first example.

## Nesting View Models

The other use case you may have for setting explicit View Models is if you wish
to **nest** them. In other words, you might want to render templates to be
included within the main View you return.

As an example, you may want the View from an action to be one primary section
that includes both an "article" and a couple of sidebars; one of the sidebars
may include content from multiple Views as well:

```php
namespace Content\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ArticleController extends AbstractActionController
{
    public function viewAction()
    {
        // get the article from the persistence layer, etc...

        $view = new ViewModel();

        // this is not needed since it matches "module/controller/action"
        $view->setTemplate('content/article/view');

        $articleView = new ViewModel(['article' => $article]);
        $articleView->setTemplate('content/article');

        $primarySidebarView = new ViewModel();
        $primarySidebarView->setTemplate('content/main-sidebar');

        $secondarySidebarView = new ViewModel();
        $secondarySidebarView->setTemplate('content/secondary-sidebar');

        $sidebarBlockView = new ViewModel();
        $sidebarBlockView->setTemplate('content/block');

        $secondarySidebarView->addChild($sidebarBlockView, 'block');

        $view->addChild($articleView, 'article')
             ->addChild($primarySidebarView, 'sidebar_primary')
             ->addChild($secondarySidebarView, 'sidebar_secondary');

        return $view;
    }
}
```

The above will create and return a View Model specifying the template
`content/article/view`. When the View is rendered, it will render three child
Views, the `$articleView`, `$primarySidebarView`, and `$secondarySidebarView`;
these will be captured to the `$view`'s `article`, `sidebar_primary`, and
`sidebar_secondary` variables, respectively, so that when it renders, you may
include that content. Additionally, the `$secondarySidebarView` will include an
additional View Model, `$sidebarBlockView`, which will be captured to its
`block` view variable.

To better visualize this, let's look at what the final content might look like,
with comments detailing where each nested view model is injected.

Here are the templates, rendered based on a 12-column grid:

```php
<?php // "content/article/view" template ?>
<!-- This is from the $view View Model, and the "content/article/view" template -->
<div class="row content">
    <?= $this->article ?>

    <?= $this->sidebar_primary ?>

    <?= $this->sidebar_secondary ?>
</div>
```

```php
<?php // "content/article" template ?>
    <!-- This is from the $articleView View Model, and the "content/article"
         template -->
    <article class="span8">
        <?= $this->escapeHtml('article') ?>
    </article>
```

```php
<?php // "content/main-sidebar" template ?>
    <!-- This is from the $primarySidebarView View Model, and the
         "content/main-sidebar" template -->
    <div class="span2 sidebar">
        sidebar content...
    </div>
```

```php
<?php // "content/secondary-sidebar template ?>
    <!-- This is from the $secondarySidebarView View Model, and the
         "content/secondary-sidebar" template -->
    <div class="span2 sidebar pull-right">
        <?= $this->block ?>
    </div>
```

```php
<?php // "content/block template ?>
        <!-- This is from the $sidebarBlockView View Model, and the
            "content/block" template -->
        <div class="block">
            block content...
        </div>
```

And here is the aggregate, generated content:

```html
<!-- This is from the $view View Model, and the "content/article/view" template -->
<div class="row content">
    <!-- This is from the $articleView View Model, and the "content/article"
         template -->
    <article class="span8">
        Lorem ipsum ....
    </article>

    <!-- This is from the $primarySidebarView View Model, and the
         "content/main-sidebar" template -->
    <div class="span2 sidebar">
        sidebar content...
    </div>

    <!-- This is from the $secondarySidebarView View Model, and the
         "content/secondary-sidebar" template -->
    <div class="span2 sidebar pull-right">
        <!-- This is from the $sidebarBlockView View Model, and the
            "content/block" template -->
        <div class="block">
            block content...
        </div>
    </div>
</div>
```

You can achieve very complex markup using nested Views, while simultaneously
keeping the details of rendering isolated from the Request/Response lifecycle of
the controller.

## Dealing with Layouts

Most sites enforce a cohesive look-and-feel which we typically call the site's
"layout". It includes the default stylesheets and JavaScript necessary, if any,
as well as the basic markup structure into which all site content will be
injected.

Within zend-mvc, layouts are handled via nesting of View Models ([see the
previous example](#nesting-view-models) for examples of View Model nesting). The
`Zend\Mvc\View\Http\ViewManager` composes a View Model which acts as the "root"
for nested View Models. As such, it should contain the skeleton (or layout)
template for the site. All other content is then rendered and captured to view
variables of this root View Model.

The `ViewManager` sets the layout template as `layout/layout` by default. To
change this, you can add some configuration to the `view_manager` area of your
[configuration](#configuration).

A listener on the controllers, `Zend\Mvc\View\Http\InjectViewModelListener`,
will take a View Model returned from a controller and inject it as a child of
the root (layout) View Model. By default, View Models will capture to the
"content" variable of the root View Model. This means you can do the following
in your layout view script:

```php
<html>
    <head>
        <title><?= $this->headTitle() ?></title>
    </head>
    <body>
        <?= $this->content; ?>
    </body>
</html>
```

If you want to specify a different View variable for which to capture,
explicitly create a view model in your controller, and set its "capture to"
value:

```php
namespace Foo\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class BazBatController extends AbstractActionController
{
    public function doSomethingCrazyAction()
    {
        $view = new ViewModel([
            'message' => 'Hello world',
        ]);

        // Capture to the layout view's "article" variable
        $view->setCaptureTo('article');

        return $view;
    }
}
```

There will be times you don't want to render a layout. For example, you might be
answering an API call which expects JSON or an XML payload, or you might be
answering an XHR request that expects a partial HTML payload. To do this,
explicitly create and return a view model from your controller, and mark it as
"terminal", which will hint to the MVC listener that normally injects the
returned View Model into the layout View Model, to instead replace the layout
view model.

```php
namespace Foo\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class BazBatController extends AbstractActionController
{
    public function doSomethingCrazyAction()
    {
        $view = new ViewModel([
            'message' => 'Hello world',
        ]);

        // Disable layouts; `MvcEvent` will use this View Model instead
        $view->setTerminal(true);

        return $view;
    }
}
```

[When discussing nesting View Models](#nesting-view-models), we detailed a
nested View Model which contained an article and sidebars. Sometimes, you may
want to provide additional View Models to the layout, instead of nesting in the
returned layout. This may be done by using the `layout()` controller plugin,
which returns the root View Model. You can then call the same `addChild()`
method on it as we did in that previous example.

```php
namespace Content\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ArticleController extends AbstractActionController
{
    public function viewAction()
    {
        // get the article from the persistence layer, etc...

        // Get the "layout" view model and inject a sidebar
        $layout = $this->layout();
        $sidebarView = new ViewModel();
        $sidebarView->setTemplate('content/sidebar');
        $layout->addChild($sidebarView, 'sidebar');

        // Create and return a view model for the retrieved article
        $view = new ViewModel(['article' => $article]);
        $view->setTemplate('content/article');
        return $view;
    }
}
```

You could also use this technique to select a different layout, by calling the
`setTemplate()` method of the layout View Model:

```php
//In a controller
namespace Content\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ArticleController extends AbstractActionController
{
    public function viewAction()
    {
        // get the article from the persistence layer, etc...

        // Get the "layout" view model and set an alternate template
        $layout = $this->layout();
        $layout->setTemplate('article/layout');

        // Create and return a view model for the retrieved article
        $view = new ViewModel(['article' => $article]);
        $view->setTemplate('content/article');
        return $view;
    }
}
```

Sometimes, you may want to access the layout from within your actual view
scripts when using the `PhpRenderer`. Reasons might include wanting to change
the layout template, or wanting to either access or inject layout view variables.
Similar to the `layout()` controller plugin, you can use the `layout()` View Helper.
If you provide a string argument to it, you will change the template; if you
provide no arguments, the root layout View Model is returned.

```php
//In a view script

// Change the layout:
$this->layout('alternate/layout'); // OR
$this->layout()->setTemplate('alternate/layout');

// Access a layout variable.
// Since access to the base view model is relatively easy, it becomes a
// reasonable place to store things such as API keys, which other view scripts
// may need.
$layout       = $this->layout();
$disqusApiKey = false;
if (isset($layout->disqusApiKey)) {
    $disqusApiKey = $layout->disqusApiKey;
}

// Set a layout variable
$this->layout()->footer = $this->render('article/footer');
```

Commonly, you may want to alter the layout based on the current **module**. This
requires (a) detecting if the controller matched in routing belongs to this
module, and then (b) changing the template of the View Model.

The place to do these actions is in a listener. It should listen either to the
`route` event at low (negative) priority, or on the `dispatch` event, at any
priority. Typically, you will register this during the bootstrap event.

```php
namespace Content;

use Zend\Mvc\MvcEvent;

class Module
{
    /**
     * @param  MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {
        // Register a dispatch event
        $app = $e->getParam('application');
        $app->getEventManager()->attach('dispatch', [$this, 'setLayout']);
    }

    /**
     * @param  MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function setLayout(MvcEvent $e)
    {
        $matches    = $e->getRouteMatch();
        $controller = $matches->getParam('controller');
        if (false === strpos($controller, __NAMESPACE__)) {
            // not a controller from this module
            return;
        }

        // Set the layout template
        $viewModel = $e->getViewModel();
        $viewModel->setTemplate('content/layout');
    }
}
```

## Creating and Registering Alternate Rendering and Response Strategies

`Zend\View\View` does very little. Its workflow is essentially to martial a
`ViewEvent`, and then trigger two events, `renderer` and `response`. You can
attach "strategies" to these events, using the methods `addRenderingStrategy()`
and `addResponseStrategy()`, respectively. A Rendering Strategy introspects the
Request object (or any other criteria) in order to select a Renderer (or fail to
select one). A Response Strategy determines how to populate the Response based
on the result of rendering.

zend-view ships with three Rendering and Response Strategies that you can use
within your application.

- `Zend\View\Strategy\PhpRendererStrategy`. This strategy is a "catch-all" in
  that it will always return the `Zend\View\Renderer\PhpRenderer` and populate
  the Response body with the results of rendering.
- `Zend\View\Strategy\JsonStrategy`. This strategy will return the
  `Zend\View\Renderer\JsonRenderer`, and populate the Response body with the
  JSON value returned, as well as set a `Content-Type` header with a value of
  `application/json`.
- `Zend\View\Strategy\FeedStrategy`. This strategy will return the 
  `Zend\View\Renderer\FeedRenderer`, setting the feed type to
  either "rss" or "atom", based on what was matched. Its Response strategy will
  populate the Response body with the generated feed, as well as set a
  `Content-Type` header with the appropriate value based on feed type.

By default, only the `PhpRendererStrategy` is registered, meaning you will need
to register the other Strategies yourself if you want to use them. Additionally,
it means that you will likely want to register these at higher priority to
ensure they match before the `PhpRendererStrategy`. As an example, let's
register the `JsonStrategy`:

```php
namespace Application;

use Zend\Mvc\MvcEvent;

class Module
{
    /**
     * @param  MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {
        // Register a "render" event, at high priority (so it executes prior
        // to the view attempting to render)
        $app = $e->getApplication();
        $app->getEventManager()->attach('render', [$this, 'registerJsonStrategy'], 100);
    }

    /**
     * @param  MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function registerJsonStrategy(MvcEvent $e)
    {
        $app          = $e->getTarget();
        $locator      = $app->getServiceManager();
        $view         = $locator->get('Zend\View\View');
        $jsonStrategy = $locator->get('ViewJsonStrategy');

        // Attach strategy, which is a listener aggregate, at high priority
        $view->getEventManager()->attach($jsonStrategy, 100);
    }
}
```

The above will register the `JsonStrategy` with the "render" event, such that it
executes prior to the `PhpRendererStrategy`, and thus ensure that a JSON payload
is created when the controller returns a `JsonModel`.

You could also use the module configuration to add the strategies:
```php
namespace Application;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    /**
     * Returns configuration to merge with application configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            /* ... */
            'view_manager' => [
                /* ... */
                'strategies' => [
                    'ViewJsonStrategy',
                ],
            ],
        ];
    }
}
```

What if you want this to happen only in specific modules, or specific
controllers? One way is similar to the last example in the
[previous section on layouts](#dealing-with-layouts), where we detailed changing
the layout for a specific module:

```php
namespace Application;

use Zend\Mvc\MvcEvent;

class Module
{
    /**
     * @param  MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {
        // Register a render event
        $app = $e->getParam('application');
        $app->getEventManager()->attach('render', [$this, 'registerJsonStrategy'], 100);
    }

    /**
     * @param  MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function registerJsonStrategy(MvcEvent $e)
    {
        $matches    = $e->getRouteMatch();
        $controller = $matches->getParam('controller');
        if (false === strpos($controller, __NAMESPACE__)) {
            // not a controller from this module
            return;
        }

        // Potentially, you could be even more selective at this point, and test
        // for specific controller classes, and even specific actions or request
        // methods.

        // Set the JSON strategy when controllers from this module are selected
        $app          = $e->getTarget();
        $locator      = $app->getServiceManager();
        $view         = $locator->get('Zend\View\View');
        $jsonStrategy = $locator->get('ViewJsonStrategy');

        // Attach strategy, which is a listener aggregate, at high priority
        $view->getEventManager()->attach($jsonStrategy, 100);
    }
}
```

While the above examples detail using the `JsonStrategy`, the same could be done
for the `FeedStrategy`.

If you successfully registered the Strategy you need to use the appropriate `ViewModel`:
```php
namespace Application;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\View\Model\FeedModel;

class MyController extends AbstractActionController
{
    /**
     * Lists the items as HTML
     */
    public function listAction()
    {
        $items = /* ... get items ... */;
        $viewModel = new ViewModel();
        $viewModel->setVariable('items', $items);
        return $viewModel;
    }
    
    /**
     * Lists the items as JSON
     */
    public function listJsonAction()
    {
        $items = /* ... get items ... */;
        $viewModel = new JsonModel();
        $viewModel->setVariable('items', $items);
        return $viewModel;
    }
    
    /**
     * Lists the items as a Feed
     */
    public function listFeedAction()
    {
        $items = /* ... get items ... */;
        $viewModel = new FeedModel();
        $viewModel->setVariable('items', $items);
        return $viewModel;
    }
}
```

Or you could switch the `ViewModel` dynamically based on the "Accept" HTTP Header with the 
[Zend-Mvc-Plugin AcceptableViewModelSelector](http://zendframework.github.io/zend-mvc/plugins/#acceptableviewmodelselector-plugin).
