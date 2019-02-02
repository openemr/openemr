# Usage

## Creating a New URI

`Zend\Uri\UriFactory` will build a new URI from scratch if only a scheme is
passed to `Zend\Uri\UriFactory::factory()`.

### Creating a New URI with ZendUriUriFactory::factory()

```php
// To create a new URI from scratch, pass only the scheme
// followed by a colon.
$uri = Zend\Uri\UriFactory::factory('http:');

// $uri instanceof Zend\Uri\UriInterface
```

To create a new URI from scratch, pass only the scheme followed by a colon to
`Zend\Uri\UriFactory::factory()`. If an unsupported scheme is passed and no
scheme-specific class is specified, a
`Zend\Uri\Exception\InvalidArgumentException` will be thrown.

If the scheme or URI passed is supported, `Zend\Uri\UriFactory::factory()` will
return a class implementing `Zend\Uri\UriInterface` that specializes in the
scheme referenced.

> ### Supported schemes
>
> At the time of writing, zend-uri provides built-in support for the following
> schemes only: HTTP, HTTPS, MAILTO and FILE.

### Creating a New Custom-Class URI

You can specify a custom class to be used when using the `Zend\Uri\UriFactory`
by registering your class with the `UriFactory` using
`Zend\Uri\UriFactory::registerScheme($scheme, $class)`.  This enables you to
create your own URI class and instantiate new URI objects based on your own
custom classes.

The 2nd parameter passed to `Zend\Uri\UriFactory::registerScheme()` must be a
string with the name of a class implementing `Zend\Uri\UriInterface`. The class
must either be already loaded, or be loadable by the autoloader.

#### Creating a URI using a custom class

The following registers the `ftp` scheme with a custom URI class:

```php
use MyNamespace\MyClass;
use Zend\Uri\UriFactory

UriFactory::registerScheme('ftp', MyClass::class);

$ftpUri = UriFactory::factory(
    'ftp://user@ftp.example.com/path/file'
);

// $ftpUri is an instance of MyLibrary\MyClass, which implements
// Zend\Uri\UriInterface
```

## Manipulating an Existing URI

To manipulate an existing URI, pass the entire URI as a string to
`Zend\Uri\UriFactory::factory()`, and then manipulate the instance returned.

### Manipulating an Existing URI with Zend\\Uri\\UriFactory::factory()

```php
use Zend\Uri\UriFactory;

// To manipulate an existing URI, pass it in.
$uri = UriFactory::factory('http://www.zend.com');

// $uri instanceof Zend\Uri\UriInterface
```

The URI will be parsed and validated. If it is found to be invalid, a
`Zend\Uri\Exception\InvalidArgumentException` will be thrown immediately.
Otherwise, `Zend\Uri\UriFactory::factory()` will return a class implementing
`Zend\Uri\UriInterface` that specializes in the scheme to be manipulated.

## Common Instance Methods

The `Zend\Uri\UriInterface` defines several instance methods that are useful for
working with any kind of URI.

### Getting the Scheme of the URI

The scheme of the URI is the part of the URI that precedes the colon. For
example, the scheme of `http://johndoe@example.com/my/path?query#token` is
'http'.

```php
$uri = Zend\Uri\UriFactory::factory('mailto:john.doe@example.com');

$scheme = $uri->getScheme();  // "mailto"
```

The `getScheme()` instance method returns only the scheme part of the URI
object (not the separator).

### Getting the Userinfo of the URI

The userinfo of the URI is the optional part of the URI that follows the
colon and comes before the host-part. For example, the userinfo of
`http://johndoe@example.com/my/path?query#token` is 'johndoe'.

```php
$uri = Zend\Uri\UriFactory::factory('mailto:john.doe@example.com');

$scheme = $uri->getUserinfo();  // "john.doe"
```

The `getUserinfo()` method returns only the userinfo part of the URI object.

### Getting the host of the URI

The host of the URI is the optional part of the URI that follows the
user-part and comes before the path-part. For example, the host of
`http://johndoe@example.com/my/path?query#token` is 'example.com'.

```php
$uri = Zend\Uri\UriFactory::factory('mailto:john.doe@example.com');

$scheme = $uri->getHost();  // "example.com"
```

The `getHost()` method returns only the host part of the URI object.

### Getting the port of the URI

The port of the URI is the optional part of the URI that follows the host-part
and comes before the path-part. For example, the host of
`http://johndoe@example.com:80/my/path?query#token` is '80'.

```php
$uri = Zend\Uri\UriFactory::factory('http://example.com:8080');

$scheme = $uri->getPort();  // "8080"
```

Concrete URI instances can define default ports that can be returned when no
port is given in the URI:

```php
$uri = Zend\Uri\UriFactory::factory('http://example.com');

$scheme = $uri->getPort();  // "80"
```

The `getHost()` method returns only the port part of the URI object.

### Getting the path of the URI

The path of the URI is a mandatory part of the URI that follows the port
and comes before the query-part. For example, the path of
`http://johndoe@example.com:80/my/path?query#token` is '/my/path'.

```php
$uri = Zend\Uri\UriFactory::factory('http://example.com:80/my/path?a=b&c=d#token');

$scheme = $uri->getPath();  // "/my/path"
```

The `getPath()` method returns only the path of the URI object.

### Getting the query-part of the URI

The query-part of the URI is an optional part of the URI that follows the
path and comes before the fragment. For example, the query of
`http://johndoe@example.com:80/my/path?query#token` is 'query'.

```php
$uri = Zend\Uri\UriFactory::factory('http://example.com:80/my/path?a=b&c=d#token');

$scheme = $uri->getQuery();  // "a=b&c=d"
```

The `getQuery()` method returns only the query-part of the URI object.

The query string often contains key=value pairs and therefore can be split into an
associative array. This array can be retrieved using `getQueryAsArray()`:

```php
$uri = Zend\Uri\UriFactory::factory('http://example.com:80/my/path?a=b&c=d#token');

$scheme = $uri->getQueryAsArray();
// [
//  'a' => 'b',
//  'c' => 'd',
// ]
```

### Getting the fragment-part of the URI

The fragment-part of the URI is an optional part of the URI that follows
the query. For example, the fragment of
`http://johndoe@example.com:80/my/path?query#token` is 'token'.

```php
$uri = Zend\Uri\UriFactory::factory('http://example.com:80/my/path?a=b&c=d#token');

$scheme = $uri->getFragment();  // "token"
```

The `getFragment()` method returns only the fragment-part of the URI object.

### Getting the Entire URI

The `toString()` method returns the string representation of the entire *URI*.

```php
$uri = Zend\Uri\UriFactory::factory('http://www.zend.com');

echo $uri->toString();  // "http://www.zend.com"

// Alternate method:
echo (string) $uri;     // "http://www.zend.com"
```

The `Zend\Uri\UriInterface` defines also the magic `__toString()` method that
returns the string representation of the URI when the object is cast to a
string.

## Validating the URI

When using `Zend\Uri\UriFactory::factory()`, the given URI will always be
validated and a `Zend\Uri\Exception\InvalidArgumentException` will be thrown
when the URI is invalid. However, after the `Zend\Uri\UriInterface` is
instantiated for a new URI or an existing valid one, it is possible that the URI
can later become invalid after it is manipulated.

```php
$uri = Zend\Uri\UriFactory::factory('http://www.zend.com');

$isValid = $uri->isValid();  // TRUE
```

The `isValid()` instance method provides a means to check that the URI object
is still valid.
