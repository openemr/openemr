# Creating tag clouds

`Zend\Tag\Cloud` is the rendering aspect of zend-tag. By default, it comes with
a set of HTML decorators, allowing you to create tag clouds for a website. It
also supplies you with two abstract classes to create your own decorators; one
use case might be to create tag clouds in PDF documents.

You can instantiate and configure `Zend\Tag\Cloud` either programmatically or
via a configuration sturcture (either an array or an instance of `Traversable`).

The following options are available:

Option | Description
------ | ------
`cloudDecorator` | Defines the decorator for the cloud. Can either be the name of the class which should be loaded by the plugin manager, an instance of `Zend\Tag\Cloud\Decorator\AbstractCloud` or an array containing the decorator under the key decorator and optionally an array under the key options, which will be passed to the decorator’s constructor.
`tagDecorator` | Defines the decorator for individual tags. This can either be the name of the class which should be loaded by the plugin manager, an instance of `Zend\Tag\Cloud\Decorator\AbstractTag` or an array containing the decorator under the key decorator and optionally an array under the key options, which will be passed to the decorator’s constructor.
`decoratorPluginManager` | A different plugin manager to use. Must be an instance of `Zend\ServiceManager\AbstractPluginManager`.
`itemList` | A different item list to use. Must be an instance of `Zend\Tag\ItemList`.
`tags` |  Array of tags to assign to the cloud. Each tag must either implement `Zend\Tag\TaggableInterface` or be an array which can be used to instantiate `Zend\Tag\Item`.

## Using Zend\\Tag\\Cloud

This example illustrates a basic example of how to create a tag cloud, add
multiple tags to it, and finally render it.

```php
// Create the cloud and assign static tags to it
$cloud = new Zend\Tag\Cloud([
    'tags' => [
        [
            'title'  => 'Code',
            'weight' => 50,
            'params' => ['url' => '/tag/code'],
        ],
        [
            'title'  => 'Zend Framework',
            'weight' => 1,
            'params' => ['url' => '/tag/zend-framework'],
        ],
        [
            'title' => 'PHP',
            'weight' => 5,
            'params' => ['url' => '/tag/php'],
        ],
    ],
]);

// Render the cloud
echo $cloud;
```

This will output the tag cloud with the three tags, spread with the default
font-sizes:

```php
<ul class="zend-tag-cloud">
    <li>
        <a href="/tag/code" style="font-size: 20px;">
            Code
        </a>
    </li>
    <li>
        <a href="/tag/zend-framework" style="font-size: 10px;">
            Zend Framework
        </a>
    </li>
    <li>
        <a href="/tag/php" style="font-size: 11px;">
            PHP
        </a>
    </li>
</ul>
```

