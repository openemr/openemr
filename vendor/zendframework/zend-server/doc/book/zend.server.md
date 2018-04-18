# Introduction to Zend\\Server

The `Zend\Server` family of classes provides functionality for the various 
server classes, including `Zend\XmlRpc\Server` and `Zend\Json\Server`. 
`Zend\Server\Server` provides an interface that mimics PHP 5’s `SoapServer` 
class; all server classes should implement this interface in order to provide a
standard server API.

The `Zend\Server\Reflection` tree provides a standard mechanism for performing 
function and class introspection for use as callbacks with the server classes, 
and provides data suitable for use with `Zend\Server\Server`‘s `getFunctions()`
and `loadFunctions()` methods.