# SMTP Transport Options

This document details the various options available to the
`Zend\Mail\Transport\Smtp` mail transport.

## Quick Start

### Basic SMTP Transport Usage

```php
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

// Setup SMTP transport
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name' => 'localhost.localdomain',
    'host' => '127.0.0.1',
    'port' => 25,
]);
$transport->setOptions($options);
```

If you require authentication, see the section on [SMTP authentication](smtp-authentication.md#examples)
for examples of configuring authentication credentials.

## Configuration Options

Option name         | Description
------------------- | -----------
`name`              | Name of the SMTP host; defaults to "localhost".
`host`              | Remote hostname or IP address; defaults to "127.0.0.1".
`port`              | Port on which the remote host is listening; defaults to "25".
`connection_class`  | Fully-qualified classname or short name resolvable via `Zend\Mail\Protocol\SmtpPluginManager`. See the [SMTP authentication](smtp-authentication.md#connection_class) documentation for details.
`connection_config` | Optional associative array of parameters to pass to the connection class in order to configure it. By default, this is empty. See the [SMTP authentication](smtp-authentication.md#connection_config) documentation for details.

## Available Methods

### getName

```php
getName() : string
```

Returns the string name of the local client hostname.

### setName

```php
setName(string $name) : void
```

Set the string name of the local client hostname.

### getConnectionClass

```php
getConnectionClass() : string
```

Returns a string indicating the connection class name to use.

### setConnectionClass

```php
setConnectionClass(string $connectionClass) : void
```

Set the connection class to use.

### getConnectionConfig

```php
getConnectionConfig() : array
```

Get configuration for the connection class.

### setConnectionConfig

```php
setConnectionConfig(array $config) : void
```

Set configuration for the connection class. Typically, if using anything other
than the default connection class, this will be an associative array with the
keys "username" and "password".

### getHost

```php
getHost() : string
```

Returns a string indicating the IP address or host name of the SMTP server via
which to send messages.

### setHost

```php
setHost(string $host) : void
```

Set the SMTP host name or IP address.

### getPort

```php
getPort() : int
```

Retrieve the integer port on which the SMTP host is listening.

### setPort

```php
setPort(int $port) : void
```

Set the port on which the SMTP host is listening.

### \_\_construct

```php
__construct(null|array|Traversable $config) : void
```

Instantiate the class, and optionally configure it with values provided.
