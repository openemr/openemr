# File Transport Options

This document details the various options available to the
`Zend\Mail\Transport\File` mail transport.

## Quick Start

```php
use Zend\Mail\Transport\File as FileTransport;
use Zend\Mail\Transport\FileOptions;

// Setup File transport
$transport = new FileTransport();
$options   = new FileOptions([
    'path'     => 'data/mail/',
    'callback' => function (FileTransport $transport) {
        return 'Message_' . microtime(true) . '_' . mt_rand() . '.txt';
    },
]);
$transport->setOptions($options);
```

## Configuration Options

Option name | Description
----------- | -----------
`path`      | The path under which mail files will be written.
`callback`  | A PHP callable to be invoked in order to generate a unique name for a message file. See below for the default used.

The default callback used is:

```php
function (Zend\Mail\FileTransport $transport) {
    return 'ZendMail_' . time() . '_' . mt_rand() . '.tmp';
}
```

## Available Methods

`Zend\Mail\Transport\FileOptions` extends `Zend\Stdlib\AbstractOptions`, and
inherits all functionality from that class; this includes property overloading.
Additionally, the following explicit setters and getters are provided.

### setPath

```php
setPath(string $path) : void
```

Set the path under which mail files will be written.

### getPath

```php
getPath() : string
```

Get the path under which mail files will be written.

### setCallback

```php
setCallback(callable $callback) : void
```

Set the callback used to generate unique filenames for messages.

### getCallback

```php
getCallback() : callable
```

Get the callback used to generate unique filenames for messages.

### \_\_construct

```php
__construct(null|array|Traversable $config) : void
```

Initialize the object. Allows passing a PHP array or `Traversable` object with
which to populate the instance.
