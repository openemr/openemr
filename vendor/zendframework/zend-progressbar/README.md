# zend-progressbar

[![Build Status](https://secure.travis-ci.org/zendframework/zend-progressbar.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-progressbar)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-progressbar/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-progressbar?branch=master)

zend-progressbar is a component to create and update progress bars in different
environments. It consists of a single backend, which outputs the progress through
one of the multiple adapters. On every update, it takes an absolute value and
optionally a status message, and then calls the adapter with some precalculated
values like percentage and estimated time left.

- File issues at https://github.com/zendframework/zend-progressbar/issues
- Documentation is at https://docs.zendframework.com/zend-progressbar/
