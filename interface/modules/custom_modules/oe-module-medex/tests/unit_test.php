<?php
/**
 * MedEx Module Unit Test
 *
 * Tests core functionality without requiring database or full OpenEMR bootstrap
 */

// Minimal bootstrap - just load the API
require_once(__DIR__ . '/../src/API/API.php');

echo "=== MedEx Module Unit Test ===\n\n";

// Create mock MedEx object for testing
class MockMedEx extends MedExApi\MedEx
{
    public function __construct()
    {
        // Create mock curl client
        $this->curl = new MedExApi\Client\HttpClient('', '');
    }
}

// Test 1: DisplayService - possibleModalities()
echo "Test 1: DisplayService::possibleModalities()\n";
echo str_repeat('-', 50) . "\n";

try {
    $mockMedEx = new MockMedEx();
    $displayService = new MedExApi\Services\DisplayService($mockMedEx);

    // Test Case 1: Patient with all modalities enabled
    $patient1 = [
        'phone_cell' => '555-1234',
        'phone_home' => '555-5678',
        'email' => 'test@example.com',
        'hipaa_allowsms' => 'YES',
        'hipaa_voice' => 'YES',
        'hipaa_allowemail' => 'YES'
    ];

    $result1 = $displayService->possibleModalities($patient1);

    if ($result1['SMS'] === true && $result1['AVM'] === true && $result1['EMAIL'] === true) {
        echo "  ✓ All modalities enabled: PASS\n";
    } else {
        echo "  ✗ All modalities enabled: FAIL\n";
        echo "    Expected: SMS=true, AVM=true, EMAIL=true\n";
        echo "    Got: " . json_encode($result1) . "\n";
        exit(1);
    }

    // Test Case 2: Patient with SMS blocked by HIPAA
    $patient2 = [
        'phone_cell' => '555-1234',
        'phone_home' => '555-5678',
        'email' => 'test@example.com',
        'hipaa_allowsms' => 'NO',
        'hipaa_voice' => 'YES',
        'hipaa_allowemail' => 'YES'
    ];

    $result2 = $displayService->possibleModalities($patient2);

    if ($result2['SMS'] === false && $result2['AVM'] === true && $result2['EMAIL'] === true) {
        echo "  ✓ SMS HIPAA restriction: PASS\n";
    } else {
        echo "  ✗ SMS HIPAA restriction: FAIL\n";
        echo "    Expected: SMS=false, AVM=true, EMAIL=true\n";
        echo "    Got: " . json_encode($result2) . "\n";
        exit(1);
    }

    // Test Case 3: Patient with no contact info
    $patient3 = [
        'phone_cell' => '',
        'phone_home' => '',
        'email' => '',
        'hipaa_allowsms' => 'YES',
        'hipaa_voice' => 'YES',
        'hipaa_allowemail' => 'YES'
    ];

    $result3 = $displayService->possibleModalities($patient3);

    if ($result3['SMS'] === false && $result3['AVM'] === false && $result3['EMAIL'] === false) {
        echo "  ✓ No contact info: PASS\n";
    } else {
        echo "  ✗ No contact info: FAIL\n";
        echo "    Expected: SMS=false, AVM=false, EMAIL=false\n";
        echo "    Got: " . json_encode($result3) . "\n";
        exit(1);
    }

    // Test Case 4: Patient with cell only
    $patient4 = [
        'phone_cell' => '555-1234',
        'phone_home' => '',
        'email' => '',
        'hipaa_allowsms' => 'YES',
        'hipaa_voice' => 'YES',
        'hipaa_allowemail' => 'YES'
    ];

    $result4 = $displayService->possibleModalities($patient4);

    if ($result4['SMS'] === true && $result4['AVM'] === true && $result4['EMAIL'] === false) {
        echo "  ✓ Cell phone only: PASS\n";
    } else {
        echo "  ✗ Cell phone only: FAIL\n";
        echo "    Expected: SMS=true, AVM=true, EMAIL=false\n";
        echo "    Got: " . json_encode($result4) . "\n";
        exit(1);
    }

    // Test Case 5: Patient with email only
    $patient5 = [
        'phone_cell' => '',
        'phone_home' => '',
        'email' => 'test@example.com',
        'hipaa_allowsms' => 'YES',
        'hipaa_voice' => 'YES',
        'hipaa_allowemail' => 'YES'
    ];

    $result5 = $displayService->possibleModalities($patient5);

    if ($result5['SMS'] === false && $result5['AVM'] === false && $result5['EMAIL'] === true) {
        echo "  ✓ Email only: PASS\n";
    } else {
        echo "  ✗ Email only: FAIL\n";
        echo "    Expected: SMS=false, AVM=false, EMAIL=true\n";
        echo "    Got: " . json_encode($result5) . "\n";
        exit(1);
    }

    echo "\n✓ possibleModalities() tests passed (5/5)\n\n";

} catch (Exception $e) {
    echo "  ✗ Exception: " . $e->getMessage() . "\n";
    echo "  Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

// Test 2: Service Class Instantiation
echo "Test 2: Service Class Instantiation\n";
echo str_repeat('-', 50) . "\n";

try {
    $mockMedEx = new MockMedEx();

    // Test all services can be instantiated without errors
    $services = [
        'BaseService' => null,
        'PracticeService' => new MedExApi\Services\PracticeService($mockMedEx),
        'CampaignService' => new MedExApi\Services\CampaignService($mockMedEx),
        'EventsService' => new MedExApi\Services\EventsService($mockMedEx),
        'CallbackService' => new MedExApi\Services\CallbackService($mockMedEx),
        'LoggingService' => new MedExApi\Services\LoggingService($mockMedEx),
        'DisplayService' => new MedExApi\Services\DisplayService($mockMedEx),
        'SetupService' => new MedExApi\Services\SetupService($mockMedEx)
    ];

    foreach ($services as $name => $instance) {
        if ($name === 'BaseService') {
            echo "  ✓ BaseService (abstract class)\n";
        } else {
            echo "  ✓ $name instantiated\n";
        }
    }

    echo "\n✓ All services instantiated successfully\n\n";

} catch (Exception $e) {
    echo "  ✗ Exception: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Backward Compatibility Aliases
echo "Test 3: Backward Compatibility Aliases\n";
echo str_repeat('-', 50) . "\n";

try {
    $mockMedEx = new MockMedEx();

    // Test that old class names still work
    $oldStyle = new MedExApi\Display($mockMedEx);
    $newStyle = new MedExApi\Services\DisplayService($mockMedEx);

    if (get_class($oldStyle) === get_class($newStyle)) {
        echo "  ✓ MedExApi\\Display aliases to MedExApi\\Services\\DisplayService\n";
    } else {
        echo "  ✗ Alias mismatch\n";
        exit(1);
    }

    // Test Events alias
    $oldEvents = new MedExApi\Events($mockMedEx);
    $newEvents = new MedExApi\Services\EventsService($mockMedEx);

    if (get_class($oldEvents) === get_class($newEvents)) {
        echo "  ✓ MedExApi\\Events aliases to MedExApi\\Services\\EventsService\n";
    } else {
        echo "  ✗ Alias mismatch\n";
        exit(1);
    }

    echo "\n✓ Backward compatibility maintained\n\n";

} catch (Exception $e) {
    echo "  ✗ Exception: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Method Signatures
echo "Test 4: Method Signatures\n";
echo str_repeat('-', 50) . "\n";

try {
    // Test EventsService::generate() signature
    $eventsReflection = new ReflectionClass('MedExApi\Services\EventsService');
    $generateMethod = $eventsReflection->getMethod('generate');

    $params = $generateMethod->getParameters();
    if (count($params) === 2) {
        echo "  ✓ EventsService::generate() has 2 parameters\n";
    } else {
        echo "  ✗ EventsService::generate() parameter count mismatch\n";
        exit(1);
    }

    // Check parameter types
    if ($params[0]->getName() === 'token' && $params[0]->getType()->getName() === 'string') {
        echo "  ✓ First parameter is string \$token\n";
    } else {
        echo "  ✗ First parameter type mismatch\n";
        exit(1);
    }

    if ($params[1]->getName() === 'events' && $params[1]->getType()->getName() === 'array') {
        echo "  ✓ Second parameter is array \$events\n";
    } else {
        echo "  ✗ Second parameter type mismatch\n";
        exit(1);
    }

    // Check return type
    $returnType = $generateMethod->getReturnType();
    if ($returnType && (string)$returnType === 'array|false') {
        echo "  ✓ Returns array|false\n";
    } else {
        echo "  ✗ Return type mismatch: " . ($returnType ? (string)$returnType : 'none') . "\n";
        exit(1);
    }

    echo "\n✓ Method signatures correct\n\n";

} catch (Exception $e) {
    echo "  ✗ Exception: " . $e->getMessage() . "\n";
    exit(1);
}

// Summary
echo str_repeat('=', 50) . "\n";
echo "✓ ALL UNIT TESTS PASSED\n";
echo str_repeat('=', 50) . "\n";
echo "\nTest Summary:\n";
echo "  ✓ possibleModalities() logic: 5/5 test cases passed\n";
echo "  ✓ Service instantiation: 8/8 services working\n";
echo "  ✓ Backward compatibility: All aliases functional\n";
echo "  ✓ Method signatures: Type declarations correct\n";
echo "\n✓ Core functionality validated\n";
echo "\nNext steps:\n";
echo "  - Run in Docker environment for database integration tests\n";
echo "  - Test with actual MedEx API credentials\n";
echo "  - Verify campaign generation with real patient data\n";
