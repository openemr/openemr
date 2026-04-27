<?php

/**
 * Entry caps for baseline files whose error identifiers represent code that
 * cannot run. Two modes:
 *
 *   - `all` — every `$ignoreErrors[] = [` entry in the file counts against
 *     the cap. Used for identifiers like `class.notFound` where every entry
 *     is a symbol that simply doesn't exist.
 *
 *   - `confidentNonObject` — only entries whose reported type narrows to a
 *     non-object (null, false, true, int, string, bool, float, array, ...)
 *     count against the cap. PHPStan emits `*.nonObject` identifiers for
 *     both "definitely a crash" types (e.g. `on bool`) and "I can't prove
 *     it's an object" types (e.g. `on mixed`, `on SomeClass|null`). Only
 *     the former are caught here.
 *
 * Caps only go down. When `composer phpstan-baseline` reduces a count,
 * lower the cap in the same commit. When a count would go up, fix the
 * underlying code instead of raising the cap.
 *
 * Preemptive zero caps (`require.fileNotFound`, `trait.notFound`,
 * `interface.notFound`) block identifiers that aren't currently in the
 * baseline — if one ever appears, the test fails instead of quietly
 * baselining it.
 *
 * See openemr/openemr#11792 for the plan to drive every cap to zero.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

return [
    'all' => [
        'class.notFound.php' => 237,
        'classConstant.notFound.php' => 0,
        'constant.notFound.php' => 0,
        'function.notFound.php' => 0,
        'include.fileNotFound.php' => 0,
        'includeOnce.fileNotFound.php' => 0,
        'interface.notFound.php' => 0,
        'method.notFound.php' => 184,
        'require.fileNotFound.php' => 0,
        'requireOnce.fileNotFound.php' => 0,
        'return.missing.php' => 0,
        'staticMethod.notFound.php' => 0,
        'trait.notFound.php' => 0,
        'variable.undefined.php' => 3075,
    ],
    'confidentNonObject' => [
        'classConstant.nonObject.php' => 0,
        'clone.nonObject.php' => 0,
        'method.nonObject.php' => 0,
        'property.nonObject.php' => 0,
        'staticMethod.nonObject.php' => 0,
    ],
];
