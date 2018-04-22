# Password

In the `Zend\Crypt\Password` namespace you will find a number of password
formats supported by the zend-crypt component. These currently include:

- bcrypt 
- Apache (htpasswd)

If you need to choose a password format to store a user’s password, we suggest
using the bcrypt algorithm, as it is considered secure against brute forcing
attacks (see details below).

## Bcrypt

The [bcrypt](http://en.wikipedia.org/wiki/Bcrypt) algorithm is a hashing algorithm that is widely used and recommended
by the security community to store user’s passwords in a secure way.

Classic hashing mechanisms like MD5 or SHA, with or without a salt value, are
not considered secure anymore ([read this post to understand
why](http://codahale.com/how-to-safely-store-a-password/)).

The security of bcrypt is related to the speed of the algorithm. Bcrypt is very
slow, and can take up to a second to generate a hash value. That means a brute
force attack is impossible to execute, due to the amount of time that required.

Bcrypt uses a *cost* parameter that specify the number of cycles to use in the
algorithm. Increasing this number the algorithm will spend more time to generate
the hash output. The cost parameter is represented by an integer value between 4
to 31. The default cost value of `Zend\Crypt\Password\Bcrypt` is 10, requiring
around 0.07s using a CPU Intel i5 at 3.3Ghz (the cost parameter is a relative
value according to the speed of the CPU used). Starting with version 2.3.0, we
changed the default value of the cost parameter from 14 to 10, in an effort to
reduce denial-of-service attacks due to too high computational time
requirements.  (Read this article on [aggressive password
stretching](http://timoh6.github.io/2013/11/26/Aggressive-password-stretching.html)
for more information).

If you want to change the cost parameter of the bcrypt algorithm, you can use
the `setCost()` method. Please note, if you change the cost parameter, the
resulting hash will be different. However, This will not affect the verification
process of the algorithm, therefore not breaking the password hashes you already
have stored; Bcrypt reads the cost parameter from the hash value during password
authentication. All of the parts needed to verify the hash are present in the
hash itself,, separated with `$`’s; first the algorithm, then the cost, the
salt, and then finally the hash.

The example below demonstrates using the bcrypt algorithm to store a user’s password:

```php
use Zend\Crypt\Password\Bcrypt;

$bcrypt = new Bcrypt();
$securePass = $bcrypt->create('user password');
```

The output of the `create()` method is the hash of the password. This value can
then be stored in a repository like a database (the output is a string of 60
bytes).

> ### Bcrypt truncates input > 72 bytes
> 
> The input string of the bcrypt algorithm is limited to 72 bytes. If you use a
> string with a length more than this limit, bcrypt will consider only the first
> 72 bytes. If you need to use a longer string, you should pre-hash it using
> SHA256 prior to passing it to the bcrypt algorithm: `$hashedPassword =
> \Zend\Crypt\Hash::compute('sha256', $password);`

To verify if a given password is valid against a bcrypt value you can use the
`verify()` method. The example below demonstrates verification:

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

Bcrypt also uses a salt value to improve the randomness of the algorithm.
By default, `Zend\Crypt\Password\Bcrypt` generates a random salt for
each hash. If you want to specify a preselected salt you can use the `setSalt()`
method.

We also provide a `getSalt()` method to retrieve the salt specified by the user.
The salt and the cost parameter can be also specified during the constructor of
the class, as demonstrated below:

```php
use Zend\Crypt\Password\Bcrypt;

$bcrypt = new Bcrypt([
    'salt' => 'random value',
    'cost' => 11
]);
```

## Apache

`Zend\Crypt\Password\Apache` supports all the password formats used by
[Apache](http://httpd.apache.org/docs/2.2/misc/password_encryptions.html)
(htpasswd). These formats include:

- CRYPT, which uses the traditional Unix crypt(3) function with a
  randomly-generated 32-bit salt (only 12 bits used) and the first 8 characters
  of the password;
- SHA1, “{SHA}” + Base64-encoded SHA-1 digest of the password;
- MD5, “$apr1$” + the result of an Apache-specific algorithm using an iterated
  (1,000 times) MD5 digest of various combinations of a random 32-bit salt and
  the password.
- Digest, the MD5 hash of the string `user:realm:password` as a 32-character
  string of hexadecimal digits. `realm` is the Authorization Realm argument to
  the AuthName directive in `httpd.conf`.

In order to specify the format of the Apache’s password, use the `setFormat()`
method. An example with all the formats usage is demostrated below:

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

$apache = new Apache([
    'format' => 'md5'
]);
```

Other possible parameters to pass in the constructor are `username` and `authname`,
for the digest format.
