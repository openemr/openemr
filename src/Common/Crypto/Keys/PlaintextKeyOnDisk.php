<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto\Keys;

/**
 * @deprecated For backwards compatibility only.
 */
readonly class PlaintextKeyOnDisk
{
    public function __construct(private string $siteDir)
    {
    }

    public function getKey(string $identifier): KeyMaterial
    {
        $path = sprintf(
            '%s/documents/logs_and_misc/methods/%s',
            $this->siteDir,
            $identifier,
        );
        $encoded = file_get_contents($path);
        if ($encoded === false) {
            throw new \Exception('Could not read key');
        }
        $decoded = base64_decode($encoded, strict: true);
        if ($decoded === false) {
            throw new \Exception('Could not decode key');
        }
        return new KeyMaterial(key: $decoded);
    }
}
