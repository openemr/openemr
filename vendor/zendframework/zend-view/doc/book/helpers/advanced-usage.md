# Advanced usage of helpers

## Registering Helpers

`Zend\View\Renderer\PhpRenderer` composes a *plugin manager* for managing
helpers, specifically an instance of `Zend\View\HelperPluginManager`, which
extends `Zend\ServiceManager\AbstractPluginManager`, which is itself an
extension of `Zend\ServiceManager\ServiceManager`.  `HelperPluginManager` is a
specialized service manager, so you can register a helper/plugin like any other
service (see the [Service Manager documentation](http://zendframework.github.io/zend-servicemanager/configuring-the-service-manager/)
for more information).

Programmatically, this is done as follows:

```php
use MyModule\View\Helper\LowerCase;

// $view is an instance of PhpRenderer
$pluginManager = $view->getHelperPluginManager();

// Register an alias:
$pluginManager->setAlias('lowercase', LowerCase::class);

// Register a factory:
$pluginManager->setFactory(LowerCase::class, function () {
   $lowercaseHelper = new LowerCase();

   // ...do some configuration or dependency injection...

   return $lowercaseHelper;
});
```

Within an MVC application, you will typically pass a map of plugins to the class
via your configuration.

```php
use MyModule\View\Helper;
use Zend\ServiceManager\Factory\InvokableFactory;

// From within a configuration file
return [
   'view_helpers' => [
        'aliases' => [
            'lowercase' => Helper\LowerCase::class,
            'uppercase' => Helper\UpperCase::class,
        ],
        'factories' => [
            LowerCase::class => InvokableFactory::class,
            UpperCase::class => InvokableFactory::class,
        ],
    ],
];
```

If your module class implements `Zend\ModuleManager\Feature\ViewHelperProviderInterface`,
or just the method `getViewHelperConfig()`, you could also do the following
(it's the same as the previous example).

```php
namespace MyModule;

class Module
{
    public function getViewHelperConfig()
    {
        return [
            'aliases' => [
                'lowercase' => Helper\LowerCase::class,
                'uppercase' => Helper\UpperCase::class,
            ],
            'factories' => [
                LowerCase::class => InvokableFactory::class,
                UpperCase::class => InvokableFactory::class,
            ],
        ];
    }
}
```

The two latter examples can be done in each module that needs to register
helpers with the `PhpRenderer`; however, be aware that another module can
register helpers with the same name, so order of modules can impact which helper
class will actually be registered!

## Writing Custom Helpers

Writing custom helpers is easy. We recommend extending
`Zend\View\Helper\AbstractHelper`, but at the minimum, you need only implement
the `Zend\View\Helper\HelperInterface` interface:

```php
namespace Zend\View\Helper;

use Zend\View\Renderer\RendererInterface as Renderer;

interface HelperInterface
{
    /**
     * Set the View object
     *
     * @param  Renderer $view
     * @return HelperInterface
     */
    public function setView(Renderer $view);

    /**
     * Get the View object
     *
     * @return Renderer
     */
    public function getView();
}
```

If you want your helper to be capable of being invoked as if it were a method call of the
`PhpRenderer`, you should also implement an `__invoke()` method within your helper.

As previously noted, we recommend extending `Zend\View\Helper\AbstractHelper`, as it implements the
methods defined in `HelperInterface`, giving you a headstart in your development.

> ### Invokable helpers
>
> Starting with version 2.7.0, helpers no longer need to be instances of
> `HelperInterface`, but can be *any* PHP callable. We recommend writing helpers
> as invokable classes (classes implementing `__invoke()`.

Once you have defined your helper class, make sure you can autoload it, and then
register it with the [plugin manager](#registering-helpers).

Here is an example helper, which we're titling "SpecialPurpose"

```php
namespace MyModule\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SpecialPurpose extends AbstractHelper
{
    protected $count = 0;

    public function __invoke()
    {
        $this->count++;
        $output = sprintf("I have seen 'The Jerk' %d time(s).", $this->count);
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
}
```

Then assume that we [register it with the plugin manager](#registering-helpers)
by the name "specialpurpose".

Within a view script, you can call the `SpecialPurpose` helper as many times as
you like; it will be instantiated once, and then it persists for the life of
that `PhpRenderer` instance.

```php
// remember, in a view script, $this refers to the Zend\View\Renderer\PhpRenderer instance.
echo $this->specialPurpose();
echo $this->specialPurpose();
echo $this->specialPurpose();
```

The output would look something like this:

```php
I have seen 'The Jerk' 1 time(s).
I have seen 'The Jerk' 2 time(s).
I have seen 'The Jerk' 3 time(s).
```

Sometimes you will need access to the calling `PhpRenderer` object; for
instance, if you need to use the registered encoding, or want to render another
view script as part of your helper. This is why we define the `setView()` and
`getView()` methods. As an example, we could rewrite the `SpecialPurpose` helper
as follows to take advantage of the `EscapeHtml` helper:

```php
namespace MyModule\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SpecialPurpose extends AbstractHelper
{
    protected $count = 0;

    public function __invoke()
    {
        $this->count++;
        $output  = sprintf("I have seen 'The Jerk' %d time(s).", $this->count);
        $escaper = $this->getView()->plugin('escapehtml');
        return $escaper($output);
    }
}
```

> ### Accessing the view or other helpers in callables
>
> As noted earlier, starting in version 2.7.0, you may use any PHP callable as a
> helper. If you do, however, how can you access the renderer or other plugins?
>
> The answer is: dependency injection.
>
> If you write your helper as a class, you can accept dependencies via the
> constructor or other setter methods. Create a factory that pulls those
> dependencies and injects them.
>
> As an example, if we need the `escapeHtml()` helper, we could write our helper
> as follows:
>
> ```php
> namespace MyModule\View\Helper;
> 
> use Zend\View\Helper\EscapeHtml;
> 
> class SpecialPurpose
> {
>     private $count = 0;
> 
>     private $escaper;
> 
>     public function __construct(EscapeHtml $escaper)
>     {
>         $this->escaper = $escaper;
>     }
> 
>     public function __invoke()
>     {
>         $this->count++;
>         $output  = sprintf("I have seen 'The Jerk' %d time(s).", $this->count);
>         $escaper = $this->escaper;
>         return $escaper($output);
>     }
> }
> ```
> 
> Then we would write a factory like the following:
> 
> ```php
> use Zend\ServiceManager\AbstractPluginManager;
> 
> class SpecialPurposeFactory
> {
>     public function __invoke($container)
>     {
>         if (! $container instanceof AbstractPluginManager) {
>             // zend-servicemanager v3. v2 passes the helper manager directly.
>             $container = $container->get('ViewHelperManager');
>         }
> 
>         return new SpecialPurpose($container->get('escapeHtml'));
>     }
> }
> ```
>
> If access to the view were required, we'd pass the `PhpRenderer` service
> instead.

## Registering Concrete Helpers

Sometimes it is convenient to instantiate a view helper, and then register it
with the renderer.  This can be done by injecting it directly into the plugin
manager.

```php
// $view is a PhpRenderer instance

$helper = new MyModule\View\Helper\LowerCase;
// ...do some configuration or dependency injection...

$view->getHelperPluginManager()->setService('lowercase', $helper);
```

The plugin manager will validate the helper/plugin, and if the validation
passes, the helper/plugin will be registered.
