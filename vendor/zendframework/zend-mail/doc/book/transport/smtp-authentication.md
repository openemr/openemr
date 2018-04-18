# SMTP Authentication

zend-mail supports the use of SMTP authentication, which can be enabled via
configuration.  The available built-in authentication methods are PLAIN, LOGIN,
and CRAM-MD5, all of which expect 'username' and 'password' values in the
configuration array.

## Configuration

In order to enable authentication, ou need to specify a "connection class" and
connection configuration when configuring your SMTP transport. The two settings
are briefly covered in the [SMTP transport configuration options](smtp-options.md#configuration-options). Below are more details.

### connection_class

The connection class should be a fully qualified class name of a
`Zend\Mail\Protocol\Smtp\Auth\*` class or extension, or the short name (name
without leading namespace). zend-mail ships with the following:

- `Zend\Mail\Protocol\Smtp\Auth\Plain`, or `plain`
- `Zend\Mail\Protocol\Smtp\Auth\Login`, or `login`
- `Zend\Mail\Protocol\Smtp\Auth\Crammd5`, or `crammd5`

Custom connection classes must be extensions of `Zend\Mail\Protocol\Smtp`.

### connection_config

The `connection_config` should be an associative array of options to provide to
the underlying connection class. All shipped connection classes require:

- `username`
- `password`

Optionally, ou may also provide:

- `ssl`: either the value `ssl` or `tls`.
- `port`: if using something other than the default port for the protocol used.
  Port 25 is the default used for non-SSL connections, 465 for SSL, and 587 for
  TLS.
- `use_complete_quit`: configuring whether or not an SMTP transport should
  issue a `QUIT` at `__destruct()` and/or end of script execution. Useful in
  long-running scripts against [SMTP servers that implements a reuse time limit](#smtp-transport-usage-for-servers-with-reuse-time-limit).

## Examples

### SMTP Transport Usage with PLAIN AUTH

```php
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

// Setup SMTP transport using PLAIN authentication
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => 'localhost.localdomain',
    'host'              => '127.0.0.1',
    'connection_class'  => 'plain',
    'connection_config' => [
        'username' => 'user',
        'password' => 'pass',
    ],
]);
$transport->setOptions($options);
```

### SMTP Transport Usage with LOGIN AUTH

```php
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

// Setup SMTP transport using LOGIN authentication
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => 'localhost.localdomain',
    'host'              => '127.0.0.1',
    'connection_class'  => 'login',
    'connection_config' => [
        'username' => 'user',
        'password' => 'pass',
    ],
]);
$transport->setOptions($options);
```

### SMTP Transport Usage with CRAM-MD5 AUTH

```php
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

// Setup SMTP transport using CRAM-MD5 authentication
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => 'localhost.localdomain',
    'host'              => '127.0.0.1',
    'connection_class'  => 'crammd5',
    'connection_config' => [
        'username' => 'user',
        'password' => 'pass',
    ],
]);
$transport->setOptions($options);
```

### SMTP Transport Usage with PLAIN AUTH over TLS

```php
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

// Setup SMTP transport using PLAIN authentication over TLS
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => 'example.com',
    'host'              => '127.0.0.1',
    'port'              => 587,
    // Notice port change for TLS is 587
    'connection_class'  => 'plain',
    'connection_config' => [
        'username' => 'user',
        'password' => 'pass',
        'ssl'      => 'tls',
    ],
]);
$transport->setOptions($options);
```

### SMTP Transport Usage for servers with reuse time limit

By default, every `Zend\Mail\Protocol\Smtp\*` class tries to disconnect from
the STMP server by sending a `QUIT` command and expecting a `221` (_Service
closing transmission channel_) response code.  This is done automatically at
object destruction (via the `__destruct()` method), and can generate errors
with SMTP servers like [Posftix](http://www.postfix.org/postconf.5.html#smtp_connection_reuse_time_limit)
that implement a reuse time limit:

```php
// [...]
$transport->send($message);

var_dump('E-mail sent');
sleep(305);
var_dump('Soon to exit...');
exit;

// E-mail sent
// Soon to exit...
// Notice: fwrite(): send of 6 bytes failed with errno=32 Broken pipe in ./zend-mail/src/Protocol/AbstractProtocol.php on line 255
// Fatal error: Uncaught Zend\Mail\Protocol\Exception\RuntimeException: Could not read from 127.0.0.1 in ./zend-mail/src/Protocol/AbstractProtocol.php:301
```

To avoid this error, you can configure any `Zend\Mail\Protocol\Smtp\*` instance
to close the connection with the SMTP server without the `QUIT` command:

```php
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

// Setup SMTP transport to exit without the `QUIT` command
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => 'localhost.localdomain',
    'host'              => '127.0.0.1',
    'connection_class'  => 'plain',
    'connection_config' => [
        'username'          => 'user',
        'password'          => 'pass',
        'use_complete_quit' => false,
    ],
]);
$transport->setOptions($options);
```

> ### NOTE: recreate old connection
>
> The flag described above aims to avoid errors that you cannot manage from PHP.
>
> If you deal with SMTP servers that exhibit this behavior from within
> long-running scripts, you will need to:
> 
> 1. Trace the time elapsed since the creation of the connection.
> 1. Close and reopen the connection if the elapsed time exceeds the reuse
>    time limit
> 
> As an example, you can perform these steps by applying a proxy class that
> wraps the real `Zend\Mail\Protocol\Smtp\*` instance to track the construction
> time, and then close and open the connection when needed.
