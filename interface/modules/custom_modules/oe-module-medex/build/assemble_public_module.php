<?php

declare(strict_types=1);

$rootDir = realpath(__DIR__ . '/..');
if ($rootDir === false) {
    fwrite(STDERR, "Unable to resolve module root\n");
    exit(1);
}

$manifestPath = __DIR__ . '/public_module_manifest.php';
$manifest = require $manifestPath;
if (!is_array($manifest)) {
    fwrite(STDERR, "Invalid public module manifest\n");
    exit(1);
}

$opts = getopt('', ['services::', 'output:']);
$outputDir = isset($opts['output']) ? (string)$opts['output'] : '';
if ($outputDir === '') {
    fwrite(STDERR, "--output is required\n");
    exit(1);
}

$rawServices = isset($opts['services']) ? (string)$opts['services'] : '';
$services = array_values(array_filter(array_map(static function (string $service): string {
    return trim($service);
}, explode(',', $rawServices)), static function (string $service): bool {
    return $service !== '';
}));

$componentMap = is_array($manifest['service_components'] ?? null) ? $manifest['service_components'] : [];
$selectedComponents = [];
foreach ($services as $serviceKey) {
    foreach ((array)($componentMap[$serviceKey] ?? []) as $componentName) {
        $name = trim((string)$componentName);
        if ($name !== '') {
            $selectedComponents[$name] = true;
        }
    }
}

rrmdir($outputDir);
mkdir($outputDir, 0775, true);

foreach ((array)($manifest['base_files'] ?? []) as $relativePath) {
    copyRelativePath($rootDir, $outputDir, (string)$relativePath);
}

foreach (array_keys($selectedComponents) as $componentName) {
    $componentRoot = __DIR__ . '/components/' . $componentName . '/stage/oe-module-medex';
    if (!is_dir($componentRoot)) {
        fwrite(STDERR, "Missing component payload: {$componentName}\n");
        exit(1);
    }
    copyTree($componentRoot, $outputDir);
}

fwrite(STDOUT, json_encode([
    'output' => $outputDir,
    'services' => $services,
    'components' => array_keys($selectedComponents),
], JSON_UNESCAPED_SLASHES) . PHP_EOL);

function copyRelativePath(string $sourceRoot, string $outputRoot, string $relativePath): void
{
    $normalized = ltrim(str_replace('\\', '/', $relativePath), '/');
    if ($normalized === '' || str_contains($normalized, '..')) {
        return;
    }

    $sourcePath = $sourceRoot . '/' . $normalized;
    if (!file_exists($sourcePath)) {
        fwrite(STDERR, "Missing base file: {$normalized}\n");
        exit(1);
    }

    $targetPath = $outputRoot . '/' . $normalized;
    $targetDir = dirname($targetPath);
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    if (is_dir($sourcePath)) {
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0775, true);
        }
        return;
    }

    copy($sourcePath, $targetPath);
}

function copyTree(string $sourceRoot, string $outputRoot): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $pathname = (string)$item->getPathname();
        if (str_ends_with($pathname, '.DS_Store')) {
            continue;
        }
        $relative = ltrim(substr($pathname, strlen($sourceRoot)), DIRECTORY_SEPARATOR);
        if ($relative === '') {
            continue;
        }
        $target = $outputRoot . '/' . str_replace('\\', '/', $relative);
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0775, true);
            }
            continue;
        }
        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        copy($pathname, $target);
    }
}

function rrmdir(string $dir): void
{
    if ($dir === '' || !file_exists($dir)) {
        return;
    }
    if (!is_dir($dir)) {
        unlink($dir);
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            rmdir((string)$item->getPathname());
        } else {
            unlink((string)$item->getPathname());
        }
    }
    rmdir($dir);
}
