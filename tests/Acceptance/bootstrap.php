<?php

/**
 * Acceptance-suite PHPUnit bootstrap.
 *
 * Minimal: loads composer autoload. Acceptance tests are black-box —
 * they don't touch OpenEMR's global-state bootstrap (Kernel, session,
 * database connection, etc.) because the artifact under test has its
 * own bootstrap running inside its own container/stack.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';
