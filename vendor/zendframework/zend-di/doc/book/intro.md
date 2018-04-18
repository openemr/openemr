# Introduction

## Dependency Injection

Dependency Injection (here-in called DI) refers to the act of providing
dependencies for an object during instantiation or via a method call. A basic
example looks like this:

```php
$b = new MovieLister(new MovieFinder());
```

Above, `MovieFinder` is a dependency of `MovieLister`, and `MovieFinder` was
injected into `MovieLister`.

If
you are not familiar with the concept of DI, here are a couple of great reads:

- [Matthew Weier O'Phinney's Analogy](http://weierophinney.net/matthew/archives/260-Dependency-Injection-An-analogy.html)
- [Ralph Schindler's Learning DI](http://ralphschindler.com/2011/05/18/learning-about-dependency-injection-and-php)
- [Fabien Potencier's Series](http://fabien.potencier.org/article/11/what-is-dependency-injection) on DI

> ### zend-servicemanager
>
> `Zend\Di` is an example of an Inversion of Control (IoC) container. IoC containers are widely used
> to create object instances that have all dependencies resolved and injected. Dependency Injection
> containers are one form of IoC, but not the only form.
> 
> Zend Framework ships with another form of IoC as well,
> [zend-servicemanager](https://zendframework.github.io/zend-servicemanager/).
> Unlike zend-di, zend-servicemanager is code-driven, meaning that you tell it
> what class to instantiate, or provide a factory for the given class. This
> approach offers several benefits:
>
> - Easier to debug (error stacks take you into your factories, not the
>   dependency injection container).
> - Easier to setup (write code to instantiate objects, instead of
>   configuration).
> - Faster (zend-di has known performance issues due to the approaches used).
>
> Unless you have specific needs for a dependency injection container versus
> more general Inversion of Control, we recommend using zend-servicemanager for
> the above reasons.

# Dependency Injection Containers

When your code is written in such a way that all your dependencies are injected
into consuming objects, you might find that the simple act of wiring an object
has gotten more complex. When this becomes the case, and you find that this
wiring is creating more boilerplate code, this makes for an excellent
opportunity to utilize a Dependency Injection Container.

In it's simplest form, a Dependency Injection Container (here-in called a DiC
for brevity), is an object that is capable of creating objects on request and
managing the "wiring", or the injection of required dependencies, for those
requested objects. Since the patterns that developers employ in writing DI
capable code vary, DiC's are generally either in the form of smallish objects
that suit a very specific pattern, or larger DiC frameworks.

zend-di is a DiC framework. While for the simplest code there is no
configuration needed, and the use cases are quite simple, zend-di is capable of
being configured to wire these complex use cases
