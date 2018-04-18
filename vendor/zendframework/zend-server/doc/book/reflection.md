# Zend\\Server\\Reflection

`Zend\Server\Reflection` provides a standard mechanism for performing function
and class introspection for use with server classes. It is based on PHP's
Reflection API, augmenting it with methods for retrieving parameter and return
value types and descriptions, a full list of function and method prototypes
(i.e., all possible valid calling combinations), and function or method
descriptions.

Typically, this functionality will only be used by developers of RPC-style
server classes for the framework.

## Usage

Basic usage is as follows:

```php
use My\Entity;
use Zend\Server\Reflection;

$class    = Reflection::reflectClass(Entity::class);
$function = Reflection::reflectFunction('my_function');

// Get prototypes
$prototypes = $function->getPrototypes();

// Loop through each prototype for the function
foreach ($prototypes as $prototype) {

    // Get prototype return type
    printf("Return type: %s\n", $prototype->getReturnType());

    // Get prototype parameters
    $parameters = $prototype->getParameters();

    echo "Parameters: \n";
    foreach ($parameters as $parameter) {
        // Get parameter type
        printf("    %s\n", $parameter->getType());
    }
}

// Get namespace for a class, function, or method.
// Namespaces may be set at instantiation time (second argument), or using
// setNamespace().
$class->getNamespace();
```

`reflectFunction()` returns a `Zend\Server\Reflection\Function` object;
`reflectClass()` returns a `Zend\Server\Reflection\Class` object. Please refer
to the API documentation to see what methods are available to each.
