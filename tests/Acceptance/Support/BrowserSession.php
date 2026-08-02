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

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Symfony\Component\Panther\Client;

/**
 * Panther/WebDriver client factory for acceptance tests that need a
 * real headless browser (JS + Knockout + full asset load).
 *
 * Two backends, selected via env vars — same pattern as the
 * source-side tests/Tests/E2e BaseTrait so acceptance follows the
 * established convention:
 *
 *   - **local ChromeDriver** (default) — Panther launches Chrome as a
 *     subprocess of the PHPUnit process. Requires `google-chrome` (or
 *     `chromium`) on PATH and the matching ChromeDriver binary
 *     (bundled with symfony/panther for common Chrome versions).
 *     Path used in CI: the acceptance workflows install Chrome via
 *     `browser-actions/setup-chrome` before running the suite.
 *
 *   - **Selenium grid** (`SELENIUM_USE_GRID=true`) — Panther drives a
 *     remote Chrome over the WebDriver protocol. Set `SELENIUM_HOST`
 *     to the grid hostname and `SELENIUM_BASE_URL` to the URL the
 *     grid's browser should use for the artifact (usually a
 *     docker-network alias, not the host-visible port). Path used
 *     for local development: the dev-easy stack ships a selenium
 *     container, so running acceptance from within the dev container
 *     with SELENIUM_USE_GRID=true works out of the box.
 *
 * Callers should invoke `create()` in setUp() and `->quit()` in
 * tearDown() to release the Chrome process/session.
 */
final class BrowserSession
{
    /**
     * Sensible defaults for headless acceptance runs. Every entry
     * here is either "required for CI runners" (no-sandbox,
     * disable-gpu, disable-dev-shm-usage) or "avoids known flakes"
     * (ignore-certificate-errors for the self-signed cert on
     * artifact HTTPS ports, headless because there's no display in
     * CI, fixed window size so viewport-dependent renders are
     * deterministic).
     */
    private const CHROME_ARGS = [
        '--headless=new',
        '--no-sandbox',
        '--disable-gpu',
        '--disable-dev-shm-usage',
        '--ignore-certificate-errors',
        '--window-size=1920,1080',
    ];

    public static function create(): Client
    {
        if (self::useGrid()) {
            return self::createGridClient();
        }
        return self::createLocalClient();
    }

    private static function useGrid(): bool
    {
        $val = getenv('SELENIUM_USE_GRID');
        return $val === 'true' || $val === '1';
    }

    /**
     * Local ChromeDriver path — subprocess launched by Panther.
     */
    private static function createLocalClient(): Client
    {
        // Signature: createChromeClient($chromeDriverBinary, $arguments,
        // $options, $baseUri). baseUri MUST be the 4th positional arg —
        // there's no `external_base_uri` key on $options for this
        // factory (that key is a Panther *internal* extra_capability,
        // and passing it via $options is silently ignored, which
        // reports as "invalid argument" from ChromeDriver on the first
        // relative-URL request because get() tries to load "/login...").
        return Client::createChromeClient(
            null,
            self::CHROME_ARGS,
            [
                // Auto-accept unexpected JS alerts — matches the grid
                // path below (and the source-side E2e BaseTrait).
                // Acceptance tests shouldn't be blocked by out-of-box
                // popups they didn't ask for: OpenEMR's Medical Record
                // Dashboard fires a "New Due Clinical Reminders"
                // alert(...) via <img onload> as soon as the widget
                // computes, which raced against a 60s explicit alert
                // wait on slow ARM CI runners (see #13348 flake
                // investigation). Driver-level accept means the alert
                // fires + closes transparently; tests just wait for
                // the real post-condition (dashboard header rendered).
                'capabilities' => [
                    'unhandledPromptBehavior' => 'accept',
                ],
            ],
            ArtifactBrowser::baseUrl(),
        );
    }

    /**
     * Selenium grid path — WebDriver session on a remote grid. Env
     * inputs mirror what tests/Tests/E2e BaseTrait already accepts,
     * so a dev running acceptance from inside the dev-easy openemr
     * container just needs SELENIUM_USE_GRID=true and things work.
     */
    private static function createGridClient(): Client
    {
        $host = getenv('SELENIUM_HOST') ?: 'selenium';
        $externalBaseUri = getenv('SELENIUM_BASE_URL') ?: 'http://openemr';

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability('goog:chromeOptions', ['args' => self::CHROME_ARGS]);
        // Auto-accept unexpected JS alerts — matches E2e BaseTrait's
        // choice; acceptance tests shouldn't be blocked by a stray
        // prompt they didn't ask for.
        $capabilities->setCapability('unhandledPromptBehavior', 'accept');

        return Client::createSeleniumClient(
            "http://{$host}:4444/wd/hub",
            $capabilities,
            $externalBaseUri,
        );
    }
}
