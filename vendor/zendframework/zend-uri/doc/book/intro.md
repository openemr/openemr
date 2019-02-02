# Introduction

zend-uri aids in manipulating and validating [Uniform 
Resource Identifiers](http://www.w3.org/Addressing/)
([URIs](http://www.ietf.org/rfc/rfc3986.txt)). zend-uri exists primarily
to assist other components, such as
[zend-http](https://zendframework.github.io/zend-http/), but is also useful as a
standalone utility.

URIs always begin with a scheme, followed by a colon. The construction of the
many different schemes varies significantly. The zend-uri component provides the
`Zend\Uri\UriFactory` that returns an instance of the appropriate class
implementing `Zend\Uri\UriInterface` for the given scheme (assuming the factory
can locate one).
