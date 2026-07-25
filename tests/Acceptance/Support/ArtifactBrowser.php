<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance\Support;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Thin factory for an HTTP client pointed at the artifact under test.
 *
 * Uses Symfony BrowserKit's HttpBrowser — real HTTP requests via
 * symfony/http-client, cookie jar + form-submit convenience built in.
 * No headless browser required, so acceptance runs on any Linux runner
 * without Chrome/Selenium (Phase 4's E2eCriticalPathTest is when we'll
 * need Panther-with-Selenium for JS-heavy flows).
 *
 * The artifact endpoint is resolved from the ACCEPTANCE_ARTIFACT_URL
 * environment variable, defaulting to http://localhost:8580 (the port
 * that tests/Acceptance/bin/boot-docker.sh binds by default).
 */
final class ArtifactBrowser
{
    public static function create(): HttpBrowser
    {
        // TLS verification stays ENABLED by default — acceptance tests
        // submit admin credentials, and disabling verification against
        // an arbitrary ACCEPTANCE_ARTIFACT_URL would be MITM-vulnerable
        // over any non-loopback network. Only disable verification when
        // the endpoint is provably local (loopback host, host-gateway
        // container-runtime alias, or explicit ACCEPTANCE_TRUST_SELF_SIGNED=1
        // opt-in). The Docker artifact under test serves a self-signed
        // cert on https, so a local acceptance run needs the trust
        // scoping to be automatic when hitting localhost.
        $options = ['verify_peer' => true, 'verify_host' => true];
        if (self::isLocalArtifact()) {
            $options['verify_peer'] = false;
            $options['verify_host'] = false;
        }
        $client = HttpClient::create($options);

        $browser = new HttpBrowser($client);
        // Don't auto-follow redirects: the login flow's 302 IS the
        // success signal we want to assert on, and GET / is a 302 to
        // the login page which we also want to inspect directly rather
        // than follow through.
        $browser->followRedirects(false);
        return $browser;
    }

    private static function isLocalArtifact(): bool
    {
        $optIn = getenv('ACCEPTANCE_TRUST_SELF_SIGNED');
        if ($optIn === '1' || $optIn === 'true') {
            return true;
        }
        $host = parse_url(self::baseUrl(), PHP_URL_HOST);
        return in_array($host, ['localhost', '127.0.0.1', '::1', 'host.docker.internal'], true);
    }

    public static function baseUrl(): string
    {
        $url = getenv('ACCEPTANCE_ARTIFACT_URL');
        return $url !== false && $url !== '' ? rtrim($url, '/') : 'http://localhost:8580';
    }
}
