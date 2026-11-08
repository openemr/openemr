<?php

/**
 * Isolated PatientFinderDetailService Test
 *
 * Checks that an expanded finder row reads only the fields the Patient Finder
 * Detail layout shows, builds combined columns from their SQL, applies the
 * finder's patient filter, and finds nothing for a bad or hidden pid.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Events\PatientFinder\PatientFinderFilterEvent;
use OpenEMR\Services\PatientFinder\PatientFinderBuiltInColumns;
use OpenEMR\Services\PatientFinder\PatientFinderDetailService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PatientFinderDetailServiceTest extends TestCase
{
    /** @var list<array{string, array<mixed>}> */
    private array $queries = [];

    /**
     * @param list<string>       $layoutFields the detail layout's field ids
     * @param list<array<mixed>> $patientRows  what the patient query returns
     */
    private function service(
        array $layoutFields,
        array $patientRows,
        ?EventDispatcher $dispatcher = null
    ): PatientFinderDetailService {
        $fetch = function (string $sql, array $binds) use ($layoutFields, $patientRows): array {
            $this->queries[] = [$sql, $binds];
            if (str_contains($sql, 'FROM layout_options')) {
                return array_map(static fn(string $id): array => ['field_id' => $id], $layoutFields);
            }
            return $patientRows;
        };
        return new PatientFinderDetailService(
            $dispatcher ?? new EventDispatcher(),
            $fetch,
            static fn(): array => ['pid', 'sex', 'DOB', 'ss'],
            new PatientFinderBuiltInColumns()
        );
    }

    public function testReadsOnlyTheLayoutFieldsAndReturnsThemRaw(): void
    {
        $service = $this->service(
            ['sex', 'address_full', 'no_such_field', 'bad`id'],
            [['pid' => 7, 'sex' => 'Female', 'address_full' => '1 Main St, Tampa, FL  33601 & Co']]
        );

        $values = $service->values(7);

        $this->assertSame(['sex' => 'Female', 'address_full' => '1 Main St, Tampa, FL  33601 & Co'], $values);
        [$sql, $binds] = end($this->queries) ?: ['', []];
        $this->assertStringStartsWith('SELECT patient_data.pid, patient_data.`sex`, CONCAT_WS(', $sql);
        $this->assertStringContainsString(' AS `address_full` FROM patient_data', $sql);
        $this->assertStringNotContainsString('no_such_field', $sql);
        $this->assertStringNotContainsString('bad', $sql);
        $this->assertStringNotContainsString('`ss`', $sql, 'a field the layout does not show is never read');
        $this->assertSame([7], $binds);
    }

    public function testAppliesTheFinderPatientFilter(): void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            PatientFinderFilterEvent::EVENT_HANDLE,
            static function (PatientFinderFilterEvent $event): void {
                $event->getBoundFilter()->setFilterClause('patient_data.pid NOT IN (?)');
                $event->getBoundFilter()->setBoundValues([42]);
            }
        );
        $service = $this->service(['sex'], [], $dispatcher);

        $this->assertNull($service->values(42), 'a patient the finder hides is not found');
        [$sql, $binds] = end($this->queries) ?: ['', []];
        $this->assertStringContainsString('WHERE patient_data.pid = ? AND (patient_data.pid NOT IN (?)) LIMIT 1', $sql);
        $this->assertSame([42, 42], $binds);
    }

    public function testNonPositivePidIsNotLookedUp(): void
    {
        $service = $this->service(['sex'], [['pid' => 1, 'sex' => 'Male']]);

        $this->assertNull($service->values(0));
        $this->assertNull($service->values(-3));
        $this->assertSame([], $this->queries);
    }

    public function testEnabledOnlyWhenTheLayoutShowsSomething(): void
    {
        $this->assertTrue($this->service(['sex'], [])->isEnabled());
        $this->assertFalse($this->service([], [])->isEnabled());
    }
}
