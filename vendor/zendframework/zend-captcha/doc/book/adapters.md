# CAPTCHA Adapters

zend-captcha defines an abstraction for CAPTCHA implementations via
`Zend\Captcha\AdapterInterface`, and provides several implementations.

## The AdapterInterface

All CAPTCHA adapters implement `Zend\Captcha\AdapterInterface`:

```php
namespace Zend\Captcha;

use Zend\Validator\ValidatorInterface;

interface AdapterInterface extends ValidatorInterface
{
    public function generate();

    public function setName($name);

    public function getName();

    // Get helper name used for rendering this CAPTCHA type
    public function getHelperName();
}
```

The `name` setter and getter are used to specify and retrieve the CAPTCHA identifier.

The most interesting method is `generate()`, which is used to create the CAPTCHA
token. This process typically will store the token in the session so that you
may compare against it in subsequent requests.

Most implementations also define a `render()` method (or delegate to one) in
order to produce a CAPTCHA representation, be it an image, a figlet, a logic
problem, or some other CAPTCHA.

## Zend\\Captcha\\AbstractWord

`Zend\Captcha\AbstractWord` is an abstract adapter that serves as the base class
for most other CAPTCHA adapters. It provides mutators for specifying word
length, session TTL and the session container object to use; it also
encapsulates validation logic.

By default, the word length is 8 characters, the session timeout is 5 minutes,
and `Zend\Session\Container` is used for persistence (using the namespace
"`Zend\Form\Captcha\<captcha ID>`").

In addition to the methods required by the `Zend\Captcha\AdapterInterface` interface,
`Zend\Captcha\AbstractWord` exposes the following methods:

- `setWordLen($length)` and `getWordLen()` allow you to specify the length of
  the generated "word" in characters, and to retrieve the current value.
- `setTimeout($ttl)` and `getTimeout()` allow you to specify the time-to-live of
  the session token, and to retrieve the current value. `$ttl` should be
  specified in *seconds*.
- `setUseNumbers($numbers)` and `getUseNumbers()` allow you to specify if
  numbers will be considered as possible characters for the random word, or if
  only letters would be used.
- `setSessionClass($class)` and `getSessionClass()` allow you to specify an
  alternate `Zend\Session\Container` implementation to use to persist the
  CAPTCHA token and to retrieve the current value.
- `getId()` allows you to retrieve the current token identifier.
- `getWord()` allows you to retrieve the generated word to use with the CAPTCHA.
  It will generate the word for you if none has been generated yet.
- `setSession(Zend\Session\Container $session)` allows you to specify a session
  object to use for persisting the CAPTCHA token. `getSession()` allows you to
  retrieve the current session object.

All word CAPTCHAs allow you to pass an array of options or a `Traversable`
object to the constructor, or, alternately, pass them to `setOptions()`. By
default, the `wordLen`, `timeout`, and `sessionClass` keys may all be used. Each
concrete implementation may define additional keys or utilize the options in
other ways.

> ### AbstractWord is marked abstract
>
> `Zend\Captcha\AbstractWord` is an abstract class and may not be instantiated
> directly.

## Zend\\Captcha\\Dumb

The `Zend\Captcha\Dumb` adapter is mostly self-descriptive. It provides a random
string that must be typed in reverse to validate. As such, it's not a good
CAPTCHA solution and should only be used for testing. It extends
`Zend\Captcha\AbstractWord`.

## Zend\\Captcha\\Figlet

The `Zend\Captcha\Figlet` adapter utilizes `Zend\Text\Figlet` to present a
figlet to the user.

