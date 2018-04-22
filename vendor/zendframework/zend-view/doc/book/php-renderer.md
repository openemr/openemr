# The PhpRenderer

`Zend\View\Renderer\PhpRenderer` "renders" view scripts written in PHP,
capturing and returning the output. It composes Variable containers and/or View
Models, a helper plugin manager for [helpers](helpers/intro.md), and optional
filtering of the captured output.

The `PhpRenderer` is template-system agnostic; you may use PHP as your template
language, or create instances of other template systems and manipulate them
within your view script. Anything you can do with PHP is available to you.

## Usage

Basic usage consists of instantiating or otherwise obtaining an instance of the
`PhpRenderer`, providing it with a resolver which will resolve templates to PHP
view scripts, and then calling its `render()` method.

Instantiating a renderer:

```php
use Zend\View\Renderer\PhpRenderer;

$renderer = new PhpRenderer();
```

zend-view ships with several types of "resolvers", which are used to resolve a
template name to a resource a renderer can consume. The ones we will usually use
with the `PhpRenderer` are:

- `Zend\View\Resolver\TemplateMapResolver`, which simply maps template names
  directly to view scripts.
- `Zend\View\Resolver\TemplatePathStack`, which creates a LIFO stack of script
  directories in which to search for a view script. By default, it appends the
  suffix `.phtml` to the requested template name, and then loops through the
  script directories; if it finds a file matching the requested template, it
  returns the full file path.
- `Zend\View\Resolver\RelativeFallbackResolver`, which allows using short
  template name into partial rendering. It is used as wrapper for each of two
  aforesaid resolvers. For example, this allows usage of partial template paths
  such as `my/module/script/path/my-view/some/partial.phtml`, while rendering
  template `my/module/script/path/my-view` by short name `some/partial`.
- `Zend\View\Resolver\AggregateResolver`, which allows attaching a FIFO queue of
  resolvers to consult.

We suggest using the `AggregateResolver`, as it allows you to create a
multi-tiered strategy for resolving template names.

Programmatically, you would then do something like this:

```php
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;

$renderer = new PhpRenderer();

$resolver = new Resolver\AggregateResolver();

$renderer->setResolver($resolver);

$map = new Resolver\TemplateMapResolver([
    'layout'      => __DIR__ . '/view/layout.phtml',
    'index/index' => __DIR__ . '/view/index/index.phtml',
]);
$stack = new Resolver\TemplatePathStack([
    'script_paths' => [
        __DIR__ . '/view',
        $someOtherPath
    ],
]);

// Attach resolvers to the aggregate:
$resolver
    ->attach($map)    // this will be consulted first, and is the fastest lookup
    ->attach($stack)  // filesystem-based lookup
    ->attach(new Resolver\RelativeFallbackResolver($map)) // allow short template names
    ->attach(new Resolver\RelativeFallbackResolver($stack));
```

You can also specify a specific priority value when registering resolvers, with
high, positive integers getting higher priority, and low, negative integers
getting low priority, when resolving.

