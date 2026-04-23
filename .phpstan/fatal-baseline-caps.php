<?php

/**
 * Entry caps for baseline files whose error identifiers represent code that
 * cannot run: missing classes/methods/functions/constants, missing includes,
 * missing return values, and undefined variables. These categories crash at
 * load or call time — a new baseline entry is never the right answer, so the
 * caps below only ever go down.
 *
 * Filename → maximum allowed count of `$ignoreErrors[] = [` entries. Tracked
 * by tests/Tests/Isolated/PHPStan/FatalBaselineCapsIsolatedTest.php.
 *
 * When `composer phpstan-baseline` reduces a count, lower the cap in the same
 * commit. When a count would go up, fix the underlying code instead of
 * raising the cap.
 *
 * See openemr/openemr#11792 for the plan to drive every cap to zero.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

return [
    'class.notFound.php' => 255,
    'classConstant.notFound.php' => 0,
    'constant.notFound.php' => 125,
    'function.notFound.php' => 13,
    'include.fileNotFound.php' => 4,
    'includeOnce.fileNotFound.php' => 1,
    'method.notFound.php' => 253,
    'requireOnce.fileNotFound.php' => 4,
    'return.missing.php' => 29,
    'staticMethod.notFound.php' => 0,
    'variable.undefined.php' => 3457,
];
