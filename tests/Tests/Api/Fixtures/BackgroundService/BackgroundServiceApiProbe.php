<?php

/**
 * Probe function loaded by BackgroundServiceApiTest.
 *
 * Kept in a namespace that PHP never autoloads (functions are not
 * autoload-able), and outside any `composer.json` `files` list, so this
 * file is only evaluated when the runner honors the probe service's
 * `require_once`. That makes it impossible for the probe to leak into a
 * production run even if the test-only service row were somehow left in
 * the database.
 *
 * Mirrors the pattern used by
 * tests/Tests/Services/Background/Probe/CliIntegrationProbe.php — the
 * API-tier counterpart to the CLI-tier integration test.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api\Fixtures\BackgroundService\Probe;

/**
 * Sentinel file used to witness execution of the API probe service. The
 * test process and the apache worker that handles the REST request both
 * compute the same path from `sys_get_temp_dir()`, which is stable within
 * the openemr container where the api test suite runs.
 */
function apiProbeSentinelPath(): string
{
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . '_api_background_service_probe_sentinel';
}

/**
 * Append a timestamp line to the sentinel file. Each successful execution
 * grows the file, letting the test distinguish "ran once" from "ran twice"
 * without depending on clock-granular equality.
 *
 * @codeCoverageIgnore This function runs inside the apache worker that
 *   handles the REST request, not inside the test process. PHPUnit's
 *   coverage driver runs in the test process only, so executions here
 *   are never recorded even though the function definitely runs
 *   (witnessed by the sentinel file the test asserts on). Without this
 *   annotation the lines appear uncovered and drag patch coverage for
 *   no real reason.
 */
function markApiProbeSentinel(): void
{
    file_put_contents(
        apiProbeSentinelPath(),
        microtime(true) . "\n",
        FILE_APPEND | LOCK_EX,
    );
}
