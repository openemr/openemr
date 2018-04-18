# WSDL Parsing and Generation

The `Zend\Soap\Wsdl` class is used by `Zend\Soap\Server` internally to operate
with WSDL documents. In most cases, you will not interact with it directly.

Nevertheless, you could also use functionality provided by this class for your
own needs.  `Zend\Soap\Wsdl` contains both a parser and a generator for WSDL
documents.

## Instantiation

The `Zend\Soap\Wsdl` constructor takes three parameters:

- `$name` - name of the web service being described.
- `$uri` - URI where the WSDL will be available (could also be a reference to
  the file in the filesystem.)
- `$strategy` - optional flag used to identify the strategy for complex types
  (objects) detection.  To read more on complex type detection strategies go to
  the section on [adding complex types](#adding-complex-type-information).
- `$classMap` - Optional array of class name translations from PHP Type (key) to
  WSDL type (value).

## addMessage() method

The `addMessage($name, $parts)` method adds a new message description to the
WSDL document (`/definitions/message` element).

Each message corresponds to methods in terms of `Zend\Soap\Server` and
`Zend\Soap\Client` functionality.

The `$name` parameter represents the message name.

The `$parts` parameter is an array of message parts which describes SOAP call
parameters, represented as an associative array of 'part name' (SOAP call
parameter name) =&gt; 'part type' pairs.

Type mapping management is performed using one of the `addTypes()` and
`addComplexType()` methods (see below).

> ### Message Typing
>
> Messages parts can use either the `element` or `type` attribute for typing (see
> [the W3C WSDL specification](http://www.w3.org/TR/wsdl#_messages)).
>
> The `element` attribute must refer to a corresponding element in the data type
> definition. A `type` attribute refers to a corresponding complexType entry.
>
> All standard XSD types have both `element` and `complexType` definitions (see
> the [SOAP encoding specification](http://schemas.xmlsoap.org/soap/encoding/)
> for details).
>
> All non-standard types, which may be added using the
> `Zend\Soap\Wsdl::addComplexType()` method, are described using the
> `complexType` node of the `/definitions/types/schema/` section of the WSDL
> document.
>
> The `addMessage()` method always uses the `type` attribute to describe types.

## addPortType() method

The `addPortType($name)` method adds a new port type to the WSDL document
(`/definitions/portType`) with the specified port type name.

In terms of the `Zend\Soap\Server` implementation, it joins a set of web service
methods into a single operation.

See [the W3C portTypes documentation](http://www.w3.org/TR/wsdl#_porttypes) for
more details.

## addPortOperation() method

The `addPortOperation($portType, $name, $input = false, $output = false, $fault
= false)` method adds new port operation to the specified port type of the WSDL
document (`/definitions/portType/operation`).

In terms of the `Zend\Soap\Server` implementation, Each port operation
corresponds to a class method (if the web service is based on a class) or
function (if the web service is based on a set of methods).

It also adds corresponding port operation messages depending on the specified
`$input`, `$output` and `$fault` parameters.

> ### Generated messages
>
> `Zend\Soap\Server` generates two messages for each port operation when
> describing operations it provides:
>
> - input message with name `<$methodName>Request`.
> - output message with name `<$methodName>Response`.

See the [W3C WSDL request/response documentation](http://www.w3.org/TR/wsdl#_request-response)
for more details.

## addBinding() method

The `addBinding($name, $portType)` method adds new binding to the WSDL document
(`/definitions/binding`).

A `binding` WSDL document node defines the message format and protocol details
for operations and messages defined by a particular portType (see the [W3C WSDL
binding documentation](http://www.w3.org/TR/wsdl#_bindings)).

The method creates a binding node and returns it; you may then fill the returned
node with data.

`Zend\Soap\Server` uses the name `<$serviceName>Binding` for the 'binding'
element in the WSDL document.

## addBindingOperation() method

The `addBindingOperation($binding, $name, $input = false, $output = false,
$fault = false)` method adds an operation to a binding element
(`/definitions/binding/operation`) with the specified name.

It takes an `XML_Tree_Node` object returned by `addBinding()` as an input
(`$binding` parameter) to add an 'operation' element with input/output/false
entries depending on the specified parameters

The `Zend\Soap\Server` implementation adds a corresponding binding entry for each web service method with
input and output entries, defining the `soap:body` element as `<soap:body use="encoded"
encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">`.

See the [W3C WSDL bindings documentation](http://www.w3.org/TR/wsdl#_bindings)
for more details.

## addSoapBinding() method

The `addSoapBinding($binding, $style = 'document', $transport =
'http://schemas.xmlsoap.org/soap/http')` method adds a SOAP binding
(`soap:binding`) entry to the binding element (which is already linked to some
port type) using the specified style and transport (the `Zend\Soap\Server`
implementation uses the RPC style over HTTP).

A `/definitions/binding/soap:binding` element is used to signify that the
binding is bound to the SOAP protocol format.

See the [W3C bindings documentation](http://www.w3.org/TR/wsdl#_bindings) for
more details.

## addSoapOperation() method

The `addSoapOperation($binding, $soap_action)` method adds a SOAP operation
(`soap:operation`) entry to the binding element with the specified action. The
`style` attribute of the `soap:operation` element is not used since the
programming model (RPC-oriented or document-oriented) may be using the
`addSoapBinding()` method already.

The `soapAction` attribute of `/definitions/binding/soap:operation` element
specifies the value of the SOAP action header for this operation. This attribute
is required for SOAP over HTTP and **must not** be specified for other
transports.

The `Zend\Soap\Server` implementation uses the format
`<$serviceUri>#<$methodName>` for the SOAP operation action name.

See the [W3C soap:operation documentation](http://www.w3.org/TR/wsdl#_soap:operation)
for more details.

## addService() method

The `addService($name, $port_name, $binding, $location)` method adds a
`/definitions/service` element to the WSDL document with the specified service
name, port name, binding, and location.

WSDL 1.1 allows several port types (sets of operations) per service; however,
zend-soap does not support this ability.

The `Zend\Soap\Server` implementation uses:

- `<$name>Service` as the service name.
- `<$name>Port` as the port type name.
- `tns:<$name>Binding` [1] as the binding name. (`tns:namespace` is defined as
  the script URI; generally this is `'http://' . $_SERVER['HTTP_HOST'] .
  $_SERVER['SCRIPT_NAME']`)
- the script URI (`'http://' . $_SERVER['HTTP_HOST'] .  $_SERVER['SCRIPT_NAME']`)
  as the service URI for the service definition.

where `$name` is either:

- a class name, for servers representing a PHP class,
- a script name, for servers representing a collection of PHP functions.

See the [W3C WSDL services documentation](http://www.w3.org/TR/wsdl#_services)
for more details.

## Type mapping

The zend-soap WSDL implementation uses the following type mappings between PHP
and SOAP types:

- PHP strings &lt;-&gt; `xsd:string`.
- PHP integers &lt;-&gt; `xsd:int`.
- PHP floats and doubles &lt;-&gt; `xsd:float`.
- PHP booleans &lt;-&gt; `xsd:boolean`.
- PHP arrays &lt;-&gt; `soap-enc:Array`.
- PHP object &lt;-&gt; `xsd:struct`.
- PHP class &lt;-&gt; based on complex type strategy (See
  [the section on adding complex types](#adding-complex-type-information)).
- PHP void &lt;-&gt; empty type.
- If a type is not matched to any of the above, then `xsd:anyType` is used.

Where:

- `xsd:` refers to the [http://www.w3.org/2001/XMLSchema](http://www.w3.org/2001/XMLSchema) namespace
- `soap-enc:` refers to the [http://schemas.xmlsoap.org/soap/encoding/](http://schemas.xmlsoap.org/soap/encoding/)
  namespace
- `tns:` is the "target namespace" for the service.

> ### Complex types
>
> By default, `Zend\Soap\Wsdl` will be created with the
> `Zend\Soap\Wsdl\ComplexTypeStrategy\DefaultComplexType` class as its detection
> algorithm for complex types. The first parameter of the `AutoDiscover`
> constructor takes any complex type strategy implementing
> `Zend\Soap\Wsdl\ComplexTypeStrategy\ComplexTypeStrategyInterface`, or a string
> class name of a class implementing the interface. For backwards compatibility
> with the `$extractComplexType` setting, boolean variables are parsed the
> following way:
>
> - If `TRUE`, `Zend\Soap\Wsdl\ComplexTypeStrategy\DefaultComplexType` is used.
> - If `FALSE`, `Zend\Soap\Wsdl\ComplexTypeStrategy\AnyType` is used.

### Retrieving type information

The `getType($type)` method may be used to retrieve the mapping for a specified
PHP type:

```php
$wsdl = new Zend\Soap\Wsdl('My_Web_Service', $myWebServiceUri);
$soapIntType = $wsdl->getType('int');

class MyClass
{
    /* ... */
}

$soapMyClassType = $wsdl->getType('MyClass');
```

### Adding complex type information

The `addComplexType($type)` method is used to add complex types (PHP classes) to
a WSDL document.

The method is automatically used by the `getType()` method to add corresponding
complex types of method parameters or return types.

The detection and generation algorithm it uses is based on the currently active
detection strategy for complex types. You can set the detection strategy either
by specifying the class name as a string or providing an instance of a
`Zend\Soap\Wsdl\ComplexTypeStrategy` implementation as the third parameter to
the constructor, or by calling the `setComplexTypeStrategy($strategy)` function
of `Zend\Soap\Wsdl`.

The following detection strategies currently exist:

- `Zend\Soap\Wsdl\ComplexTypeStrategy\DefaultComplexType`: Enabled by default
  (when no third constructor parameter is set). Iterates over the public
  attributes of a class type and registers them as subtypes of the complex
  object type.
- `Zend\Soap\Wsdl\ComplexTypeStrategy\AnyType`: Casts all complex types into the
  simple XSD type `xsd:anyType`. Warning: this shortcut for complex type
  detection can probably only be handled successfully by weakly typed languages
  such as PHP.
- `Zend\Soap\Wsdl\ComplexTypeStrategy\ArrayOfTypeSequence`: This strategy allows
  specifying arrays of the given type, which can be any PHP scalar type (`int`,
  `string`, `bool`, `float`), as well as objects or arrays of objects.
- `Zend\Soap\Wsdl\ComplexTypeStrategy\ArrayOfTypeComplex`: This strategy allows
  detecting very complex arrays of objects. Objects types are detected based on
  the `Zend\Soap\Wsdl\Strategy\DefaultComplexType`, and an array is wrapped
  around that definition.
- `Zend\Soap\Wsdl\ComplexTypeStrategy\Composite`: This strategy can combine all
  strategies by connecting PHP complex types (classes/objects) to the desired
  strategy via the `connectTypeToStrategy($type, $strategy)` method. A complete
  typemap can be given to the constructor as an array with `$type` / `$strategy`
  pairs. The second parameter specifies the default strategy that will be used
  if an unknown type is requested for adding, and defaults to the
  `Zend\Soap\Wsdl\Strategy\DefaultComplexType` strategy.

The `addComplexType()` method creates a
`/definitions/types/xsd:schema/xsd:complexType` element for each described
complex type, using the specified PHP class name.

Class properties **MUST** have a docblock section with the described PHP type in
order to be included in the WSDL description.

`addComplexType()` checks if the type is already described within types section
of the WSDL document, and prevents duplication of types. Additionally, it has
recursion detection.

See the [W3C WSDL types documentation](http://www.w3.org/TR/wsdl#_types) for
more details.

## addDocumentation() method

The `addDocumentation($input_node, $documentation)` method adds human readable
documentation using the optional `wsdl:document` element.

The `/definitions/binding/soap:binding` element is used to signify that the
binding is bound to the SOAP protocol format.

See the [W3C WSDL documentation section](http://www.w3.org/TR/wsdl#_documentation)
for more details.

## Retrieve the final WSDL document

Several methods exist for retrieving the full WSDL definition document:

- `toXML()` will generate an XML string.
- `toDomDocument()` will generate a PHP `DOMDocument` instance.
- `dump($filename = false)` will dump the XML to the specified filename, or, if
  no filename is provided, return the XML string.
