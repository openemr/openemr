<?php

/**
 * Read $v_major/$v_minor/$v_patch from a version.php file and emit
 * "X.Y.Z" to stdout. Validates each variable is either a native int
 * or a digit-only string (version.php in the repo uses string
 * literals like $v_major = '8'), rejects other types loudly.
 *
 * Extracted from acceptance-docker.yml's inline `php -r` block so
 * PHPUnit-isolated tests can pin the parse/validate behavior against
 * fixture version.php files without needing a live docker container.
 *
 * Usage:
 *   php .github/scripts/read-version-php.php [path]
 *
 * Args:
 *   path (optional) Path to version.php. Defaults to
 *                   /var/www/localhost/htdocs/openemr/version.php
 *                   (the acceptance-docker container's canonical
 *                   location).
 *
 * Exit codes:
 *   0  Emitted "X.Y.Z\n" to stdout.
 *   1  version.php not found, or one of v_major/v_minor/v_patch
 *      failed the int-or-digit-string validation.
 *
 * Unit-tested by
 * tests/Tests/Isolated/Common/Command/Ci/ReadVersionPhpTest.php.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

$path = $argv[1] ?? '/var/www/localhost/htdocs/openemr/version.php';

if (!is_readable($path)) {
    fwrite(STDERR, "read-version-php.php: cannot read '{$path}'\n");
    exit(1);
}

// Isolate variable scope so the require can't clobber $path or $argv.
$reader = static function (string $file): array {
    require $file;
    return [
        'v_major' => $v_major ?? null,
        'v_minor' => $v_minor ?? null,
        'v_patch' => $v_patch ?? null,
    ];
};

$vars = $reader($path);
foreach ($vars as $name => $value) {
    // is_int() || (is_string() && ctype_digit()) — version.php in
    // the repo assigns as string literals ('8', '5', '0') which
    // is_int() would reject. Accept native int or digit-only string;
    // reject any other type or non-digit content.
    if (!(is_int($value) || (is_string($value) && ctype_digit($value)))) {
        fwrite(
            STDERR,
            sprintf(
                "read-version-php.php: %s must be int or digit-only string, got: %s\n",
                $name,
                var_export($value, true),
            ),
        );
        exit(1);
    }
}

echo "{$vars['v_major']}.{$vars['v_minor']}.{$vars['v_patch']}\n";
