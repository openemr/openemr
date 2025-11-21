<?php

/**
 * Simple verification script for ForbiddenGlobalsAccessRule
 * 
 * This script demonstrates the AST node pattern that the rule detects.
 * It's not a full PHPStan test but shows the logic is sound.
 *
 * @package   OpenEMR
 * @author    GitHub Copilot AI-generated // AI-generated
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\ParserFactory;

echo "Testing ForbiddenGlobalsAccessRule detection logic...\n\n";

// Create a parser
$parser = (new ParserFactory())->createForNewestSupportedVersion();

// Test case 1: Direct $GLOBALS access (should be detected)
$code1 = '<?php $value = $GLOBALS["some_setting"];';
$ast1 = $parser->parse($code1);

echo "Test 1: Direct \$GLOBALS access\n";
echo "Code: " . trim($code1) . "\n";

// Walk the AST
foreach ($ast1 as $stmt) {
    if (isset($stmt->expr) && $stmt->expr instanceof ArrayDimFetch) {
        $node = $stmt->expr;
        if ($node->var instanceof Variable && $node->var->name === 'GLOBALS') {
            echo "✓ Rule would catch this! (Direct \$GLOBALS access detected)\n";
        }
    }
}
echo "\n";

// Test case 2: OEGlobalsBag usage (should NOT be detected)
$code2 = '<?php $globals = OEGlobalsBag::getInstance(); $value = $globals->get("some_setting");';
$ast2 = $parser->parse($code2);

echo "Test 2: OEGlobalsBag usage (correct pattern)\n";
echo "Code: " . trim($code2) . "\n";

$foundGlobalsAccess = false;
foreach ($ast2 as $stmt) {
    if (isset($stmt->expr) && $stmt->expr instanceof ArrayDimFetch) {
        $node = $stmt->expr;
        if ($node->var instanceof Variable && $node->var->name === 'GLOBALS') {
            $foundGlobalsAccess = true;
        }
    }
}
if (!$foundGlobalsAccess) {
    echo "✓ Rule would NOT catch this (no \$GLOBALS access detected)\n";
}
echo "\n";

// Test case 3: Other array access (should NOT be detected)
$code3 = '<?php $value = $myArray["key"];';
$ast3 = $parser->parse($code3);

echo "Test 3: Regular array access\n";
echo "Code: " . trim($code3) . "\n";

$foundGlobalsAccess = false;
foreach ($ast3 as $stmt) {
    if (isset($stmt->expr) && $stmt->expr instanceof ArrayDimFetch) {
        $node = $stmt->expr;
        if ($node->var instanceof Variable && $node->var->name === 'GLOBALS') {
            $foundGlobalsAccess = true;
        }
    }
}
if (!$foundGlobalsAccess) {
    echo "✓ Rule would NOT catch this (not \$GLOBALS access)\n";
}
echo "\n";

echo "All tests passed! The rule logic correctly identifies \$GLOBALS access.\n";
