<?php

/**
 * OnsiteDocumentCriteriaSqlInjectionTest
 *
 * Regression test for the patient-portal OnsiteDocument query builder. The
 * bitwise comparators of the shared Phreeze Criteria builder previously
 * concatenated request values into an unquoted SQL context, allowing an
 * authenticated patient to inject arbitrary SQL into the WHERE clause via
 * mass-assigned criteria properties such as `Id_BitwiseAnd`.
 *
 * These tests drive the real OnsiteDocumentCriteria (plus its generated field
 * map) through the shared builder and assert that bitwise operands are emitted
 * as integer literals, so attacker input can never change the query structure,
 * while legitimate integer bitmasks and quoted equality filters are unaffected.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Security Team <security@open-emr.org>
 * @copyright Copyright (c) 2026 OpenEMR <info@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OnsiteDocumentCriteriaSqlInjectionTest extends TestCase
{
    private const PORTAL = __DIR__ . '/../../../../portal/patient';

    protected function setUp(): void
    {
        parent::setUp();

        $fwk = self::PORTAL . '/fwk/libs';
        $model = self::PORTAL . '/libs';
        set_include_path($fwk . PATH_SEPARATOR . $model . PATH_SEPARATOR . get_include_path());

        require_once($fwk . '/verysimple/Phreeze/Criteria.php');
        require_once($fwk . '/verysimple/Phreeze/DataAdapter.php');
        require_once($fwk . '/verysimple/DB/DataDriver/MySQLi.php');
        require_once(self::PORTAL . '/libs/Model/OnsiteDocumentCriteria.php');

        // Escape()/GetQuotedSql() dispatch to the active driver; wire the MySQLi
        // driver directly so the builder runs without a live DB connection. The
        // legacy static is untyped, so set it via reflection.
        (new \ReflectionClass(\DataAdapter::class))
            ->setStaticPropertyValue('DRIVER_INSTANCE', new \DataDriverMySQLi());
    }

    /**
     * Error-based and boolean-based payloads that the reporter demonstrated, plus
     * simpler structure-breaking inputs. None must survive into the WHERE clause.
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function injectionPayloadProvider(): array
    {
        return [
            'extractvalue error-based' => ['0 OR extractvalue(1,concat(0x7e,(select concat(username,0x7c,left(password,12)) from users_secure limit 1),0x7e))'],
            'boolean OR 1=1'          => ['0 OR 1=1'],
            'subselect'               => ['0 UNION SELECT password FROM users_secure'],
            'comment-obfuscated'      => ['0/**/or/**/1=1'],
        ];
    }

    #[DataProvider('injectionPayloadProvider')]
    public function testBitwiseAndNeutralisesInjection(string $payload): void
    {
        $criteria = new \OnsiteDocumentCriteria();
        $criteria->Pid_Equals = 42;
        $criteria->Id_BitwiseAnd = $payload;

        $where = $criteria->GetWhere();
        $this->assertIsString($where);

        $this->assertStringNotContainsStringIgnoringCase('extractvalue', $where);
        $this->assertStringNotContainsStringIgnoringCase('users_secure', $where);
        $this->assertStringNotContainsStringIgnoringCase('union', $where);
        $this->assertStringNotContainsStringIgnoringCase(' or ', $where);
        // Operand collapses to the integer 0 for any non-numeric leading input.
        $this->assertStringContainsString('(`onsite_documents`.`id` & 0)', $where);
    }

    #[DataProvider('injectionPayloadProvider')]
    public function testBitwiseOrNeutralisesInjection(string $payload): void
    {
        $criteria = new \OnsiteDocumentCriteria();
        $criteria->Pid_Equals = 42;
        $criteria->Id_BitwiseOr = $payload;

        $where = $criteria->GetWhere();
        $this->assertIsString($where);

        $this->assertStringNotContainsStringIgnoringCase('extractvalue', $where);
        $this->assertStringNotContainsStringIgnoringCase('users_secure', $where);
        $this->assertStringContainsString('(`onsite_documents`.`id` | 0)', $where);
        // Regression: the old branch emitted a stray unbalanced single quote.
        $this->assertStringNotContainsString("| '", $where);
    }

    public function testBitwiseAndPreservesLegitimateBitmask(): void
    {
        $criteria = new \OnsiteDocumentCriteria();
        $criteria->Pid_Equals = 42;
        $criteria->Id_BitwiseAnd = '4';

        $where = $criteria->GetWhere();
        $this->assertIsString($where);

        $this->assertStringContainsString('(`onsite_documents`.`id` & 4)', $where);
    }

    public function testEqualsFilterStillQuotesAndEscapes(): void
    {
        // A comparator that legitimately takes a string must remain quoted and
        // escaped — proving the fix is scoped to the bitwise branches only.
        $criteria = new \OnsiteDocumentCriteria();
        $criteria->Pid_Equals = "1' OR '1'='1";

        $where = $criteria->GetWhere();
        $this->assertIsString($where);

        $this->assertStringContainsString("`onsite_documents`.`pid` = '1\\' OR \\'1\\'=\\'1'", $where);
    }
}
