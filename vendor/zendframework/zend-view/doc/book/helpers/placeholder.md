# Placeholder

The `Placeholder` view helper is used to persist content between view scripts
and view instances. It also offers some useful features such as aggregating
content, capturing view script content for later use, and adding pre- and
post-text to content (and custom separators for aggregated content).

## Basic Usage

Basic usage of placeholders is to persist view data. Each invocation of the
`Placeholder` helper expects a placeholder name; the helper then returns a
placeholder container object that you can either manipulate or echo.

```php
<?php $this->placeholder('foo')->set("Some text for later") ?>

<?= $this->placeholder('foo'); ?>
```

Results in:

```html
Some text for later
```

## Aggregate Content

Aggregating content via placeholders can be useful at times as well. For
instance, your view script may have a variable array from which you wish to
retrieve messages to display later; a later view script can then determine how
those will be rendered.

The `Placeholder` view helper uses containers that extend `ArrayObject`,
providing a rich feature set for manipulating arrays. In addition, it offers a
variety of methods for formatting the content stored in the container:

- `setPrefix($prefix)` sets text with which to prefix the content. Use
  `getPrefix()` at any time to determine what the current setting is.
- `setPostfix($prefix)` sets text with which to append the content. Use
  `getPostfix()` at any time to determine what the current setting is.
- `setSeparator($prefix)` sets text with which to separate aggregated content.
  Use `getSeparator()` at any time to determine what the current setting is.
- `setIndent($prefix)` can be used to set an indentation value for content. If
  an integer is passed, that number of spaces will be used; if a string is
  passed, the string will be used. Use `getIndent()` at any time to determine
  what the current setting is.

```php
<!-- first view script -->
<?php $this->placeholder('foo')->exchangeArray($this->data) ?>
```

```php
<!-- later view script -->
<?php
$this->placeholder('foo')
    ->setPrefix("<ul>\n    <li>")
    ->setSeparator("</li><li>\n")
    ->setIndent(4)
    ->setPostfix("</li></ul>\n");
?>

<?= $this->placeholder('foo') ?>
```

The above results in an unodered list with pretty indentation.

Because the `Placeholder` container objects extend `ArrayObject`, you can also
assign content to a specific key in the container easily, instead of simply
pushing it into the container. Keys may be accessed either as object properties
or as array keys.

```php
<?php $this->placeholder('foo')->bar = $this->data ?>
<?= $this->placeholder('foo')->bar ?>

<?php
$foo = $this->placeholder('foo');
echo $foo['bar'];
```

## Capture Content

Occasionally you may have content for a placeholder in a view script that is
easiest to template; the `Placeholder` view helper allows you to capture
arbitrary content for later rendering using the following API.

- `captureStart($type, $key)` begins capturing content.
  - `$type` should be one of the `Placeholder` constants `APPEND` or `SET`. If
    `APPEND`, captured content is appended to the list of current content in the
    placeholder; if `SET`, captured content is used as the sole value of the
    placeholder (potentially replacing any previous content). By default,
    `$type` is `APPEND`.
  - `$key` can be used to specify a specific key in the placeholder container to
    which you want content captured.
  - `captureStart()` locks capturing until `captureEnd()` is called; you cannot
    nest capturing with the same placeholder container. Doing so will raise an
    exception.
- `captureEnd()` stops capturing content, and places it in the container object
  according to how `captureStart()` was called.

As an example:

```php
<!-- Default capture: append -->
<?php $this->placeholder('foo')->captureStart();
foreach ($this->data as $datum): ?>
<div class="foo">
    <h2><?= $datum->title ?></h2>
    <p><?= $datum->content ?></p>
</div>
<?php endforeach; ?>
<?php $this->placeholder('foo')->captureEnd() ?>

<?= $this->placeholder('foo') ?>
```

Alternately, capture to a key:

```php
<!-- Capture to key -->
<?php $this->placeholder('foo')->captureStart('SET', 'data');
foreach ($this->data as $datum): ?>
<div class="foo">
    <h2><?= $datum->title ?></h2>
    <p><?= $datum->content ?></p>
</div>
 <?php endforeach; ?>
<?php $this->placeholder('foo')->captureEnd() ?>

<?= $this->placeholder('foo')->data ?>
```

## Concrete Implementations

zend-view ships with a number of "concrete" placeholder implementations. These
are for commonly used placeholders: doctype, page title, and various `<head>`
elements. In all cases, calling the placeholder with no arguments returns the
element itself.

Documentation for each element is covered separately, as linked below:

- [Doctype](doctype.md)
- [HeadLink](head-link.md)
- [HeadMeta](head-meta.md)
- [HeadScript](head-script.md)
- [HeadStyle](head-style.md)
- [HeadTitle](head-title.md)
- [InlineScript](inline-script.md)
