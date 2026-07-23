<?php

namespace OpenEMR\RestControllers\Authorization;

interface ExternalBearerTokenValidatorInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function validateBearerToken(string $rawToken, string $siteId): ?array;
}
