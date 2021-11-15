# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.14.3 - 2021-02-18


-----

### Release Notes for [2.14.3](https://github.com/laminas/laminas-http/milestone/8)

2.14.x bugfix release (patch)

### 2.14.3

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Enhancement

 - [50: Migrate to Laminas CI workflow for GHA](https://github.com/laminas/laminas-http/pull/50) thanks to @weierophinney

## 2.14.2 - 2021-01-05


-----

### Release Notes for [2.14.2](https://github.com/laminas/laminas-http/milestone/7)

2.14.x bugfix release (patch)

### 2.14.2

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug,Enhancement

 - [48: Security tightening: verify a stream file name is a string before unlinking](https://github.com/laminas/laminas-http/pull/48) thanks to @weierophinney

## 2.14.1 - 2020-12-31


-----

### Release Notes for [2.14.1](https://github.com/laminas/laminas-http/milestone/4)

2.14.x bugfix release (patch)

### 2.14.1

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [46: fix Curl error &quot;transfer closed with ... bytes remaining to read&quot; with HEAD HTTP method](https://github.com/laminas/laminas-http/pull/46) thanks to @karneds

## 2.14.0 - 2020-12-29


-----

### Release Notes for [2.14.0](https://github.com/laminas/laminas-http/milestone/2)



### 2.14.0

- Total issues resolved: **0**
- Total pull requests resolved: **2**
- Total contributors: **2**

#### Enhancement,hacktoberfest-accepted

 - [45: PHP 8 support](https://github.com/laminas/laminas-http/pull/45) thanks to @ocean

#### Enhancement

 - [33: Add Content-Security-Policy-Report-Only header](https://github.com/laminas/laminas-http/pull/33) thanks to @xmorave2

## 2.13.0 - 2020-08-18

### Added

- [#41](https://github.com/laminas/laminas-http/pull/41) adds a new method to `Laminas\Http\PhpEnvironment\Response`, `setHeadersSentHandler(callable $handler): void`. When a handler is injected, `sendHeaders()` will call it with the current response instance if it detects headers have already been sent (e.g., by the SAPI due to emitting content). Prior to this change, the class would silently ignore the fact, and simply not emit headers from the response instance. Now it is possible to log those headers, or raise an exception.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.12.0 - 2020-06-23

### Added

- [#33](https://github.com/laminas/laminas-http/pull/33) adds a new header type, `Laminas\Http\Header\ContentSecurityPolicyReportOnly`, mapping to Content-Security-Policy-Report-Only headers, which can be used for experimenting with policies without impacting your application.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.11.3 - 2020-06-23

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#39](https://github.com/laminas/laminas-http/pull/39) fixes the default user-agent header to replace escape characters with underscores, ensuring it works with all clients and servers.

- [#31](https://github.com/laminas/laminas-http/pull/31) updates the socket and proxy adapters to retain the previous TLS defaults, which had broken with PHP 5.6.7+ due to a change in the meaning of the STREAM_CRYPTO_METHOD_TLS_CLIENT constant.

## 2.11.2 - 2019-12-30

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-http#207](https://github.com/zendframework/zend-http/pull/207) fixes case sensitivity for SameSite directive.

## 2.11.1 - 2019-12-04

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-http#204](https://github.com/zendframework/zend-http/pull/204) fixes numerous header classes to cast field value to string (since `HeaderInterface::getFieldValue()` specifies a return value of a string).

- [zendframework/zend-http#182](https://github.com/zendframework/zend-http/pull/182) fixes detecting base uri in Request. Now `argv` is used only for CLI request as a fallback to detect script filename.

## 2.11.0 - 2019-12-03

### Added

- [zendframework/zend-http#175](https://github.com/zendframework/zend-http/pull/175) adds support for Content Security Policy Level 3 Header directives.

- [zendframework/zend-http#200](https://github.com/zendframework/zend-http/pull/200) adds support for additional directives in Content Security Policy header:
  - `block-all-mixed-content`,
  - `require-sri-for`,
  - `trusted-types`,
  - `upgrade-insecure-requests`.

- [zendframework/zend-http#177](https://github.com/zendframework/zend-http/pull/177) adds support for Feature Policy header.

- [zendframework/zend-http#186](https://github.com/zendframework/zend-http/pull/186) adds support for SameSite directive in Set-Cookie header.

### Changed

- [zendframework/zend-http#194](https://github.com/zendframework/zend-http/pull/194) changes range of valid HTTP status codes to 100-599 (inclusive).

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-http#200](https://github.com/zendframework/zend-http/pull/200) fixes support for directives without value in Content Security Policy header.

## 2.10.1 - 2019-12-02

### Added

- Nothing.

### Changed

- [zendframework/zend-http#190](https://github.com/zendframework/zend-http/pull/190) changes `ContentSecurityPolicy` to allow multiple values. Before it was not possible to provide multiple headers of that type.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-http#184](https://github.com/zendframework/zend-http/pull/184) fixes responses for request through the proxy with `HTTP/1.1 200 Connection established` header. 

- [zendframework/zend-http#187](https://github.com/zendframework/zend-http/pull/187) fixes infinite recursion on invalid header. Now `InvalidArgumentException` exception is thrown. 

- [zendframework/zend-http#188](https://github.com/zendframework/zend-http/pull/188) fixes `Client::setCookies` method to properly handle array of `SetCookie` objects. Per [documentation](https://docs.laminas.dev/laminas-http/client/cookies/#usage) it should be allowed. 

- [zendframework/zend-http#189](https://github.com/zendframework/zend-http/pull/189) fixes `Headers::toArray` method to properly handle headers of the same type. Behaviour was different depends how header has been attached (`addHeader` or `addHeaderLine` broken before). 

- [zendframework/zend-http#198](https://github.com/zendframework/zend-http/pull/198) fixes merging options in Curl adapter. It was not possible to override integer-key options (constants) set via constructor with method `setOptions`. 

- [zendframework/zend-http#198](https://github.com/zendframework/zend-http/pull/198) fixes allowed options type in `Proxy::setOptions`. `Traversable`, `array` or `Laminas\Config` object is expected.

- [zendframework/zend-http#198](https://github.com/zendframework/zend-http/pull/198) fixes various issues with `Proxy` adapter.

- [zendframework/zend-http#199](https://github.com/zendframework/zend-http/pull/199) fixes saving resource to the file when streaming while client supports compression. Before, incorrectly, compressed resource was saved into the file.

## 2.10.0 - 2019-02-19

### Added

- [zendframework/zend-http#173](https://github.com/zendframework/zend-http/pull/173) adds support for HTTP/2 requests and responses.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.9.1 - 2019-01-22

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-http#168](https://github.com/zendframework/zend-http/pull/168) fixes a problem when validating the connection timeout for the `Curl` and
  `Socket` client adapters; it now correctly identifies both integer and string
  integer values.

## 2.9.0 - 2019-01-08

### Added

- [zendframework/zend-http#154](https://github.com/zendframework/zend-http/pull/154) adds the method `SetCookie::setEncodeValue()`. By default, Set-Cookie
  values are passed through `urlencode()`; when a boolean `false` is provided to
  this new method, the raw value will be used instead.

- [zendframework/zend-http#166](https://github.com/zendframework/zend-http/pull/166) adds support for PHP 7.3.

### Changed

- [zendframework/zend-http#154](https://github.com/zendframework/zend-http/pull/154) changes the behavior of `SetCookie::fromString()` slightly: if the parsed
  cookie value is the same as the one passed through `urldecode()`, the
  `SetCookie` header's `$encodeValue` property will be toggled off to ensure the
  value is not encoded in subsequent serializations, thus retaining the
  integrity of the value between usages.

- [zendframework/zend-http#161](https://github.com/zendframework/zend-http/pull/161) changes how the Socket and Test adapters aggregate headers. Previously,
  they would `ucfirst()` the header name; now, they correctly leave the header
  names untouched, as header names should be considered case-insensitive.

- [zendframework/zend-http#156](https://github.com/zendframework/zend-http/pull/156) changes how gzip and deflate decompression occur in responses, ensuring
  that if the Content-Length header reports 0, no decompression is attempted,
  and an empty string is returned.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-http#166](https://github.com/zendframework/zend-http/pull/166) removes support for laminas-stdlib v2 releases.

### Fixed

- Nothing.

## 2.8.3 - 2019-01-08

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-http#165](https://github.com/zendframework/zend-http/pull/165) fixes detection of the base URL when operating under a CLI environment.

- [zendframework/zend-http#149](https://github.com/zendframework/zend-http/pull/149) provides fixes to `Client::setUri()` to ensure its status as a relative
  or absolute URI is correctly memoized.

- [zendframework/zend-http#162](https://github.com/zendframework/zend-http/pull/162) fixes a typo in an exception message raised within `Cookies::fromString()`.

- [zendframework/zend-http#121](https://github.com/zendframework/zend-http/pull/121) adds detection for non-numeric connection timeout values as well as
  integer casting to ensure the timeout is set properly in both the Curl and
  Socket adapters.

## 2.8.2 - 2018-08-13

### Added

- Nothing.

### Changed

- [zendframework/zend-http#153](https://github.com/zendframework/zend-diactoros/pull/153) changes the reason phrase associated with the status code 425
  from "Unordered Collection" to "Too Early", corresponding to a new definition
  of the code as specified by the IANA.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-http#151](https://github.com/zendframework/zend-http/pull/151) fixes how Referer and other location-based headers report problems with
  invalid URLs provided in the header value, raising a `Laminas\Http\Exception\InvalidArgumentException`
  in such cases. This change ensures the behavior is consistent with behavior
  prior to the 2.8.0 release.

## 2.8.1 - 2018-08-01

### Added

- Nothing.

### Changed

- This release modifies how `Laminas\Http\PhpEnvironment\Request` marshals the
  request URI. In prior releases, we would attempt to inspect the
  `X-Rewrite-Url` and `X-Original-Url` headers, using their values, if present.
  These headers are issued by the ISAPI_Rewrite module for IIS (developed by
  HeliconTech). However, we have no way of guaranteeing that the module is what
  issued the headers, making it an unreliable source for discovering the URI. As
  such, we have removed this feature in this release of laminas-http.

  If you are developing a laminas-mvc application, you can mimic the
  functionality by adding a bootstrap listener like the following:

  ```php
  public function onBootstrap(MvcEvent $mvcEvent)
  {
      $request = $mvcEvent->getRequest();
      $requestUri = null;

      $httpXRewriteUrl = $request->getHeader('X-Rewrite-Url');
      if ($httpXRewriteUrl) {
          $requestUri = $httpXRewriteUrl->getFieldValue();
      }

      $httpXOriginalUrl = $request->getHeader('X-Original-Url');
      if ($httpXOriginalUrl) {
          $requestUri = $httpXOriginalUrl->getFieldValue();
      }

      if ($requestUri) {
          $request->setUri($requestUri)
      }
  }
  ```

  If you use a listener such as the above, make sure you also instruct your web
  server to strip any incoming headers of the same name so that you can
  guarantee they are issued by the ISAPI_Rewrite module.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.0 - 2018-04-26

### Added

- [zendframework/zend-http#135](https://github.com/zendframework/zend-http/pull/135) adds a package suggestion of paragonie/certainty, which provides automated
  management of cacert.pem files.

- [zendframework/zend-http#143](https://github.com/zendframework/zend-http/pull/143) adds support for PHP 7.2.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-http#140](https://github.com/zendframework/zend-http/pull/140) fixes retrieval of headers when multiple headers of the same name
  are added to the `Headers` instance; it now ensures that the last header added of the same
  type is retrieved when it is not a multi-value type. Previous values are overwritten.

- [zendframework/zend-http#112](https://github.com/zendframework/zend-http/pull/112) provides performance improvements when parsing large chunked messages.

- introduces changes to `Response::fromString()` to pull the next line of the response
  and parse it for the status when a 100 status code is initially encountered, per https://tools.ietf.org/html/rfc7231\#section-6.2.1

- [zendframework/zend-http#122](https://github.com/zendframework/zend-http/pull/122) fixes an issue with the stream response whereby if the `outputstream`
  option is set, the output file was opened twice; it is now opened exactly once.

- [zendframework/zend-http#147](https://github.com/zendframework/zend-http/pull/147) fixes an issue with header retrieval when the header line is malformed.
  Previously, an exception would be raised if a specific `HeaderInterface` implementation determined
  the header line was invalid. Now, `Header::has()` will return false for such headers, allowing
  `Request::getHeader()` to return `false` or the provided default value. Additionally, in cases
  where the header name is malformed (e.g., `Useragent` instead of `User-Agent`, users can still
  retrieve by the submitted header name; they will receive a `GenericHeader` instance in such
  cases, however.

- [zendframework/zend-http#133](https://github.com/zendframework/zend-http/pull/133) Adds back missing
  sprintf placeholder in CacheControl exception message

## 2.7.0 - 2017-10-13

### Added

- [zendframework/zend-http#110](https://github.com/zendframework/zend-http/pull/110) Adds status
  codes 226, 308, 444, 499, 510, 599 with their corresponding constants and
  reason phrases.

### Changed

- [zendframework/zend-http#120](https://github.com/zendframework/zend-http/pull/120) Changes handling
  of Cookie Max-Age parameter to conform to specification
  [rfc6265#section-5.2.2](https://tools.ietf.org/html/rfc6265#section-5.2.2).
  Specifically, non-numeric values are ignored and negative numbers are changed
  to 0.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-http#115](https://github.com/zendframework/zend-http/pull/115) dropped php 5.5
  support

### Fixed

- [zendframework/zend-http#130](https://github.com/zendframework/zend-http/pull/130) Fixed cURL
  adapter not resetting headers from previous request when used with output
  stream.

## 2.6.0 - 2017-01-31

### Added
- [zendframework/zend-http#99](https://github.com/zendframework/zend-http/pull/99) added
  TimeoutException for cURL adapter.
- [zendframework/zend-http#98](https://github.com/zendframework/zend-http/pull/98) added connection
  timeout (`connecttimeout`) for cURL and Socket adapters.
- [zendframework/zend-http#97](https://github.com/zendframework/zend-http/pull/97) added support to
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

- [zendframework/zend-http#107](https://github.com/zendframework/zend-http/pull/107) fixes the
  `Expires` header to allow values of `0` or `'0'`; these now resolve
  to the start of the unix epoch (1970-01-01).
- [zendframework/zend-http#102](https://github.com/zendframework/zend-http/pull/102) fixes the Curl
  adapter timeout detection.
- [zendframework/zend-http#93](https://github.com/zendframework/zend-http/pull/93) fixes the Content
  Security Policy CSP HTTP header when it is `none` (empty value).
- [zendframework/zend-http#92](https://github.com/zendframework/zend-http/pull/92) fixes the flatten
  cookies value for array value (also multidimensional).
- [zendframework/zend-http#34](https://github.com/zendframework/zend-http/pull/34) fixes the
  standard separator (&) for application/x-www-form-urlencoded.

## 2.5.5 - 2016-08-08

### Added

- [zendframework/zend-http#44](https://github.com/zendframework/zend-http/pull/44),
  [zendframework/zend-http#45](https://github.com/zendframework/zend-http/pull/45),
  [zendframework/zend-http#46](https://github.com/zendframework/zend-http/pull/46),
  [zendframework/zend-http#47](https://github.com/zendframework/zend-http/pull/47),
  [zendframework/zend-http#48](https://github.com/zendframework/zend-http/pull/48), and
  [zendframework/zend-http#49](https://github.com/zendframework/zend-http/pull/49) prepare the
  documentation for publication at https://docs.laminas.dev/laminas-http/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-http#87](https://github.com/zendframework/zend-http/pull/87) fixes the
  `ContentLength` constructor to test for a non null value (vs a falsy value)
  before validating the value; this ensures 0 values may be specified for the
  length.
- [zendframework/zend-http#85](https://github.com/zendframework/zend-http/pull/85) fixes infinite recursion
  on AbstractAccept. If you create a new Accept and try to call getFieldValue(),
  an infinite recursion and a fatal error happens.
- [zendframework/zend-http#58](https://github.com/zendframework/zend-http/pull/58) avoid triggering a notice
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

- [zendframework/zend-http#42](https://github.com/zendframework/zend-http/pull/42) updates dependencies
  to ensure it can work with PHP 5.5+ and 7.0+, as well as laminas-stdlib
  2.5+/3.0+.

## 2.5.3 - 2015-09-14

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-http#23](https://github.com/zendframework/zend-http/pull/23) fixes a BC break
  introduced with fixes for [ZF2015-04](https://getlaminas.org/security/advisory/ZF2015-04),
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

- [zendframework/zend-http#7](https://github.com/zendframework/zend-http/pull/7) fixes a call in the
  proxy adapter to `Response::extractCode()`, which does not exist, to
  `Response::fromString()->getStatusCode()`, which does.
- [zendframework/zend-http#8](https://github.com/zendframework/zend-http/pull/8) ensures that the Curl
  client adapter enables the `CURLINFO_HEADER_OUT`, which is required to ensure
  we can fetch the raw request after it is sent.
- [zendframework/zend-http#14](https://github.com/zendframework/zend-http/pull/14) fixes
  `Laminas\Http\PhpEnvironment\Request` to ensure that empty `SCRIPT_FILENAME` and
  `SCRIPT_NAME` values which result in an empty `$baseUrl` will not raise an
  `E_WARNING` when used to do a `strpos()` check during base URI detection.
