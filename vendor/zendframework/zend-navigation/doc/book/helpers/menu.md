# Menu

The `menu()` helper is used for rendering menus from navigation containers. By
default, the menu will be rendered using HTML `UL` and `LI` tags, but the helper
also allows using a partial view script.

Methods in the Menu helper:

Method signature                                                                            | Description
------------------------------------------------------------------------------------------- | -----------
`getUlClass() : string`                                                                     | Retrieve the CSS class used when rendering `ul` elements in `renderMenu()`.
`setUlClass(string $class) : self`                                                          | Set the CSS class to use when rendering `ul` elements in `renderMenu()`.
`getOnlyActiveBranch() : bool`                                                              | Retrieve the flag specifing whether or not to render only the active branch of a container.
`setOnlyActiveBranch(bool $flag) : self`                                                    | Set the flag specifing whether or not to render only the active branch of a container.
`getRenderParents() : bool`                                                                 | Retrieve the flag specifying whether or not to render parent pages when rendering the active branch of a container.
`setRenderParents(bool $flag) : self`                                                       | Set the flag specifying whether or not to render parent pages when rendering the active branch of a container. When set to `false`, only the deepest active menu will be rendered.
`getPartial() : string|array`                                                               | Retrieve a partial view script that should be used for rendering breadcrumbs. If a partial view script is set, the helper's `render()` method will use the `renderPartial()` method. The helper expects the partial to be a `string` or an `array` with two elements. If the partial is a `string`, it denotes the name of the partial script to use. If it is an `array`, the first element will be used as the name of the partial view script, and the second element is the module where the script is found.
`setPartial(string|array $partial) : self`                                                  | Set the partial view script to use when rendering breadcrumbs; see `getPartial()` for acceptable values.
`htmlify(/* ... */) : string`                                                               | Overrides the method from the abstract class, with the argument list `AbstractPage $page, bool $escapeLabel = true, bool $addClassToListItem = false`. Returns `span` elements if the page has no `href`.
`renderMenu(AbstractContainer $container = null, $options = []) : string`                   | Default rendering method; renders a container as an HTML `UL` list. If `$container` is not given, the container registered in the helper will be rendered.  `$options` is used for overriding options specified temporarily without resetting the values in the helper instance; if none are set, those already provided to the helper will be used. Options are an associative array where each key corresponds to an option in the helper. See the table below for recognized options.
`renderPartial(AbstractContainer $container = null, string|array $partial = null) : string` | Used for rendering the menu using a partial view script.
`renderSubMenu(/* ... */) : string`                                                         | Renders the deepest menu level of a container's active branch. Accepts the arguments `AbstractContainer $container`, `string $ulClass = null`, `string|int $indent = null` (an integer value indicates number of spaces to use), `string $liActiveClass = null`.

The following are options recognized by the `renderMenu()` method:

Option name        | Description
------------------ | -----------
`indent`           | Indentation. Expects a `string` or an `int` value.
`minDepth`         | Minimum depth. Expects an `int` or `null` (no minimum depth).
`maxDepth`         | Maximum depth. Expects an `int` or `null` (no maximum depth).
`ulClass`          | CSS class for `ul` element. Expects a `string`.
`onlyActiveBranch` | Whether only active branch should be rendered. Expects a `boolean` value.
`renderParents`    | Whether parents should be rendered if only rendering active branch. Expects a `boolean` value.


## Basic usage

This example shows how to render a menu from a container registered/found in the
view helper. Notice how pages are filtered out based on visibility and ACL.

In a view script or layout:

```php
<?= $this->navigation()->menu()->render() ?>
```

Or:

```php
<?= $this->navigation()->menu() ?>
```

Output:

```html
<ul class="navigation">
    <li>
        <a title="Go Home" href="/">Home</a>
    </li>
    <li class="active">
        <a href="/products">Products</a>
        <ul>
            <li class="active">
                <a href="/products/server">Foo Server</a>
                <ul>
                    <li class="active">
                        <a href="/products/server/faq">FAQ</a>
                    </li>
                    <li>
                        <a href="/products/server/editions">Editions</a>
                    </li>
                    <li>
                        <a href="/products/server/requirements">System Requirements</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="/products/studio">Foo Studio</a>
                <ul>
                    <li>
                        <a href="/products/studio/customers">Customer Stories</a>
                    </li>
                    <li>
                        <a href="/products/studio/support">Support</a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
    <li>
        <a title="About us" href="/company/about">Company</a>
        <ul>
            <li>
                <a href="/company/about/investors">Investor Relations</a>
            </li>
            <li>
                <a class="rss" href="/company/news">News</a>
                <ul>
                    <li>
                        <a href="/company/news/press">Press Releases</a>
                    </li>
                    <li>
                        <a href="/archive">Archive</a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
    <li>
        <a href="/community">Community</a>
        <ul>
            <li>
                <a href="/community/account">My Account</a>
            </li>
            <li>
                <a class="external" href="http://forums.example.com/">Forums</a>
            </li>
        </ul>
    </li>
</ul>
```

