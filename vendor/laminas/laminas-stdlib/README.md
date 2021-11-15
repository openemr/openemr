# laminas-stdlib

[![Build Status](https://travis-ci.com/laminas/laminas-stdlib.svg?branch=master)](https://travis-ci.com/laminas/laminas-stdlib)
[![Coverage Status](https://coveralls.io/repos/github/laminas/laminas-stdlib/badge.svg?branch=master)](https://coveralls.io/github/laminas/laminas-stdlib?branch=master)

`Laminas\Stdlib` is a set of components that implements general purpose utility
class for different scopes like:

- array utilities functions;
- general messaging systems;
- string wrappers;
- etc.

---

- File issues at https://github.com/laminas/laminas-stdlib/issues
- Documentation is at https://docs.laminas.dev/laminas-stdlib/

## Benchmarks

We provide scripts for benchmarking laminas-stdlib using the
[PHPBench](https://github.com/phpbench/phpbench) framework; these can be
found in the `benchmark/` directory.

To execute the benchmarks you can run the following command:

```bash
$ vendor/bin/phpbench run --report=aggregate
```
