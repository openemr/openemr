# Uri Validator

`Zend\Validator\Uri` allows you to validate a URI using the `Zend\Uri\Uri`
handler to parse the URI. The validator allows for both validation of absolute
and/or relative URIs. There is the possibility to exchange the handler for
another one in case the parsing of the uri should be done differently.

## Supported options

The following options are supported for `Zend\Validator\Uri`:

- `uriHandler`: Defines the handler to be used to parse the uri. This options
  defaults to a new instance of `Zend\Uri\Uri`.
- `allowRelative`: Defines if relative paths are allowed. This option defaults
  to `true`.
- `allowAbsolute`: Defines if absolute paths are allowed. This option defaults
  to `true`.

## Basic usage

```php
$validator = new Zend\Validator\Uri();
$uri = 'http://framework.zend.com/manual';

if ($validator->isValid($uri)) {
    // $uri was valid
} else {
    // false. You can use $validator->getMessages() to retrieve error messages
}
```
