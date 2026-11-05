<?php

/**
 * SNOMED-PR, the SNOMED Procedure code type, holds SNOMED CT codes, so it gets the SNOMED CT code
 * system wherever OpenEMR turns a stored code into a coded value: FHIR (a URL) and QDM (an OID).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\ProcedureService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CodeTypesServiceSnomedProcedureTest extends TestCase
{
    private const SNOMED_OID = '2.16.840.1.113883.6.96';

    /**
     * @return array<string, array{string, bool, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function codeTypes(): array
    {
        return [
            'SNOMED-PR as a URL' => [CodeTypesService::CODE_TYPE_SNOMED_PROCEDURE, false, FhirCodeSystemConstants::SNOMED_CT],
            'SNOMED-PR as an OID' => [CodeTypesService::CODE_TYPE_SNOMED_PROCEDURE, true, self::SNOMED_OID],
            // The other two SNOMED code types already had it.
            'SNOMED-CT as a URL' => [CodeTypesService::CODE_TYPE_SNOMED_CT, false, FhirCodeSystemConstants::SNOMED_CT],
            'SNOMED-CT as an OID' => [CodeTypesService::CODE_TYPE_SNOMED_CT, true, self::SNOMED_OID],
            'SNOMED as a URL' => [CodeTypesService::CODE_TYPE_SNOMED, false, FhirCodeSystemConstants::SNOMED_CT],
            'SNOMED as an OID' => [CodeTypesService::CODE_TYPE_SNOMED, true, self::SNOMED_OID],
            'a code type OpenEMR has no system for' => ['SNOMED-XX', false, null],
        ];
    }

    #[DataProvider('codeTypes')]
    public function testEachSnomedCodeTypeHasTheSnomedCtSystem(string $codeType, bool $useOid, ?string $expected): void
    {
        $this->assertSame($expected, (new CodeTypesService())->getSystemForCodeType($codeType, $useOid));
    }

    public function testAStoredSnomedProcedureCodeIsCodedInSnomedCt(): void
    {
        $codeTypes = new CodeTypesService();
        $this->assertSame(FhirCodeSystemConstants::SNOMED_CT, $codeTypes->getSystemForCode('SNOMED-PR:103696004'));
        // The system SNOMED CT reads back as includes SNOMED-PR, so the two directions agree.
        $this->assertContains(CodeTypesService::CODE_TYPE_SNOMED_PROCEDURE, $codeTypes->getCodeTypeListForSystem(FhirCodeSystemConstants::SNOMED_CT));
    }

    public function testTheEcqmsReadASnomedProcedureCodeAsSnomedCt(): void
    {
        // A procedure or a referral's Requested Service picked from SNOMED-PR reaches the measures' value sets.
        $code = (new ProcedureService(new QdmRequestSome([1]), new CodeTypesService()))->makeQdmCode('SNOMED-PR:103696004');
        $this->assertInstanceOf(Code::class, $code);
        $this->assertSame(['103696004', self::SNOMED_OID], [$code->code, $code->system]);
    }
}
