# zend-cache

[![Build Status](https://secure.travis-ci.org/zendframework/zend-cache.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-cache)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-cache/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-cache?branch=master)

`Zend\Cache` provides a general cache system for PHP. The `Zend\Cache` component
is able to cache different patterns (class, object, output, etc) using different
storage adapters (DB, File, Memcache, etc).


- File issues at https://github.com/zendframework/zend-cache/issues
- Documentation is at https://docs.zendframework.com/zend-cache/

## Benchmarks

We provide scripts for benchmarking zend-cache using the
[PHPBench](https://github.com/phpbench/phpbench) framework; these can be
found in the `benchmark/` directory.

To execute the benchmarks you can run the following command:

```bash
$ vendor/bin/phpbench run --report=aggregate
```
