# Zend\\XmlRpc\\Server

`Zend\XmlRpc\Server` is a fully-featured XML-RPC server, following
[the specifications outlined at www.xmlrpc.com](http://www.xmlrpc.com/spec).
Additionally, it implements the `system.multicall()` method, allowing boxcarring
of requests.

## Basic Usage

```php
$server = new Zend\XmlRpc\Server();
$server->setClass('My\Service\Class');
echo $server->handle();
```

## Server Structure

`Zend\XmlRpc\Server` is composed of a variety of components, ranging from the
server itself to request, response, and fault objects.

To bootstrap `Zend\XmlRpc\Server`, the developer must attach one or more classes
or functions to the server, via the `setClass()` and `addFunction()` methods.

Once done, you may either pass a `Zend\XmlRpc\Request` object to
`Zend\XmlRpc\Server::handle()`, or it will instantiate a
`Zend\XmlRpc\Request\Http` object if none is provided, thus grabbing the request
from `php://input`.

`Zend\XmlRpc\Server::handle()` then attempts to dispatch to the appropriate
handler based on the method requested. It then returns either a
`Zend\XmlRpc\Response`-based object or a `Zend\XmlRpc\Server\Fault` object.
These objects both have `__toString()` methods that create valid XML-RPC XML
responses, allowing them to be directly echoed.

## Anatomy of a webservice

### General considerations

For maximum performance it is recommended to use a simple bootstrap file for the
server component.  Using `Zend\XmlRpc\Server` inside a
[Zend\\Mvc\\Controller](https://zendframework.github.io/zend-mvc/) is strongly
discouraged to avoid the overhead.

Services change over time and while webservices are generally less change
intense as code-native APIs, it is recommended to version your service. Do so to
lay grounds to provide compatibility for clients using older versions of your
service and manage your service lifecycle including deprecation timeframes. To
do so just include a version number into your URI. It is also recommended to
include the remote protocol name in the URI to allow easy integration of
upcoming remoting technologies. `http://myservice.ws/1.0/XMLRPC/`.

### What to expose?

Most of the time it is not sensible to expose business objects directly.
Business objects are usually small and under heavy change, because change is
cheap in this layer of your application.  Once deployed and adopted, web
services are hard to change. Another concern is I/O and latency: the best
webservice calls are those not happening. Therefore service calls need to be
more coarse-grained than usual business logic is. Often an additional layer in
front of your business objects makes sense. This layer is sometimes referred to
as [Remote Facade](http://martinfowler.com/eaaCatalog/remoteFacade.html). Such a
service layer adds a coarse grained interface on top of your business logic and
groups verbose operations into smaller ones.

## Conventions

`Zend\XmlRpc\Server` allows the developer to attach functions and class method
calls as dispatchable XML-RPC methods. Via `Zend\Server\Reflection`, it does
introspection on all attached methods, using the function and method docblocks
to determine the method help text and method signatures.

XML-RPC types do not necessarily map one-to-one to PHP types. However, the code
will do its best to guess the appropriate type based on the values listed in
`@param` and `@return` annotations. Some XML-RPC types have no immediate PHP
equivalent, however, and should be hinted using the XML-RPC type in the PHPDoc.
These include:

* `dateTime.iso8601`, a string formatted as '`YYYYMMDDTHH:mm:ss`'
* `base64`, base64 encoded data
* `struct`, any associative array

An example of how to hint follows:

```php
/**
 * This is a sample function
 *
 * @param base64 $val1 Base64-encoded data
 * @param dateTime.iso8601 $val2 An ISO date
 * @param struct $val3 An associative array
 * @return struct
 */
function myFunc($val1, $val2, $val3)
{
}
```

PhpDocumentor does not validate types specified for params or return values, so
this will have no impact on your API documentation. Providing the hinting is
necessary, however, when the server is validating the parameters provided to the
method call.

It is perfectly valid to specify multiple types for both params and return
values; the XML-RPC specification even suggests that `system.methodSignature`
should return an array of all possible method signatures (i.e., all possible
combinations of param and return values). You may do so just as you normally
would with PhpDocumentor, using the `|` operator:

```php
/**
 * This is a sample function
 *
 * @param string|base64 $val1 String or base64-encoded data
 * @param string|dateTime.iso8601 $val2 String or an ISO date
 * @param array|struct $val3 Normal indexed array or an associative array
 * @return boolean|struct
 */
function myFunc($val1, $val2, $val3)
{
}
```

> ### Use multiple values sparingly
>
> Allowing multiple signatures can lead to confusion for developers using the
> services; to keep things simple, a XML-RPC service method should typically
> only present a single signature.

## Utilizing Namespaces

XML-RPC allows grouping related methods under dot-delimited *namespaces*. This
helps prevent naming collisions between methods served by different classes. As
an example, the XML-RPC server is expected to server several methods in the
`system` namespace:

- `system.listMethods`
- `system.methodHelp`
- `system.methodSignature`

Internally, these map to the methods of the same name in `Zend\XmlRpc\Server`.

If you want to add namespaces to the methods you serve, simply provide a
namespace to the appropriate method when attaching a function or class:

```php
// All public methods in My\Service\Class will be accessible as
// myservice.METHODNAME
$server->setClass('My\Service\Class', 'myservice');

// Function 'somefunc' will be accessible as funcs.somefunc
$server->addFunction('somefunc', 'funcs');
```

## Custom Request Objects

Most of the time, you'll simply use the default request type included with
`Zend\XmlRpc\Server`, `Zend\XmlRpc\Request\Http`. However, there may be times
when you need XML-RPC to be available via the CLI, a GUI, or other environment,
or want to log incoming requests. To do so, you may create a custom request
object that extends `Zend\XmlRpc\Request`. The most important thing to remember
is to ensure that the `getMethod()` and `getParams()` methods are implemented so
that the XML-RPC server can retrieve that information in order to dispatch the
request.

## Custom Responses

Similar to request objects, `Zend\XmlRpc\Server` can return custom response
objects; by default, a `Zend\XmlRpc\Response\Http` object is returned, which
sends an appropriate `Content-Type` HTTP header for use with XML-RPC. Possible
uses of a custom object would be to log responses, or to send responses back to
`STDOUT`.

To use a custom response class, use `Zend\XmlRpc\Server::setResponseClass()`
prior to calling `handle()`.

## Handling Exceptions via Faults

`Zend\XmlRpc\Server` catches Exceptions generated by a dispatched method, and
generates an XML-RPC fault response when such an exception is caught. By
default, however, the exception messages and codes are not used in a fault
response. This is an intentional decision to protect your code; many exceptions
expose more information about the code or environment than a developer would
necessarily intend (a prime example includes database exceptions).

Exception classes can be whitelisted to be used as fault responses, however. To
do so, call `Zend\XmlRpc\Server\Fault::attachFaultException()` and pass an
exception class to whitelist:

```php
Zend\XmlRpc\Server\Fault::attachFaultException('My\Project\Exception');
```

If you utilize an exception class that your other project exceptions inherit,
you can then whitelist a whole family of exceptions at a time.
`Zend\XmlRpc\Server\Exception`s are always whitelisted, to allow reporting
specific internal errors (undefined methods, etc.).

Any exception not specifically whitelisted will generate a fault response with a
code of '404' and a message of 'Unknown error'.

## Caching Server Definitions Between Requests

Attaching many classes to an XML-RPC server instance can utilize a lot of
resources; each class must introspect using the Reflection API (via
`Zend\Server\Reflection`), which in turn generates a list of all possible method
signatures to provide to the server class.

To reduce this performance hit somewhat, `Zend\XmlRpc\Server\Cache` can be used
to cache the server definition between requests.

An sample usage follows:

```php
use My\Service as s;
use Zend\XmlRpc\Server as XmlRpcServer;

$cacheFile = dirname(__FILE__) . '/xmlrpc.cache';
$server = new XmlRpcServer();

if (! XmlRpcServer\Cache::get($cacheFile, $server)) {

    $server->setClass(s\Glue::class, 'glue');   // glue. namespace
    $server->setClass(s\Paste::class, 'paste'); // paste. namespace
    $server->setClass(s\Tape::class, 'tape');   // tape. namespace

    XmlRpcServer\Cache::save($cacheFile, $server);
}

echo $server->handle();
```

The above example attempts to retrieve a server definition from `xmlrpc.cache`
in the same directory as the script. If unsuccessful, it loads the service
classes it needs, attaches them to the server instance, and then attempts to
create a new cache file with the server definition.

## Usage Examples

Below are several usage examples, showing the full spectrum of options available
to developers.  Usage examples will each build on the previous example provided.

### Basic Usage

The example below attaches a function as a dispatchable XML-RPC method and
handles incoming calls.

```php
/**
 * Return the MD5 sum of a value
 *
 * @param string $value Value to md5sum
 * @return string MD5 sum of value
 */
function md5Value($value)
{
    return md5($value);
}

$server = new Zend\XmlRpc\Server();
$server->addFunction('md5Value');
echo $server->handle();
```

### Attaching a class

The example below illustrates attaching a class' public methods as dispatchable
XML-RPC methods.

```php
require_once 'Services/Comb.php';

$server = new Zend\XmlRpc\Server();
$server->setClass('Services\Comb');
echo $server->handle();
```

### Attaching a class with arguments

The following example illustrates how to attach a class' public methods and
passing arguments to its methods. This can be used to specify certain defaults
when registering service classes.

```php
namespace Services;

class PricingService
{
    /**
     * Calculate current price of product with $productId
     *
     * @param ProductRepository $productRepository
     * @param PurchaseRepository $purchaseRepository
     * @param integer $productId
     */
    public function calculate(
        ProductRepository $productRepository,
        PurchaseRepository $purchaseRepository,
        $productId
    ) {
        /* ... */
    }
}

$server = new Zend\XmlRpc\Server();
$server->setClass(
    'Services\PricingService',
    'pricing',
    new ProductRepository(),
    new PurchaseRepository()
);
```

The arguments passed to `setClass()` are injected into the method call
`pricing.calculate()` on remote invocation. In the example above, only the
argument `$productId` is expected from the client.

### Passing arguments only to constructor

`Zend\XmlRpc\Server` allows providing constructor arguments when specifying
classes, instead of when invoking methods.

To limit injection to constructors, call `sendArgumentsToAllMethods` and pass
`FALSE` as an argument. This disables the default behavior of all arguments
being injected into the remote method. In the example below, the instance of
`ProductRepository` and `PurchaseRepository` is only injected into the
constructor of `Services\PricingService2`.

```php
class Services\PricingService2
{
    /**
     * @param ProductRepository $productRepository
     * @param PurchaseRepository $purchaseRepository
     */
    public function __construct(
        ProductRepository $productRepository,
        PurchaseRepository $purchaseRepository
    ) {
        /* ... */
    }

    /**
     * Calculate current price of product with $productId
     *
     * @param integer $productId
     * @return double
     */
    public function calculate($productId)
    {
        ...
    }
}

$server = new Zend\XmlRpc\Server();

// Tell the server to pass arguments to constructors instead of at invocation:
$server->sendArgumentsToAllMethods(false);

// Map the class:
$server->setClass(
    'Services\PricingService2',
    'pricing',
    new ProductRepository(),
    new PurchaseRepository()
);
```

### Attaching a class instance

`setClass()` allows registering a previously instantiated class with the server,
instead of specifying the class name. Obviously, passing arguments to the
constructor is not possible with pre-instantiated classes.

### Attaching several classes using namespaces

The example below illustrates attaching several classes, each with their own
namespace.

```php
use Services as s;
use Zend\XmlRpc\Server as XmlRpcServer;

$server = new XmlRpcServer();
$server->setClass(s\Comb::class, 'comb');   // methods called as comb.*
$server->setClass(s\Brush::class, 'brush'); // methods called as brush.*
$server->setClass(s\Pick::class, 'pick');   // methods called as pick.*
echo $server->handle();
```

### Specifying exceptions to use as valid fault responses

The example below allows any `Services\Exception`-derived class to report its
code and message in the fault response.

```php
use Services as s;
use Zend\XmlRpc\Server as XmlRpcServer;
use Zend\XmlRpc\Server\Fault as XmlRpcFault;

// Allow Services_Exceptions to report as fault responses
XmlRpcFault::attachFaultException(s\Exception::class);

$server = new XmlRpcServer();
$server->setClass(s\Comb::class, 'comb');   // methods called as comb.*
$server->setClass(s\Brush::class, 'brush'); // methods called as brush.*
$server->setClass(s\Pick::class, 'pick');   // methods called as pick.*
echo $server->handle();
```

### Utilizing custom request and response objects

Some use cases require custom request objects; XML-RPC is not bound to HTTP as a
transfer protocol. It is possible to use other transfer protocols like SSH or
telnet to send the request and response data over the wire. Another use case is
authentication and authorization. When a different transfer protocol is
required, you will need to change the implementation to read request data.

The example below instantiates a custom request class and passes it to the
server to handle.

```php
use Services as s;
use Zend\XmlRpc\Server as XmlRpcServer;
use Zend\XmlRpc\Server\Fault as XmlRpcFault;

// Allow Services_Exceptions to report as fault responses
XmlRpcFault::attachFaultException(s\Exception::class);

$server = new XmlRpcServer();
$server->setClass(s\Comb::class, 'comb');   // methods called as comb.*
$server->setClass(s\Brush::class, 'brush'); // methods called as brush.*
$server->setClass(s\Pick::class, 'pick');   // methods called as pick.*

// Create a request object
$request = new s\Request();

echo $server->handle($request);
```

### Specifying a custom response class

The example below illustrates specifying a custom response class for the returned response.

```php
use Services as s;
use Zend\XmlRpc\Server as XmlRpcServer;
use Zend\XmlRpc\Server\Fault as XmlRpcFault;

// Allow Services_Exceptions to report as fault responses
XmlRpcFault::attachFaultException(s\Exception::class);

$server = new XmlRpcServer();
$server->setClass(s\Comb::class, 'comb');   // methods called as comb.*
$server->setClass(s\Brush::class, 'brush'); // methods called as brush.*
$server->setClass(s\Pick::class, 'pick');   // methods called as pick.*

// Create a request object
$request = new s\Request();

// Utilize a custom response
$server->setResponseClass(s\Response::class);

echo $server->handle($request);
```

## Performance optimization

### Cache server definitions between requests

The example below illustrates caching server definitions between requests.

```php
use Services as s;
use Zend\XmlRpc\Server as XmlRpcServer;
use Zend\XmlRpc\Server\Fault as XmlRpcFault;

// Specify a cache file
$cacheFile = dirname(__FILE__) . '/xmlrpc.cache';

// Allow Services\Exceptions to report as fault responses
XmlRpcFault::attachFaultException(s\Exception::class);

$server = new XmlRpcServer();

// Attempt to retrieve server definition from cache
if (! XmlRpcServer\Cache::get($cacheFile, $server)) {
    $server->setClass(s\Comb::class, 'comb');   // methods called as comb.*
    $server->setClass(s\Brush::class, 'brush'); // methods called as brush.*
    $server->setClass(s\Pick::class, 'pick');   // methods called as pick.*

    // Save cache
    XmlRpcServer\Cache::save($cacheFile, $server);
}

// Create a request object
$request = new s\Request();

// Utilize a custom response
$server->setResponseClass(s\Response::class);

echo $server->handle($request);
```

> ### Cache file location
>
> The server cache file should be located outside the document root.

### Optimizing XML generation

`Zend\XmlRpc\Server` uses `DOMDocument` to generate it's XML output. While this
functionality is available on most hosts, it's not always the most performant
solution; benchmarks have shown that `XmlWriter` performs better.

If `ext/xmlwriter` is available on your host, you can select the
`XmlWriter`-based generator to leverage the performance differences.

```php
use Zend\XmlRpc;

XmlRpc\AbstractValue::setGenerator(new XmlRpc\Generator\XmlWriter());

$server = new XmlRpc\Server();
```

> #### Benchmark your application
>
> Performance is determined by many parameters, and benchmarks only apply for
> the specific test case. Differences come from PHP version, installed
> extensions, webserver, and operating system just to name a few. Please make
> sure to benchmark your application on your own and decide which generator to
> use based on **your** numbers.

> #### Benchmark your client
>
> Optimization makes sense for the client side too. Just select the alternate
> XML generator before doing any work with `Zend\XmlRpc\Client`.
