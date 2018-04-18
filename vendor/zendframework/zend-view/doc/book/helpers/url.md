# URL

The URL view helper is used to create a string representation of the routes that
you define within your application. The syntax for the view helper is
`$this->url($name, $params, $options, $reuseMatchedParameters)`, using the
following definitions for the helper arguments:

- `$name`: The name of the route you want to output.
- `$params`: An array of parameters that is defined within the respective route
  configuration.
- `$options`: An array of options that will be used to create the URL.
- `$reuseMatchedParams`: A flag indicating if the currently matched route
  parameters should be used when generating the new URL.

Let's take a look at how this view helper is used in real-world applications.

## Basic Usage

The following example shows a simple configuration for a news module. The route
is called `news` and it has two **optional** parameters called `action` and
`id`.

```php
// In a configuration array (e.g. returned by some module's module.config.php)
'router' => [
    'routes' => [
        'news' => [
            'type'    => 'segment',
            'options' => [
                'route'       => '/news[/:action][/:id]',
                'constraints' => [
                    'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                ],
                'defaults' => [
                    'controller' => 'news',
                    'action'     => 'index',
                ],
            ],
        ],
    ],
],
```

First, let's use the view helper to create the output for the URL `/news` without any of the
optional parameters being used:

```php
<a href="<?= $this->url('news'); ?>">News Index</a>
```

This will render the output:

```html
<a href="/news">News Index</a>
```

Now let's assume we want to get a link to display the detail page of a single
news entry. For this task, the optional parameters `action` and `id` need to
have values assigned. This is how you do that:

```php
<a href="<?= $this->url('news', ['action' => 'details', 'id' => 42]); ?>">
    Details of News #42
</a>
```

This will render the output:

```html
<a href="/news/details/42">News Index</a>
```

## Query String Arguments

Most SEO experts agree that pagination parameters should not be part of the URL
path; for example, the following URL would be considered a bad practice:
`/news/archive/page/13`. Pagination is more correctly accomplished using a query
string arguments, such as `/news/archive?page=13`. To achieve this, you'll need
to make use of the `$options` argument from the view helper.

We will use the same route configuration as defined above:

```php
// In a configuration array (e.g. returned by some module's module.config.php)
'router' => [
    'routes' => [
        'news' => [
            'type'    => 'segment',
            'options' => [
                'route'       => '/news[/:action][/:id]',
                'constraints' => [
                    'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                ],
                'defaults' => [
                    'controller' => 'news',
                    'action'     => 'index',
                ],
            ],
        ],
    ],
],
```

To generate query string arguments from the view helper, you need to assign them
as the third argument using the `query` key like this:

```php
<?php
$url = $this->url(
    'news',
    ['action' => 'archive'],
    [
        'query' => [
            'page' => 13,
        ],
    ]
);
?>
<a href="<?= $url; ?>">News Archive Page #13</a>
```

The above code sample would output:

```html
<a href="/news/archive?page=13">News Archive Page #13</a>
```

## Fragments

Another possible entry within the `$options` array is the assignment of URL
fragments (typically used to link to in-page anchors), denoted with using the
`fragment` key. Let's assume we want to enter a link for users to directly jump
to the comment section of a details page:

```php
<?php
$url = $this->url(
    'news',
    ['action' => 'details', 'id' => 42],
    [
        'fragment' => 'comments',
    ]
);
?>
<a href="<?= $url; ?>">Comment Section of News #42</a>
```

The above code sample would output:

```html
<a href="/news/details/42#comments">Comment Section of News #42</a>
```

You can use `fragment` and `query` options at the same time!

```php
<?php
$url = $this->url(
    'news',
    ['action' => 'details', 'id' => 42],
    [
        'query' => [
            'commentPage' => 3,
        ],
        'fragment' => 'comments',
    ]
);
?>
<a href="<?= $url; ?>">Comment Section of News #42</a>
```

The above code sample would output:

```html
<a href="/news/details/42?commentPage=3#comments">Comment Section of News #42</a>
```

## Fully Qualified Domain Name

Another possible entry within the `$options` array is to output a fully
qualified domain name (absolute URL), denoted using the `force_canonical` key:

```php
<?php
$url = $this->url(
    'news',
    [],
    [
        'force_canonical' => true,
    ]
);
?>
<a href="<?= $url; ?>">News Index</a>
```

The above code sample would output:

```html
<a href="http://www.example.com/news">News Index</a>
```

## Reusing Matched Parameters

When you're on a route that has many parameters, often times it makes sense to
reuse currently matched parameters instead of assigning them explicitly. In this
case, the argument `$reuseMatchedParams` will come in handy.

As an example, we will imagine being on a detail page for our `news` route. We
want to display links to the `edit` and `delete` actions without having to
assign the ID again:

```php
// Currently url /news/details/777

<a href="<?= $this->url('news', ['action' => 'edit'], null, true); ?>">Edit Me</a>
<a href="<?= $this->url('news', ['action' => 'delete'], null, true); ?>">Delete Me</a>
```

Notice the `true` argument in the fourth position. This tells the view helper to
use the matched `id` (`777`) when creating the new URL:

```html
<a href="/news/edit/777">Edit Me</a>
<a href="/news/delete/777">Edit Me</a>
```

### Shorthand

Due to the fact that reusing parameters is a use case that can happen when no
route options are set, the third argument for the URL view helper will be
checked against its type; when a `boolean` is passed, the helper uses it to set
the value of the `$reuseMatchedParams` flag:

```php
$this->url('news', ['action' => 'archive'], null, true);
// is equal to
$this->url('news', ['action' => 'archive'], true);
```
