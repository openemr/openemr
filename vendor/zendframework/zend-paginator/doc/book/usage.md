# Usage

## Paginating data collections

In order to paginate items into pages, `Zend\Paginator` must have a generic way
of accessing that data. For that reason, all data access takes place through
data source adapters. Several adapters ship with zend-paginator by default:

Adapter      | Description
------------ | -----------
ArrayAdapter | Accepts a PHP array.
DbSelect     | Accepts a `Zend\Db\Sql\Select` instance, plus either a `Zend\Db\Adapter\Adapter` or `Zend\Db\Sql\Sql` instance; paginates rows from a database.
Iterator     | Accepts any `Iterator` instance.
NullFill     | Dummy paginator.

> ### Database optimizations
>
> Instead of selecting every matching row of a given query, the `DbSelect` adapter
> retrieves only the smallest amount of data necessary for displaying the
> current page. Because of this, a second query is dynamically generated to
> determine the total number of matching rows.

To create a paginator instance, you must supply an adapter to the constructor:

```php
use Zend\Paginator\Adapter;
use Zend\Paginator\Paginator;

$paginator = new Paginator(new Adapter\ArrayAdapter($array));
```

In the case of the `NullFill` adapter, in lieu of a data collection you must
supply an item count to its constructor.

Although the instance is technically usable in this state, in your controller
action you'll need to tell the paginator what page number the user requested.
This allows advancing through the paginated data.

```php
$paginator->setCurrentPageNumber($page);
```