Options passed to the constructor will also be passed to the
`Zend\Text\Figlet` object. See the
[`Zend\Text\Figlet`](https://zendframework.github.io/zend-text/figlet/)
documentation for details on what configuration options are available.

## Zend\\Captcha\\Image

The `Zend\Captcha\Image` adapter takes the generated word and renders it as an
image, performing various skewing permutations to make it difficult to
automatically decipher. It requires the [GD extension](http://php.net/gd),
compiled with TrueType or Freetype support. Currently, the `Zend\Captcha\Image`
adapter can only generate PNG images.

`Zend\Captcha\Image` extends `Zend\Captcha\AbstractWord`, and additionally
exposes the following methods:

- `setExpiration($expiration)` and `getExpiration()` allow you to specify a
  maximum lifetime the CAPTCHA image may reside on the filesystem. This is
  typically longer than the session lifetime.  Garbage collection is run
  periodically each time the CAPTCHA object is invoked, deleting all images
  that have expired. Expiration values should be specified in *seconds*.
- `setGcFreq($gcFreq)` and `getGcFreg()` allow you to specify how frequently
  garbage collection should run. Garbage collection will run every `1/$gcFreq`
  calls. The default is 100.
- `setFont($font)` and `getFont()` allow you to specify the font you will use.
  `$font` should be a fully qualified path to the font file. This value is
  required; the CAPTCHA will throw an exception during generation if the font
  file has not been specified.
- `setFontSize($fsize)` and `getFontSize()` allow you to specify the font size
  in pixels for generating the CAPTCHA. The default is 24px.
- `setHeight($height)` and `getHeight()` allow you to specify the height in
  pixels of the generated CAPTCHA image. The default is 50px.
- `setWidth($width)` and `getWidth()` allow you to specify the width in pixels
  of the generated CAPTCHA image. The default is 200px.
- `setImgDir($imgDir)` and `getImgDir()` allow you to specify the directory for
  storing CAPTCHA images. The default is `./images/captcha/`, relative to
  the bootstrap script (typically `public/index.php`, resulting in
  `public/images/captcha/`).
- `setImgUrl($imgUrl)` and `getImgUrl()` allow you to specify the relative path
  to a CAPTCHA image to use for HTML markup. The default is
  `/images/captcha/`.
- `setSuffix($suffix)` and `getSuffix()` allow you to specify the filename
  suffix for the CAPTCHA image. The default is `.png`. Note: changing this
  value will not change the type of the generated image.
- `setDotNoiseLevel($level)` and `getDotNoiseLevel()`, along with
  `setLineNoiseLevel($level)` and `getLineNoiseLevel()`, allow you to control
  how much "noise" in the form of random dots and lines the image would contain.
  Each unit of `$level` produces one random dot or line. The default is 100 dots
  and 5 lines. The noise is added twice, both before and after the image
  distortion transformation.

All of the above options may be passed to the constructor by simply removing the
'set' method prefix and casting the initial letter to lowercase: "suffix",
"height", "imgUrl", etc.

## Zend\\Captcha\\ReCaptcha

The `Zend\Captcha\ReCaptcha` adapter uses [`Zend\Service\ReCaptcha\ReCaptcha`](https://github.com/zendframework/ZendService_ReCaptcha)
to generate and validate CAPTCHAs.  It exposes the following methods:

- `setSecretKey($key)` and `getSecretKey()` allow you to specify the secret key to
  use for the ReCaptcha service. This must be specified during construction,
  although it may be overridden at any point.
- `setSiteKey($key)` and `getSiteKey()` allow you to specify the site key to use
  with the ReCaptcha service. This must be specified during construction,
  although it may be overridden at any point.
- `setService(ZendService\ReCaptcha\ReCaptcha $service)` and `getService()`
  allow you to set and get the ReCaptcha service object.

When constructing `Zend\Captcha\ReCaptcha`, you can use the same set of keys
to the `$options` array as supported by [`Zend\Service\ReCaptcha\ReCaptcha`](https://github.com/zendframework/ZendService_ReCaptcha).

### Updating from v2 to v3 of this adapter

As this adapter takes the same option keys as [`Zend\Service\ReCaptcha\ReCaptcha`](https://github.com/zendframework/ZendService_ReCaptcha) which supports Recaptcha API v2, this 
component no longer supports the `ssl`, `xhtml` and `lang` keys in the `$options`
array that is passed to the constructor.

The options keys `pubKey` and `privKey` and the getters and setters for these
keys are supported in this version, but are deprecated.


