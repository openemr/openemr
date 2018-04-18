# Introduction

From its [home page](http://www.xmlrpc.com/), XML-RPC is described as:

> "...remote procedure calling using HTTP as the transport and XML as the
> encoding. XML-RPC is designed to be as simple as possible, while allowing
> complex data structures to be transmitted, processed and returned."

zend-xmlrpc provides support for both consuming remote XML-RPC services and
providing XML-RPC servers.

## Quick Start

The following demonstrates the most basic use case for `Zend\XmlRpc\Server`:

```php
class Greeter
{
    /**
    * Say hello to someone.
    *
    * @param string $name Who to greet
    * @return string
    */
    public function sayHello($name = 'Stranger')
    {
        return sprintf("Hello %s!", $name);
    }
}

$server = new Zend\XmlRpc\Server;

// Our Greeter class will be called "greeter" from the client:
$server->setClass('Greeter', 'greeter');
$server->handle();
```

> ### Docblock annotations are required
>
> Function and method docblocks containing parameter and return value
> annotations **are required** when exposing them via `Zend\XmlRpc\Server`. The
> values will be used to validate method parameters and provide method
> signatures to clients.
>
> Docblock descriptions will also be used to provide method help text.

The following demonstrates an XML-RPC client that can consume the above service:

```php
$client = new Zend\XmlRpc\Client('http://example.com/xmlrpcserver.php');

echo $client->call('greeter.sayHello');
// will output "Hello Stranger!"

echo $client->call('greeter.sayHello', ['Dude']);
// will output "Hello Dude!"
```
