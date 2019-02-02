# Messages

`Zend\Mail\Message` encapsulates a single email message as described in RFCs
[822](http://www.w3.org/Protocols/rfc822/) and
[2822](http://www.ietf.org/rfc/rfc2822.txt). It acts as a value object for
setting mail headers and content.

If desired, multi-part email messages may also be created. This can be done
using the [zend-mime](https://github.com/zendframework/zend-mime) component,
and assigning the generated MIME part to the mail message body.

The `Message` class is a value object. It is not capable of sending or storing itself; for
those purposes, you will need to use, respectively, a [Transport adapter](../transport/intro.md) or
a [Storage adapter](../read.md).

## Quick Start

Creating a `Message` by instantiating it:

```php
use Zend\Mail\Message;

$message = new Message();
```

Once you have your `Message` instance, you can start adding content or headers.
Let's set who the mail is from, who it's addressed to, a subject, and some
content:

```php
$message->addFrom('matthew@example.org', 'Matthew Somelli');
$message->addTo('foobar@example.com');
$message->setSubject('Sending an email from Zend\Mail!');
$message->setBody('This is the message body.');
```

You can also add recipients to carbon-copy ("Cc:") or blind carbon-copy
("Bcc:").

```php
$message->addCc('ralph@example.org');
$message->addBcc('enrico@example.org');
```

If you want to specify an alternate address to which replies may be sent, that
can be done, too.

```php
$message->addReplyTo('matthew@example.com', 'Matthew');
```

Interestingly, RFC-822 allows for multiple "From:" addresses. When you do this,
the first one will be used as the sender, **unless** you specify a "Sender:"
header. The `Message` class allows for this.

```php
/*
 * Mail headers created:
 * From: Ralph Nader <ralph@example.org>, Enrico Volante <enrico@example.org>
 * Sender: Matthew Sommeli <matthew@example.org>
 */
$message->addFrom('ralph@example.org', 'Ralph Nader');
$message->addFrom('enrico@example.org', 'Enrico Volante');
$message->setSender('matthew@example.org', 'Matthew Sommeli');
```

By default, the `Message` class assumes ASCII encoding for your email. If you
wish to use another encoding, you can do so; setting this will ensure all
headers and body content are properly encoded using quoted-printable encoding.

```php
$message->setEncoding('UTF-8');
```

If you wish to set other headers, you can do that as well.

```php
/*
 * Mail headers created:
 * X-API-Key: FOO-BAR-BAZ-BAT
 */
$message->getHeaders()->addHeaderLine('X-API-Key', 'FOO-BAR-BAZ-BAT');
```

Sometimes you may want to provide HTML content, or multi-part content. To do
that, you'll first create a MIME message object, and then set it as the body of
your mail message object. When you do so, the `Message` class will automatically
set a "MIME-Version" header, as well as an appropriate "Content-Type" header.

If you are interested in multipart emails or using attachments, read the chapter
on [Adding Attachments](attachments.md).

If you want a string representation of your email, you can get that:

```php
echo $message->toString();
```

Finally, you can fully introspect the message, including getting all addresses
of recipients and senders, all headers, and the message body.

```php
// Headers
// Note: this will also grab all headers for which accessors/mutators exist in
// the Message object itself.
foreach ($message->getHeaders() as $header) {
    echo $header->toString();
    // or grab values: $header->getFieldName(), $header->getFieldValue()
}

// The logic below also works for the methods cc(), bcc(), to(), and replyTo()
foreach ($message->getFrom() as $address) {
    printf("%s: %s\n", $address->getEmail(), $address->getName());
}

// Sender
$address = $message->getSender();
if (! is_null($address)) {
   printf("%s: %s\n", $address->getEmail(), $address->getName());
}

// Subject
echo "Subject: ", $message->getSubject(), "\n";

// Encoding
echo "Encoding: ", $message->getEncoding(), "\n";

// Message body:
echo $message->getBody();     // raw body, or MIME object
echo $message->getBodyText(); // body as it will be sent
```

Once your message is shaped to your liking, pass it to a
[mail transport](../transport/intro.md) in order to send it!

```php
$transport->send($message);
```

## Configuration Options

The `Message` class has no configuration options, and is instead a value object.

## Available Methods

### isValid

```php
isValid() : bool
```

Messages without a `From` address are invalid, per RFC-2822.

### setEncoding

```php
setEncoding(string $encoding) : void
```

Set the message encoding.

### getEncoding

```php
getEncoding() : string
```

Get the message encoding.

### setHeaders

```php
setHeaders(Zend\Mail\Headers $headers) : void
```

Compose headers.

### getHeaders

```php
getHeaders() : Zend\Mail\Headers
```

Access headers collection, lazy-loading a `Headers` instance if none was
previously attached.

### setFrom

```php
setFrom(
    string|AddressInterface|array|AddressList|Traversable $emailOrAddressList,
    string|null $name
) : void
```

Set (overwrite) `From` addresses. If an associative array is provided, it must
be a set of key/value pairs where the key is the human readable name, and the
value is the email address.

### addFrom

```php
addFrom(
    string|AddressInterface|array|AddressList|Traversable $emailOrAddressOrList,
    string|null $name
) : void
```

Add a `From` address. If an associative array is provided, it must be a set of
key/value pairs where the key is the human readable name, and the value is the
email address.

### getFrom

```php
getFrom() : AddressList
```

Retrieve list of `From` senders.

### setTo

```php
setTo(
    string|AddressInterface|array|AddressList|Traversable $emailOrAddressList,
    null|string $name
) : void
```

Overwrite the address list in the `To` recipients. If an associative array is
provided, it must be a set of key/value pairs where the key is the human
readable name, and the value is the email address.

### addTo

```php
addTo(
    string|AddressInterface|array|AddressList|Traversable $emailOrAddressOrList,
    null|string $name
) : void
```

Add one or more addresses to the `To` recipients; appends to the list. If an
associative array is provided, it must be a set of key/value pairs where the key
is the human readable name, and the value is the email address.

### getTo

```php
getTo() : AddressList
```

Access the address list of the `To` header.  Lazy-loads an `AddressList` and
populates the `To` header if not previously done.

### setCc

```php
setCc(
    string|AddressInterface|array|AddressList|Traversable $emailOrAddressList,
    string|null $name
) : void
```

Set (overwrite) `Cc` addresses. If an associative array is provided, it must be
a set of key/value pairs where the key is the human readable name, and the value
is the email address.

### addCc

```php
addCc(
    string|AddressInterface|array|AddressList|Traversable $emailOrAddressList,
    string|null $name
) : void
```

Add a `Cc` address. If an associative array is provided, it must be a set of
key/value pairs where the key is the human readable name, and the value is the
email address.

### getCc

```php
getCc() : AddressList
```

Retrieve list of `Cc` recipients.  Lazy-loads an `AddressList` and populates the
`Cc` header if not previously done.

### setBcc

```php
setBcc(
    string|AddressInterface|array|AddressList|Traversable $emailOrAddressList,
    string|null $name
) : void
```

Set (overwrite) `Bcc` addresses. If an associative array is provided, it must be
a set of key/value pairs where the key is the human readable name, and the value
is the email address.

### addBcc

```php
addBcc(
    string|AddressInterface|array|AddressList|Traversable $emailOrAddressList,
    string|null $name
) : void
```

Add a `Bcc` address. If an associative array is provided, it must be a set of
key/value pairs where the key is the human readable name, and the value is the
email address.

### getBcc

```php
getBcc() : AddressList
```

Retrieve list of `Bcc` recipients.  Lazy-loads an `AddressList` and populates
the `Bcc` header if not previously done.

### setReplyTo

```php
setReplyTo(
    string|AddressInterface|array|AddressList|Traversable $emailOrAddressList,
    string|null $name
) : void
```

Overwrite the address list in the `Reply-To` recipients. If an associative array
is provided, it must be a set of key/value pairs where the key is the human
readable name, and the value is the email address.

### addReplyTo

```php
addReplyTo(
    string|AddressInterface|array|AddressList|Traversable $emailOrAddressList,
    string|null $name
) : void
```

Add one or more addresses to the `Reply-To` recipients. If an associative array
is provided, it must be a set of key/value pairs where the key is the human
readable name, and the value is the email address.

### getReplyTo

```php
getReplyTo() : AddressList
```

Access the address list of the `Reply-To` header.  Lazy-loads an `AddressList`
and populates the `Reply-To` header if not previously done.

### setSender

```php
setSender(
    string|AddressInterface $emailOrAddress,
    null|string $name
) : void
```

Set the message envelope `Sender` header.

### getSender

```php
getSender() : null|AddressInterface
```

Retrieve the sender address, if any.

### setSubject

```php
setSubject(string $subject) :void
```

Set the message subject header value.

### getSubject

```php
getSubject() : null|string
```

Get the message subject header value.

### setBody

```php
setBody(null|string|Zend\Mime\Message|object $body) : void
```

Set the message body. If a generic object is provided, it must implement
`__toString()`.

### getBody

```php
getBody() : null|string|object
```

Return the currently set message body. Object return values include
`Zend\Mime\Message` instances or objects implementing `__toString()`.

### getBodyText

```php
getBodyText() : null|string
```

Get the string-serialized message body text.

### toString

```php
toString() : string
```

Serialize to string.