> ### Formatting
>
> The HTML code examples are preformatted for a better visualization in the
> documentation.  You can define a output separator for the [HTML cloud
> decorator](#html-cloud-decorator).

The following example shows how create the **same** tag cloud from a `Zend\Config\Config` object.

```ini
; An example tags.ini file
tags.1.title = "Code"
tags.1.weight = 50
tags.1.params.url = "/tag/code"
tags.2.title = "Zend Framework"
tags.2.weight = 1
tags.2.params.url = "/tag/zend-framework"
tags.3.title = "PHP"
tags.3.weight = 2
tags.3.params.url = "/tag/php"
```

```php
// Create the cloud from a Zend\Config\Config object
$config = Zend\Config\Factory::fromFile('tags.ini');
$cloud = new Zend\Tag\Cloud($config);

// Render the cloud
echo $cloud;
```

## Decorators

`Zend\Tag\Cloud` requires two types of decorators to be able to render a tag cloud:

- A decorator for rendering an individual tag.
- A decorator for rendering the surrounding cloud.

`Zend\Tag\Cloud` ships a default decorator set for formatting a tag cloud in
HTML. This set will, by default, create a tag cloud as a `<ul>/<li>` list,
spread with different font-sizes according to the weight values of the tags
assigned to them.

### HTML Tag decorator

The HTML tag decorator will by default render every tag in an anchor element, surrounded by a
`<li>` element. The anchor itself is fixed and cannot be changed, but the surrounding element(s)
can.

> #### URL parameter
>
> As the HTML tag decorator always surounds the tag title with an anchor, you
> should define a URL parameter for every tag used in it.

The tag decorator can either spread different font-sizes over the anchors or a
defined list of classnames. When setting options for one of those possibilities,
the corresponding one will automatically be enabled.

The following configuration options are available:

Option | Default | Description
------ | ------- | -----------
`fontSizeUnit` | `px` | Defines the font-size unit used for all font-sizes. The possible values are: em, ex, px, in, cm, mm, pt, pc and %.
`minFontSize` | `10` | The minimum font-size distributed through the tags (must be numeric).
`maxFontSize` | `20` | The maximum font-size distributed through the tags (must be numeric).
`classList` | `null` | An array of classes distributed through the tags.
`htmlTags` | `array('li')` | An array of HTML tags surrounding the anchor. Each element can either be a string, which is used as element type, or an array containing an attribute list for the element, defined as key/value pair. In this case, the array key is used as element type.

The following example shows how to create a tag cloud with a customized HTML tag decorator.

```php
$cloud = new Zend\Tag\Cloud([
    'tagDecorator' => [
        'decorator' => 'htmltag',
        'options'   => [
            'minFontSize' => '20',
            'maxFontSize' => '50',
            'htmlTags'    => [
                'li' => ['class' => 'my_custom_class'],
            ],
        ],
    ],
    'tags' => [
       [
           'title'  => 'Code',
           'weight' => 50,
           'params' => ['url' => '/tag/code'],
       ],
       [
           'title'  => 'Zend Framework',
           'weight' => 1,
           'params' => ['url' => '/tag/zend-framework'],
       ],
       [
           'title'  => 'PHP',
           'weight' => 5,
           'params' => ['url' => '/tag/php']
       ],
   ],
]);

// Render the cloud
echo $cloud;
```

The output:

```php
<ul class="zend-tag-cloud">
    <li class="my_custom_class">
        <a href="/tag/code" style="font-size: 50px;">Code</a>
    </li>
    <li class="my_custom_class">
        <a href="/tag/zend-framework" style="font-size: 20px;">Zend Framework</a>
    </li>
    <li class="my_custom_class">
        <a href="/tag/php" style="font-size: 23px;">PHP</a>
    </li>
</ul>
```

### HTML Cloud decorator

By default, the HTML cloud decorator will surround the HTML tags with a `<ul>`
element and add no separation. Like the tag decorator, you can define multiple
surrounding HTML tags and additionally define a separator. The available options
are:

Option | Default | Description
------ | ------- | -----------
`separator` | `' '` (a whitespace) | Defines the separator which is placed between all tags.
`htmlTags` | `array('ul' => array('class' => 'zend-tag-cloud'))` | An array of HTML tags surrounding all tags. Each element can either be a string, which is used as element type, or an array containing an attribute list for the element, defined as key/value pair. In this case, the array key is used as element type.

```php
// Create the cloud and assign static tags to it
$cloud = new Zend\Tag\Cloud([
    'cloudDecorator' => [
        'decorator' => 'htmlcloud',
        'options'   => [
            'separator' => "\n\n",
            'htmlTags'  => [
                'ul' => [
                    'class' => 'my_custom_class',
                    'id'    => 'tag-cloud',
                ],
            ],
        ],
    ],
    'tags' => [
        array(
            'title'  => 'Code',
            'weight' => 50,
            'params' => ['url' => '/tag/code'],
        ],
        [
            'title'  => 'Zend Framework',
            'weight' => 1,
            'params' => ['url' => '/tag/zend-framework'],
        ],
        [
            'title' => 'PHP',
            'weight' => 5,
            'params' => ['url' => '/tag/php'],
        ],
    ],
]);

// Render the cloud
echo $cloud;
```

The ouput:

```php
<ul class="my_custom_class" id="tag-cloud"><li><a href="/tag/code" style="font-size:
20px;">Code</a></li>

<li><a href="/tag/zend-framework" style="font-size: 10px;">Zend Framework</a></li>

<li><a href="/tag/php" style="font-size: 11px;">PHP</a></li></ul>
```
