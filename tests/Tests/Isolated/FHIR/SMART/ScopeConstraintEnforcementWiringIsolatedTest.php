<?php

/**
 * Pins where SMART scope constraints (e.g. ?category=) are enforced on FHIR endpoints whose
 * controllers need a database or globals to construct. A scope such as
 * patient/DocumentReference.rs?category=...|clinical-note must narrow every route that returns
 * that resource, and bulk export, which cannot narrow its output, must refuse it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FHIR\SMART;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('security')]
class ScopeConstraintEnforcementWiringIsolatedTest extends TestCase
{
    private static function source(string $relativePath): string
    {
        $content = file_get_contents(__DIR__ . '/../../../../../' . $relativePath);
        self::assertIsString($content, 'could not read ' . $relativePath);
        return $content;
    }

    private static function methodBody(string $source, string $signature): string
    {
        $start = strpos($source, $signature);
        self::assertIsInt($start, 'missing ' . $signature);
        $end = strpos($source, "\n    }\n", $start);
        self::assertIsInt($end);
        return substr($source, $start, $end - $start);
    }

    public function testDocumentReferenceReadsAreFilteredByScopeConstraints(): void
    {
        $source = self::source('src/RestControllers/FHIR/FhirDocumentReferenceRestController.php');
        foreach (['public function getOne(', 'public function getAll('] as $method) {
            $this->assertStringContainsString(
                '$this->filterByScopeConstraints(',
                self::methodBody($source, $method),
                'FhirDocumentReferenceRestController::' . $method . ' must filter by scope constraints'
            );
        }
        $this->assertStringContainsString('->canAccessResource(', self::methodBody($source, 'private function filterByScopeConstraints('));
    }

    public function testExportRequiresUnconstrainedScopes(): void
    {
        $source = self::source('src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php');
        $this->assertStringNotContainsString(
            '->requestHasScope(',
            $source,
            'bulk export cannot filter by constraint; use requestHasUnconstrainedScopeEntity()'
        );
        $this->assertSame(2, substr_count($source, '->requestHasUnconstrainedScopeEntity('));
    }
}