The simplest way to keep track of this value is through a URL parameter. The
following is an example [zend-router](https://zendframework.github.com/zend-router)
route configuration:

```php
return [
    'routes' => [
        'paginator' => [
            'type' => 'segment',
            'options' => [
                'route' => '/list/[page/:page]',
                'defaults' => [
                    'page' => 1,
                ],
            ],
        ],
    ],
];
```

With the above route (and using [zend-mvc](https://zendframework.github.io/zend-mvc/)
controllers), you might set the current page number in your controller action
like so:

```php
$paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
```

There are other options available; see the [Configuration chapter](configuration.md)
for more on them.

Finally, you'll need to assign the paginator instance to your view. If you're
using zend-mvc and zend-view, you can assign the paginator object to your view
model:

```php
$vm = new ViewModel();
$vm->setVariable('paginator', $paginator);
return $vm;
```

## The DbSelect adapter

Most adapters receive their datasets directly. However, the `DbSelect` adapter
requires a more detailed explanation regarding the retrieval and count of the
data from the database.

You do not have to retrieve data from the database prior to using the `DbSelect`
adapter; the adapter will do the retrieval for you, as well as provide a count
of total pages. If additional work has to be done on the database results which
cannot be expressed via the provided `Zend\Db\Sql\Select`, object you must
extend the adapter and override the `getItems()` method.

Additionally this adapter does **not** fetch all records from the database in
order to count them.  Instead, the adapter manipulates the original query to
produce a corresponding `COUNT` query, and uses the new query to get the number
of rows.  While this approach requires an extra round-trip to the database,
doing so is stillmany times faster than fetching an entire result set and using
`count()`, especially with large collections of data.

The database adapter will try and build the most efficient query that will
execute on pretty much any modern database. However, depending on your database
or even your own schema setup, there might be more efficient ways to get a
rowcount.

There are two approaches for doing this. The first is to extend the `DbSelect`
adapter and override the `count()` method:

```php
class MyDbSelect extends DbSelect
{
    public function count()
    {
        if ($this->rowCount) {
            return $this->rowCount;
        }

        $select = new Select();
        $select
          ->from('item_counts')
          ->columns(['c'=>'post_count']);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();
        $row       = $result->current();
        $this->rowCount = $row['c'];

        return $this->rowCount;
    }
}

$adapter = new MyDbSelect($query, $adapter);
```

Alternately, you can pass an additional `Zend\Db\Sql\Select` object as the
fourth constructor argument to the `DbSelect` adapter to implement a custom
count query.

For example, if you keep track of the count of blog posts in a separate table,
you could achieve a faster count query with the following setup:

```php
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

$countQuery = new Select();
$countQuery
    ->from('item_counts')
    ->columns([ DbSelect::ROW_COUNT_COLUMN_NAME => 'post_count' ]);

$adapter = new DbSelect($query, $dbAdapter, null, $countQuery);
$paginator = new Paginator($adapter);
```

Alternatively, the same can be achieved using the provided factory:

```php
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Factory as PaginatorFactory;

$countQuery = new Select();
$countQuery
    ->from('item_counts')
    ->columns([ DbSelect::ROW_COUNT_COLUMN_NAME => 'post_count' ]);

$paginator = PaginatorFactory::factory(
    [
        $query,
        $dbAdapter,
        null,
        $countQuery,
    ],
    DbSelect::class
);
```

This approach will probably not give you a huge performance gain on small
collections and/or simple select queries. However, with complex queries and
large collections, a similar approach could give you a significant performance
boost.

The `DbSelect` adapter also supports returning of fetched records using the
[ResultSet subcomponent of zend-db](http://zendframework.github.io/zend-db/result-set/).
You can override the concrete `ResultSet` implementation by passing an object
implementing `Zend\Db\ResultSet\ResultSetInterface` as the third constructor
argument to the `DbSelect` adapter:

```php
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

// $objectPrototype is an instance of our custom entity
// $hydrator is a custom hydrator for our entity
// (implementing Zend\Hydrator\HydratorInterface)
$resultSet = new HydratingResultSet($hydrator, $objectPrototype);

$adapter = new DbSelect($query, $dbAdapter, $resultSet)
$paginator = new Zend\Paginator\Paginator($adapter);
```

Now when we iterate over `$paginator` we will get instances of our custom entity
instead of associative arrays.

## Rendering pages with view scripts

The view script is used to render the page items (if you're using
zend-paginator to do so) and display the pagination control.

Because `Zend\Paginator\Paginator` implements the SPL interface
[IteratorAggregate](http://php.net/IteratorAggregate), you can loop over an
instance using `foreach`:

```php
<html>
<body>
<h1>Example</h1>
<?php if (count($this->paginator)): ?>
<ul>
<?php foreach ($this->paginator as $item): ?>
  <li><?= $item; ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?= $this->paginationControl(
    $this->paginator,
    'Sliding',
    'my_pagination_control',
    ['route' => 'application/paginator']
) ?>
</body>
</html>
```

Notice the view helper call near the end. `PaginationControl` accepts up to four
parameters: the paginator instance, a scrolling style, a view script name, and
an array of additional parameters.

The second and third parameters are very important. Whereas the view script name
is used to determine how the pagination control should **look**, the scrolling
style is used to control how it should **behave**. Say the view script is in the
style of a search pagination control, like the one below:

![Pagination controls](images/usage-rendering-control.png)

What happens when the user clicks the "next" link a few times? Well, any number of things could
happen:

- The current page number could stay in the middle as you click through (as it
  does on Yahoo!)
- It could advance to the end of the page range and then appear again on the
  left when the user clicks "next" one more time. 
- The page numbers might even expand and contract as the user advances (or
  "scrolls") through them (as they do on Google).

There are four scrolling styles packaged with Zend Framework:

Scrolling style | Description
--------------- | -----------
All             | Returns every page. This is useful for dropdown menu pagination controls with relatively few pages. In these cases, you want all pages available to the user at once.
Elastic         | A Google-like scrolling style that expands and contracts as a user scrolls through the pages.
Jumping         | As users scroll through, the page number advances to the end of a given range, then starts again at the beginning of the new range.
Sliding         | A Yahoo!-like scrolling style that positions the current page number in the center of the page range, or as close as possible. This is the default style.

The fourth and final parameter is reserved for an optional associative array of
variables that you want available in your view (available via `$this`). For
instance, these values could include extra URL parameters for pagination links.

By setting the default view script name, default scrolling style, and view
instance, you can eliminate the calls to `PaginationControl` completely:

```php
use Zend\Paginator\Paginator;
use Zend\View\Helper\PaginationControl;

Paginator::setDefaultScrollingStyle('Sliding');
PaginationControl::setDefaultViewPartial('my_pagination_control');
```

When all of these values are set, you can render the pagination control inside
your view script by echoing the paginator instance:

```php
<?= $this->paginator ?>
```

> ### Using other template engines
>
> Of course, it's possible to use zend-paginator with other template engines.
> For example, with Smarty you might do the following:
>
> ```php
> $smarty-assign('pages', $paginator->getPages());
> ```
>
> You could then access paginator values from a template like so:
>
> ```php
> {$pages.pageCount}
> ```

### Example pagination controls

The following example pagination controls will help you get started with
zend-view:

Search pagination:

```php
<!--
See http://developer.yahoo.com/ypatterns/pattern.php?pattern=searchpagination
-->

<?php if ($this->pageCount): ?>
<div class="paginationControl">
<!-- Previous page link -->
<?php if (isset($this->previous)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->previous]); ?>">
    &lt; Previous
  </a> |
<?php else: ?>
  <span class="disabled">&lt; Previous</span> |
<?php endif; ?>

<!-- Numbered page links -->
<?php foreach ($this->pagesInRange as $page): ?>
  <?php if ($page != $this->current): ?>
    <a href="<?= $this->url($this->route, ['page' => $page]); ?>">
        <?= $page; ?>
    </a> |
  <?php else: ?>
    <?= $page; ?> |
  <?php endif; ?>
<?php endforeach; ?>

<!-- Next page link -->
<?php if (isset($this->next)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->next]); ?>">
    Next &gt;
  </a>
<?php else: ?>
  <span class="disabled">Next &gt;</span>
<?php endif; ?>
</div>
<?php endif; ?>
```

Item pagination:

```php
<!--
See http://developer.yahoo.com/ypatterns/pattern.php?pattern=itempagination
-->

<?php if ($this->pageCount): ?>
<div class="paginationControl">
<?= $this->firstItemNumber; ?> - <?= $this->lastItemNumber; ?>
of <?= $this->totalItemCount; ?>

<!-- First page link -->
<?php if (isset($this->previous)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->first]); ?>">
    First
  </a> |
<?php else: ?>
  <span class="disabled">First</span> |
<?php endif; ?>

<!-- Previous page link -->
<?php if (isset($this->previous)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->previous]); ?>">
    &lt; Previous
  </a> |
<?php else: ?>
  <span class="disabled">&lt; Previous</span> |
<?php endif; ?>

<!-- Next page link -->
<?php if (isset($this->next)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->next]); ?>">
    Next &gt;
  </a> |
<?php else: ?>
  <span class="disabled">Next &gt;</span> |
<?php endif; ?>

<!-- Last page link -->
<?php if (isset($this->next)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->last]); ?>">
    Last
  </a>
<?php else: ?>
  <span class="disabled">Last</span>
<?php endif; ?>

</div>
<?php endif; ?>
```

Dropdown pagination:

```php
<?php if ($this->pageCount): ?>
<select id="paginationControl" size="1">
<?php foreach ($this->pagesInRange as $page): ?>
  <?php $selected = ($page == $this->current) ? ' selected="selected"' : ''; ?>
  <option value="<?= $this->url($this->route, ['page' => $page]);?>"<?= $selected ?>>
    <?= $page; ?>
  </option>
<?php endforeach; ?>
</select>
<?php endif; ?>

<script type="text/javascript"
     src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.2/prototype.js">
</script>
<script type="text/javascript">
$('paginationControl').observe('change', function() {
    window.location = this.options[this.selectedIndex].value;
})
</script>
```

### Listing of properties

The following options are available to pagination control view scripts:

Property         | Type    | Description
---------------- | ------- | ------------
first            | integer | First page number (typically 1).
firstItemNumber  | integer | Absolute number of the first item on this page.
firstPageInRange | integer | First page in the range returned by the scrolling style.
current          | integer | Current page number.
currentItemCount | integer | Number of items on this page.
itemCountPerPage | integer | Maximum number of items available to each page.
last             | integer | Last page number.
lastItemNumber   | integer | Absolute number of the last item on this page.
lastPageInRange  | integer | Last page in the range returned by the scrolling style.
next             | integer | Next page number.
pageCount        | integer | Number of pages.
pagesInRange     | array   | Array of pages returned by the scrolling style.
previous         | integer | Previous page number.
totalItemCount   | integer | Total number of items.
