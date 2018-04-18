# Introduction

zend-navigation manages trees of pointers to web pages. Simply put: It can be
used for creating menus, breadcrumbs, links, and sitemaps, or serve as a model
for other navigation related purposes.

## Pages and Containers

There are two main concepts in zend-navigation: pages and containers.

### Pages

A page (`Zend\Navigation\AbstractPage`) in zend-navigation, in its most basic
form, is an object that holds a pointer to a web page. In addition to the
pointer itself, the page object contains a number of other properties that are
typically relevant for navigation, such as `label`, `title`, etc.

Read more about pages in the [pages](pages.md) section.

### Containers

A navigation container (`Zend\Navigation\AbstractContainer`) holds pages. It has
methods for adding, retrieving, deleting and iterating pages. It implements the
[SPL](http://php.net/spl) interfaces `RecursiveIterator` and `Countable`, and
can thus be iterated with SPL iterators such as `RecursiveIteratorIterator`.

Read more about containers in the [containers](containers.md) section.

> ### Pages are containers
>
> `Zend\Navigation\AbstractPage` extends `Zend\Navigation\AbstractContainer`,
> which means that a page can have sub pages.

## View Helpers

### Separation of data (model) and rendering (view)

Classes in the zend-navigation namespace do not deal with rendering of
navigational elements.  Rendering is done with navigational view helpers.
However, pages contain information that is used by view helpers when rendering,
such as `label`, `class` (CSS), `title`, `lastmod`, and `priority` properties
for sitemaps, etc.

Read more about rendering navigational elements in the
[view helpers](helpers/intro.md) section.
