<?php

/**
 * Indirection for DNS A/AAAA lookups in the OIDC SSRF-defense path.
 *
 * Sole purpose: give {@see OidcUrlValidator} a stub-able seam for unit
 * tests of the DNS-results-cap branch (Aisle round-4 #5 / CWE-400).
 * Production code wires {@see SystemOidcDnsResolver} which calls PHP's
 * `dns_get_record(DNS_A + DNS_AAAA)` directly.
 *
 * Returning the raw `dns_get_record` shape (rather than a normalized
 * IP list) keeps the validator's per-record validation in one place
 * and avoids divergence between the production resolver and stubs.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Discovery;

interface OidcDnsResolverInterface
{
    /**
     * Resolve the host's A and AAAA records.
     *
     * Returns the same shape as PHP's native `dns_get_record`: a list
     * of associative arrays with at least a `type` key and either an
     * `ip` (A) or `ipv6` (AAAA) key. Return `false` on resolution
     * failure (host unknown, transient DNS error) and `[]` for
     * "host has no A/AAAA records of this type".
     *
     * @return list<array<string, mixed>>|false
     */
    public function getRecords(string $host): array|false;
}
