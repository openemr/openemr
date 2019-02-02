# View Helper - Cycle

The `Cycle` helper is used to alternate a set of values.

## Basic Usage

To add elements to cycle, specify them in constructor:

```php
<table>
    <?php foreach ($this->books as $book): ?>
        <tr style="background-color: <?= $this->cycle(['#F0F0F0', '#FFF'])->next() ?>">
            <td><?= $this->escapeHtml($book['author']) ?></td>
        </tr>
    <?php endforeach ?>
</table>
```

The output:

```php
<table>
    <tr style="background-color: #F0F0F0">
       <td>First</td>
    </tr>
    <tr style="background-color: #FFF">
       <td>Second</td>
    </tr>
</table>
```

Instead of passing the data at invocation, you can assign it ahead of time:

```php
<?php $this->cycle()->assign(['#F0F0F0', '#FFF'])); ?>
```

You can also cycle in reverse, using the `prev()` method instead of `next()`:

```php
<table>
    <?php foreach ($this->books as $book): ?>
    <tr style="background-color: <?php echo $this->cycle()->prev() ?>">
       <td><?php echo $this->escapeHtml($book['author']) ?></td>
    </tr>
    <?php endforeach ?>
</table>
```

The output of the two previous examples combined becomes:

```php
<table>
    <tr style="background-color: #FFF">
        <td>First</td>
    </tr>
    <tr style="background-color: #F0F0F0">
        <td>Second</td>
    </tr>
</table>
```

## Working with two or more cycles

If you are nesting cycles, you must provide all but one of them with a name; do
this by providing a second parameter to the `cycle()` invocation:
`$this->cycle(array('#F0F0F0', '#FFF'), 'cycle2')`

```php
<table>
    <?php foreach ($this->books as $book): ?>
        <tr style="background-color: <?= $this->cycle(['#F0F0F0', '#FFF'])->next() ?>">
            <td><?= $this->cycle([1, 2, 3], 'number')->next() ?></td>
            <td><?= $this->escapeHtml($book['author']) ?></td>
        </tr>
    <?php endforeach ?>
</table>
```

You can also provide a `$name` argument to `assign()`:

```php
<?php $this->cycle()->assign([1, 2, 3], 'number'); ?>
```

Or use the `setName()` method priort to invoking either of `next()` or `prev()`.

As a combined example:

```php
<?php
$this->cycle()->assign(['#F0F0F0', '#FFF'], 'colors');
$this->cycle()->assign([1, 2, 3], 'numbers');
?>
<table>
    <?php foreach ($this->books as $book): ?>
        <tr style="background-color: <?= $this->cycle()->setName('colors')->next() ?>">
            <td><?= $this->cycle()->setName('numbers')->next() ?></td>
            <td><?= $this->escapeHtml($book['author']) ?></td>
        </tr>
    <?php endforeach ?>
</table>
```
