# Asset

The `Asset` helper is used to map asset names to versioned assets.

This can be used to allow using a single, canonical name for an asset within
your view scripts, while having that map to:

- A versioned asset name, used to prevent browser caching.
- A product of a build process (such as a CSS pre-processor, JS compiler, etc.)

## Configuration and Basic Usage

`Zend\View\Helper\Service\AssetFactory` checks the application configuration,
making it possible to set up the resource map through your `module.config.php`
or application configuration. As an example:

```php
'view_helper_config' => [
    'asset' => [
        'resource_map' => [
            'css/style.css' => 'css/style-3a97ff4ee3.css',
            'js/vendor.js' => 'js/vendor-a507086eba.js',
        ],
    ],
],
```

Within your view script, you would reference the asset name:

```php
// Usable in any of your .phtml files:
echo $this->asset('css/style.css');
```

which then emits the following output:

```html
css/style-3a97ff4ee3.css
```

The first argument of the `asset` helper is the regular asset name, which will
be replaced by the associated value defined in the `resource_map` of the
configuration.

> ### Exceptions
>
> When an `asset` key is specified but the `resource_map` is not provided or is not
> an array, the helper will raise a `Zend\View\Exception\RuntimeException`.
>
> When you call the `asset` helper with a parameter not defined in your
> `resource_map`, the helper will raise a `Zend\View\Exception\InvalidArgumentException`.

## Resource map in JSON file

A number of build tools, such as gulp-rev and grunt-rev, will create a JSON
resource map file such as `rev-manifest.json`:

```javascript
{
    "css/style.css": "css/style-3a97ff4ee3.css",
    "js/vendor.js": "js/vendor-a507086eba.js"
}
```

You can incorporate these into your configuration manually by fetching and
decoding the contents:

```php
'view_helper_config' => [
    'asset' => [
        'resource_map' => json_decode(file_get_contents('path/to/rev-manifest.json'), true),
    ],
],
```

If you have enabled configuration caching, these values _will also be cached_,
meaning that the above operation will occur exactly once in your production
configuration.
