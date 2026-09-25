<?php

/**
 * @package   OpenEMR
 *
 * @link https://www.open-emr.org
 * @author RobinALG87
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Tabs;

use OpenEMR\Tabs\DefaultTabsFilter;
use OpenEMR\Tabs\PatientDashboardTab;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class PatientDashboardTabTest extends TestCase
{
    public function testPatientDashboardIsFirstAndExistingTabsArePreserved(): void
    {
        $defaultTabs = [
            [
                'notes' => 'interface/main/messages/messages.php?form_active=1',
                'id' => 'msg',
                'label' => 'Message Center',
            ],
            [
                'notes' => 'interface/main/calendar/index.php',
                'id' => 'cal',
                'label' => 'Calendar',
            ],
        ];

        $patientNotes = 'interface/patient_file/summary/demographics.php?set_pid=42';
        $result = PatientDashboardTab::prepend($defaultTabs, $patientNotes, 'Dashboard');

        self::assertSame(
            [
                [
                    'notes' => $patientNotes,
                    'id' => 'pat',
                    'label' => 'Dashboard',
                ],
                ...$defaultTabs,
            ],
            $result,
        );
    }

    public function testPrependDoesNotMutateTheInputArray(): void
    {
        $defaultTabs = [
            ['notes' => 'interface/main/messages/messages.php', 'id' => 'msg', 'label' => 'Messages'],
        ];

        PatientDashboardTab::prepend(
            $defaultTabs,
            'interface/patient_file/summary/demographics.php?set_pid=42',
            'Dashboard',
        );

        self::assertCount(1, $defaultTabs);
        self::assertSame('msg', $defaultTabs[0]['id']);
    }

    public function testPatientDashboardPathSurvivesDefaultTabValidation(): void
    {
        $tabs = PatientDashboardTab::prepend(
            [],
            'interface/patient_file/summary/demographics.php?set_pid=42',
            'Dashboard',
        );

        $result = (new DefaultTabsFilter())->filter($tabs, dirname(__DIR__, 4));

        self::assertCount(1, $result);
        self::assertSame('pat', $result[0]['option_id']);
        self::assertSame($tabs[0]['notes'], $result[0]['notes']);
    }
}
