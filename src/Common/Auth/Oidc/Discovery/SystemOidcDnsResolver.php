<?php

/**
 * Default DNS resolver used by {@see OidcUrlValidator} in production.
 *
 * Thin wrapper around `dns_get_record(DNS_A + DNS_AAAA)`. Exists so the
 * validator can stay testable through {@see OidcDnsResolverInterface}
 * (Aisle round-4 #5).
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Discovery;

final readonly class SystemOidcDnsResolver implements OidcDnsResolverInterface
{
    public function getRecords(string $host): array|false
    {
        // The `@` suppresses the warning emitted on transient DNS
        // failures (e.g. SERVFAIL); the validator translates `false`
        // into a structured OidcUrlValidationException for the caller.
        /** @var list<array<string, mixed>>|false $records */
        $records = @dns_get_record($host, DNS_A + DNS_AAAA);
        return $records;
    }
}
