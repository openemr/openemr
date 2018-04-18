# MimeType

`Zend\Validator\File\MimeType` checks the MIME type of files. It will assert
`true` when a given file matches any defined MIME type.

This validator is inversely related to the
[ExcludeMimeType validator](exclude-mime-type.md)

> ### Compatibility
>
> This component will use the `FileInfo` extension if it is available. If it's
> not, it will degrade to the `mime_content_type()` function. And if the
> function call fails, it will use the MIME type which is given by HTTP. You
> should be aware of possible security problems when you do not have `FileInfo`
> or `mime_content_type()` available; the MIME type given by HTTP is not secure
> and can be easily manipulated.

## Supported Options

The following set of options are supported:

- `mimeType`: Comma-delimited string of MIME types, or array of MIME types,
  against which to test. Types can be specific (e.g., `image/jpg`), or refer
  only to the group (e.g., `image`).
- `magicFile`: Location of the magicfile to use for MIME type comparisons;
  defaults to the value of the `MAGIC` constant.
- `enableHeaderCheck`: Boolean flag indicating whether or not to use HTTP
  headers when determining the MIME type if neither the `FileInfo` nor
  `mime_magic` extensions are available; defaults to `false`.

## Basic Usage

```php
use Zend\Validator\File\MimeType;

// Only allow 'gif' or 'jpg' files
$validator = new MimeType('image/gif,image/jpg');

// ... or with array notation:
$validator = new MimeType(['image/gif', 'image/jpg']);

// ... or restrict to  entire group of types:
$validator = new MimeType(['image', 'audio']);

// Specify a different magicFile:
$validator = new MimeType([
    'mimeType' => ['image/gif', 'image/jpg'],
    'magicFile' => '/path/to/magicfile.mgx',
]);

// Enable HTTP header scanning (do not do this!):
$validator = new MimeType([
    'mimeType' => ['image/gif', 'image/jpg'],
    'enableHeaderCheck' => true,
]);

// Perform validation
if ($validator->isValid('./myfile.jpg')) {
    // file is valid
}
```

> ### Validating MIME groups is potentially dangerous
>
> Allowing "groups" of MIME types will accept **all** members of this group, even
> if your application does not support them. For instance, When you allow
> `image` you also allow `image/xpixmap` and `image/vasa`, both of which could
> be problematic.
