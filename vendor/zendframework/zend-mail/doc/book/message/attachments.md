# Adding Attachments

zend-mail does not directly provide the ability to create and use mail
attachments. However, it allows using `Zend\Mime\Message` instances, from the
[zend-mime](https://github.com/zendframework/zend-mime) component, for message
bodies, allowing you to create multipart emails.

## Basic multipart content

The following example creates an email with two parts, HTML content and an
image.

```php
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

$html = new MimePart($htmlMarkup);
$html->type = Mime::TYPE_HTML;
$html->charset = 'utf-8';
$html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

$image = new MimePart(fopen($pathToImage, 'r'));
$image->type = 'image/jpeg';
$image->filename = 'image-file-name.jpg';
$image->disposition = Mime::DISPOSITION_ATTACHMENT;
$image->encoding = Mime::ENCODING_BASE64;

$body = new MimeMessage();
$body->setParts([$html, $image]);

$message = new Message();
$message->setBody($body);

$contentTypeHeader = $message->getHeaders()->get('Content-Type');
$contentTypeHeader->setType('multipart/related');
```

Note that the above code requires us to manually specify the message content
type; zend-mime does not automatically select the multipart type for us, nor
does zend-mail populate it by default.

## multipart/alternative content

One of the most common email types sent by web applications is
`multipart/alternative` messages with both text and HTML parts.

```php
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

$text = new MimePart($textContent);
$text->type = Mime::TYPE_TEXT;
$text->charset = 'utf-8';
$text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

$html = new MimePart($htmlMarkup);
$html->type = Mime::TYPE_HTML;
$html->charset = 'utf-8';
$html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

$body = new MimeMessage();
$body->setParts([$text, $html]);

$message = new Message();
$message->setBody($body);

$contentTypeHeader = $message->getHeaders()->get('Content-Type');
$contentTypeHeader->setType('multipart/alternative');
```

The only differences from the first example are:

- We have text and HTML parts instead of an HTML and image part.
- The `Content-Type` header is now `multipart/alternative`.

## multipart/alternative emails with attachments

Another common task is creating `multipart/alternative` emails where the HTML
content refers to assets attachments (images, CSS, etc.).

To accomplish this, we need to:

- Create a `Zend\Mime\Part` instance containing our `multipart/alternative`
  message.
- Add that part to a `Zend\Mime\Message`.
- Add additional `Zend\Mime\Part` instances to the MIME message.
- Attach the MIME message as the `Zend\Mail\Message` content body.
- Mark the message as `multipart/related` content.

The following example creates a MIME message with three parts: text and HTML
alternative versions of an email, and an image attachment.

```php
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

$body = new MimeMessage();

$text           = new MimePart($textContent);
$text->type     = Mime::TYPE_TEXT;
$text->charset  = 'utf-8';
$text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

$html           = new MimePart($htmlMarkup);
$html->type     = Mime::TYPE_HTML;
$html->charset  = 'utf-8';
$html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

$content = new MimeMessage();
// This order is important for email clients to properly display the correct version of the content
$content->setParts([$text, $html]);

$contentPart = new MimePart($content->generateMessage());

$image              = new MimePart(fopen($pathToImage, 'r'));
$image->type        = 'image/jpeg';
$image->filename    = 'image-file-name.jpg';
$image->disposition = Mime::DISPOSITION_ATTACHMENT;
$image->encoding    = Mime::ENCODING_BASE64;

$body = new MimeMessage();
$body->setParts([$contentPart, $image]);

$message = new Message();
$message->setBody($body);

$contentTypeHeader = $message->getHeaders()->get('Content-Type');
$contentTypeHeader->setType('multipart/related');
```

## Setting custom MIME boundaries

In a multipart message, a MIME boundary for separating the different parts of
the message is normally generated at random. In some cases, however, you might
want to specify the MIME boundary that is used. This can be done by injecting a
new `Zend\Mime\Mime` instance into the MIME message.

```php
use Zend\Mime\Mime;

$mimeMessage->setMime(new Mime($customBoundary));
```
