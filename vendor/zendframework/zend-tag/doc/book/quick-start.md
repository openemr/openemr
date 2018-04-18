# Introduction and Quick Start

zend-tag provides the ability to work with taggable items. At its foundation, it
provides two classes to work with tags, `Zend\Tag\Item` and `Zend\Tag\ItemList`.
Additionally, it comes with the interface `Zend\Tag\TaggableInterface`, which
allows you to use any of your models as a taggable item in conjunction with the
component.

`Zend\Tag\Item` provides the essential functionality required to work with all
other functionality within the component. A taggable item always consists of a
title and a relative weight (e.g. number of occurrences). It also stores
parameters which are used by the different sub-components.

`Zend\Tag\ItemList` exists to group multiple items together as an array
iterator, and provides additional functionality to calculate absolute weight
values based on the given relative weights of each item in it.

## Quick Start

This example illustrates how to create a list of tags and spread absolute weight
values over them.

```php
// Create the item list
$list = new Zend\Tag\ItemList();

// Assign tags to it
$list[] = new Zend\Tag\Item(['title' => 'Code', 'weight' => 50]);
$list[] = new Zend\Tag\Item(['title' => 'Zend Framework', 'weight' => 1]);
$list[] = new Zend\Tag\Item(['title' => 'PHP', 'weight' => 5]);

// Spread absolute values on the items
$list->spreadWeightValues([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

// Output the items with their absolute values
foreach ($list as $item) {
    printf("%s: %d\n", $item->getTitle(), $item->getParam('weightValue'));
}
```

This will output the three items "Code", "Zend Framework", and "PHP", with the
absolute values 10, 1 and 2:

```
Code: 10
Zend Framework: 1
PHP: 2
```
