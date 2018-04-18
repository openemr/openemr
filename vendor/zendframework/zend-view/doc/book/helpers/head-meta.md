# HeadMeta

The HTML `<meta>` element is used to provide meta information about your HTML
document, typically keywords, document character set, caching pragmas, etc. Meta
tags may be either of the `http-equiv` or `name` types, must contain a `content`
attribute, and can also have either of the `lang` or `scheme` modifier
attributes.

The `HeadMeta` helper supports the following methods for setting and adding meta tags:

- `appendName($keyValue, $content, $conditionalName)`
- `offsetSetName($index, $keyValue, $content, $conditionalName)`
- `prependName($keyValue, $content, $conditionalName)`
- `setName($keyValue, $content, $modifiers)`
- `appendHttpEquiv($keyValue, $content, $conditionalHttpEquiv)`
- `offsetSetHttpEquiv($index, $keyValue, $content, $conditionalHttpEquiv)`
- `prependHttpEquiv($keyValue, $content, $conditionalHttpEquiv)`
- `setHttpEquiv($keyValue, $content, $modifiers)`
- `setCharset($charset)`

The following methods are also supported with `XHTML1_RDFA` doctype set with the
[Doctype helper](doctype.md).

- `appendProperty($property, $content, $modifiers)`
- `offsetSetProperty($index, $property, $content, $modifiers)`
- `prependProperty($property, $content, $modifiers)`
- `setProperty($property, $content, $modifiers)`

The `$keyValue` item is used to define a value for the `name` or `http-equiv`
key; `$content` is the value for the 'content' key, and `$modifiers` is an
optional associative array that can contain keys for `lang` and/or `scheme`.

You may also set meta tags using the `headMeta()` helper method, which has the
following signature: `headMeta($content, $keyValue, $keyType = 'name',
$modifiers = array(), $placement = 'APPEND')`.  `$keyValue` is the content for
the key specified in `$keyType`, which should be either `name` or `http-equiv`.
`$keyType` may also be specified as `property` if the doctype has been set to
`XHTML1_RDFA`. `$placement` can be `SET` (overwrites all previously stored
values), `APPEND` (added to end of stack), or `PREPEND` (added to top of stack).

`HeadMeta` overrides each of `append()`, `offsetSet()`, `prepend()`, and `set()`
to enforce usage of the special methods as listed above. Internally, it stores
each item as a `stdClass` token, which it later serializes using the
`itemToString()` method. This allows you to perform checks on the items in the
stack, and optionally modify these items by simply modifying the object
returned.

The `HeadMeta` helper is a concrete implementation of the
[Placeholder helper](placeholder.md).

## Basic Usage

You may specify a new meta tag at any time. Typically, you will specify
client-side caching rules or SEO keywords.

For instance, if you wish to specify SEO keywords, you'd be creating a meta name
tag with the name `keywords` and the content the keywords you wish to associate
with your page:

```php
// setting meta keywords
$this->headMeta()->appendName('keywords', 'framework, PHP, productivity');
```

If you wished to set some client-side caching rules, you'd set `http-equiv` tags
with the rules you wish to enforce:

```php
// disabling client-side cache
$this->headMeta()
    ->appendHttpEquiv('expires', 'Wed, 26 Feb 1997 08:21:57 GMT')
    ->appendHttpEquiv('pragma', 'no-cache')
    ->appendHttpEquiv('Cache-Control', 'no-cache');
```

Another popular use for meta tags is setting the content type, character set,
and language:

```php
// setting content type and character set
$this->headMeta()
    ->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8')
    ->appendHttpEquiv('Content-Language', 'en-US');
```

If you are serving an HTML5 document, you should provide the character set like
this:

```php
// setting character set in HTML5
$this->headMeta()->setCharset('UTF-8'); // Will look like <meta charset="UTF-8">
```

As a final example, an easy way to display a transitional message before a
redirect is using a "meta refresh":

```php
// setting a meta refresh for 3 seconds to a new url:
$this->headMeta()
    ->appendHttpEquiv('Refresh', '3;URL=http://www.some.org/some.html');
```

When you're ready to place your meta tags in the layout, echo the helper:

```php
<?= $this->headMeta() ?>
```

## Usage with XHTML1\_RDFA doctype

Enabling the RDFa doctype with the [Doctype helper](doctype.md) enables the use
of the `property` attribute (in addition to the standard `name` and
`http-equiv`) with `HeadMeta`.  This is commonly used with the Facebook [Open
Graph Protocol](http://opengraphprotocol.org/).

For instance, you may specify an open graph page title and type as follows:

```php
$this->doctype(Zend\View\Helper\Doctype::XHTML1_RDFA);
$this->headMeta()->setProperty('og:title', 'my article title');
$this->headMeta()->setProperty('og:type', 'article');
echo $this->headMeta();

// output is:
//   <meta property="og:title" content="my article title" />
//   <meta property="og:type" content="article" />
```
