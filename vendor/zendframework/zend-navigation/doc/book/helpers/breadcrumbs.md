# Breadcrumbs

Breadcrumbs are used for indicating where in a sitemap a user is currently browsing, and are
typically rendered like the following:

```text
You are here: Home > Products > FantasticProduct 1.0
```

The `breadcrumbs()` helper follows the [Breadcrumbs Pattern](http://developer.yahoo.com/ypatterns/pattern.php?pattern=breadcrumbs)
as outlined in the Yahoo! Design Pattern Library, and allows simple
customization (minimum/maximum depth, indentation, separator, and whether the
last element should be linked), or rendering using a partial view script.

The Breadcrumbs helper finds the deepest active page in a navigation container,
and renders an upwards path to the root. For MVC pages, the "activeness" of a
page is determined by inspecting the request object, as stated in the section on
[MVC pages](../pages.md#mvc-pages).

The helper sets the `minDepth` property to 1 by default, meaning breadcrumbs
will not be rendered if the deepest active page is a root page. If `maxDepth` is
specified, the helper will stop rendering when at the specified depth (e.g. stop
at level 2 even if the deepest active page is on level 3).

Methods in the breadcrumbs helper:

Method signature                           | Description
------------------------------------------ | -----------
`getSeparator() : string`                  | Retrieves the separator string to use between breadcrumbs; default is ` &gt; `.
`setSeparator(string $separator) : self`   | Set the separator string to use between breadcrumbs.
`getLinkLast() : bool`                     | Retrieve the flag indicating whether the last breadcrumb should be rendered as an anchor; defaults to `false`.
`setLinkLast(bool $flag) : self`           | Set the flag indicating whether the last breadcrumb should be rendered as an anchor.
`getPartial() : string|array`              | Retrieve a partial view script that should be used for rendering breadcrumbs. If a partial view script is set, the helper's `render()` method will use the `renderPartial()` method. The helper expects the partial to be a `string` or an `array` with two elements. If the partial is a `string`, it denotes the name of the partial script to use. If it is an `array`, the first element will be used as the name of the partial view script, and the second element is the module where the script is found.
`setPartial(string|array $partial) : self` | Set the partial view script to use when rendering breadcrumbs; see `getPartial()` for acceptable values.
`renderStraight()`                         | The default render method used when no partial view script is present.
`renderPartial()`                          | Used for rendering using a partial view script.

## Basic usage

This example shows how to render breadcrumbs with default settings.

In a view script or layout:

```php
<?= $this->navigation()->breadcrumbs(); ?>
```

The call above takes advantage of the magic `__toString()` method, and is
equivalent to:

```php
<?= $this->navigation()->breadcrumbs()->render(); ?>
```

Output:

```html
<a href="/products">Products</a> &gt; <a href="/products/server">Foo Server</a> &gt; FAQ
```

## Specifying indentation

This example shows how to render breadcrumbs with initial indentation.

Rendering with 8 spaces indentation:

```php
<?= $this->navigation()->breadcrumbs()->setIndent(8) ?>
```

Output:

```html
        <a href="/products">Products</a> &gt; <a href="/products/server">Foo Server</a> &gt; FAQ
```

## Customize output

This example shows how to customize breadcrumbs output by specifying multiple options.

In a view script or layout:

```php
<?= $this->navigation()->breadcrumbs()
    ->setLinkLast(true)                   // link last page
    ->setMaxDepth(1)                      // stop at level 1
    ->setSeparator(' ▶' . PHP_EOL);       // cool separator with newline
?>
```

Output:

```html
<a href="/products">Products</a> ▶
<a href="/products/server">Foo Server</a>
```

Setting minimum depth required to render breadcrumbs:

```php
<?= $this->navigation()->breadcrumbs()->setMinDepth(10) ?>
```

Output: Nothing, because the deepest active page is not at level 10 or deeper.

## Rendering using a partial view script

This example shows how to render customized breadcrumbs using a partial vew
script. By calling `setPartial()`, you can specify a partial view script that
will be used when calling `render()`.  When a partial is specified, the
`renderPartial()` method will be called when emitting the breadcrumbs. This
method will find the deepest active page and pass an array of pages that leads
to the active page to the partial view script.

In a layout:

```php
echo $this->navigation()->breadcrumbs()
    ->setPartial('my-module/partials/breadcrumbs');
```

Contents of `module/MyModule/view/my-module/partials/breadcrumbs.phtml`:

```php
<?= implode(', ', array_map(function ($a) {
  return $a->getLabel();
}, $this->pages)); ?>
```

Output:

```html
Products, Foo Server, FAQ
```
