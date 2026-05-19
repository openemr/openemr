<?php

/**
 * Single-method abstraction over "resolve a hostname to its IPs and check
 * each IP against a privacy policy". Lets {@see SsrfSafeHttpClient} depend
 * on a mockable contract instead of the concrete `final readonly`
 * {@see OidcUrlValidator}.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Discovery;

interface OidcHostResolverInterface
{
    /**
     * Resolve the hostname and return every validated IP. The same set
     * must be used to pin the subsequent network connection so DNS
     * resolution and connect-time use the same answer (no rebinding
     * window).
     *
     * @return non-empty-list<string>
     * @throws OidcUrlValidationException When the host can't be resolved
     *   or any resolved IP fails the privacy policy.
     */
    public function resolveAndAssert(string $host): array;
}
