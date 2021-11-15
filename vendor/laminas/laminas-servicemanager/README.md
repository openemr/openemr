# laminas-servicemanager

Master:
[![Build Status](https://travis-ci.com/laminas/laminas-servicemanager.svg?branch=master)](https://travis-ci.com/laminas/laminas-servicemanager)
[![Coverage Status](https://coveralls.io/repos/github/laminas/laminas-servicemanager/badge.svg?branch=master)](https://coveralls.io/github/laminas/laminas-servicemanager?branch=master)
Develop:
[![Build Status](https://travis-ci.com/laminas/laminas-servicemanager.svg?branch=develop)](https://travis-ci.com/laminas/laminas-servicemanager)
[![Coverage Status](https://coveralls.io/repos/github/laminas/laminas-servicemanager/badge.svg?branch=develop)](https://coveralls.io/github/laminas/laminas-servicemanager?branch=develop)

The Service Locator design pattern is implemented by the `Laminas\ServiceManager`
component. The Service Locator is a service/object locator, tasked with
retrieving other objects.

- File issues at https://github.com/laminas/laminas-servicemanager/issues
- [Online documentation](https://docs.laminas.dev/laminas-servicemanager)
- [Documentation source files](docs/book/)

## Benchmarks

We provide scripts for benchmarking laminas-servicemanager using the
[PHPBench](https://github.com/phpbench/phpbench) framework; these can be
found in the `benchmarks/` directory.

To execute the benchmarks you can run the following command:

```bash
$ vendor/bin/phpbench run --report=aggregate
```
