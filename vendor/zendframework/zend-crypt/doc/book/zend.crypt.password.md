# Password

In the `Zend\Crypt\Password` namespace you can find all the password formats supported by Zend
Framework. We currently support the following passwords:

> -   bcrypt;
- Apache (htpasswd).

If you need to choose a password format to store the user's password we suggest to use the *bcrypt*
algorithm that is considered secure against brute forcing attacks (see the details below).

## Bcrypt

The [bcrypt](http://en.wikipedia.org/wiki/Bcrypt) algorithm is an hashing algorithm that is widely
used and suggested by the security community to store user’s passwords in a secure way.

Classic hashing mechanisms like MD5 or SHA, with or without a *salt* value, are not considered
secure anymore ([read this post to know why](http://codahale.com/how-to-safely-store-a-password/)).

The security of bcrypt is related to the speed of the algorithm. Bcrypt is very slow, it can request
even a second to generate an hash value. That means a brute force attack is impossible to execute,
due to the amount of time that its need.

Bcrypt uses a *cost* parameter that specify the number of cycles to use in the algorithm. Increasing
this number the algorithm will spend more time to generate the hash output. The *cost* parameter is
represented by an integer value between 4 to 31. The default *cost* value of the
`Zend\Crypt\Password\Bcrypt` component is 10, that means about 0.07 second using a CPU Intel i5 at
3.3Ghz (the *cost* parameter is a relative value according to the speed of the CPU used). We changed
the default value of the cost parameter from 14 to 10, starting from Zend Framework 2.3.0, due to
high computational time to prevent potential denial-of-service attacks (you can read this article
[Aggressive password
stretching](http://timoh6.github.io/2013/11/26/Aggressive-password-stretching.html) for more
information).

If you want to change the *cost* parameter of the bcrypt algorithm you can use the `setCost()`
method. Please note, if you change the cost parameter, the resulting hash will be different. This
will not affect the verification process of the algorithm, therefore not breaking the password
hashes you already have stored. Bcrypt reads the *cost* parameter from the hash value, during the
password authentication. All of the parts needed to verify the hash are all together, separated with
$'s, first the algorithm, then the cost, the salt, and then finally the hash.

The example below shows how to use the bcrypt algorithm to store a user's password:

```php
use Zend\Crypt\Password\Bcrypt;

$bcrypt = new Bcrypt();
$securePass = $bcrypt->create('user password');
```

The output of the `create()` method is the hash of the password. This value can then be stored in a
repository like a database (the output is a string of 60 bytes).

> ## Note
#### Bcrypt truncates input &gt; 72 bytes
The input string of the bcrypt algorithm is limited to 72 bytes. If you use a string with a length
more than this limit, bcrypt will consider only the first 72 bytes. If you need to use a longer
string, you should pre-hash it using SHA256 prior to passing it to the bcrypt algorithm:
`$hashedPassword = \Zend\Crypt\Hash::compute('sha256', $password);`

To verify if a given password is valid against a bcrypt value you can use the `verify()` method. An
example is reported below:

```php
use Zend\Crypt\Password\Bcrypt;

$bcrypt = new Bcrypt();
$securePass = 'the stored bcrypt value';
$password = 'the password to check';

if ($bcrypt->verify($password, $securePass)) {
    echo "The password is correct! \n";
} else {
    echo "The password is NOT correct.\n";
}
```

In the bcrypt uses also a *salt* value to improve the randomness of the algorithm. By default, the
`Zend\Crypt\Password\Bcrypt` component generates a random salt for each hash. If you want to specify
a preselected salt you can use the `setSalt()` method.

We provide also a `getSalt()` method to retrieve the *salt* specified by the user. The *salt* and
the *cost* parameter can be also specified during the constructor of the class, below is reported an
example:

```php
use Zend\Crypt\Password\Bcrypt;

$bcrypt = new Bcrypt(array(
    'salt' => 'random value',
    'cost' => 11
));
```

> ## Note
#### Bcrypt with non-ASCII passwords (8-bit characters)
The bcrypt implementation used by PHP &lt; 5.3.7 can contains a security flaw if the password uses
8-bit characters ([here's the security report](http://php.net/security/crypt_blowfish.php)). The
impact of this bug was that most (but not all) passwords containing non-ASCII characters with the
8th bit set were hashed incorrectly, resulting in password hashes incompatible with those of
OpenBSD's original implementation of bcrypt. This security flaw has been fixed starting from PHP
5.3.7 and the prefix used in the output was changed to '$2y$' in order to put evidence on the
correctness of the hash value. If you are using PHP &lt; 5.3.7 with 8-bit passwords, the
`Zend\Crypt\Password\Bcrypt` throws an exception suggesting to upgrade to PHP 5.3.7+ or use only
7-bit passwords.

## Apache

The `Zend\Crypt\Password\Apache` supports all the password formats used by
[Apache](http://httpd.apache.org/docs/2.2/misc/password_encryptions.html) (htpasswd). These formats
are:

> -   *CRYPT*, uses the traditional Unix crypt(3) function with a randomly-generated 32-bit salt
(only 12 bits used) and the first 8 characters of the password;
- *SHA1*, "{SHA}" + Base64-encoded SHA-1 digest of the password;
- *MD5*, "$apr1$" + the result of an Apache-specific algorithm using an iterated (1,000 times) MD5
digest of various combinations of a random 32-bit salt and the password.
- *Digest*, the MD5 hash of the string *user*:*realm*:*password* as a 32-character string of
hexadecimal digits. *realm* is the Authorization Realm argument to the *AuthName* directive in
httpd.conf.

In order to specify the format of the Apache's password you can use the `setFormat()` method. An
example with all the formats usage is reported below:

```php
use Zend\Crypt\Password\Apache;

$apache = new Apache();

$apache->setFormat('crypt');
printf ("CRYPT output: %s\n", $apache->create('password'));

$apache->setFormat('sha1');
printf ("SHA1 output: %s\n", $apache->create('password'));

$apache->setFormat('md5');
printf ("MD5 output: %s\n", $apache->create('password'));

$apache->setFormat('digest');
$apache->setUserName('enrico');
$apache->setAuthName('test');
printf ("Digest output: %s\n", $apache->create('password'));
```

You can also specify the format of the password during the constructor of the class:

```php
use Zend\Crypt\Password\Apache;

$apache = new Apache(array(
    'format' => 'md5'
));
```

Other possible parameters to pass in the constructor are *username* and *authname*, for the digest
format.
