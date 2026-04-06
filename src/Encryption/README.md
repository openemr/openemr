# Encryption in OpenEMR

Intended usage:
in services that need encryption, use dependency injection to provide a `CipherSuiteInterface` into the constructor.
Use its `encrypt()` and `decrypt()` methods to handle cryptographic operations.
It will handle key management, format shifting, etc.

In the database (plan/future scope):
once doctrine/orm is fully set up, it will most likely let you define a column as `Plaintext`, and it will automatically encrypt that field.
Models won't need to do anyhting special beyond using the type/marker.

## Structure

The classes and structures in the top level of the `Encryption` namespace represent the core touch points to encryption tooling.

`CipherSuiteInterface` is what you'll use 99% of the time when encrypting and decrypting data.
Get it from the DI container.

`Ciphertext`, `KeyId`, `Message`, and `Plaintext` are domain-specific wrapers to enforce type safety.

### `Cipher/`

A `Cipher` is a pairing of key material and a cryptographic algorithm.
It performs the low-level cryptographic operations.

### `Keys/`

A `Keychain` holds (or can construct) ciphers.


### `Storage/`

These classes handle accessing raw key material.
