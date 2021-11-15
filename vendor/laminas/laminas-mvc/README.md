# laminas-mvc

[![Build Status](https://travis-ci.com/laminas/laminas-mvc.svg?branch=master)](https://travis-ci.com/laminas/laminas-mvc)
[![Coverage Status](https://coveralls.io/repos/github/laminas/laminas-mvc/badge.svg?branch=master)](https://coveralls.io/github/laminas/laminas-mvc?branch=master)

`Laminas\Mvc` is an MVC implementation focusing on performance and flexibility.

The MVC layer is built on top of the following components:

- `Laminas\ServiceManager` - Laminas provides a set of default service
  definitions set up at `Laminas\Mvc\Service`. The ServiceManager creates and
  configures your application instance and workflow.
- `Laminas\EventManager` - The MVC is event driven. This component is used
  everywhere from initial bootstrapping of the application, through returning
  response and request calls, to setting and retrieving routes and matched
  routes, as well as render views.
- `Laminas\Http` - specifically the request and response objects, used within:
  `Laminas\Stdlib\DispatchableInterface`. All “controllers” are simply dispatchable
  objects.


- File issues at https://github.com/laminas/laminas-mvc/issues
- Documentation is at https://docs.laminas.dev/laminas-mvc/
