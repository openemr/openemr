# Character Sets

`Zend\Mail\Message` assumes a default ASCII character set, and headers and
content are quoted accordingly. If you wish to specify alternate characters
sets, you will need to:

- Notify the `Message` instance of the desired character-set encoding, to ensure
  headers are encoded correctly.
- Set an appropriate `Content-Type` header.
- In multipart messages, set the character set per-part.

> ### Only in text format
> 
> Character sets are only applicable for message parts in text format.

## Example

The following example is how to use `Zend\Mail\Message` to send a message in
Japanese.

```php
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

// Typically, PHP will use UTF-8 internally; the following converts
// the text to a Japanese encoding.
function convertToJapaneseEncoding($string) {
    return mb_convert_encoding($string, 'ISO-2022-JP', 'UTF-8');
}

$mail = new Message();

// Set message encoding; this only affects headers.
$mail->setEncoding('ISO-2022-JP');

// Set the message content type:
$mail->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=ISO-2022-JP');

// Add some headers. Textual content needs to be encoded first.
$mail->setFrom('somebody@example.com', convertToJapaneseEncoding('Some Sender'));
$mail->addTo('somebody_else@example.com', convertToJapaneseEncoding('Some Recipient'));
$mail->setSubject(convertToJapaneseEncoding('TestSubject'));

// Create a MIME part specifying 7bit encoding:
$part = new MimePart(convertToJapaneseEncoding($content));
$part->encoding = Mime::ENCODING_7BIT;

// Create a MIME message, add the part, and attach it to the mail message:
$body = new MimeMessage();
$body->addPart($part);
$mail->setBody($body);
```
