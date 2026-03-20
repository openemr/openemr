<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use OpenEMR\Encryption\Keys\KeyResolverInterface;
use SensitiveParameter;

readonly class CipherSuite
{
    public function __construct(
        private KeyResolverInterface $keyResolver,
    ) {
    }

    public function encrypt(#[SensitiveParameter] Plaintext $plaintext): Message
    {
        // mostly inverse of decrypt but this isn't supported yet
        throw new \BadMethodCallException('Encryption not yet supported');
    }

    public function decrypt(Message $message): Plaintext
    {
        $cipher = $this->keyResolver->resolve($message->keyId);
        return $cipher->decrypt($message->ciphertext);
    }
}
