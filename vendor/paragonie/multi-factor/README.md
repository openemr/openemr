# Multi-Factor

[![Build Status](https://travis-ci.org/paragonie/multi_factor.svg?branch=master)](https://travis-ci.org/paragonie/multi_factor)

Designed to be a vendor-agnostic implementation of various Two-Factor 
Authentication solutions.

Developed by [Paragon Initiative Enterprises](https://paragonie.com) for use
in our own projects. It's released under a dual license: GPL and MIT. As with
all dual-licensed projects, feel free to choose the license that fits your
needs.

## Requirements

* PHP 7
  * As per [Paragon Initiative Enterprise's commitment to open source](https://paragonie.com/blog/2016/04/go-php-7-our-commitment-maintaining-our-open-source-projects),
    all new software will no longer be written for PHP 5.

## Installing

```sh
composer require paragonie/multi-factor
```

## Example Usage

```php
<?php
use ParagonIE\MuiltiFactor\FIDOU2F;
use ParagonIE\MultiFactor\OTP\TOTP;

$seed = random_bytes(20);

// You can use TOTP or HOTP
$fido = new FIDOU2F($seed, new TOTP());

if (\password_verify($_POST['password'], $storedHash)) {
    if ($fido->validateCode($_POST['2facode'])) {
        // Login successful    
    }
}
```