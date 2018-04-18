# Encrypt and decrypt files

`Zend\Crypt\FileCipher` implements file encryption and decryption using a
symmetric cipher in
[CBC](http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher-block_chaining_.28CBC.29)
mode with the encrypt-then-authenticate approach, using
[HMAC](http://en.wikipedia.org/wiki/HMAC) to provide authentication (the same
solution used by `Zend\Crypt\BlockCipher` component).

Encrypting and decrypting a file is not an easy task, especially with large
files. For instance, in CBC mode you must be sure to handle the
[IV](http://en.wikipedia.org/wiki/Initialization_vector) correctly for each
block. For large files, that means that you need to use a buffer and use the
last block of the buffer as the new IV for the next encryption step.

The `FileCipher` uses a symmetric cipher, with the `Zend\Crypt\Symmetric\Mcrypt` component.

The usage of this component is very simple; create an instance of `FileCipher`,
specify the key, and you are ready to encrypt/decrypt any file:

```php
use Zend\Crypt\FileCipher;

$fileCipher = new FileCipher;
$fileCipher->setKey('encryption key');

// encryption
if ($fileCipher->encrypt('path/to/file_to_encrypt', 'path/to/output')) {
    echo "The file has been encrypted successfully\n";
}

// decryption
if ($fileCipher->decrypt('path/to/file_to_decrypt', 'path/to/output')) {
    echo "The file has been decrypted successfully\n";
}
```

By default, `FileCipher` uses the [AES](http://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
encryption algorithm (with a 256-bit key) and the [SHA-256](http://en.wikipedia.org/wiki/SHA-2)
hash algorithm to authenticate the data using the HMAC function. This component uses the
[PBKDF2](http://en.wikipedia.org/wiki/PBKDF2) key derivation algorithm to generate the encryption
key and the authentication key, for the HMAC, based on the key specified using the method
`setKey()`.

If you want to change the encryption algorithm, you can use the `setCipherAlgorithm()` function. For
instance, you could specify the [Blowfish](http://en.wikipedia.org/wiki/Blowfish_%28cipher%29)
encryption algorithm using `setCipherAlgorithm('blowfish')`. You can retrieve the list of all
supported encryption algorithms in your environment using the function
`getCipherSupportedAlgorithms()`.

If you need to customize the cipher algorithm &mdash; for instance, to change
the Padding mode &mdash; you can inject your `Mcrypt` object in the `FileCipher`
using the `setCipher()` method. The only parameter of the cipher that you cannot
change is the cipher mode, which is hard-coded to CBC.

> ## Output format
>
> The output of the encryption file is in binary format. We used this format to
> reduce impact on output size. If you encrypt a file using the `FileCipher`
> component, you will notice that the output file size is almost the same as the
> input size, with a few additional bytes to store the HMAC and the IV vector.
> The format of the output is the concatenation of the HMAC, the IV, and the
> encrypted file contents.
