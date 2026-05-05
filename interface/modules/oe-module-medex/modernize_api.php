<?php
/**
 * Batch modernization script for API.php
 * Replaces legacy SQL patterns with QueryUtils
 */

$file = __DIR__ . '/src/API/API.php';
$content = file_get_contents($file);

// Add imports at the top (after namespace)
if (!str_contains($content, 'use OpenEMR\Common\Database\QueryUtils;')) {
    $content = str_replace(
        "use OpenEMR\Common\Http\oeHttp;\nuse OpenEMR\Core\OEGlobalsBag;",
        "use OpenEMR\Common\Database\QueryUtils;\nuse OpenEMR\Common\Http\oeHttp;\nuse OpenEMR\Core\OEGlobalsBag;",
        $content
    );
}

// Replace sqlStatement + sqlFetchArray loops
// Pattern: $result = sqlStatement($sql, $params); while ($row = sqlFetchArray($result))
$content = preg_replace(
    '/\$(\w+)\s*=\s*sqlStatement\((.*?)\);\s*while\s*\(\$(\w+)\s*=\s*sqlFetchArray\(\$\1\)\)\s*\{/',
    '$rows = QueryUtils::fetchRecords($2);' . "\n        foreach (\$rows as \$$3) {",
    $content
);

// Replace simple sqlQuery calls
// $result = sqlQuery($sql, $params);
$content = preg_replace(
    '/\$(\w+)\s*=\s*sqlQuery\((.*?)\);/',
    '$records = QueryUtils::fetchRecords($2);' . "\n        \$$1 = \$records[0] ?? null;",
    $content
);

// Replace sqlStatement calls that don't need results
$content = preg_replace(
    '/sqlStatement\(/','QueryUtils::sqlStatementThrowException(',
    $content
);

// Replace $GLOBALS with OEGlobalsBag (simple cases)
$content = preg_replace(
    '/\$GLOBALS\[([\'"])(\w+)\1\]/',
    'OEGlobalsBag::getInstance()->get(\'$2\')',
    $content
);

// Replace global $GLOBALS declarations
$content = preg_replace(
    '/global\s+\$GLOBALS;/',
    '$globalsBag = OEGlobalsBag::getInstance();',
    $content
);

echo "Replacements made:\n";
echo "- Added QueryUtils import\n";
echo "- Replaced sqlStatement + sqlFetchArray loops\n";
echo "- Replaced sqlQuery calls\n";
echo "- Replaced $GLOBALS access\n";
echo "\nWriting to file...\n";

file_put_contents($file, $content);

echo "Done! Please review the changes and run PHPStan.\n";
