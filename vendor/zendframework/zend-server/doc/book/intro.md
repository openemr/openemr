# Introduction

The zend-server family of classes provides functionality for the various server
classes, including [zend-xmlrpc](https://zendframework.github.io/zend-xmlrpc) and
[zend-json-server](https://zendframework.github.io/zend-json-server/).
`Zend\Server\Server` provides an interface that mimics PHP’s `SoapServer` class;
all RPC-style server classes should implement this interface in order to provide a
standard server API.

The `Zend\Server\Reflection` tree provides a standard mechanism for performing
function and class introspection for use as callbacks with the server classes,
and provides data suitable for use with `Zend\Server\Server`'s `getFunctions()`
and `loadFunctions()` methods.
