# Sending Multiple Mails per SMTP Connection

By default, a single SMTP transport creates a single connection and re-uses it
for the lifetime of the script execution. You may send multiple e-mails through
this SMTP connection. A `RSET` command is issued before each delivery to ensure
the correct SMTP handshake is followed.

## Examples

### Sending Multiple Mails per SMTP Connection

```php
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;

// Create transport
$transport = new Smtp([
    'host' => 'mail.example.com'
]);

// Create a base message:
$template = new Message();
$template->addFrom('sender@example.com', 'John Doe');
$template->addReplyTo('replyto@example.com', 'Jane Doe');
$template->setSubject('Demo of multiple mails per SMTP connection');
$template->setBody('... Your message here ...');

// Loop through recipients:
foreach ($recipients as $address) {
    // Clone the message and add a recipient:
    $message = clone $template;
    $message->addTo($template);

    $transport->send($message);
}
```

If you wish to have a separate connection for each mail delivery, you will need
to create and destroy your transport before and after each `send()` method is
called.

### Manipulating the transport between messages

You can manipulate the connection between each delivery by accessing the
transport's protocol object.

```php
use Zend\Mail\Message;
use Zend\Mail\Protocol\Smtp as SmtpProtocol;
use Zend\Mail\Transport\Smtp as SmtpTransport;

// Create transport
$transport = new SmtpTransport();

$protocol = new SmtpProtocol('mail.example.com');
$protocol->connect();
$protocol->helo('sender.example.com');

$transport->setConnection($protocol);

// Loop through messages
foreach ($recipients as $address) {
    $mail = new Message();
    $mail->addTo($address);
    $mail->setFrom('studio@example.com', 'Test');
    $mail->setSubject(
        'Demonstration - Sending Multiple Mails per SMTP Connection'
    );
    $mail->setBodyText('...Your message here...');

    // Manually control the connection
    $protocol->rset();
    $transport->send($message);
}

$protocol->quit();
$protocol->disconnect();
```
