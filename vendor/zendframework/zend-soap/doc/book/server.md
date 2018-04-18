# Zend\\Soap\\Server

`Zend\Soap\Server` provides a wrapper around PHP's
[SoapServer](http://php.net/SoapServer) implementation with convenience
functionality for generating WSDL and registering internal handlers.

It may be used in WSDL or non-WSDL mode, and can map functionality to either PHP
classes or functions in order to define your web service API.

When in WSDL mode, it uses a prepared WSDL document to define server object
behavior and transport layer options.

WSDL documents may be auto-generated with functionality provided by the
[Zend\Soap\AutoDiscover](auto-discovery.md) component, or constructed manually
using the [Zend\Soap\Wsdl](wsdl.md) class or any other XML generation tool.

If the non-WSDL mode is used, then all protocol options must be provided via the
options mechanism.

## Zend\Soap\Server instantiation

Instantiation of `Server` instances varies based on whether or not you are using
WSDL mode.

### Options available in either mode

- `parse_huge` (since 2.7.0): when set to a boolean true, ensures the
  `LIBXML_PARSEHUGE` flag is passed to `DOMDocument::loadXML()` when handling an
  incoming request. This can resolve issues with receiving large payloads.

### Instantiation for WSDL mode

When in WSDL mode, the constructor expects two optional parameters:

- `$wsdl`: the URI of a WSDL file. This may be set after-the-fact using
  `$server->setWsdl($wsdl)`.
- `$options`: options to use when creating the instance. These may be set later
  using `$server->setOptions($options)`.

The following options are recognized in the WSDL mode:

- `soap_version` (`soapVersion`) - soap version to use (`SOAP_1_1` or `SOAP_1_2`).
- `actor` - the actor URI for the server.
- `classmap` (`classMap`) which can be used to map some WSDL types to PHP
  classes. The option must be an array with WSDL types as keys, and names of PHP
  classes as values.
- `encoding` - internal character encoding (UTF-8 is always used as an external encoding).
- `wsdl` - equivalent to calling `setWsdl($wsdlValue)`.

### Instantiation for non-WSDL mode

The first constructor parameter **must** be set to `NULL` if you plan to use
`Zend\Soap\Server` functionality in non-WSDL mode.

You also have to set the `uri` option in this case (see below).

The second constructor parameter, `$options`, is an array of options for
configuring the behavior of the server; these may also be provided later using
`$server->setOptions($options)`. Options recognized in non-WSDL mode include:

- `soap_version` (`soapVersion`) - soap version to use (`SOAP_1_1` or `SOAP_1_2`).
- `actor` - the actor URI for the server.
- `classmap` (`classMap`) - an associative array used to map WSDL types to PHP
  classes. The option must be an associative array using WSDL types as the
  keys, and PHP class names as values.
- `encoding` - internal character encoding (UTF-8 is always used as an external
  encoding).
- `uri` (required) - URI namespace for SOAP server.

## Defining your SOAP API

There are two ways to define your SOAP API in order to expose PHP functionality.

The first one is to attach a class to the `Zend\Soap\Server` object that
completely describes your API:

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
        // ...
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
        // ...
    }

    /* ... */
}

$server = new Zend\Soap\Server(null, $options);

// Bind class to Soap Server:
$server->setClass(MyClass::class);

// Or bind an instance:
$server->setObject(new MyClass());

// Handle a request:
$server->handle();
```

> ### Docblocks are required
>
> You should completely describe each method using a method docblock if you plan
> to use autodiscover functionality to prepare your WSDL.

The second method for defining your API is to use one or more functions, passing
them to one or more of the `addFunction()` or `loadFunctions()` methods:

```php
/**
 * This function ...
 *
 * @param integer $inputParam
 * @return string
 */
function function1($inputParam)
{
    // ...
}

/**
 * This function ...
 *
 * @param integer $inputParam1
 * @param string  $inputParam2
 * @return float
 */
function function2($inputParam1, $inputParam2)
{
    // ...
}

$server = new Zend\Soap\Server(null, $options);
$server->addFunction('function1');
$server->addFunction('function2');

$server->handle();
```

## Request and response handling

`Zend\Soap\Server` component performs request/response processing automatically,
but allows you to intercept each in order to perform pre- or post-processing.

### Request pre- and post-processing

The `Zend\Soap\Server::handle()` method handles a request from the standard
input stream ('php://input'). It may be overridden either by supplying a request
instance to the `handle()` method, or by setting the request via the
`setRequest()` method:

```php
$server = new Zend\Soap\Server(/* ... */);

// Set request using optional $request parameter to the handle() method:
$server->handle($request);

// Set request using setRequest() method:
$server->setRequest();
$server->handle();
```

A request object may be represented using any of the following, and handled as
follows:

- `DOMDocument` (casts to XML)
- `DOMNode` (owner document is retrieved and cast to XML)
- `SimpleXMLElement` (casts to XML)
- `stdClass` (`__toString()` is called and verified to be valid XML)
- `string` (verified to be valid XML)

The last request processed may be retrieved using the `getLastRequest()` method,
which returns the XML string:

```php
$server = new Zend\Soap\Server(/* ... */);

$server->handle();
$request = $server->getLastRequest();
```

### Response post-processing

The `Zend\Soap\Server::handle()` method automatically emits the generated
response to the output stream. It may be blocked using `setReturnResponse()`
with `true` or `false` as a parameter. When set to `true`, `handle()` will
return the generated response instead of emitting it.

The returned response will be either an XML string representing the response, or
a `SoapFault` exception instance.

> #### Do not return SoapFaults
>
> SoapFault instances, when cast to a string, will contain the full exception
> stack trace. For security purposes, you do not want to return that
> information. As such, check your return type before emitting the response
> manually.

```php
$server = new Zend\Soap\Server(/* ... */);

// Get a response as a return value of handle(),
// instead of emitting it to standard output:
$server->setReturnResponse(true);

$response = $server->handle();

if ($response instanceof SoapFault) {
    /* ... */
} else {
    /* ... */
}
```

The last response emitted may also be retrieved for post-processing using
`getLastResponse()`:

```php
$server = new Zend\Soap\Server(/* ... */);

$server->handle();

$response = $server->getLastResponse();

if ($response instanceof SoapFault) {
    /* ... */
} else {
    /* ... */
}
```

## Document/Literal WSDL Handling

The document/literal binding-style/encoding pattern is used to make SOAP
messages as human-readable as possible and allow abstraction between very
incompatible languages. The .NET framework uses this pattern for SOAP service
generation by default. The central concept of this approach to SOAP is the
introduction of a Request and an Response object for every function/method of
the SOAP service. The parameters of the function are properties on the request
object, and the response object contains a single parameter that is built in the
style `<methodName>Result`

zend-soap supports this pattern in both the AutoDiscover and Server
components. You can write your service object without knowledge of the pattern.
Use docblock comments to hint the parameter and return types as usual. The
`Zend\Soap\Server\DocumentLiteralWrapper` wraps around your service object and
converts request and response into normal method calls on your service.

See the class doc block of the `DocumentLiteralWrapper` for a detailed example
and discussion.
