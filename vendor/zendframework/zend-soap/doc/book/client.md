# Zend\\Soap\\Client

The `Zend\Soap\Client` class simplifies SOAP client development for PHP
programmers, and may be used in either WSDL or non-WSDL mode.

Under WSDL mode, `Zend\Soap\Client` uses a WSDL document to define transport
layer options.

The WSDL description is usually provided by the web service the client will
access.  If the WSDL description is not made available, you may want to use
`Zend\Soap\Client` in non-WSDL mode. Under this mode, all SOAP protocol options
have to be set explicitly on the `Zend\Soap\Client` class.

## Instantiation

The `Zend\Soap\Client` constructor takes two parameters:

- `$wsdl` - the URI of a WSDL file.
- `$options` - options for modifying the behavior of the client instance.

Both of these parameters may be set later using the `setWsdl($wsdl)` and
`setOptions($options)` methods respectively.

> ### Non-WSDL mode requirements
>
> If you use `Zend\Soap\Client` component in non-WSDL mode, you **must** set the
> 'location' and 'uri' options.

The following options are recognized:

- `soap_version` (`soapVersion`) - soap version to use (`SOAP_1_1` or
  `SOAP_1_2`).
- `classmap` (`classMap`) - maps WSDL types to PHP classes; option must be an
  array where keys are the WSDL types, and values are the PHP class to which
  to map.
- `encoding` - internal character encoding (UTF-8 is always used as an external
  encoding).
- `wsdl` - specifying this option sets the client in WSDL mode. Can be set
  after-the-fact using `setWsdl($wsdl)`.
- `uri` - target namespace for the SOAP service (required for non-WSDL-mode;
  no-op when in WSDL mode).
- `location` - the URL to request (required for non-WSDL-mode; no-op when in
  WSDL mode).
- `style` - request style (non-WSDL mode only); one of `SOAP_RPC` or
  `SOAP_DOCUMENT`.
- `use` - method to use when encoding messages (non-WSDL mode only);
  either `SOAP_ENCODED` or `SOAP_LITERAL`.
- `login` and `password` - login and password for HTTP authentication.
- `proxy_host`, `proxy_port`, `proxy_login`, and `proxy_password` - use when
  specifying a service behind a proxy server.
- `local_cert` and `passphrase` - HTTPS client certificate authentication
  options.
- `compression` - compression options; combination of
  `SOAP_COMPRESSION_ACCEPT`, `SOAP_COMPRESSION_GZIP` and/or
  `SOAP_COMPRESSION_DEFLATE` options.

The following demonstrate usage of compression options:

```php
// Accept response compression
$client = new Zend\Soap\Client(
    'some.wsdl',
    ['compression' => SOAP_COMPRESSION_ACCEPT]
);

// Compress requests using gzip with compression level 5
$client = new Zend\Soap\Client(
    'some.wsdl',
    ['compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | 5]
);

// Compress requests using deflate compression
$client = new Zend\Soap\Client(
    "some.wsdl",
    ['compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE]
);
```

## Performing SOAP Requests

After we've created a `Zend\Soap\Client` instance, we can perform SOAP requests.

Each web service method is mapped to a virtual `Zend\Soap\Client` instance
method which takes parameters with common PHP types.

As an example, given the following server:

```php
class MyClass
{
    /**
     * This method takes ...
     *
     * @param integer $inputParam
     * @return string
     */
    public function method1($inputParam)
    {
        /* ... */
    }

    /**
     * This method takes ...
     *
     * @param integer $inputParam1
     * @param string  $inputParam2
     * @return float
     */
    public function method2($inputParam1, $inputParam2)
    {
        /* ... */
    }

    /* ... */
}

$server = new Zend\Soap\Server(null, $options);
$server->setClass('MyClass');
$server->handle();
```

We can write a client as follows:

```php
$client = new Zend\Soap\Client("MyService.wsdl");

// $result1 is a string
$result1 = $client->method1(10);

// $result2 is a float
$result2 = $client->method2(22, 'some string');
```
