<?php

/**
 * Database test: a 271 eligibility response's plan description (EB05) is
 * saved with each benefit and shown on the patient's eligibility card.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\EDI270;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class EligibilityPlanDescriptionTest extends TestCase
{
    /**
     * Synthetic ids, clear of the demo data. The save looks up the patient's
     * primary policy, and the card joins its insurer; no patient row is read.
     */
    private const PID = 99990081;
    private const INSURER = 99990083;

    protected function setUp(): void
    {
        $this->cleanUp();
        QueryUtils::sqlInsert('INSERT INTO insurance_companies (id, name) VALUES (?, ?)', [self::INSURER, 'Eligibility Test Plan']);
        QueryUtils::sqlInsert(
            "INSERT INTO insurance_data (type, provider, policy_number, date, pid, subscriber_lname, subscriber_fname,"
            . " subscriber_relationship) VALUES ('primary', ?, 'ELIG-TEST-1', '2026-01-01', ?, 'Eligtest', 'Pat', 'self')",
            [self::INSURER, self::PID]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testPlanDescriptionIsSavedAndShown(): void
    {
        EDI270::parseEdi271($this->response271());

        $this->assertSame(
            [['type' => '1', 'plan_description' => 'GOLD PPO PLAN'], ['type' => 'C', 'plan_description' => '']],
            $this->savedBenefits(),
            "Each benefit keeps its EB05 plan description; one sent without it stays empty"
        );

        ob_start();
        try {
            EDI270::showEligibilityInformation(self::PID);
        } finally {
            $card = (string) ob_get_clean();
        }
        $this->assertStringContainsString('<b>Plan Description:</b> GOLD PPO PLAN<br />', $card);
        $this->assertSame(1, substr_count($card, 'Plan Description'), 'Only the benefit with a description lists one');
    }

    /**
     * A 271 for the synthetic patient (REF*EJ carries the pid, as OpenEMR's
     * own 270 sends it): an active-coverage benefit with a plan description
     * and a deductible without one.
     */
    private function response271(): string
    {
        $segments = [
            'ISA*00*          *00*          *ZZ*TESTPAYER      *ZZ*TESTCLINIC     *260101*1200*^*00501*000000001*0*T*:',
            'GS*HB*TESTPAYER*TESTCLINIC*20260101*1200*1*X*005010X279A1',
            'ST*271*0001*005010X279A1',
            'BHT*0022*11*TEST271*20260101*1200',
            'HL*1**20*1',
            'NM1*PR*2*ELIGIBILITY TEST PLAN*****PI*TESTPAYER',
            'HL*2*1*21*1',
            'NM1*1P*2*TEST CLINIC*****XX*1999999984',
            'HL*3*2*22*0',
            'TRN*2*TEST271*9TEST',
            'NM1*IL*1*ELIGTEST*PAT****MI*ELIG-TEST-1',
            'REF*EJ*' . self::PID,
            'DMG*D8*19800101*F',
            implode('*', ['EB', '1', 'IND', '30', 'HM', 'GOLD PPO PLAN', '', '', '', '', '', '', 'Y']),
            implode('*', ['EB', 'C', 'IND', '30', '', '', '23', '500', '', '', '', '', 'Y']),
            'SE*15*0001',
            'GE*1*1',
            'IEA*1*000000001',
        ];
        return implode('~', $segments) . '~';
    }

    /**
     * @return list<array{type: mixed, plan_description: mixed}>
     */
    private function savedBenefits(): array
    {
        $rows = [];
        foreach (
            QueryUtils::fetchRecords(
                'SELECT be.type, be.plan_description FROM benefit_eligibility be'
                . ' JOIN eligibility_verification ev ON ev.verification_id = be.verification_id'
                . ' JOIN insurance_data i ON i.id = ev.insurance_id WHERE i.pid = ? ORDER BY be.type',
                [self::PID]
            ) as $row
        ) {
            $rows[] = ['type' => $row['type'], 'plan_description' => $row['plan_description']];
        }
        return $rows;
    }

    private function cleanUp(): void
    {
        $policies = QueryUtils::fetchTableColumn('SELECT id FROM insurance_data WHERE pid = ?', 'id', [self::PID]);
        foreach ($policies as $policy) {
            $verifications = QueryUtils::fetchTableColumn(
                'SELECT verification_id FROM eligibility_verification WHERE insurance_id = ?',
                'verification_id',
                [$policy]
            );
            foreach ($verifications as $verification) {
                QueryUtils::sqlStatementThrowException('DELETE FROM benefit_eligibility WHERE verification_id = ?', [$verification]);
            }
            QueryUtils::sqlStatementThrowException('DELETE FROM eligibility_verification WHERE insurance_id = ?', [$policy]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_companies WHERE id = ?', [self::INSURER]);
    }
}