If you are started your application via the [skeleton
application](https://github.com/zendframework/ZendSkeletonApplication), you can
provide the above via configuration:

```php
// In the Application module configuration
// (module/Application/config/module.config.php):
return [
    'view_manager' => [
        'template_map' => [
            'layout'      => __DIR__ . '/../view/layout.phtml',
            'index/index' => __DIR__ . '/../view/index/index.phtml',
        ],
        'template_path_stack' => [
            'application' => __DIR__ . '/../view',
        ],
    ],
];
```

If you did not begin with the skeleton application, you will need to write your
own factories for creating each resolver and wiring them to the
`AggregateResolver` and injecting into the `PhpRenderer`.

Now that we have our `PhpRenderer` instance, and it can find templates, let's
inject some variables. This can be done in 4 different ways.

- Pass an associative array (or `ArrayAccess` instance, or `Zend\View\Variables`
  instance) of items as the second argument to `render()`:
  `$renderer->render($templateName, array('foo' =&gt; 'bar))`
- Assign a `Zend\View\Variables` instance, associative array, or `ArrayAccess`
  instance to the `setVars()` method.
- Assign variables as instance properties of the renderer: `$renderer->foo =
  'bar'`. This essentially proxies to an instance of `Variables` composed
  internally in the renderer by default.
- Create a `ViewModel` instance, assign variables to that, and pass the
  `ViewModel` to the `render()` method:

As an example of the latter:

```php
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

$renderer = new PhpRenderer();

$model    = new ViewModel();
$model->setVariable('foo', 'bar');
// or
$model = new ViewModel(['foo' => 'bar']);

$model->setTemplate($templateName);
$renderer->render($model);
```

Now, let's render something. As an example, let us say you have a list of
book data.

```php
// use a model to get the data for book authors and titles.
$data = [
    [
        'author' => 'Hernando de Soto',
        'title' => 'The Mystery of Capitalism',
    ],
    [
        'author' => 'Henry Hazlitt',
        'title' => 'Economics in One Lesson',
    ],
    [
        'author' => 'Milton Friedman',
        'title' => 'Free to Choose',
    ],
];

// now assign the book data to a renderer instance
$renderer->books = $data;

// and render the template "booklist"
echo $renderer->render('booklist');
```

More often than not, you'll likely be using the MVC layer. As such, you should
be thinking in terms of view models. Let's consider the following code from
within an action method of a controller.

```php
namespace Bookstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class BookController extends AbstractActionController
{
    public function listAction()
    {
        // do some work...

        // Assume $data is the list of books from the previous example
        $model = new ViewModel(['books' => $data]);

        // Optionally specify a template; if we don't, by default it will be
        // auto-determined based on the module name, controller name and this action.
        // In this example, the template would resolve to "bookstore/book/list",
        // and thus the file "bookstore/book/list.phtml"; the following overrides
        // that to set the template to "booklist", and thus the file "booklist.phtml"
        // (note the lack of directory preceding the filename).
        $model->setTemplate('booklist');

        return $model
    }
}
```

This will then be rendered as if the following were executed:

```php
$renderer->render($model);
```

Now we need the associated view script. At this point, we'll assume that the
template `booklist` resolves to the file `booklist.phtml`. This is a PHP script
like any other, with one exception: it executes inside the scope of the
`PhpRenderer` instance, which means that references to `$this` point to the
`PhpRenderer` instance properties and methods. Thus, a very basic view script
could look like this:

```php
<?php if ($this->books): ?>

    <!-- A table of some books. -->
    <table>
        <tr>
            <th>Author</th>
            <th>Title</th>
        </tr>

        <?php foreach ($this->books as $key => $val): ?>
        <tr>
            <td><?= $this->escapeHtml($val['author']) ?></td>
            <td><?= $this->escapeHtml($val['title']) ?></td>
        </tr>
        <?php endforeach; ?>

    </table>

<?php else: ?>

    <p>There are no books to display.</p>

<?php endif;?>
```

> ### Escape Output
>
> The security mantra is "Filter input, escape output." If you are unsure of the
> source of a given variable &mdash; which is likely most of the time &mdash;
> you should escape it based on which HTML context it is being injected into.
> The primary contexts to be aware of are HTML Body, HTML Attribute, Javascript,
> CSS and URI. Each context has a dedicated helper available to apply the
> escaping strategy most appropriate to each context. You should be aware that
> escaping does vary significantly between contexts; there is no one single
> escaping strategy that can be globally applied.  In the example above, there
> are calls to an `escapeHtml()` method. The method is actually
> [a helper](helpers/intro.md), a plugin available via method overloading.
> Additional escape helpers provide the `escapeHtmlAttr()`, `escapeJs()`,
> `escapeCss()`, and `escapeUrl()` methods for each of the HTML contexts you are
> most likely to encounter. By using the provided helpers and being aware of
> your variables' contexts, you will prevent your templates from running afoul
> of [Cross-Site Scripting (XSS)](http://en.wikipedia.org/wiki/Cross-site_scripting)
> vulnerabilities.

We've now toured the basic usage of the `PhpRenderer`. By now you should know
how to instantiate the renderer, provide it with a resolver, assign variables
and/or create view models, create view scripts, and render view scripts.

## Options and Configuration

`Zend\View\Renderer\PhpRenderer` utilizes several collaborators in order to do
its work. Use the following methods to configure the renderer.

Unless otherwise noted, class names are relative to the `Zend\View` namespace.

### setHelperPluginManager

```php
setHelperPluginManager(string|HelperPluginManager $helpers): void
```

Set the helper plugin manager instance used to load, register, and retrieve
[helpers](helpers/intro.md).

### setResolver

```php
setResolver(Resolver\\ResolverInterface $resolver) : void
```

Set the resolver instance.

### setFilterChain

```php
setFilterChain(\Zend\Filter\FilterChain $filters) : void
```

Set a filter chain to use as an output filter on rendered content.

### setVars

```php
setVars(array|\ArrayAccess|Variables $variables) : void
```

Set the variables to use when rendering a view script/template.

### setCanRenderTrees

```php
setCanRenderTrees(boolean $canRenderTrees) : void
```

Set the flag indicating whether or not we should render trees of view models. If
set to true, the `Zend\View\View` instance will not attempt to render children
separately, but instead pass the root view model directly to the `PhpRenderer`.
It is then up to the developer to render the children from within the view
script. This is typically done using the `RenderChildModel` helper:
`$this->renderChildModel('child_name')`.

## Additional Methods

Typically, you'll only ever access variables and [helpers](helpers/intro.md)
within your view scripts or when interacting with the `PhpRenderer`. However,
there are a few additional methods you may be interested in.

Unless otherwise noted, class names are relative to the `Zend\View` namespace.

### render

```php
render(
    string|Model\ModelInterface $nameOrModel,
    array|\Traversable $values = null
) : string
```

Render a template/view model.

If `$nameOrModel` is a string, it is assumed to be a template name. That
template will be resolved using the current resolver, and then rendered.

If `$values` is non-null, those values, and those values only, will be used
during rendering, and will replace whatever variable container previously was in
the renderer; however, the previous variable container will be reset when done.

If `$values` is empty, the current variables container (see [setVars()](#setvars))
will be injected when rendering.

If `$nameOrModel` is a `ModelInterface` instance, the template name will be
retrieved from it and used.  Additionally, if the model contains any variables,
these will be used when rendering; otherwise, the variables container already
present, if any, will be used.

The method returns the script output.

### resolver

```php
resolver() : Resolver\ResolverInterface
```

Retrieves the current `Resolver` instance.

### vars

```php
vars(string $key = null) : mixed
```

Retrieve a single variable from the container if a key is provided; otherwise it
will return the variables container.

### plugin

```php
plugin(string $name, array $options = null) : Helper\HelperInterface
```

Retrieve a plugin/helper instance. Proxies to the plugin manager's `get()`
method; as such, any `$options` you pass will be passed to the plugin's
constructor if this is the first time the plugin has been retrieved. See the
section on [helpers](helpers/intro.md) for more information.

### addTemplate

```php
addTemplate(string $template) : void
```

Add a template to the stack. When used, the next call to `render()` will loop
through all templates added using this method, rendering them one by one; the
output of the last will be returned.