## Calling renderMenu() directly

This example shows how to render a menu that is not registered in the view
helper by calling `renderMenu()` directly and specifying options.

```php
<?php
// render only the 'Community' menu
$community = $this->navigation()->findOneByLabel('Community');
$options = [
    'indent'  => 16,
    'ulClass' => 'community'
];
echo $this->navigation()
          ->menu()
          ->renderMenu($community, $options);
?>
```

Output:

```html
<ul class="community">
    <li>
        <a href="/community/account">My Account</a>
    </li>
    <li>
        <a class="external" href="http://forums.example.com/">Forums</a>
    </li>
</ul>
```

## Rendering the deepest active menu

This example shows how `renderSubMenu()` will render the deepest sub menu of
the active branch.

Calling `renderSubMenu($container, $ulClass, $indent)` is equivalent to calling
`renderMenu($container, $options)` with the following options:

```php
[
    'ulClass'          => $ulClass,
    'indent'           => $indent,
    'minDepth'         => null,
    'maxDepth'         => null,
    'onlyActiveBranch' => true,
    'renderParents'    => false,
]
```

```php
<?= $this->navigation()
    ->menu()
    ->renderSubMenu(null, 'sidebar', 4) ?>
```

The output will be the same if 'FAQ' or 'Foo Server' is active:

```html
<ul class="sidebar">
    <li class="active">
        <a href="/products/server/faq">FAQ</a>
    </li>
    <li>
        <a href="/products/server/editions">Editions</a>
    </li>
    <li>
        <a href="/products/server/requirements">System Requirements</a>
    </li>
</ul>
```

## Rendering with maximum depth

```php
<?= $this->navigation()
    ->menu()
    ->setMaxDepth(1) ?>
```

Output:

```html
<ul class="navigation">
    <li>
        <a title="Go Home" href="/">Home</a>
    </li>
    <li class="active">
        <a href="/products">Products</a>
        <ul>
            <li class="active">
                <a href="/products/server">Foo Server</a>
            </li>
            <li>
                <a href="/products/studio">Foo Studio</a>
            </li>
        </ul>
    </li>
    <li>
        <a title="About us" href="/company/about">Company</a>
        <ul>
            <li>
                <a href="/company/about/investors">Investor Relations</a>
            </li>
            <li>
                <a class="rss" href="/company/news">News</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="/community">Community</a>
        <ul>
            <li>
                <a href="/community/account">My Account</a>
            </li>
            <li>
                <a class="external" href="http://forums.example.com/">Forums</a>
            </li>
        </ul>
    </li>
</ul>
```

## Rendering with minimum depth

```php
<?= $this->navigation()
    ->menu()
    ->setMinDepth(1) ?>
```

Output:

```html
<ul class="navigation">
    <li class="active">
        <a href="/products/server">Foo Server</a>
        <ul>
            <li class="active">
                <a href="/products/server/faq">FAQ</a>
            </li>
            <li>
                <a href="/products/server/editions">Editions</a>
            </li>
            <li>
                <a href="/products/server/requirements">System Requirements</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="/products/studio">Foo Studio</a>
        <ul>
            <li>
                <a href="/products/studio/customers">Customer Stories</a>
            </li>
            <li>
                <a href="/products/studio/support">Support</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="/company/about/investors">Investor Relations</a>
    </li>
    <li>
        <a class="rss" href="/company/news">News</a>
        <ul>
            <li>
                <a href="/company/news/press">Press Releases</a>
            </li>
            <li>
                <a href="/archive">Archive</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="/community/account">My Account</a>
    </li>
    <li>
        <a class="external" href="http://forums.example.com/">Forums</a>
    </li>
</ul>
```

## Rendering only the active branch

```php
<?= $this->navigation()
    ->menu()
    ->setOnlyActiveBranch(true) ?>
```

Output:

```html
<ul class="navigation">
    <li class="active">
        <a href="/products">Products</a>
        <ul>
            <li class="active">
                <a href="/products/server">Foo Server</a>
                <ul>
                    <li class="active">
                        <a href="/products/server/faq">FAQ</a>
                    </li>
                    <li>
                        <a href="/products/server/editions">Editions</a>
                    </li>
                    <li>
                        <a href="/products/server/requirements">System Requirements</a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
</ul>
```

