# Zend\\Json\\Server - JSON-RPC server

## Introduction

`Zend\Json\Server` is a [JSON-RPC](http://groups.google.com/group/json-rpc/) server implementation.
It supports both the [JSON-RPC version 1 specification](http://json-rpc.org/wiki/specification) as
well as the [version 2 specification](http://www.jsonrpc.org/specification); additionally, it
provides a *PHP* implementation of the [Service Mapping Description (SMD)
specification](http://www.jsonrpc.org/specification) for providing service metadata to service
consumers.

JSON-RPC is a lightweight Remote Procedure Call protocol that utilizes *JSON* for its messaging
envelopes. This JSON-RPC implementation follows *PHP*'s
[SoapServer](http://www.php.net/manual/en/class.soapserver.php) *API*. This means, in a typical
situation, you will simply:

- Instantiate the server object
- Attach one or more functions and/or classes/objects to the server object
- handle() the request

`Zend\Json\Server` utilizes \[Zend\\Server\\Reflection\](zend.server.reflection) to perform
reflection on any attached classes or functions, and uses that information to build both the SMD and
enforce method call signatures. As such, it is imperative that any attached functions and/or class
methods have full *PHP* docblocks documenting, minimally:

- All parameters and their expected variable types
- The return value variable type

`Zend\Json\Server` listens for POST requests only at this time; fortunately, most JSON-RPC client
implementations in the wild at the time of this writing will only POST requests as it is. This makes
it simple to utilize the same server end point to both handle requests as well as to deliver the
service SMD, as is shown in the next example.

## Basic Usage

First, let's define a class we wish to expose via the JSON-RPC server. We'll call the class
'Calculator', and define methods for 'add', 'subtract', 'multiply', and 'divide':

```php
/**
 * Calculator - sample class to expose via JSON-RPC
 */
class Calculator
{
    /**
     * Return sum of two variables
     *
     * @param  int $x
     * @param  int $y
     * @return int
     */
    public function add($x, $y)
    {
        return $x + $y;
    }

    /**
     * Return difference of two variables
     *
     * @param  int $x
     * @param  int $y
     * @return int
     */
    public function subtract($x, $y)
    {
        return $x - $y;
    }

    /**
     * Return product of two variables
     *
     * @param  int $x
     * @param  int $y
     * @return int
     */
    public function multiply($x, $y)
    {
        return $x * $y;
    }

    /**
     * Return the division of two variables
     *
     * @param  int $x
     * @param  int $y
     * @return float
     */
    public function divide($x, $y)
    {
        return $x / $y;
    }
}
```

Note that each method has a docblock with entries indicating each parameter and its type, as well as
an entry for the return value. This is **absolutely critical** when utilizing `Zend\Json\Server` or
any other server component in Zend Framework, for that matter.

Now we'll create a script to handle the requests:

```php
$server = new Zend\Json\Server\Server();

// Indicate what functionality is available:
$server->setClass('Calculator');

// Handle the request:
$server->handle();
```

However, this will not address the issue of returning an SMD so that the JSON-RPC client can
autodiscover methods. That can be accomplished by determining the *HTTP* request method, and then
specifying some server metadata:

```php
$server = new Zend\Json\Server\Server();
$server->setClass('Calculator');

if ('GET' == $_SERVER['REQUEST_METHOD']) {
    // Indicate the URL endpoint, and the JSON-RPC version used:
    $server->setTarget('/json-rpc.php')
           ->setEnvelope(Zend\Json\Server\Smd::ENV_JSONRPC_2);

    // Grab the SMD
    $smd = $server->getServiceMap();

    // Return the SMD to the client
    header('Content-Type: application/json');
    echo $smd;
    return;
}

$server->handle();
```

If utilizing the JSON-RPC server with Dojo toolkit, you will also need to set a special
compatibility flag to ensure that the two interoperate properly:

```php
$server = new Zend\Json\Server\Server();
$server->setClass('Calculator');

if ('GET' == $_SERVER['REQUEST_METHOD']) {
    $server->setTarget('/json-rpc.php')
           ->setEnvelope(Zend\Json\Server\Smd::ENV_JSONRPC_2);
    $smd = $server->getServiceMap();

    // Set Dojo compatibility:
    $smd->setDojoCompatible(true);

    header('Content-Type: application/json');
    echo $smd;
    return;
}

$server->handle();
```

## Advanced Details

While most functionality for `Zend\Json\Server` is spelled out in \[this
section\](zend.json.server.usage), more advanced functionality is available.

### Zend\\Json\\Server\\Server

`Zend\Json\Server\Server` is the core class in the JSON-RPC offering; it handles all requests and
returns the response payload. It has the following methods:

- `addFunction($function)`: Specify a userland function to attach to the server.
- `setClass($class)`: Specify a class or object to attach to the server; all public methods of that
item will be exposed as JSON-RPC methods.
- `fault($fault = null, $code = 404, $data = null)`: Create and return a `Zend\Json\Server\Error`
object.
- `handle($request = false)`: Handle a JSON-RPC request; optionally, pass a
`Zend\Json\Server\Request` object to utilize (creates one by default).
- `getFunctions()`: Return a list of all attached methods.
- `setRequest(Zend\Json\Server\Request $request)`: Specify a request object for the server to
utilize.
- `getRequest()`: Retrieve the request object used by the server.
- `setResponse(Zend\Json\Server\Response $response)`: Set the response object for the server to
utilize.
- `getResponse()`: Retrieve the response object used by the server.
- `setAutoEmitResponse($flag)`: Indicate whether the server should automatically emit the response
and all headers; by default, this is `TRUE`.
- `autoEmitResponse()`: Determine if auto-emission of the response is enabled.
- `getServiceMap()`: Retrieve the service map description in the form of a `Zend\Json\Server\Smd`
object

### Zend\\Json\\Server\\Request

The JSON-RPC request environment is encapsulated in the `Zend\Json\Server\Request` object. This
object allows you to set necessary portions of the JSON-RPC request, including the request ID,
parameters, and JSON-RPC specification version. It has the ability to load itself via *JSON* or a
set of options, and can render itself as *JSON* via the `toJson()` method.

The request object has the following methods available:

- `setOptions(array $options)`: Specify object configuration. `$options` may contain keys matching
any 'set' method: `setParams()`, `setMethod()`, `setId()`, and `setVersion()`.
- `addParam($value, $key = null)`: Add a parameter to use with the method call. Parameters can be
just the values, or can optionally include the parameter name.
- `addParams(array $params)`: Add multiple parameters at once; proxies to `addParam()`
- `setParams(array $params)`: Set all parameters at once; overwrites any existing parameters.
- `getParam($index)`: Retrieve a parameter by position or name.
- `getParams()`: Retrieve all parameters at once.
- `setMethod($name)`: Set the method to call.
- `getMethod()`: Retrieve the method that will be called.
- `isMethodError()`: Determine whether or not the request is malformed and would result in an error.
- `setId($name)`: Set the request identifier (used by the client to match requests to responses).
- `getId()`: Retrieve the request identifier.
- `setVersion($version)`: Set the JSON-RPC specification version the request conforms to. May be
either '1.0' or '2.0'.
- `getVersion()`: Retrieve the JSON-RPC specification version used by the request.
- `loadJson($json)`: Load the request object from a *JSON* string.
- `toJson()`: Render the request as a *JSON* string.

An *HTTP* specific version is available via `Zend\Json\Server\Request\Http`. This class will
retrieve the request via `php://input`, and allows access to the raw *JSON* via the `getRawJson()`
method.

### Zend\\Json\\Server\\Response

The JSON-RPC response payload is encapsulated in the `Zend\Json\Server\Response` object. This object
allows you to set the return value of the request, whether or not the response is an error, the
request identifier, the JSON-RPC specification version the response conforms to, and optionally the
service map.

The response object has the following methods available:

- `setResult($value)`: Set the response result.
- `getResult()`: Retrieve the response result.
- `setError(Zend\Json\Server\Error $error)`: Set an error object. If set, this will be used as the
response when serializing to *JSON*.
- `getError()`: Retrieve the error object, if any.
- `isError()`: Whether or not the response is an error response.
- `setId($name)`: Set the request identifier (so the client may match the response with the original
request).
- `getId()`: Retrieve the request identifier.
- `setVersion($version)`: Set the JSON-RPC version the response conforms to.
- `getVersion()`: Retrieve the JSON-RPC version the response conforms to.
- `toJson()`: Serialize the response to *JSON*. If the response is an error response, serializes the
error object.
- `setServiceMap($serviceMap)`: Set the service map object for the response.
- `getServiceMap()`: Retrieve the service map object, if any.

An *HTTP* specific version is available via `Zend\Json\Server\Response\Http`. This class will send
the appropriate *HTTP* headers as well as serialize the response as *JSON*.

### Zend\\Json\\Server\\Error

JSON-RPC has a special format for reporting error conditions. All errors need to provide, minimally,
an error message and error code; optionally, they can provide additional data, such as a backtrace.

Error codes are derived from those recommended by [the XML-RPC EPI
project](http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php). `Zend\Json\Server`
appropriately assigns the code based on the error condition. For application exceptions, the code
'-32000' is used.

`Zend\Json\Server\Error` exposes the following methods:

- `setCode($code)`: Set the error code; if the code is not in the accepted XML-RPC error code range,
-32000 will be assigned.
- `getCode()`: Retrieve the current error code.
- `setMessage($message)`: Set the error message.
- `getMessage()`: Retrieve the current error message.
- `setData($data)`: Set auxiliary data further qualifying the error, such as a backtrace.
- `getData()`: Retrieve any current auxiliary error data.
- `toArray()`: Cast the error to an array. The array will contain the keys 'code', 'message', and
'data'.
- `toJson()`: Cast the error to a JSON-RPC error representation.

### Zend\\Json\\Server\\Smd

SMD stands for Service Mapping Description, a *JSON* schema that defines how a client can interact
with a particular web service. At the time of this writing, the
[specification](http://www.jsonrpc.org/specification) has not yet been formally ratified, but it is
in use already within Dojo toolkit as well as other JSON-RPC consumer clients.

At its most basic, a Service Mapping Description indicates the method of transport (POST, `GET`,
*TCP*/IP, etc), the request envelope type (usually based on the protocol of the server), the target
*URL* of the service provider, and a map of services available. In the case of JSON-RPC, the service
map is a list of available methods, which each method documenting the available parameters and their
types, as well as the expected return value type.

`Zend\Json\Server\Smd` provides an object-oriented way to build service maps. At its most basic, you
pass it metadata describing the service using mutators, and specify services (methods and
functions).

The service descriptions themselves are typically instances of `Zend\Json\Server\Smd\Service`; you
can also pass all information as an array to the various service mutators in `Zend\Json\Server\Smd`,
and it will instantiate a service for you. The service objects contain information such as the name
of the service (typically the function or method name), the parameters (names, types, and position),
and the return value type. Optionally, each service can have its own target and envelope, though
this functionality is rarely used.

`Zend\Json\Server\Server` actually does all of this behind the scenes for you, by using reflection
on the attached classes and functions; you should create your own service maps only if you need to
provide custom functionality that class and function introspection cannot offer.

Methods available in `Zend\Json\Server\Smd` include:

- `setOptions(array $options)`: Setup an SMD object from an array of options. All mutators (methods
beginning with 'set') can be used as keys.
- `setTransport($transport)`: Set the transport used to access the service; only POST is currently
supported.
- `getTransport()`: Get the current service transport.
- `setEnvelope($envelopeType)`: Set the request envelope that should be used to access the service.
Currently, supports the constants `Zend\Json\Server\Smd::ENV_JSONRPC_1` and
`Zend\Json\Server\Smd::ENV_JSONRPC_2`.
- `getEnvelope()`: Get the current request envelope.
- `setContentType($type)`: Set the content type requests should use (by default, this is
'application/json').
- `getContentType()`: Get the current content type for requests to the service.
- `setTarget($target)`: Set the *URL* endpoint for the service.
- `getTarget()`: Get the *URL* endpoint for the service.
- `setId($id)`: Typically, this is the *URL* endpoint of the service (same as the target).
- `getId()`: Retrieve the service ID (typically the *URL* endpoint of the service).
- `setDescription($description)`: Set a service description (typically narrative information
describing the purpose of the service).
- `getDescription()`: Get the service description.
- `setDojoCompatible($flag)`: Set a flag indicating whether or not the SMD is compatible with Dojo
toolkit. When `TRUE`, the generated *JSON* SMD will be formatted to comply with the format that
Dojo's JSON-RPC client expects.
- `isDojoCompatible()`: Returns the value of the Dojo compatibility flag (`FALSE`, by default).
- `addService($service)`: Add a service to the map. May be an array of information to pass to the
constructor of `Zend\Json\Server\Smd\Service`, or an instance of that class.
- `addServices(array $services)`: Add multiple services at once.
- `setServices(array $services)`: Add multiple services at once, overwriting any previously set
services.
- `getService($name)`: Get a service by its name.
- `getServices()`: Get all attached services.
- `removeService($name)`: Remove a service from the map.
- `toArray()`: Cast the service map to an array.
- `toDojoArray()`: Cast the service map to an array compatible with Dojo Toolkit.
- `toJson()`: Cast the service map to a *JSON* representation.

`Zend\Json\Server\Smd\Service` has the following methods:

- `setOptions(array $options)`: Set object state from an array. Any mutator (methods beginning with
'set') may be used as a key and set via this method.
- `setName($name)`: Set the service name (typically, the function or method name).
- `getName()`: Retrieve the service name.
- `setTransport($transport)`: Set the service transport (currently, only transports supported by
`Zend\Json\Server\Smd` are allowed).
- `getTransport()`: Retrieve the current transport.
- `setTarget($target)`: Set the *URL* endpoint of the service (typically, this will be the same as
the overall SMD to which the service is attached).
- `getTarget()`: Get the *URL* endpoint of the service.
- `setEnvelope($envelopeType)`: Set the service envelope (currently, only envelopes supported by
`Zend\Json\Server\Smd` are allowed).
- `getEnvelope()`: Retrieve the service envelope type.
- `addParam($type, array $options = array(), $order = null)`: Add a parameter to the service. By
default, only the parameter type is necessary. However, you may also specify the order, as well as
options such as:
- **name**: the parameter name
- **optional**: whether or not the parameter is optional
- **default**: a default value for the parameter
- **description**: text describing the parameter
- `addParams(array $params)`: Add several parameters at once; each param should be an assoc array
containing minimally the key 'type' describing the parameter type, and optionally the key 'order';
any other keys will be passed as `$options` to `addOption()`.
- `setParams(array $params)`: Set many parameters at once, overwriting any existing parameters.
- `getParams()`: Retrieve all currently set parameters.
- `setReturn($type)`: Set the return value type of the service.
- `getReturn()`: Get the return value type of the service.
- `toArray()`: Cast the service to an array.
- `toJson()`: Cast the service to a *JSON* representation.

