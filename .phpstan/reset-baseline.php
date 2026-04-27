<?php

/**
 * Wipe the PHPStan baseline directory so `composer phpstan-baseline` can
 * regenerate from a clean slate.
 *
 * Deletes every file under `.phpstan/baseline/` and writes a minimal
 * `loader.php` with an empty includes list. PHPStan then starts with no
 * pre-existing baseline and `composer phpstan-baseline` records the
 * current set of errors from scratch. Useful when the baseline has
 * drifted, when resolving the per-identifier split files by hand is not
 * worth the effort, or when the baseline files are in a state PHPStan
 * can't even load (e.g. leftover merge conflict markers).
 *
 * Run via `composer phpstan-baseline-reset`.
 */

declare(strict_types=1);

$baselineDir = __DIR__ . '/baseline';

if (!is_dir($baselineDir)) {
    fwrite(STDERR, "Baseline directory not found: {$baselineDir}\n");
    exit(1);
}

$deleted = 0;
foreach (glob($baselineDir . '/*.php') ?: [] as $path) {
    if (!unlink($path)) {
        fwrite(STDERR, "Failed to delete: {$path}\n");
        exit(1);
    }
    $deleted++;
}

$loader = <<<'PHP'
<?php declare(strict_types = 1);

return ['includes' => []];

PHP;

if (file_put_contents($baselineDir . '/loader.php', $loader) === false) {
    fwrite(STDERR, "Failed to write loader.php\n");
    exit(1);
}

echo "Wiped {$deleted} baseline file(s); wrote empty loader.php.\n";