## Rendering only the active branch with minimum depth

```php
<?= $this->navigation()
    ->menu()
    ->setOnlyActiveBranch(true)
    ->setMinDepth(1) ?>
```

Output:

```html
<ul class="navigation">
    <li class="active">
        <a href="/products/server">Foo Server</a>
        <ul>
            <li class="active">
                <a href="/products/server/faq">FAQ</a>
            </li>
            <li>
                <a href="/products/server/editions">Editions</a>
            </li>
            <li>
                <a href="/products/server/requirements">System Requirements</a>
            </li>
        </ul>
    </li>
</ul>
```

## Rendering only the active branch with maximum depth

```php
<?= $this->navigation()
    ->menu()
    ->setOnlyActiveBranch(true)
    ->setMaxDepth(1) ?>
```

Output:

```html
<ul class="navigation">
    <li class="active">
        <a href="/products">Products</a>
        <ul>
            <li class="active">
                <a href="/products/server">Foo Server</a>
            </li>
            <li>
                <a href="/products/studio">Foo Studio</a>
            </li>
        </ul>
    </li>
</ul>
```

## Rendering only the active branch with maximum depth and no parents

```php
<?= $this->navigation()
    ->menu()
    ->setOnlyActiveBranch(true)
    ->setRenderParents(false)
    ->setMaxDepth(1) ?>
```

Output:

```html
<ul class="navigation">
    <li class="active">
        <a href="/products/server">Foo Server</a>
    </li>
    <li>
        <a href="/products/studio">Foo Studio</a>
    </li>
</ul>
```

## Rendering a custom menu using a partial view script

This example shows how to render a custom menu using a partial view script. By
calling `setPartial()`, you can specify a partial view script that will be used
when calling `render()`; when a partial is specified, that method will proxy to
the `renderPartial()` method.

The `renderPartial()`  method will assign the container to the view with the key
`container`.

In a layout:

```php
$this->navigation()->menu()->setPartial('my-module/partials/menu');
echo $this->navigation()->menu()->render();
```

In `module/MyModule/view/my-module/partials/menu.phtml`:

```php
foreach ($this->container as $page) {
    echo $this->navigation()->menu()->htmlify($page) . PHP_EOL;
}
```

Output:

```html
<a title="Go Home" href="/">Home</a>
<a href="/products">Products</a>
<a title="About us" href="/company/about">Company</a>
<a href="/community">Community</a>
```

### Using additional parameters in partial view scripts

Starting with version 2.6.0, you can assign custom variables to a
partial script.

In a layout:

```php
// Set partial
$this->navigation()->menu()->setPartial('my-module/partials/menu');

// Output menu
echo $this->navigation()->menu()->renderPartialWithParams(
    [
        'headline' => 'Links',
    ]
);
```

In `module/MyModule/view/my-module/partials/menu.phtml`:

```php
<h1><?= $headline ?></h1>

<?php
foreach ($this->container as $page) {
    echo $this->navigation()->menu()->htmlify($page) . PHP_EOL;
}
?>
```

Output:

```html
<h1>Links</h1>
<a title="Go Home" href="/">Home</a>
<a href="/products">Products</a>
<a title="About us" href="/company/about">Company</a>
<a href="/community">Community</a>
```

### Using menu options in partial view scripts

In a layout:

```php
// Set options
$this->navigation()->menu()
    ->setUlClass('my-nav')
    ->setPartial('my-module/partials/menu');

// Output menu
echo $this->navigation()->menu()->render();
```

In `module/MyModule/view/my-module/partials/menu.phtml`:

```php
<div class"<?= $this->navigation()->menu()->getUlClass() ?>">
    <?php
    foreach ($this->container as $page) {
        echo $this->navigation()->menu()->htmlify($page) . PHP_EOL;
    }
    ?>
</div>
```

Output:

```html
<div class="my-nav">
    <a title="Go Home" href="/">Home</a>
    <a href="/products">Products</a>
    <a title="About us" href="/company/about">Company</a>
    <a href="/community">Community</a>
</div>
```

### Using ACLs with partial view scripts

If you want to use an ACL within your partial view script, then you will have to
check the access to a page manually.

In `module/MyModule/view/my-module/partials/menu.phtml`:

```php
foreach ($this->container as $page) {
    if ($this->navigation()->accept($page)) {
        echo $this->navigation()->menu()->htmlify($page) . PHP_EOL;
    }
}
```
