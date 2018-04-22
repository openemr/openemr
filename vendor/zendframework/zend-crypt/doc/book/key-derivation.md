# Key derivation function

In cryptography, a key derivation function (or KDF) derives one or more secret
keys from a secret value such as a master key, or known information &mdash; such
as a password or passphrase &mdash; using a pseudo-random function. For
instance, a KDF function can be used to generate encryption or authentication
keys from a user password. `Zend\Crypt\Key\Derivation` implements a key
derivation function using specific adapters.

User passwords are not really suitable to be used as keys in cryptographic
algorithms, since users normally choose keys they can write with a keyboard. These
passwords use only 6 to 7 bits per character (or less). It is highly recommended
always to use a KDF function to transform a user's password in a cryptographic
key.

The output of the following key derivation functions is a binary string. If you
need to store the value in a database or a different persistent storage, we
suggest converting it to Base64 format, using the
[base64_encode()](http://php.net/manual/en/function.base64-encode.php) function,
or to hex format, using the
[bin2hex()](http://php.net/manual/en/function.bin2hex.php) function.

## Pbkdf2 adapter

[Pbkdf2](http://en.wikipedia.org/wiki/PBKDF2) is a KDF that applies a
pseudorandom function, such as a cryptographic hash, to the input password or
passphrase along with a salt value and repeats the process many times to produce
a derived key, which can then be used as a cryptographic key in subsequent
operations. The added computational work makes password cracking much more
difficult, and is known as [key
stretching](http://en.wikipedia.org/wiki/Key_stretching).

In the example below we demonstrate typical usage of the `Pbkdf2` adapter.

```php
use Zend\Crypt\Key\Derivation\Pbkdf2;
use Zend\Math\Rand;

$pass = 'password';
$salt = Rand::getBytes(32, true);
$key  = Pbkdf2::calc('sha256', $pass, $salt, 10000, 32);

printf ("Original password: %s\n", $pass);
printf ("Derived key (hex): %s\n", bin2hex($key));
```

The `Pbkdf2` adapter takes the password (`$pass`) and generates a binary key of
32 bytes. The syntax is `calc($hash, $pass, $salt, $iterations, $length)` where
`$hash` is the name of the hash function to use, `$pass` is the password,
`$salt` is a pseudo random value, `$iterations` is the number of iterations of
the algorithm, and `$length` is the size of the key to be generated. We use the
`Rand::getBytes()` function from the class `Zend\Math\Rand` to generate a random
string of 32 bytes for the salt, using a strong generator (the `true` value
means the usage of a cryptographically strong generator).

The number of iterations is a very important parameter for the security of the
algorithm; bigger values guarantee more security. There is no fixed value for
the parameter because the number of iterations depends on CPU power. You should
always choose a number of iterations that prevents brute force attacks.

## SaltedS2k adapter

The [SaltedS2k](http://www.faqs.org/rfcs/rfc2440.html) algorithm uses a hash
function and a salt to generate a key based on a user's password. This algorithm
doesn't use a parameter to specify the number of iterations, and for that reason
it's considered less secure compared to Pbkdf2. We suggest using the SaltedS2k
algorithm only if you really need it (for instance, due to hardware
limitations).

The following demonstrates usage of the `SaltedS2k` adapter to generate a 32
byte key.

```php
use Zend\Crypt\Key\Derivation\SaltedS2k;
use Zend\Math\Rand;

$pass = 'password';
$salt = Rand::getBytes(32, true);
$key  = SaltedS2k::calc('sha256', $pass, $salt, 32);

printf ("Original password: %s\n", $pass);
printf ("Derived key (hex): %s\n", bin2hex($key));
```

## Scrypt adapter

The [scrypt](http://www.tarsnap.com/scrypt.html) algorithm uses the [Salsa20/8
core](http://cr.yp.to/salsa20.html) algorithm and Pbkdf2-SHA256 to generate a
key based on a user's password. This algorithm has been designed to be more
secure against hardware brute-force attacks than alternative functions such as
[Pbkdf2](http://en.wikipedia.org/wiki/PBKDF2) or
[bcrypt](http://en.wikipedia.org/wiki/Bcrypt).

The scrypt algorithm is based on the idea of memory-hard algorithms and
sequential memory-hard functions. A memory-hard algorithm is an algorithm which
asymptotically uses almost as many memory locations as it uses
operations<sup>[1](#footnotes)</sup>. A natural way to reduce the advantage
provided by an attacker’s ability to construct highly parallel circuits is to
increase the size of a single key derivation circuit — if a circuit is twice as
large, only half as many copies can be placed on a given area of silicon — while
still operating within the resources available to software implementations,
including a powerful CPU and large amounts of RAM.

> "From a test executed on modern (2009) hardware, if 5 seconds are spent
> computing a derived key, the cost of a hardware brute-force attack against
> scrypt is roughly 4000 times greater than the cost of a similar attack against
> bcrypt (to find the same password), and 20000 times greater than a similar
> attack against Pbkdf2."
>
> *&mdash; *Colin Percival* (author of the scrypt algorithm)*

This algorithm uses 4 parameters to generate a key of 32 bytes:

- `salt`, a random string;
- `N`, the CPU cost;
- `r`, the memory cost;
- `p`, the parallelization cost.

Following is a usage example for the `Scrypt` adapter:

```php
use Zend\Crypt\Key\Derivation\Scrypt;
use Zend\Math\Rand;

$pass = 'password';
$salt = Rand::getBytes(32, true);
$key  = Scrypt::calc($pass, $salt, 2048, 2, 1, 32);

printf ("Original password: %s\n", $pass);
printf ("Derived key (hex): %s\n", bin2hex($key));
```

> ### Performance of the scrypt implementation
>
> The aim of the scrypt algorithm is to generate a secure derived key that
> prevents brute force attacks.  Just like the other derivation functions, the
> more time (and memory) spent executing the algorithm, the more secure the
> derived key will be. Unfortunately a pure PHP implementation of the scrypt
> algorithm is very slow compared with the C implementation (this is always
> true, if you compare execution time of C with PHP). If you want use a faster
> scrypt algorithm, we suggest installing the PECL [scrypt
> extension](http://pecl.php.net/package/scrypt). The `Scrypt` adapter we
> provide is able to recognize if the PECL extension is loaded and will use it
> instead of the pure PHP implementation.

## Footnotes

- <sup>1</sup> See Colin Percival's [slides on scrypt from BSDCan'09](http://www.tarsnap.com/scrypt/scrypt-slides.pdf).
