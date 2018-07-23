# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.8.0 - 2018-04-26

### Added

- [#135](https://github.com/zendframework/zend-http/pull/135) adds a package suggestion of paragonie/certainty, which provides automated
  management of cacert.pem files.

- [#143](https://github.com/zendframework/zend-http/pull/143) adds support for PHP 7.2.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#140](https://github.com/zendframework/zend-http/pull/140) fixes retrieval of headers when multiple headers of the same name
  are added to the `Headers` instance; it now ensures that the last header added of the same
  type is retrieved when it is not a multi-value type. Previous values are overwritten.

- [#112](https://github.com/zendframework/zend-http/pull/112) provides performance improvements when parsing large chunked messages.

- introduces changes to `Response::fromString()` to pull the next line of the response
  and parse it for the status when a 100 status code is initially encountered, per https://tools.ietf.org/html/rfc7231\#section-6.2.1

- [#122](https://github.com/zendframework/zend-http/pull/122) fixes an issue with the stream response whereby if the `outputstream`
  option is set, the output file was opened twice; it is now opened exactly once.

- [#147](https://github.com/zendframework/zend-http/pull/147) fixes an issue with header retrieval when the header line is malformed.
  Previously, an exception would be raised if a specific `HeaderInterface` implementation determined
  the header line was invalid. Now, `Header::has()` will return false for such headers, allowing
  `Request::getHeader()` to return `false` or the provided default value. Additionally, in cases
  where the header name is malformed (e.g., `Useragent` instead of `User-Agent`, users can still
  retrieve by the submitted header name; they will receive a `GenericHeader` instance in such
  cases, however.

- [#133](https://github.com/zendframework/zend-http/pull/133) Adds back missing
  sprintf placeholder in CacheControl exception message

## 2.7.0 - 2017-10-13

### Added

- [#110](https://github.com/zendframework/zend-http/pull/110) Adds status
  codes 226, 308, 444, 499, 510, 599 with their corresponding constants and
  reason phrases.

### Changed

- [#120](https://github.com/zendframework/zend-http/pull/120) Changes handling
  of Cookie Max-Age parameter to conform to specification
  [rfc6265#section-5.2.2](https://tools.ietf.org/html/rfc6265#section-5.2.2).
  Specifically, non-numeric values are ignored and negative numbers are changed
  to 0.

### Deprecated

- Nothing.

### Removed

- [#115](https://github.com/zendframework/zend-http/pull/115) dropped php 5.5
  support

### Fixed

- [#130](https://github.com/zendframework/zend-http/pull/130) Fixed cURL
  adapter not resetting headers from previous request when used with output
  stream.

## 2.6.0 - 2017-01-31

### Added
- [#99](https://github.com/zendframework/zend-http/pull/99) added
  TimeoutException for cURL adapter.
- [#98](https://github.com/zendframework/zend-http/pull/98) added connection
  timeout (`connecttimeout`) for cURL and Socket adapters.
- [#97](https://github.com/zendframework/zend-http/pull/97) added support to
  `sslcafile` and `sslcapath` to cURL adapter.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.6 - 2017-01-31

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#107](https://github.com/zendframework/zend-http/pull/107) fixes the
  `Expires` header to allow values of `0` or `'0'`; these now resolve
  to the start of the unix epoch (1970-01-01).
- [#102](https://github.com/zendframework/zend-http/pull/102) fixes the Curl
  adapter timeout detection.
- [#93](https://github.com/zendframework/zend-http/pull/93) fixes the Content
  Security Policy CSP HTTP header when it is `none` (empty value).
- [#92](https://github.com/zendframework/zend-http/pull/92) fixes the flatten
  cookies value for array value (also multidimensional).
- [#34](https://github.com/zendframework/zend-http/pull/34) fixes the
  standard separator (&) for application/x-www-form-urlencoded.

## 2.5.5 - 2016-08-08

### Added

- [#44](https://github.com/zendframework/zend-http/pull/44),
  [#45](https://github.com/zendframework/zend-http/pull/45),
  [#46](https://github.com/zendframework/zend-http/pull/46),
  [#47](https://github.com/zendframework/zend-http/pull/47),
  [#48](https://github.com/zendframework/zend-http/pull/48), and
  [#49](https://github.com/zendframework/zend-http/pull/49) prepare the
  documentation for publication at https://zendframework.github.io/zend-http/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#87](https://github.com/zendframework/zend-http/pull/87) fixes the
  `ContentLength` constructor to test for a non null value (vs a falsy value)
  before validating the value; this ensures 0 values may be specified for the
  length.
- [#85](https://github.com/zendframework/zend-http/pull/85) fixes infinite recursion
  on AbstractAccept. If you create a new Accept and try to call getFieldValue(),
  an infinite recursion and a fatal error happens.
- [#58](https://github.com/zendframework/zend-http/pull/58) avoid triggering a notice
  with special crafted accept headers. In the case the value of an accept header
  does not contain an equal sign, an "Undefined offset" notice is triggered.

## 2.5.4 - 2016-02-04

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#42](https://github.com/zendframework/zend-http/pull/42) updates dependencies
  to ensure it can work with PHP 5.5+ and 7.0+, as well as zend-stdlib
  2.5+/3.0+.

## 2.5.3 - 2015-09-14

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#23](https://github.com/zendframework/zend-http/pull/23) fixes a BC break
  introduced with fixes for [ZF2015-04](http://framework.zend.com/security/advisory/ZF2015-04),
  pertaining specifically to the `SetCookie` header. The fix backs out a
  check for message splitting syntax, as that particular class already encodes
  the value in a manner that prevents the attack. It also adds tests to ensure
  the security vulnerability remains patched.

## 2.5.2 - 2015-08-05

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#7](https://github.com/zendframework/zend-http/pull/7) fixes a call in the
  proxy adapter to `Response::extractCode()`, which does not exist, to
  `Response::fromString()->getStatusCode()`, which does.
- [#8](https://github.com/zendframework/zend-http/pull/8) ensures that the Curl
  client adapter enables the `CURLINFO_HEADER_OUT`, which is required to ensure
  we can fetch the raw request after it is sent.
- [#14](https://github.com/zendframework/zend-http/pull/14) fixes
  `Zend\Http\PhpEnvironment\Request` to ensure that empty `SCRIPT_FILENAME` and
  `SCRIPT_NAME` values which result in an empty `$baseUrl` will not raise an
  `E_WARNING` when used to do a `strpos()` check during base URI detection.
