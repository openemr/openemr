<?php

/**
 * Every FHIR search controller answers a parameter value that didn't parse through the one shared helper, so a
 * search controller added or changed later can't go back to answering it with an empty 200 Bundle.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\FHIR;

use OpenEMR\RestControllers\FHIR\FhirGenericRestController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FhirSearchControllersSyntaxErrorTest extends TestCase
{
    /** Controllers whose search hands the request to another controller, which is checked itself. */
    private const DELEGATES = [
        'FhirTaskRestController' => FhirGenericRestController::class,
    ];

    public function testEverySearchControllerAnswersASyntaxErrorThroughTheSharedHelper(): void
    {
        $checked = [];
        $missing = [];
        $files = glob(__DIR__ . '/../../../../../src/RestControllers/FHIR/*RestController.php');
        $this->assertIsArray($files);
        foreach ($files as $file) {
            $class = 'OpenEMR\\RestControllers\\FHIR\\' . basename($file, '.php');
            if (!class_exists($class)) {
                $this->fail($file . ' does not declare ' . $class);
            }
            $reflection = new ReflectionClass($class);
            if (!$reflection->hasMethod('getAll') || $reflection->getMethod('getAll')->getDeclaringClass()->getName() !== $class) {
                continue;
            }
            $body = $this->methodSource($reflection, 'getAll');
            $short = $reflection->getShortName();
            $checked[] = $short;
            if (isset(self::DELEGATES[$short])) {
                $this->assertStringContainsString('->getAll(', $body, $short . ' hands its search to ' . self::DELEGATES[$short]);
                continue;
            }
            if (!str_contains($body, 'RestControllerHelper::getFhirSearchErrorResponse(') && !str_contains($body, 'RestControllerHelper::getFhirSearchErrorPsrResponse(')) {
                $missing[] = $short;
            }
        }

        // A floor, so a moved directory or a renamed method can't make this pass by checking nothing.
        $this->assertGreaterThanOrEqual(30, count($checked), 'FHIR search controllers found: ' . implode(', ', $checked));
        $this->assertSame([], $missing, 'These FHIR search controllers build a Bundle without RestControllerHelper::getFhirSearchErrorResponse()');
    }

    /**
     * @param ReflectionClass<object> $reflection
     */
    private function methodSource(ReflectionClass $reflection, string $method): string
    {
        $method = $reflection->getMethod($method);
        $file = $method->getFileName();
        $start = $method->getStartLine();
        $end = $method->getEndLine();
        $this->assertIsString($file);
        $this->assertIsInt($start);
        $this->assertIsInt($end);
        $lines = file($file);
        $this->assertIsArray($lines);
        return implode('', array_slice($lines, $start - 1, $end - $start + 1));
    }
}
