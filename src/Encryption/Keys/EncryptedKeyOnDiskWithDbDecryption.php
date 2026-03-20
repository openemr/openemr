<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OpenEMR\Common\Crypto\KeyVersion;

readonly class EncryptedKeyOnDiskWithDbDecryption implements KeyManagerInterface
{
    public function __construct(
        private string $storageDir,
        private KeyManagerInterface $dbKeyManager,
    ) {
    }

    public function getKey(string $identifier): KeyMaterial
    {
        // Read out the encrypted key from disk
        $path = sprintf('%s/%s', $this->storageDir, $identifier);
        $encrypted = file_get_contents($path);
        if ($encrypted === false) {
            throw new \Exception('Key not found');
        }

        // Parse version prefix - tells us which db keys were used to encrypt
        $version = KeyVersion::fromPrefix($encrypted);

        // Nit: I don't love this extra decode pass - it's redundant w/ CG
        // itself. The message parsing should be centralized and reusable.
        $payload = base64_decode(substr($encrypted, KeyVersion::PREFIX_LENGTH), true);
        if ($payload === false) {
            throw new \RuntimeException("Invalid base64 in encrypted key file: $identifier");
        }

        // Decrypt using strategy + db key manager directly
        $strategy = $version->getDecryptionStrategy();
        $decrypted = $strategy->decrypt(
            ciphertext: $payload,
            keyId: $version->toString(),
            manager: $this->dbKeyManager,
        );

        return new KeyMaterial($decrypted);
    }
}
