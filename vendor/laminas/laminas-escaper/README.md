# laminas-escaper

[![Build Status](https://travis-ci.com/laminas/laminas-escaper.svg?branch=master)](https://travis-ci.com/laminas/laminas-escaper)
[![Coverage Status](https://coveralls.io/repos/github/laminas/laminas-escaper/badge.svg?branch=master)](https://coveralls.io/github/laminas/laminas-escaper?branch=master)

The OWASP Top 10 web security risks study lists Cross-Site Scripting (XSS) in
second place. PHP’s sole functionality against XSS is limited to two functions
of which one is commonly misapplied. Thus, the laminas-escaper component was written.
It offers developers a way to escape output and defend from XSS and related
vulnerabilities by introducing contextual escaping based on peer-reviewed rules.

## Installation

Run the following to install this library:

```bash
$ composer require laminas/laminas-escaper
```

## Documentation

Browse the documentation online at https://docs.laminas.dev/laminas-escaper/

## Support

* [Issues](https://github.com/laminas/laminas-escaper/issues/)
* [Chat](https://laminas.dev/chat/)
* [Forum](https://discourse.laminas.dev/)
