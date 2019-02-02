# JSON

When creating views that return JSON, it's important to also set the appropriate
response header.  The JSON view helper does exactly that. In addition, by
default, it disables layouts (if currently enabled), as layouts generally aren't
used with JSON responses.

The JSON helper sets the following header:

```http
Content-Type: application/json
```

Most XmlHttpRequest libraries look for this header when parsing responses to
determine how to handle the content.

## Basic Usage

```php
<?= $this->json($this->data) ?>
```

> ### Enabling encoding using Zend\Json\Expr
>
> The JSON helper accepts an array of options that will be passed to `Zend\Json\Json::encode()` and
> used internally to encode data.
> `Zend\Json\Json::encode` allows the encoding of native JSON expressions using `Zend\Json\Expr`
> objects. This option is disabled by default. To enable this option, pass a boolean `true` to the
> `enableJsonExprFinder` key of the options array:
>
> ```php
> <?= $this->json($this->data, ['enableJsonExprFinder' => true]) ?>
> ```
