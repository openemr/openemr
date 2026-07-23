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
        $client = HttpClient::create([
            // Accept the self-signed cert that the openemr image serves
            // when we hit https. Redirect-following is toggled at the
            // BrowserKit layer (below), not the HTTP-client layer.
            'verify_peer' => false,
            'verify_host' => false,
        ]);
        $browser = new HttpBrowser($client);
        // Don't auto-follow redirects: the login flow's 302 IS the
        // success signal we want to assert on, and GET / is a 302 to
        // the login page which we also want to inspect directly rather
        // than follow through.
        $browser->followRedirects(false);
        return $browser;
    }

    public static function baseUrl(): string
    {
        $url = getenv('ACCEPTANCE_ARTIFACT_URL');
        return $url !== false && $url !== '' ? rtrim($url, '/') : 'http://localhost:8580';
    }
}
