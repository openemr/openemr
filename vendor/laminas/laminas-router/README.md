# laminas-router

[![Build Status](https://github.com/laminas/laminas-router/workflows/Continuous%20Integration/badge.svg)](https://github.com/laminas/laminas-router/actions?query=workflow%3A"Continuous+Integration")

laminas-router provides flexible HTTP routing.

Routing currently works against the [laminas-http](https://github.com/laminas/laminas-http)
request and responses, and provides capabilities around:

- Literal path matches
- Path segment matches (at path boundaries, and optionally validated using regex)
- Regular expression path matches
- HTTP request scheme
- HTTP request method
- Hostname

Additionally, it supports combinations of different route types in tree
structures, allowing for fast, b-tree lookups.

## Installation

Run the following to install this library:

```bash
$ composer require laminas/laminas-router
```

## Documentation

Browse the documentation online at https://docs.laminas.dev/laminas-router/

## Support

* [Issues](https://github.com/laminas/laminas-router/issues/)
* [Chat](https://laminas.dev/chat/)
* [Forum](https://discourse.laminas.dev/)
