# AutoDiscovery

SOAP functionality implemented within this component is intended to make all
steps required for SOAP communications more simple. SOAP is a language
independent protocol, however, which means it may be used for more than just
PHP-to-PHP communications, adding some complexity to its implementation.

There are three configurations for SOAP applications supported by zend-soap:

- SOAP server PHP application &lt;---&gt; SOAP client PHP application
- SOAP server non-PHP application &lt;---&gt; SOAP client PHP application
- SOAP server PHP application &lt;---&gt; SOAP client non-PHP application

In each situation, the SOAP server must expose the functionality it provides so
the client knows how to interact with it. This is done via a
[WSDL](http://www.w3.org/TR/wsdl) (Web Services Description Language) document.
The WSDL language is quite complex, making preparation of WSDL documents
difficult; this task is complicated when the API for your service changes, as
any changes then need to be synced back to the WSDL.

These problems may be solved via WSDL autodiscovery, which zend-soap provides
via its `Zend\Soap\AutoDiscover` class.

Autodiscovery in zend-soap follows the same patterns as you use for creating a
zend-soap `Server`, but uses the classes and functions attached to it to extract
the information required to generate a WSDL document.

As a refresher, zend-soap allows using either of the following to define a
server:

- PHP classes.
- PHP functions.

Each are also supported by the autodiscovery functionality. Additionally,
`AutoDiscover` supports datatype mappins from PHP to [XSD types](http://www.w3.org/TR/xmlschema-2/).

The following is a basic example demonstrating the autodiscovery functionality.
It uses similar functionality as when using [Zend\Soap\Server](server.md), but
instead of using `handle()` to handle an incoming SOAP request, it provides a
`generate()` method, which returns a [Zend\Soap\Wsdl](wsdl.md) instance. This
can then be used to return an XML representation to the client.

```php
class MySoapServerClass
{
    /* ... */
}

$autodiscover = new Zend\Soap\AutoDiscover();
$autodiscover
    ->setClass('MySoapServerClass')
    ->setUri('http://localhost/server.php')
    ->setServiceName('MySoapService');

$wsdl = $autodiscover->generate();

// Emit the XML:
echo $wsdl->toXml();

// Or dump it to a file; this is a good way to cache the WSDL
$wsdl->dump("/path/to/file.wsdl");

// Or create a DOMDocument, which you can then manipulate:
$dom = $wsdl->toDomDocument();
```

> ### AutoDiscover !== Server
>
> `AutoDiscover` *is not a `Server` instance*; it cannot and does not act as a
> SOAP server on its own, but instead provides the WSDL used by clients that
> will interact with your SOAP server.
>
> SOAP interactions are always performed over HTTP POST requests, while
> retrieval of WSDL is performed using HTTP GET. As such, you *can* server both
> from the same script, provided you detect the incoming method and respond
> accordingly:
>
> ```php
> if ($_SERVER['REQUEST_METHOD'] == 'GET') {
>     if (! isset($_GET['wsdl'])) {
>         header('HTTP/1.1 400 Client Error');
>         return;
>     }
>
>     $autodiscover = new Zend\Soap\AutoDiscover();
>     $autodiscover->setClass('HelloWorldService')
>         ->setUri('http://example.com/soap.php');
>     header('Content-Type: application/wsdl+xml');
>     echo $autodiscover->toXml();
>     return;
> }
>
> if ($_SERVER['REQUEST_METHOD'] != 'POST') {
>     header('HTTP/1.1 400 Client Error');
>     return;
> }
>
> // pointing to the current file here
> $soap = new Zend\Soap\Server("http://example.com/soap.php?wsdl");
> $soap->setClass('HelloWorldService');
> $soap->handle();
> ```

## Class autodiscovery

If a class is used to provide SOAP server functionality, then the same class
should be provided to `Zend\Soap\AutoDiscover` for WSDL generation:

```php
$autodiscover = new Zend\Soap\AutoDiscover();
$autodiscover
    ->setClass('My_SoapServer_Class')
    ->setUri('http://localhost/server.php')
    ->setServiceName('MySoapService');
$wsdl = $autodiscover->generate();
```

The following rules are used during WSDL generation:

- The generated WSDL describes an RPC/Encoded style web service. If you want to
  describe a document/literal server, use the `setBindingStyle()` and
  `setOperationBodyStyle()` methods.
- The PHP class name is used as the web service name unless `setServiceName()`
  is used explicitly to set the name. When only functions are used, the service
  name has to be set explicitly or an exception will be thrown during WSDL
  document generation.
- You can set the endpoint of the actual SOAP Server via the `setUri()` method.
  This is a required option, and also used as the target namespace for all
  service related names (including described complex types).

Complex types are generated using the following rules:

- Class methods are joined into one [Port Type](http://www.w3.org/TR/wsdl#_porttypes),
  with port names using the format `<$serviceName>Port`.
- Each class method/function is registered as a corresponding port operation.
- Only the "longest" available method prototype is used for WSDL generation.
- WSDL autodiscovery utilizes PHP docblocks provided by the developer to determine the
  parameter and return types. In fact, for scalar types, this is the only way to
  determine the parameter types, and for return types, this is the only way to
  determine them.  This means that *providing correct and fully detailed
  docblocks is not only best practice, but required for autodiscovery*.

## Function autodiscovery

If a set of functions are used to provide your SOAP server functionality, then
the same set should be provided to `Zend\Soap\AutoDiscovery` for WSDL
generation:

```php
$autodiscover = new Zend\Soap\AutoDiscover();
$autodiscover->addFunction('function1');
$autodiscover->addFunction('function2');
$autodiscover->addFunction('function3');

$wsdl = $autodiscover->generate();
```

The same rules apply to generation as described in the class autodiscovery section above.

## Autodiscovering Datatypes

Input/output datatypes are converted into network service types using the
following mapping:

- PHP strings &lt;-&gt; `xsd:string`.
- PHP integers &lt;-&gt; `xsd:int`.
- PHP floats and doubles &lt;-&gt; `xsd:float`.
- PHP booleans &lt;-&gt; `xsd:boolean`.
- PHP arrays &lt;-&gt; `soap-enc:Array`.
- PHP object &lt;-&gt; `xsd:struct`.
- PHP class &lt;-&gt; based on complex type strategy (See the [WSDL section on adding complex types](wsdl.md#adding-complex-type-information)).
- type\[\] or object\[\] (ie. int\[\]) &lt;-&gt; based on complex type strategy
- PHP void &lt;-&gt; empty type.
- If type is not matched to any of these types by some reason, then `xsd:anyType` is used.

Where:

- `xsd:` refers to the [http://www.w3.org/2001/XMLSchema](http://www.w3.org/2001/XMLSchema)
  namespace
- `soap-enc:` refers to the [http://schemas.xmlsoap.org/soap/encoding/](http://schemas.xmlsoap.org/soap/encoding/)
  namespace
- `tns:` is the "target namespace" for the service.

> ### Complex type discovery
>
> `Zend\Soap\AutoDiscover` will be created with the
> `Zend\Soap\Wsdl\ComplexTypeStrategy\DefaultComplexType` class as its detection
> algorithm for complex types. The first parameter of the `AutoDiscover`
> constructor takes any complex type strategy implementing
> `Zend\Soap\Wsdl\ComplexTypeStrategy\ComplexTypeStrategyInterface` (or a string
> class name of an implementation).  name of the class. See the
> [Zend\Soap\Wsdl documentation on adding complex types](wsdl.md#adding-complex-type-information)
> for more information.

## WSDL Binding Styles

WSDL offers different transport mechanisms and styles. This affects the
`soap:binding` and `soap:body` tags within the `Binding` section of the WSDL
document. Different clients have different requirements as to what options
really work. Therefore you can set the styles before you call either the
`setClass()` or `addFunction()` method on the `AutoDiscover` class.

```php
$autodiscover = new Zend\Soap\AutoDiscover();

// Defaults are
// - 'use' => 'encoded'
// - 'encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/'
$autodiscover->setOperationBodyStyle([
    'use'       => 'literal',
    'namespace' => 'http://framework.zend.com',
]);

// Defaults are:
// - 'style' => 'rpc'
// - 'transport' => 'http://schemas.xmlsoap.org/soap/http'
$autodiscover->setBindingStyle([
    'style'     => 'document',
    'transport' => 'http://framework.zend.com',
]);

$autodiscover->addFunction('myfunc1');
$wsdl = $autodiscover->generate();
```
