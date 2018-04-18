# HeadScript

The HTML `<script>` element is used to either provide inline client-side
scripting elements or link to a remote resource containing client-side scripting
code. The `HeadScript` helper allows you to manage both.

The `HeadScript` helper supports the following methods for setting and adding scripts:

- `appendFile($src, $type = 'text/javascript', $attrs = [])`
- `offsetSetFile($index, $src, $type = 'text/javascript', $attrs = [])`
- `prependFile($src, $type = 'text/javascript', $attrs = [])`
- `setFile($src, $type = 'text/javascript', $attrs = [])`
- `appendScript($script, $type = 'text/javascript', $attrs = [])`
- `offsetSetScript($index, $script, $type = 'text/javascript', $attrs = [])`
- `prependScript($script, $type = 'text/javascript', $attrs = [])`
- `setScript($script, $type = 'text/javascript', $attrs = [])`

In the case of the `*File()` methods, `$src` is the remote location of the
script to load; this is usually in the form of a URL or a path. For the
`*Script()` methods, `$script` is the client-side scripting directives you wish
to use in the element.

> ### Setting Conditional Comments
>
> `HeadScript` allows you to wrap the script tag in conditional comments, which
> allows you to hide it from specific browsers. To add the conditional tags,
> pass the conditional value as part of the `$attrs` parameter in the method
> calls.
>
> ```php
> // adding scripts
> $this->headScript()->appendFile(
>     '/js/prototype.js',
>     'text/javascript',
>     ['conditional' => 'lt IE 7']
> );
> ```

> ### Preventing HTML style comments or CDATA wrapping of scripts
>
> By default, `HeadScript` will wrap scripts with HTML comments or it wraps
> scripts with XHTML CDATA.  This behavior can be problematic when you intend to
> use the script tag in an alternative way by setting the type to something
> other then `text/javascript`. To prevent such escaping, pass an `noescape`
> with a value of true as part of the `$attrs` parameter in the method calls.
> 
> ```php
> // jquery template
> $template = '<div class="book"{{:title}}</div';
>
> $this->headScript()->appendScript(
>     $template,
>     'text/x-jquery-tmpl',
>     ['id' => 'tmpl-book', 'noescape' => true]
> );
> ```

`HeadScript` also allows capturing scripts; this can be useful if you want to
create the client-side script programmatically, and then place it elsewhere. The
usage for this will be showed in an example below.

Finally, you can also use the `headScript()` method to quickly add script
elements; the signature for this is `headScript($mode = 'FILE', $spec = null,
$placement = 'APPEND', array $attrs = [], $type = 'text/javascript')`. The
`$mode` is either 'FILE' or 'SCRIPT', depending on if you're linking a script or
defining one. `$spec` is either the script file to link or the script source
itself. `$placement` should be either 'APPEND', 'PREPEND', or 'SET'. `$attrs` is
an array of script attributes. `$type` is the script type attribute.

`HeadScript` overrides each of `append()`, `offsetSet()`, `prepend()`, and
`set()` to enforce usage of the special methods as listed above. Internally, it
stores each item as a `stdClass` token, which it later serializes using the
`itemToString()` method. This allows you to perform checks on the items in the
stack, and optionally modify these items by modifying the object returned.

The `HeadScript` helper is a concrete implementation of the
[Placeholder helper](placeholder.md).

> ### Use InlineScript for HTML Body Scripts
>
> `HeadScript`'s sibling helper, [InlineScript](inline-script.md), should be
> used when you wish to include scripts inline in the HTML **body**. Placing
> scripts at the end of your document is a good practice for speeding up
> delivery of your page, particularly when using 3rd party analytics scripts.

> ### Arbitrary Attributes are Disabled by Default
>
> By default, `HeadScript` only will render `<script>` attributes that are blessed by the W3C.
> These include `id`, `charset`, `crossorigin`, `defer`, `integrity`,
> `language`, `src`, and `type`. However, some JavaScript frameworks, notably
> [Dojo](http://www.dojotoolkit.org/), utilize custom attributes in order to
> modify behavior. To allow such attributes, you can enable them via the
> `setAllowArbitraryAttributes()` method:
>
> ```php
> $this->headScript()->setAllowArbitraryAttributes(true);
> ```

## Basic Usage

You may specify a new script tag at any time. As noted above, these may be links
to outside resource files or scripts themselves.

```php
// adding scripts
$this->headScript()
    ->appendFile('/js/prototype.js')
    ->appendScript($onloadScript);
```

Order is often important with client-side scripting; you may need to ensure that
libraries are loaded in a specific order due to dependencies each have; use the
various `append`, `prepend`, and `offsetSet` directives to aid in this task:

```php
// Putting scripts in order

// place at a particular offset to ensure loaded last
$this->headScript()->offsetSetFile(100, '/js/myfuncs.js');

// use scriptaculous effects (append uses next index, 101)
$this->headScript()->appendFile('/js/scriptaculous.js');

// but always have base prototype script load first:
$this->headScript()->prependFile('/js/prototype.js');
```

When you're finally ready to output all scripts in your layout script, simply
echo the helper:

```php
<?= $this->headScript() ?>
```

## Capturing Scripts

Sometimes you need to generate client-side scripts programmatically. While you
could use string concatenation, heredocs, and the like, often it's easier just
to do so by creating the script and sprinkling in PHP tags. `HeadScript` lets
you do just that, capturing it to the stack:

```php
<?php $this->headScript()->captureStart() ?>
var action = '<?= $this->baseUrl ?>';
$('foo_form').action = action;
<?php $this->headScript()->captureEnd() ?>
```

The following assumptions are made:

- The script will be appended to the stack. If you wish for it to replace the
  stack or be added to the top, you will need to pass 'SET' or 'PREPEND',
  respectively, as the first argument to `captureStart()`.
- The script MIME type is assumed to be `text/javascript`; if you wish to
  specify a different type, you will need to pass it as the second argument to
  `captureStart()`.
- If you wish to specify any additional attributes for the `<script>` tag, pass
  them in an array as the third argument to `captureStart()`.
