<?php

/**
 * Tests the patient portal's prescription list (portal/get_prescriptions.php).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Portal;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PortalPrescriptionListTest extends TestCase
{
    /** A patient id no fixture uses. */
    private const PID = 9915041;

    /** Every row this test adds has a drug name starting with this. */
    private const DRUG_PREFIX = 'Portal Rx ';

    private const SESSION_KEYS = ['pid', 'patient_portal_onsite_two'];

    /** @var array<string, array{bool, mixed}> */
    private array $savedSession = [];

    /** @var list<int> */
    private array $insertedIds = [];

    protected function setUp(): void
    {
        // Rows an interrupted earlier run left behind: this patient's, with this test's names only.
        $this->removeLeftovers();

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach (self::SESSION_KEYS as $key) {
            $this->savedSession[$key] = [$session->has($key), $session->get($key)];
        }
        // A signed-in portal patient, as portal/verify_session.php checks it.
        SessionUtil::setSession('pid', self::PID);
        SessionUtil::setSession('patient_portal_onsite_two', 1);
    }

    protected function tearDown(): void
    {
        foreach ($this->insertedIds as $id) {
            QueryUtils::sqlStatementThrowException('DELETE FROM `prescriptions` WHERE `id` = ?', [$id]);
        }
        $this->insertedIds = [];
        $this->assertSame(0, $this->countLeftovers(), 'the test left prescriptions behind');
        foreach ($this->savedSession as $key => [$present, $value]) {
            if ($present) {
                SessionUtil::setSession($key, $value);
            } else {
                SessionUtil::unsetSession($key);
            }
        }
    }

    #[Test]
    public function listsTheActivePrescriptionsTheChartShows(): void
    {
        $courseEnd = date('Y-m-d', strtotime('+10 days'));
        $this->insertPrescription('Portal Rx Current', 1, null);
        // A course entered with its stop date: active until then.
        $this->insertPrescription('Portal Rx Course', 1, $courseEnd);
        // "Currently Active" unticked in the prescription editor.
        $this->insertPrescription('Portal Rx Unticked', -1, null);
        // Deleted through the REST or FHIR API (a soft delete).
        $this->insertPrescription('Portal Rx Deleted', 0, null);

        $output = $this->renderList();

        $this->assertStringContainsString('Portal Rx Current', $output);
        $this->assertStringContainsString('Portal Rx Course', $output);
        $this->assertStringContainsString($courseEnd, $output, 'the End Date column shows the course end');
        $this->assertStringNotContainsString('Portal Rx Unticked', $output);
        $this->assertStringNotContainsString('Portal Rx Deleted', $output);
    }

    private function renderList(): string
    {
        $bufferLevel = ob_get_level();
        ob_start();
        try {
            require dirname(__DIR__, 4) . '/portal/get_prescriptions.php';
            $output = (string) ob_get_clean();
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }

        return $output;
    }

    private function insertPrescription(string $drug, int $active, ?string $endDate): void
    {
        $this->insertedIds[] = QueryUtils::sqlInsert(
            'INSERT INTO `prescriptions` (`patient_id`, `drug`, `active`, `start_date`, `end_date`, `date_added`,'
            . " `date_modified`, `txDate`, `usage_category_title`, `request_intent_title`)"
            . " VALUES (?, ?, ?, CURDATE(), ?, NOW(), NOW(), CURDATE(), '', '')",
            [self::PID, $drug, $active, $endDate]
        );
    }

    private function removeLeftovers(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `prescriptions` WHERE `patient_id` = ? AND `drug` LIKE ?',
            [self::PID, self::DRUG_PREFIX . '%']
        );
    }

    private function countLeftovers(): int
    {
        $count = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `n` FROM `prescriptions` WHERE `patient_id` = ? AND `drug` LIKE ?',
            'n',
            [self::PID, self::DRUG_PREFIX . '%']
        );
        return is_numeric($count) ? (int) $count : -1;
    }
}
