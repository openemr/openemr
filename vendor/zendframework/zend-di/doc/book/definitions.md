# Dependency Definitions

Definitions are what zend-di uses to understand the structure of the code it is
attempting to wire. This means that if you've written non-ambiguous, clear and
concise code, zend-di has a very good chance of understanding how to wire things
up without much added complexity.

## DefinitionList

Definitions are introduced to the `Zend\Di\Di` object through a definition list
implemented as `Zend\Di\DefinitionList` (which extends `SplDoublyLinkedList`).
Order is important. Definitions in the front of the list will be consulted on a
class before definitions at the end of the list.

> ### Autoloading
>
> Regardless of what kind of DefinitionList strategy you decide to use, it is
> important that your autoloaders are already setup and ready to use.

## RuntimeDefinition

The default `DefinitionList` instantiated by `Zend\Di\Di` when no other
DefinitionList is provided is `Zend\Di\Definition\RuntimeDefinition`. The
`RuntimeDefinition` will respond to queries about classes by using PHP's
Reflection API. The `RuntimeDefinition` uses any available information inside
methods &mdash; including their signature, the names of parameters, the
type-hints of the parameters, and the default values &mdash; to determine if
something is optional or required when making a call to that method. The more
explicit you can be in your method naming and method signatures, the more likely
`Zend\Di\Definition\RuntimeDefinition` will accurately understand the structure
of your code.

The constructor of `RuntimeDefinition` looks like the following:

```php
public function __construct(
    IntrospectionStrategy $introspectionStrategy = null,
    array $explicitClasses = null
) {
    $this->introspectionStrategy = ($introspectionStrategy) ?: new IntrospectionStrategy();
    if ($explicitClasses) {
        $this->setExplicitClasses($explicitClasses);
    }
}
```

The `IntrospectionStrategy` object is an object that defines the rules by which
the `RuntimeDefinition` will introspect information about your classes. Here are
the things it knows how to do:

- Whether or not to use annotations (scanning and parsing annotations is
  expensive, and thus disabled by default)
- Which method names to include in the introspection; this is a list of
  patterns. By default, it registers the pattern `/^set\[A-Z\]{1}\\w\*/`.
- Which interface names represent the interface injection pattern; this is a
  list of patterns. By default, the pattern `/\\w\*Aware\\w\*/` is registered.

The constructor for the `IntrospectionStrategy` looks like this:

```php
public function __construct(AnnotationManager $annotationManager = null)
{
    $this->annotationManager = ($annotationManager) ?: $this->createDefaultAnnotationManager();
}
```

The `AnnotationManager` is not required. If you wish to create a special
`AnnotationManager` with your own annotations, and also wish to extend the
`RuntimeDefinition` to look for those annotations, this is the place to do it.

The `RuntimeDefinition` also can be used to look up either all classes
(implicitly, which is default), or explicitly look up for particular pre-defined
classes. This is useful when your strategy for inspecting one set of classes
might differ from those of another strategy for another set of classes. This can
be achieved by using the `setExplicitClasses()` method or by passing a list of
classes as the second constructor argument of the `RuntimeDefinition`.

## CompilerDefinition

The `CompilerDefinition` is similar in nature to the `RuntimeDefinition` with the exception
that it can be seeded with more information for the purposes of "compiling" a
definition. Compiled definitions eliminate reflection calls and annotation
scannning, which can be a performance bottleneck in your production
applications.

For example, let's assume we want to create a script that will create
definitions for some of our library code:

```php
// in "package name" format
$components = [
    'My_MovieApp',
    'My_OtherClasses',
];

foreach ($components as $component) {
    $diCompiler = new Zend\Di\Definition\CompilerDefinition;
    $diCompiler->addDirectory('/path/to/classes/' . str_replace('_', '/', $component));

    $diCompiler->compile();
    file_put_contents(
        __DIR__ . '/../data/di/' . $component . '-definition.php',
        '<?php return ' . var_export($diCompiler->toArrayDefinition()->toArray(), true) . ';'
    );
}
```

The above creates a file for each "package", containing the full definition for
the classes defined for each. To utilize this in an application, use the
following:

```php
protected function setupDi(Application $app)
{
    $definitionList = new DefinitionList([
        new Definition\ArrayDefinition(include __DIR__ .  '/path/to/data/di/My_MovieApp-definition.php'),
        new Definition\ArrayDefinition(include __DIR__ .  '/path/to/data/di/My_OtherClasses-definition.php'),
        $runtime = new Definition\RuntimeDefinition(),
    ]);
    $di = new Di($definitionList, null, new Config($this->config->di));
    $di->instanceManager()->addTypePreference('Zend\Di\LocatorInterface', $di);
    $app->setLocator($di);
}
```

The above code would more than likely go inside your application's bootstrap or
within a `Module` class. This represents the simplest and most performant way
of configuring your DiC for usage.

## ClassDefinition

The idea behind using a `ClassDefinition` is two-fold. First, you may want to
override some information inside of a `RuntimeDefinition`. Secondly, you might
want to simply define your complete class's definition with an xml, ini, or php
file describing the structure. This class definition can be fed in via
`Configuration` or by directly instantiating and registering the `Definition`
with the `DefinitionList`.

(@todo - example)
