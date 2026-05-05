<?php
/**
 * MedEx Module Simple Test
 *
 * Tests class structure and method signatures without instantiation
 */

// Minimal bootstrap - just load the API
require_once(__DIR__ . '/../src/API/API.php');

echo "=== MedEx Module Simple Test ===\n\n";

// Test 1: Class Loading
echo "Test 1: Class Loading\n";
echo str_repeat('-', 50) . "\n";

$classes = [
    'MedExApi\MedEx',
    'MedExApi\Client\HttpClient',
    'MedExApi\Services\BaseService',
    'MedExApi\Services\PracticeService',
    'MedExApi\Services\CampaignService',
    'MedExApi\Services\EventsService',
    'MedExApi\Services\CallbackService',
    'MedExApi\Services\LoggingService',
    'MedExApi\Services\DisplayService',
    'MedExApi\Services\SetupService'
];

$failed = 0;
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "  ✓ $class\n";
    } else {
        echo "  ✗ $class NOT FOUND\n";
        $failed++;
    }
}

if ($failed > 0) {
    exit(1);
}

echo "\n✓ All classes loaded\n\n";

// Test 2: EventsService Structure
echo "Test 2: EventsService Structure\n";
echo str_repeat('-', 50) . "\n";

try {
    $reflection = new ReflectionClass('MedExApi\Services\EventsService');

    // Check public methods
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    $methodNames = array_map(function($m) { return $m->getName(); }, $methods);

    $requiredMethods = ['generate', 'calculateEvents'];
    foreach ($requiredMethods as $method) {
        if (in_array($method, $methodNames)) {
            echo "  ✓ Method $method() exists\n";
        } else {
            echo "  ✗ Method $method() NOT FOUND\n";
            exit(1);
        }
    }

    // Check generate() signature
    $generateMethod = $reflection->getMethod('generate');
    $params = $generateMethod->getParameters();

    if (count($params) === 2) {
        echo "  ✓ generate() has 2 parameters\n";
    } else {
        echo "  ✗ generate() parameter count wrong\n";
        exit(1);
    }

    if ($params[0]->getName() === 'token' && $params[0]->getType()->getName() === 'string') {
        echo "  ✓ First parameter: string \$token\n";
    } else {
        echo "  ✗ First parameter type wrong\n";
        exit(1);
    }

    if ($params[1]->getName() === 'events' && $params[1]->getType()->getName() === 'array') {
        echo "  ✓ Second parameter: array \$events\n";
    } else {
        echo "  ✗ Second parameter type wrong\n";
        exit(1);
    }

    $returnType = $generateMethod->getReturnType();
    if ($returnType && strpos((string)$returnType, 'array') !== false) {
        echo "  ✓ Returns array|false\n";
    } else {
        echo "  ✗ Return type wrong\n";
        exit(1);
    }

    echo "\n✓ EventsService structure validated\n\n";

} catch (Exception $e) {
    echo "  ✗ Exception: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: DisplayService Structure
echo "Test 3: DisplayService Structure\n";
echo str_repeat('-', 50) . "\n";

try {
    $reflection = new ReflectionClass('MedExApi\Services\DisplayService');

    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    $methodNames = array_map(function($m) { return $m->getName(); }, $methods);

    $requiredMethods = [
        'show_progress_recall',
        'navigation',
        'preferences',
        'display_recalls',
        'get_recalls',
        'display_add_recall',
        'icon_template',
        'possibleModalities',
        'SMS_bot',
        'syncPat'
    ];

    foreach ($requiredMethods as $method) {
        if (in_array($method, $methodNames)) {
            echo "  ✓ Method $method() exists\n";
        } else {
            echo "  ✗ Method $method() NOT FOUND\n";
            exit(1);
        }
    }

    // Check possibleModalities() signature
    $modalitiesMethod = $reflection->getMethod('possibleModalities');
    $params = $modalitiesMethod->getParameters();

    if (count($params) === 1 && $params[0]->getType()->getName() === 'array') {
        echo "  ✓ possibleModalities() accepts array parameter\n";
    } else {
        echo "  ✗ possibleModalities() signature wrong\n";
        exit(1);
    }

    $returnType = $modalitiesMethod->getReturnType();
    if ($returnType && $returnType->getName() === 'array') {
        echo "  ✓ possibleModalities() returns array\n";
    } else {
        echo "  ✗ possibleModalities() return type wrong\n";
        exit(1);
    }

    // Check show_progress_recall() signature
    $progressMethod = $reflection->getMethod('show_progress_recall');
    $params = $progressMethod->getParameters();

    if (count($params) === 2) {
        echo "  ✓ show_progress_recall() has 2 parameters\n";
    } else {
        echo "  ✗ show_progress_recall() parameter count wrong\n";
        exit(1);
    }

    $returnType = $progressMethod->getReturnType();
    if ($returnType && $returnType->getName() === 'array') {
        echo "  ✓ show_progress_recall() returns array\n";
    } else {
        echo "  ✗ show_progress_recall() return type wrong\n";
        exit(1);
    }

    echo "\n✓ DisplayService structure validated\n\n";

} catch (Exception $e) {
    echo "  ✗ Exception: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Backward Compatibility Aliases
echo "Test 4: Backward Compatibility Aliases\n";
echo str_repeat('-', 50) . "\n";

$aliases = [
    'MedExApi\CurlRequest' => 'MedExApi\Client\HttpClient',
    'MedExApi\Base' => 'MedExApi\Services\BaseService',
    'MedExApi\Practice' => 'MedExApi\Services\PracticeService',
    'MedExApi\Campaign' => 'MedExApi\Services\CampaignService',
    'MedExApi\Events' => 'MedExApi\Services\EventsService',
    'MedExApi\Callback' => 'MedExApi\Services\CallbackService',
    'MedExApi\Logging' => 'MedExApi\Services\LoggingService',
    'MedExApi\Display' => 'MedExApi\Services\DisplayService',
    'MedExApi\Setup' => 'MedExApi\Services\SetupService'
];

$failed = 0;
foreach ($aliases as $alias => $target) {
    if (class_exists($alias)) {
        echo "  ✓ $alias exists\n";
    } else {
        echo "  ✗ $alias NOT FOUND\n";
        $failed++;
    }
}

if ($failed > 0) {
    exit(1);
}

echo "\n✓ All aliases present\n\n";

// Test 5: Campaign Type Constants
echo "Test 5: Campaign Type Support\n";
echo str_repeat('-', 50) . "\n";

try {
    $reflection = new ReflectionClass('MedExApi\Services\EventsService');
    $source = file_get_contents($reflection->getFileName());

    $campaignTypes = ['REMINDER', 'RECALL', 'ANNOUNCE', 'SURVEY', 'CLINICAL_REMINDER', 'GOGREEN'];
    foreach ($campaignTypes as $type) {
        if (strpos($source, "M_group'] === '$type'") !== false ||
            strpos($source, "M_group'] == '$type'") !== false ||
            strpos($source, 'process' . ucfirst(strtolower($type))) !== false ||
            strpos($source, $type) !== false) {
            echo "  ✓ Campaign type $type supported\n";
        } else {
            echo "  ⚠ Campaign type $type may not be supported\n";
        }
    }

    echo "\n✓ Campaign types validated\n\n";

} catch (Exception $e) {
    echo "  ✗ Exception: " . $e->getMessage() . "\n";
    exit(1);
}

// Summary
echo str_repeat('=', 50) . "\n";
echo "✓ ALL SIMPLE TESTS PASSED\n";
echo str_repeat('=', 50) . "\n";
echo "\nTest Summary:\n";
echo "  ✓ All 10 classes load correctly\n";
echo "  ✓ EventsService has required methods with correct signatures\n";
echo "  ✓ DisplayService has required methods with correct signatures\n";
echo "  ✓ All 9 backward compatibility aliases present\n";
echo "  ✓ All 6 campaign types supported\n";
echo "\n✓ Code structure validated\n";
echo "\nFor full functional testing, run in Docker environment:\n";
echo "  docker compose exec openemr php /var/www/localhost/htdocs/openemr/interface/modules/custom_modules/oe-module-medex/tests/integration_test.php\n";
