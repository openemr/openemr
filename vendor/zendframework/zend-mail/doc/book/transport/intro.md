# Transports

Transports take care of the actual delivery of mail. Typically, you only need to
worry about two possibilities: using PHP's native `mail()` functionality, which
uses system resources to deliver mail, or using the SMTP protocol for delivering
mail via a remote server. Zend Framework also includes a "File" transport, which
creates a mail file for each message sent; these can later be introspected as
logs or consumed for the purposes of sending via an alternate transport
mechanism later.

The `Zend\Mail\Transport` interface defines exactly one method, `send()`. This
method accepts a `Zend\Mail\Message` instance, which it then introspects and
serializes in order to send.

## Quick Start

Using a mail transport involves instantiating it, optionally configuring it, and
then passing a message to it.

### Sendmail Transport Usage

```php
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as SendmailTransport;

$message = new Message();
$message->addTo('matthew@example.org');
$message->addFrom('ralph@example.org');
$message->setSubject('Greetings and Salutations!');
$message->setBody("Sorry, I'm going to be late today!");

$transport = new SendmailTransport();
$transport->send($message);
```

### SMTP Transport Usage

```php
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

$message = new Message();
$message->addTo('matthew@example.org');
$message->addFrom('ralph@example.org');
$message->setSubject('Greetings and Salutations!');
$message->setBody("Sorry, I'm going to be late today!");

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
$transport->send($message);
```

### File Transport Usage

```php
use Zend\Mail\Message;
use Zend\Mail\Transport\File as FileTransport;
use Zend\Mail\Transport\FileOptions;
use Zend\Math\Rand;

$message = new Message();
$message->addTo('matthew@example.org');
$message->addFrom('ralph@example.org');
$message->setSubject('Greetings and Salutations!');
$message->setBody("Sorry, I'm going to be late today!");

// Setup File transport
$transport = new FileTransport();
$options   = new FileOptions([
    'path'     => 'data/mail/',
    'callback' => function (FileTransport $transport) {
        return sprintf(
            'Message_%f_%s.txt',
            microtime(true),
            Rand::getString(8)
        );
    },
]);
$transport->setOptions($options);
$transport->send($message);
```

### InMemory Transport Usage

```php
use Zend\Mail\Message;
use Zend\Mail\Transport\InMemory as InMemoryTransport;

$message = new Message();
$message->addTo('matthew@example.org');
$message->addFrom('ralph@example.org');
$message->setSubject('Greetings and Salutations!');
$message->setBody("Sorry, I'm going to be late today!");

// Setup InMemory transport
$transport = new InMemoryTransport();
$transport->send($message);

// Verify the message:
$received = $transport->getLastMessage();
```

The `InMemory` transport is primarily of interest when in development or when
testing.

### Migration from 2.0-2.3 to 2.4+

Version 2.4 adds support for PHP 7. In PHP 7, `null` is a reserved keyword,
which required renaming the `Null` transport. If you were using the `Null`
transport directly previously, you will now receive an `E_USER_DEPRECATED`
notice on instantiation. Please update your code to refer to the `InMemory`
class instead.

Users pulling their `Null` transport instance from the transport factory
(`Zend\Mail\Transport\Factory`) receive an `InMemory` instance instead starting
in 2.4.0.

## Configuration Options

Configuration options are per transport. Please follow the links below for
transport-specific options.

- [SMTP Transport Options](smtp-options.md)
- [File Transport Options](file-options.md)

## Available Methods

### send

```php
send(Zend\Mail\Message $message) : void
```

Send a mail message.
