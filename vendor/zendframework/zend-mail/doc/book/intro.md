# Introduction

zend-mail provides generalized functionality to compose and send both text and
MIME-compliant multipart email messages. Mail can be sent with zend-mail via any
of the Sendmail, SMTP, or file-based transports it defines.  You can also
implement your own transport by implementing the
`Zend\Mail\Transport\TransportInterface`.

## Basic email

A basic email consists of one or more recipients, a subject, a body and a
sender. To send such a mail using `Zend\Mail\Transport\Sendmail`, do the
following:

```php
use Zend\Mail;

$mail = new Mail\Message();
$mail->setBody('This is the text of the email.');
$mail->setFrom('Freeaqingme@example.org', "Sender's name");
$mail->addTo('Matthew@example.com', 'Name of recipient');
$mail->setSubject('TestSubject');

$transport = new Mail\Transport\Sendmail();
$transport->send($mail);
```

> ### Minimum definitions
>
> In order to send an email using zend-mail you have to specify at least one
> recipient as well as a message body. Please note that each transport may
> require additional parameters to be set.

For most mail attributes there are "get" methods to read the information stored
in the message object. for further details, please refer to the API
documentation.

## Configuring the default sendmail transport

The default transport is `Zend\Mail\Transport\Sendmail`. It is a wrapper to the
PHP [mail()](http://php.net/mail) function. If you wish to pass additional
parameters to the [mail()](http://php.net/mail) function, create a new transport
instance and pass your parameters to the constructor.

### Passing additional mail() parameters

This example shows how to change the Return-Path of the
[mail()](http://php.net/mail) function.

```php
use Zend\Mail;

$mail = new Mail\Message();
$mail->setBody('This is the text of the email.');
$mail->setFrom('Freeaqingme@example.org', 'Dolf');
$mail->addTo('matthew@example.com', 'Matthew');
$mail->setSubject('TestSubject');

$transport = new Mail\Transport\Sendmail('-freturn_to_me@example.com');
$transport->send($mail);
```

> ### Chose your transport wisely
>
> Although the sendmail transport is the transport that requires least
> configuration, it may not be suitable for your production environment. This is
> because emails sent using the sendmail transport will be more often delivered
> to SPAM-boxes. This can partly be remedied by using the
> [SMTP Transport](transport/intro.md#smtp-transport-usage) combined with an SMTP
> server that has an overall good reputation. Additionally, techniques such as
> SPF and DKIM may be employed to ensure even more email messages are delivered
> successfully.

> ### Warning: Sendmail Transport and Windows
>
> As the PHP manual states, the `mail()` function has different behaviour on
> Windows than it does on \*nix based systems. Using the sendmail transport on
> Windows will not work in combination with `addBcc()`.  The `mail()` function
> will send to the BCC recipient such that all the other recipients can see that
> address as the recipient!
>
> Therefore if you want to use BCC on a windows server, use the SMTP transport
> for sending!
