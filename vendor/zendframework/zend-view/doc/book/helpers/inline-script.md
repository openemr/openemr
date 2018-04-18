# InlineScript

The HTML `<script>` element is used to either provide inline client-side
scripting elements or link to a remote resource containing client-side scripting
code. The `InlineScript` helper allows you to manage both. It is derived from
[HeadScript](head-script.md), and any method of that helper is available;
replce the usage of `headScript()` in those examples with `inlineScript()`.

> ### Use InlineScript for HTML body scripts
>
> `InlineScript` should be used when you wish to include scripts inline in the
> HTML `<body>`.  Placing scripts at the end of your document is a good practice
> for speeding up delivery of your page, particularly when using 3rd party
> analytics scripts.  Some JS libraries need to be included in the HTML
> `<head>`; use [HeadScript](head-script.md) for those scripts.

## Basic Usage

Add to the layout script:

```php
<body>
    <!-- Content -->

    <?php
    echo $this->inlineScript()
        ->prependFile($this->basePath('js/vendor/foundation.min.js'))
        ->prependFile($this->basePath('js/vendor/jquery.js'));
    ?>
</body>
```

Output:

```html
<body>
    <!-- Content -->

    <script type="text/javascript" src="/js/vendor/jquery.js"></script>
    <script type="text/javascript" src="/js/vendor/foundation.min.js"></script>
</body>
```

## Capturing Scripts

Add in your view scripts:

```php
$this->inlineScript()->captureStart();
echo <<<JS
    $('select').change(function(){
        location.href = $(this).val();
    });
JS;
$this->inlineScript()->captureEnd();
```

Output:

```html
<body>
    <!-- Content -->

    <script type="text/javascript" src="/js/vendor/jquery.js"></script>
    <script type="text/javascript" src="/js/vendor/foundation.min.js"></script>
    <script type="text/javascript">
        //<!--
        $('select').change(function(){
            location.href = $(this).val();
        });
        //-->
    </script>
</body>
```
