<?php

/**
 * Which medications the active and inactive lists keep.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\ActiveMedicationListService;
use PHPUnit\Framework\TestCase;

class ActiveMedicationListEligibilityTest extends TestCase
{
    private int $pid = 0;

    protected function setUp(): void
    {
        $this->pid = random_int(800_000_000, 899_999_999);
        $this->insertList('Current aspirin', 1, 'none');
        $this->insertList('Ends today', 1, 'today');
        $this->insertList('Ended yesterday', 1, 'yesterday');
        $this->insertList('Stopped lisinopril', 0, 'none');
    }

    protected function tearDown(): void
    {
        if ($this->pid < 1) {
            return;
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM lists WHERE pid = ?', [$this->pid]);
    }

    /**
     * The active list keeps current medications and drops stopped or expired ones.
     */
    public function testActiveListOmitsStoppedAndExpiredMedications(): void
    {
        $titles = $this->titles((new ActiveMedicationListService())->getActiveList($this->pid));

        $this->assertSame(['Current aspirin', 'Ends today'], $titles);
    }

    /**
     * The inactive list keeps stopped and expired medications and drops current ones.
     */
    public function testInactiveListKeepsHistoryAndOmitsCurrentMedications(): void
    {
        $service = new ActiveMedicationListService();
        $titles = $this->titles($service->getInactiveList($this->pid, $service->getActiveList($this->pid)));

        $this->assertSame(['Ended yesterday', 'Stopped lisinopril'], $titles);
    }

    /**
     * @param list<array{title: string}> $rows
     * @return list<string>
     */
    private function titles(array $rows): array
    {
        $titles = [];
        foreach ($rows as $row) {
            $titles[] = $row['title'];
        }
        sort($titles);

        return $titles;
    }

    /**
     * Store one medication issue dated from the database clock.
     */
    private function insertList(string $title, int $activity, string $endKind): void
    {
        $sql = match ($endKind) {
            'none' => 'INSERT INTO lists (pid, type, title, activity, begdate, enddate) VALUES (?, ?, ?, ?, CURDATE(), NULL)',
            'today' => 'INSERT INTO lists (pid, type, title, activity, begdate, enddate) VALUES (?, ?, ?, ?, CURDATE(), CURDATE())',
            'yesterday' => 'INSERT INTO lists (pid, type, title, activity, begdate, enddate) VALUES (?, ?, ?, ?, CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))',
            default => throw new \LogicException('Unknown medication end kind'),
        };
        QueryUtils::sqlInsert($sql, [$this->pid, 'medication', $title, $activity]);
    }
}
