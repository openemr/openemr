<?php

/**
 * PatientTransactionService Integration Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Lavoy <david@p.industries>
 * @copyright Copyright (c) 2026 P Industries
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\PatientTransactionService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PatientTransactionServiceTest extends TestCase
{
    private const TEST_PID = 9876502;

    private PatientTransactionService $service;

    private SessionInterface $session;

    protected function setUp(): void
    {
        $this->service = new PatientTransactionService();
        $this->session = SessionWrapperFactory::getInstance()->getActiveSession();
        $this->session->set('authUser', 'admin');
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE `lbt_data` FROM `lbt_data` JOIN `transactions` ON `transactions`.`id` = `lbt_data`.`form_id` WHERE `transactions`.`pid` = ?",
            [self::TEST_PID]
        );
        QueryUtils::sqlStatementThrowException("DELETE FROM `transactions` WHERE `pid` = ?", [self::TEST_PID]);
        $this->session->clear();
    }

    #[Test]
    public function insertSavesTheReferralForm(): void
    {
        $result = $this->service->insert(self::TEST_PID, [
            'type' => 'LBTref',
            'groupname' => 'Default',
            'body' => 'test-fixture referral body',
            'referralDate' => '2026-01-15',
            'referDiagnosis' => 'ICD10:E11.9',
            'referByNpi' => '',
            'referToNpi' => '',
            'riskLevel' => 'Low',
            'includeVitals' => '1',
            'authorization' => '',
            'visits' => '',
            'validFrom' => '',
            'validThrough' => '',
        ]);

        $this->assertIsArray($result);
        $this->assertIsNumeric($result['id']);
        $this->assertGreaterThan(0, $result['id']);
        $this->assertSame($result['id'], $result['form_id']);

        $body = QueryUtils::fetchSingleValue(
            "SELECT `field_value` FROM `lbt_data` WHERE `form_id` = ? AND `field_id` = 'body'",
            'field_value',
            [$result['id']]
        );
        $this->assertSame('test-fixture referral body', $body);
    }
}
