# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.6.0 - 2016-02-03

### Added

- [#18](https://github.com/zendframework/zend-crypt/pull/18) adds documentation,
  and publishes it to https://zendframework.github.io/zend-crypt/

### Deprecated

- Nothing.

### Removed

- Removes the (development) dependency on zend-config; tests that used it
  previously have been updated to use `ArrayObject`, which implements the same
  behavior being tested.

### Fixed

- [#4](https://github.com/zendframework/zend-crypt/pull/4) replaces
  the zend-servicemanager with container-interop, and refactors the
  various plugin managers to implement that interface instead of extending the
  `AbstractPluginManager`.

## 2.5.2 - 2015-11-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- **ZF2015-10**: `Zend\Crypt\PublicKey\Rsa\PublicKey` has a call to `openssl_public_encrypt()`
  which used PHP's default `$padding` argument, which specifies
  `OPENSSL_PKCS1_PADDING`, indicating usage of PKCS1v1.5 padding. This padding
  has a known vulnerability, the
  [Bleichenbacher's chosen-ciphertext attack](http://crypto.stackexchange.com/questions/12688/can-you-explain-bleichenbachers-cca-attack-on-pkcs1-v1-5),
  which can be used to recover an RSA private key. This release contains a patch
  that changes the padding argument to use `OPENSSL_PKCS1_OAEP_PADDING`.

  Users upgrading to this version may have issues decrypting previously stored
  values, due to the change in padding. If this occurs, you can pass the
  constant `OPENSSL_PKCS1_PADDING` to a new `$padding` argument in
  `Zend\Crypt\PublicKey\Rsa::encrypt()` and `decrypt()` (though typically this
  should only apply to the latter):

  ```php
  $decrypted = $rsa->decrypt($data, $key, $mode, OPENSSL_PKCS1_PADDING);
  ```

  where `$rsa` is an instance of `Zend\Crypt\PublicKey\Rsa`.

  (The `$key` and `$mode` argument defaults are `null` and
  `Zend\Crypt\PublicKey\Rsa::MODE_AUTO`, if you were not using them previously.)

  We recommend re-encrypting any such values using the new defaults.

## 2.4.9 - 2015-11-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- **ZF2015-10**: `Zend\Crypt\PublicKey\Rsa\PublicKey` has a call to `openssl_public_encrypt()`
  which used PHP's default `$padding` argument, which specifies
  `OPENSSL_PKCS1_PADDING`, indicating usage of PKCS1v1.5 padding. This padding
  has a known vulnerability, the
  [Bleichenbacher's chosen-ciphertext attack](http://crypto.stackexchange.com/questions/12688/can-you-explain-bleichenbachers-cca-attack-on-pkcs1-v1-5),
  which can be used to recover an RSA private key. This release contains a patch
  that changes the padding argument to use `OPENSSL_PKCS1_OAEP_PADDING`.

  Users upgrading to this version may have issues decrypting previously stored
  values, due to the change in padding. If this occurs, you can pass the
  constant `OPENSSL_PKCS1_PADDING` to a new `$padding` argument in
  `Zend\Crypt\PublicKey\Rsa::encrypt()` and `decrypt()` (though typically this
  should only apply to the latter):

  ```php
  $decrypted = $rsa->decrypt($data, $key, $mode, OPENSSL_PKCS1_PADDING);
  ```

  where `$rsa` is an instance of `Zend\Crypt\PublicKey\Rsa`.

  (The `$key` and `$mode` argument defaults are `null` and
  `Zend\Crypt\PublicKey\Rsa::MODE_AUTO`, if you were not using them previously.)

  We recommend re-encrypting any such values using the new defaults.
>>>>>>> hotfix/5
