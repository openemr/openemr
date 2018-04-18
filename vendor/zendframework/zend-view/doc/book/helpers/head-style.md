# HeadStyle

The HTML `<style>` element is used to include CSS stylesheets inline in the HTML
`<head>` element.

> ### Use HeadLink to link CSS files
>
> [HeadLink](head-link.md) should be used to create `<link>` elements for
> including external stylesheets. `HeadStyle` is used when you wish to define
> your stylesheets inline.

The `HeadStyle` helper supports the following methods for setting and adding stylesheet
declarations:

- `appendStyle($content, $attributes = [])`
- `offsetSetStyle($index, $content, $attributes = [])`
- `prependStyle($content, $attributes = [])`
- `setStyle($content, $attributes = [])`

In all cases, `$content` is the actual CSS declarations. `$attributes` are any
additional attributes you wish to provide to the `style` tag: lang, title,
media, or dir are all permissible.

> ### Setting Conditional Comments
>
> `HeadStyle` allows you to wrap the style tag in conditional comments, which
> allows you to hide it from specific browsers. To add the conditional tags,
> pass the conditional value as part of the `$attributes` parameter in the
> method calls.
>
> ```php
> // adding comments
> $this->headStyle()->appendStyle($styles, ['conditional' => 'lt IE 7']);
> ```

`HeadStyle` also allows capturing style declarations; this can be useful if you
want to create the declarations programmatically, and then place them elsewhere.
The usage for this will be showed in an example below.

Finally, you can also use the `headStyle()` method to quickly add declarations
elements; the signature for this is `headStyle($content = null, $placement =
'APPEND', $attributes = array())`.  `$placement` should be either `APPEND`,
`PREPEND`, or `SET`.

`HeadStyle` overrides each of `append()`, `offsetSet()`, `prepend()`, and
`set()` to enforce usage of the special methods as listed above. Internally, it
stores each item as a `stdClass` token, which it later serializes using the
`itemToString()` method. This allows you to perform checks on the items in the
stack, and optionally modify these items by modifying the object returned.

The `HeadStyle` helper is a concrete implementation of the
[Placeholder helper](placeholder.md).

> ### UTF-8 encoding used by default
>
> By default, zend-view uses *UTF-8* as its default encoding.  If you want to
> use another encoding with `headStyle`, you must:
>
> 1. Create a custom renderer and implement a `getEncoding()` method;
> 2. Create a custom rendering strategy that will return an instance of your custom renderer;
> 3. Attach the custom strategy in the `ViewEvent`.
>
> First we have to write the custom renderer:
> 
> ```php
> // module/MyModule/View/Renderer/MyRenderer.php
> namespace MyModule\View\Renderer;
> 
> // Since we just want to implement the getEncoding() method, we can extend the Zend native renderer
> use Zend\View\Renderer\PhpRenderer;
> 
> class MyRenderer extends PhpRenderer
> {
>    /**
>     * @var string
>     */
>    protected $encoding;
> 
>    /**
>     * Constructor
>     *
>     * @param  string $encoding The encoding to be used
>     */
>    public function __construct($encoding)
>    {
>       parent::__construct();
>       $this->encoding = $encoding;
>    }
> 
>    /**
>     * Sets the encoding
>     *
>     * @param string $encoding The encoding to be used
>     */
>    public function setEncoding($encoding)
>    {
>       $this->encoding = $encoding;
>    }
> 
>    /**
>     * Gets the encoding
>     *
>     * @return string The encoding being used
>     */
>    public function getEncoding()
>    {
>       return $this->encoding;
>    }
> }
> ```
> 
> Now we make some configuration in the module class:
> 
> ```php
> // module/MyModule.php
> namespace MyModule;
> 
> use MyModule\View\Renderer\MyRenderer;
> use Zend\Mvc\MvcEvent;
> use Zend\View\Strategy\PhpRendererStrategy;
> 
> class Module
> {
>    public function getConfig(){/* ... */}
> 
>    public function getAutoloaderConfig(){/* ... */}
> 
>    public function getServiceConfig()
>    {
>       return [
>          'factories' => [
>             // Register our custom renderer in the container
>             'MyCustomRenderer' => function ($container) {
>                return new MyRenderer('ISO-8859-1');
>             },
>             'MyCustomStrategy' => function ($container) {
>                // As stated before, we just want to implement the
>                // getEncoding() method, so we can use the base PhpRendererStrategy
>                // and provide our custom renderer to it.
>                $myRenderer = $container->get('MyCustomRenderer');
>                return new PhpRendererStrategy($myRenderer);
>             },
>          ],
>       ];
>    }
> 
>    public function onBootstrap(MvcEvent $e)
>    {
>       // Register a render event
>       $app = $e->getParam('application');
>       $app->getEventManager()->attach('render', [$this, 'registerMyStrategy'], 100);
>    }
> 
>     public function registerMyStrategy(MvcEvent $e)
>     {
>         $app        = $e->getTarget();
>         $locator    = $app->getServiceManager();
>         $view       = $locator->get('Zend\View\View');
>         $myStrategy = $locator->get('MyCustomStrategy');
> 
>         // Attach strategy, which is a listener aggregate, at high priority
>         $view->getEventManager()->attach($myStrategy, 100);
>     }
> }
> ```
> 
> See the quick start [Creating and Registering Alternate Rendering and Response Strategies](../quick-start.md#creating-and-registering-alternate-rendering-and-response-strategies)
> chapter for more information on how to create and register custom strategies
> to your view.

## Basic Usage

You may specify a new style tag at any time:

```php
// adding styles
$this->headStyle()->appendStyle($styles);
```

Order is very important with CSS; you may need to ensure that declarations are
loaded in a specific order due to the order of the cascade; use the various
`append`, `prepend`, and `offsetSet` directives to aid in this task:

```php
// Putting styles in order

// place at a particular offset:
$this->headStyle()->offsetSetStyle(100, $customStyles);

// place at end:
$this->headStyle()->appendStyle($finalStyles);

// place at beginning
$this->headStyle()->prependStyle($firstStyles);
```

When you're finally ready to output all style declarations in your layout
script, echo the helper:

```php
<?= $this->headStyle() ?>
```

## Capturing Style Declarations

Sometimes you need to generate CSS style declarations programmatically. While
you could use string concatenation, heredocs, and the like, often it's easier
just to do so by creating the styles and sprinkling in PHP tags. `HeadStyle`
lets you do just that, capturing it to the stack:

```php
<?php $this->headStyle()->captureStart() ?>
body {
    background-color: <?= $this->bgColor ?>;
}
<?php $this->headStyle()->captureEnd() ?>
```

The following assumptions are made:

- The style declarations will be appended to the stack. If you wish for them to
  replace the stack or be added to the top, you will need to pass `SET` or
  `PREPEND`, respectively, as the first argument to `captureStart()`.
- If you wish to specify any additional attributes for the `<style>` tag, pass
  them in an array as the second argument to `captureStart()`.
