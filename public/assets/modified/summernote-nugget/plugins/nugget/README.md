summernote-nugget
=============

Allow users to insert custom "nuggets" into the WYSIWYG.


Installation
-------------

### 1) Copy the plugin

You must copy the plugin/nugget folder into your local summernote plugin folder.

### 2) Configure the plugin

After that, to initialize the nugget plugin, you have to set these options here list contains your nuggets:

``` js
$('#summernote').summernote({
    toolbar: [
        ['insert', ['nugget']]
    ],
    nugget: {
        list: [ // list of your nuggets
            '[[code nugget 1]]',
            '[[code nugget 2]]',
            '[[code nugget 2]]'
        ]
    },
});
```