<?php

/**
 * Probe function loaded by the background:services CLI integration test.
 *
 * Kept in a namespace that PHP never autoloads (functions are not
 * autoload-able), and outside any `composer.json` `files` list, so this
 * file is only evaluated when the runner honors the probe service's
 * `require_once`. That makes it impossible for the probe to leak into a
 * production run even if the test-only service row were somehow left in
 * the database.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Background\Probe;

/**
 * Sentinel file used to witness execution of the CLI probe service. The
 * test process and the shelled-out `bin/console` process both compute the
 * same path from `sys_get_temp_dir()`, which is stable within the
 * container where the `services` test suite runs.
 */
function cliProbeSentinelPath(): string
{
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . '_e2e_cli_probe_sentinel';
}

/**
 * Append a timestamp line to the sentinel file. Each successful execution
 * grows the file, letting the test distinguish "ran once" from "ran twice"
 * without depending on clock-granular equality.
 *
 * @codeCoverageIgnore This function only runs inside the `bin/console`
 *   subprocess spawned by BackgroundServicesCliIntegrationTest. PHPUnit's
 *   coverage driver runs in the parent process only, so executions here are
 *   never recorded even though the function definitely runs (witnessed by
 *   the sentinel file the test asserts on). Without this annotation the
 *   lines appear uncovered and drag patch coverage for no real reason.
 */
function markCliProbeSentinel(): void
{
    file_put_contents(
        cliProbeSentinelPath(),
        microtime(true) . "\n",
        FILE_APPEND | LOCK_EX,
    );
}
