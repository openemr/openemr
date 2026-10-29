<?php

/**
 * CdaTemplateParseTest.php
 *
 * Unit tests for CdaTemplateParse fetch* methods. Uses reflection to bypass the
 * constructor (which requires OEGlobalsBag) and injects mock dependencies.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\Cda;

use OpenEMR\Cda\InternalToCdaConverter;
use OpenEMR\Services\Cda\CdaTemplateParse;
use OpenEMR\Services\Cda\XmlExtended;
use OpenEMR\Services\CodeTypesService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CdaTemplateParseTest extends TestCase
{
    private CdaTemplateParse $parser;
    private CodeTypesService&MockObject $codeService;
    /** @var ReflectionClass<CdaTemplateParse> */
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(CdaTemplateParse::class);
        $this->parser = $this->reflection->newInstanceWithoutConstructor();

        $this->codeService = $this->createMock(CodeTypesService::class);
        $this->codeService->method('resolveCode')
            ->willReturnCallback(fn(?string $code, ?string $codeType, ?string $codeText): array => [
                'code' => $code ?? '',
                'formatted_code' => ($code ?? '') . ':' . ($codeType ?? ''),
                'formatted_code_type' => $codeType ?? '',
                'code_text' => $codeText ?? '',
                'system_oid' => '',
                'valueset' => '',
                'valueset_name' => '',
            ]);

        $ed = $this->createMock(EventDispatcherInterface::class);

        $this->setProperty('templateData', []);
        $this->setProperty('codeService', $this->codeService);
        $this->setProperty('ed', $ed);
        $this->setProperty('is_qrda_import', false);
        $this->setProperty('currentOid', '');
    }

    // ──────────────────────────────────────────────────────────────────────
    // fetchDeceasedObservationData
    // ──────────────────────────────────────────────────────────────────────

    public function testFetchDeceasedObservationDataLeavesTemplateDataEmpty(): void
    {
        $this->parser->fetchDeceasedObservationData(['some' => 'data']);

        $this->assertSame([], $this->getTemplateData());
    }

    // ──────────────────────────────────────────────────────────────────────
    // fetchPaymentSourceData
    // ──────────────────────────────────────────────────────────────────────

    public function testFetchPaymentSourceDataPopulatesPayer(): void
    {
        $entry = [
            'observation' => [
                'statusCode' => ['code' => 'completed'],
                'value' => ['code' => '1'],
                'effectiveTime' => [
                    'low' => ['value' => '20210101'],
                    'high' => ['value' => '20211231'],
                ],
            ],
        ];

        $this->parser->fetchPaymentSourceData($entry);

        $payer = $this->getFieldRecord('payer', 1);
        $this->assertSame('completed', $payer['status']);
        $this->assertSame('1', $payer['code']);
        $this->assertSame('20210101', $payer['low_date']);
        $this->assertSame('20211231', $payer['high_date']);
        $this->assertSame(1, $this->getEntryId('payer', 1));
    }

    public function testFetchPaymentSourceDataSkipsWhenMissingLowDate(): void
    {
        $entry = [
            'observation' => [
                'statusCode' => ['code' => 'completed'],
                'value' => ['code' => '1'],
                'effectiveTime' => [
                    'high' => ['value' => '20211231'],
                ],
            ],
        ];

        $this->parser->fetchPaymentSourceData($entry);

        $this->assertSame([], $this->getTemplateData());
    }

    public function testFetchPaymentSourceDataIncrementsIndex(): void
    {
        $entry = [
            'observation' => [
                'statusCode' => ['code' => 'active'],
                'value' => ['code' => '2'],
                'effectiveTime' => [
                    'low' => ['value' => '20220101'],
                    'high' => ['value' => '20221231'],
                ],
            ],
        ];

        $this->parser->fetchPaymentSourceData($entry);
        $this->parser->fetchPaymentSourceData($entry);

        $this->assertEntityCount('payer', 2);
        $payer2 = $this->getFieldRecord('payer', 2);
        $this->assertSame('active', $payer2['status']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // fetchReferralData
    // ──────────────────────────────────────────────────────────────────────

    public function testFetchReferralDataWithArrayOfParagraphs(): void
    {
        $data = [
            'text' => [
                'paragraph' => [
                    "Referral  to\ncardiology",
                    'Follow up in 2 weeks',
                ],
            ],
        ];

        $this->parser->fetchReferralData($data);

        $ref1 = $this->getFieldRecord('referral', 1);
        $ref2 = $this->getFieldRecord('referral', 2);
        $this->assertSame('Referral to cardiology', $ref1['body']);
        $this->assertSame('Follow up in 2 weeks', $ref2['body']);
        $this->assertSame(1, $this->getEntryId('referral', 1));
        $this->assertSame(2, $this->getEntryId('referral', 2));
    }

    public function testFetchReferralDataWithSingleParagraph(): void
    {
        $data = [
            'templateId' => ['root' => '1.2.3.4'],
            'text' => [
                'paragraph' => 'Single  referral  note',
            ],
        ];

        $this->parser->fetchReferralData($data);

        $ref = $this->getFieldRecord('referral', 1);
        $this->assertSame('1.2.3.4', $ref['root']);
        $this->assertSame('Single referral note', $ref['body']);
    }

    public function testFetchReferralDataArraySkipsEmptyValues(): void
    {
        $data = [
            'text' => [
                'paragraph' => [
                    'First note',
                    '',
                    null,
                    'Third note',
                ],
            ],
        ];

        $this->parser->fetchReferralData($data);

        $this->assertEntityCount('referral', 2);
        $ref1 = $this->getFieldRecord('referral', 1);
        $ref2 = $this->getFieldRecord('referral', 2);
        $this->assertSame('First note', $ref1['body']);
        $this->assertSame('Third note', $ref2['body']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // fetchFileForImport
    // ──────────────────────────────────────────────────────────────────────

    public function testFetchFileForImportPopulatesImportFile(): void
    {
        $component = [
            'hash' => 'abc123',
            'mediaType' => 'application/pdf',
            'category' => 'document',
            'name' => 'report.pdf',
            'compression' => 'DF',
            '_' => 'base64content==',
        ];
        $uuid = 'test-uuid-1234';

        $this->parser->fetchFileForImport($component, $uuid);

        $file = $this->getFieldRecord('import_file', 1);
        $this->assertSame($uuid, $file['uuid']);
        $this->assertSame('abc123', $file['hash']);
        $this->assertSame('application/pdf', $file['mediaType']);
        $this->assertSame('document', $file['category']);
        $this->assertSame('report.pdf', $file['file_name']);
        $this->assertSame('DF', $file['compression']);
        $this->assertSame('base64content==', $file['content']);
        $this->assertSame(1, $this->getEntryId('import_file', 1));
    }

    public function testFetchFileForImportIncrementsIndex(): void
    {
        $component = ['hash' => 'h1', 'mediaType' => 'text/xml', 'category' => 'cda', 'name' => 'a.xml', 'compression' => '', '_' => ''];
        $this->parser->fetchFileForImport($component, 'uuid-1');
        $this->parser->fetchFileForImport($component, 'uuid-2');

        $this->assertEntityCount('import_file', 2);
        $file2 = $this->getFieldRecord('import_file', 2);
        $this->assertSame('uuid-2', $file2['uuid']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // fetchMedicalProblemData
    // ──────────────────────────────────────────────────────────────────────

    public function testFetchMedicalProblemDataWithValueCode(): void
    {
        $entry = [
            'act' => [
                'id' => ['extension' => 'ext-1', 'root' => 'root-1'],
                'effectiveTime' => [
                    'low' => ['value' => '20200301'],
                    'high' => ['value' => '20200401'],
                ],
                'entryRelationship' => [
                    'observation' => [
                        'value' => [
                            'code' => '44054006',
                            'codeSystemName' => 'SNOMED-CT',
                            'displayName' => 'Type 2 diabetes mellitus',
                        ],
                        'statusCode' => null,
                        'performer' => ['assignedEntity' => ['time' => ['value' => '20200302']]],
                    ],
                ],
            ],
        ];

        $this->parser->fetchMedicalProblemData($entry);

        $item = $this->getFieldRecord('lists1', 1);
        $this->assertSame('44054006:SNOMED-CT', $item['list_code']);
        $this->assertSame('Type 2 diabetes mellitus', $item['list_code_text']);
        $this->assertSame('medical_problem', $item['type']);
        $this->assertSame('diagnosis', $item['subtype']);
        $this->assertSame('ext-1', $item['extension']);
        $this->assertSame('root-1', $item['root']);
        $this->assertSame('20200301', $item['begdate']);
        $this->assertSame('20200401', $item['enddate']);
        $this->assertSame('20200302', $item['modified_time']);
        $this->assertSame(1, $this->getEntryId('lists1', 1));
    }

    public function testFetchMedicalProblemDataWithTranslationFallback(): void
    {
        $entry = [
            'act' => [
                'id' => ['extension' => 'ext-2', 'root' => 'root-2'],
                'effectiveTime' => ['low' => ['value' => '20200501']],
                'entryRelationship' => [
                    'observation' => [
                        'value' => [
                            'codeSystem' => '2.16.840.1.113883.6.96',
                            'translation' => [
                                'code' => 'E11',
                                'codeSystemName' => 'ICD10',
                                'displayName' => 'Type 2 diabetes',
                            ],
                        ],
                        'statusCode' => null,
                        'performer' => [],
                    ],
                ],
            ],
        ];

        $this->parser->fetchMedicalProblemData($entry);

        $item = $this->getFieldRecord('lists1', 1);
        $this->assertSame('E11:ICD10', $item['list_code']);
        $this->assertSame('Type 2 diabetes', $item['list_code_text']);
    }

    public function testFetchMedicalProblemDataConcernSubtype(): void
    {
        $this->setProperty('currentOid', '2.16.840.1.113883.10.20.24.3.138');

        $entry = [
            'act' => [
                'id' => [],
                'effectiveTime' => [],
                'entryRelationship' => [
                    'observation' => [
                        'value' => [
                            'code' => '12345',
                            'codeSystemName' => 'SNOMED-CT',
                            'displayName' => 'Some concern',
                        ],
                        'statusCode' => null,
                        'performer' => [],
                    ],
                ],
            ],
        ];

        $this->parser->fetchMedicalProblemData($entry);

        $item = $this->getFieldRecord('lists1', 1);
        $this->assertSame('concern', $item['subtype']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // fetchAllergyIntoleranceObservation
    // ──────────────────────────────────────────────────────────────────────

    public function testFetchAllergyIntoleranceActPath(): void
    {
        $entry = [
            'act' => [
                'id' => ['extension' => 'allergy-ext-1'],
                'effectiveTime' => [
                    'low' => ['value' => '20190601'],
                    'high' => ['value' => '20190701'],
                ],
                'entryRelationship' => [
                    'observation' => [
                        'participant' => [
                            'participantRole' => [
                                'playingEntity' => [
                                    'code' => [
                                        'code' => '7980',
                                        'displayName' => 'Penicillin',
                                        'codeSystemName' => 'RXNORM',
                                    ],
                                ],
                            ],
                        ],
                        'entryRelationship' => [
                            0 => ['observation' => ['value' => ['displayName' => 'Active']]],
                            1 => ['observation' => ['value' => ['code' => '247472004', 'displayName' => 'Hives']]],
                            2 => ['observation' => ['value' => ['code' => '24484000']]],
                        ],
                        'performer' => ['assignedEntity' => ['time' => ['value' => '20190605']]],
                    ],
                ],
            ],
        ];

        $this->parser->fetchAllergyIntoleranceObservation($entry);

        $item = $this->getFieldRecord('lists2', 1);
        $this->assertSame('allergy', $item['type']);
        $this->assertSame('allergy-ext-1', $item['extension']);
        $this->assertSame('20190601', $item['begdate']);
        $this->assertSame('20190701', $item['enddate']);
        $this->assertSame('7980', $item['list_code']);
        $this->assertSame('Penicillin', $item['list_code_text']);
        $this->assertSame('RXNORM', $item['codeSystemName']);
        $this->assertSame('Active', $item['status']);
        $this->assertSame('247472004', $item['reaction']);
        $this->assertSame('Hives', $item['reaction_text']);
        $this->assertSame('24484000', $item['severity_al_code']);
        $this->assertSame('20190605', $item['modified_time']);
        $this->assertSame(1, $this->getEntryId('lists2', 1));
    }

    public function testFetchAllergyIntoleranceObservationPath(): void
    {
        $entry = [
            'observation' => [
                'id' => ['extension' => 'obs-ext-1'],
                'effectiveTime' => [
                    'low' => ['value' => '20200101'],
                    'high' => ['value' => '20200201'],
                ],
                'statusCode' => ['code' => 'completed'],
                'participant' => [
                    'participantRole' => [
                        'playingEntity' => [
                            'code' => [
                                'code' => '2670',
                                'codeSystemName' => 'RXNORM',
                                'displayName' => 'Codeine',
                            ],
                            'name' => 'Codeine Fallback',
                        ],
                    ],
                ],
                'entryRelationship' => [
                    0 => ['observation' => ['value' => ['displayName' => 'Inactive']]],
                    1 => ['observation' => ['value' => ['code' => 'R-CODE', 'displayName' => 'Nausea']]],
                    2 => ['observation' => ['value' => ['code' => 'S-CODE']]],
                ],
                'performer' => ['assignedEntity' => ['time' => ['value' => '20200105']]],
            ],
        ];

        $this->parser->fetchAllergyIntoleranceObservation($entry);

        $item = $this->getFieldRecord('lists2', 1);
        $this->assertSame('allergy', $item['type']);
        $this->assertSame('obs-ext-1', $item['extension']);
        // resolveCode mock: formatted_code = "2670:RXNORM"
        $this->assertSame('2670:RXNORM', $item['list_code']);
        $this->assertSame('Codeine', $item['list_code_text']);
        $this->assertSame('RXNORM', $item['codeSystemName']);
        $this->assertSame('Inactive', $item['status']);
        $this->assertSame('R-CODE', $item['reaction']);
        $this->assertSame('Nausea', $item['reaction_text']);
        $this->assertSame('S-CODE', $item['severity_al_code']);
        $this->assertSame('20200105', $item['modified_time']);
    }

    public function testFetchAllergyIntoleranceNullFlavorWithValidDate(): void
    {
        $entry = [
            'act' => [
                'id' => ['extension' => 'null-ext'],
                'effectiveTime' => [
                    'low' => ['value' => '20210301'],
                    'high' => ['value' => '20210401'],
                ],
                'entryRelationship' => [
                    'observation' => [
                        'participant' => [
                            'participantRole' => [
                                'playingEntity' => [
                                    'code' => [
                                        'nullFlavor' => 'NA',
                                        'displayName' => 'Unknown allergen',
                                    ],
                                ],
                            ],
                        ],
                        'entryRelationship' => [
                            0 => ['observation' => ['value' => ['displayName' => 'Active']]],
                            1 => ['observation' => ['value' => []]],
                            2 => ['observation' => ['value' => []]],
                        ],
                        'performer' => [],
                    ],
                ],
            ],
        ];

        $this->parser->fetchAllergyIntoleranceObservation($entry);

        $item = $this->getFieldRecord('lists2', 1);
        $this->assertSame('allergy', $item['type']);
        $this->assertSame('null-ext', $item['extension']);
        $this->assertSame('20210301', $item['begdate']);
        $this->assertSame('', $item['list_code']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // fetchVitalSignData
    // ──────────────────────────────────────────────────────────────────────

    public function testFetchVitalSignDataPopulatesVitalSign(): void
    {
        $data = [
            'organizer' => [
                'id' => ['extension' => 'vs-ext', 'root' => 'vs-root'],
                'component' => [
                    0 => [
                        'observation' => [
                            'effectiveTime' => ['value' => '202201011030'],
                            'code' => ['code' => '8310-5'],
                            'value' => ['value' => '98.6'],
                        ],
                    ],
                    1 => [
                        'observation' => [
                            'effectiveTime' => ['value' => '202201011030'],
                            'code' => ['code' => '8462-4'],
                            'value' => ['value' => '80'],
                        ],
                    ],
                    2 => [
                        'observation' => [
                            'effectiveTime' => ['value' => '202201011030'],
                            'code' => ['code' => '8302-2'],
                            'value' => ['value' => '170'],
                        ],
                    ],
                ],
            ],
        ];

        $this->parser->fetchVitalSignData($data);

        $vital = $this->getFieldRecord('vital_sign', 1);
        $this->assertSame('vs-ext', $vital['extension']);
        $this->assertSame('vs-root', $vital['root']);
        $this->assertSame('202201011030', $vital['date']);
        $this->assertSame('98.6', $vital['temperature']);
        $this->assertSame('80', $vital['bpd']);
        $this->assertSame('170', $vital['height']);
        $this->assertSame(1, $this->getEntryId('vital_sign', 1));
    }

    public function testFetchVitalSignDataSkipsWhenMissingEffectiveTime(): void
    {
        $data = [
            'organizer' => [
                'id' => ['extension' => 'vs-ext'],
                'component' => [
                    0 => [
                        'observation' => [
                            'code' => ['code' => '8310-5'],
                            'value' => ['value' => '98.6'],
                        ],
                    ],
                ],
            ],
        ];

        $this->parser->fetchVitalSignData($data);

        $this->assertSame([], $this->getTemplateData());
    }

    // ──────────────────────────────────────────────────────────────────────
    // fetchSocialHistoryData
    // ──────────────────────────────────────────────────────────────────────

    /**
     * A Smoking Status observation (template 2.16.840.1.113883.10.20.22.4.78).
     *
     * @param array<mixed> $templateId
     * @param array<mixed> $effectiveTime
     * @return array<string, mixed>
     */
    private static function smokingStatus(array $templateId, array $effectiveTime, string $code = '8517006'): array
    {
        return [
            'observation' => [
                'templateId' => $templateId,
                'id' => ['root' => 'sh-root', 'extension' => 'sh-ext'],
                'code' => ['code' => '72166-2'],
                'effectiveTime' => $effectiveTime,
                'value' => ['code' => $code, 'displayName' => 'Ex-smoker (finding)'],
            ],
        ];
    }

    public function testFetchSocialHistoryDataStoresTheFirstSmokingStatus(): void
    {
        $this->parser->fetchSocialHistoryData(self::smokingStatus(
            ['root' => '2.16.840.1.113883.10.20.22.4.78'],
            ['low' => ['value' => '20200101'], 'high' => ['value' => '20240121']],
        ));

        $this->assertSame([
            'extension' => 'sh-ext',
            'root' => 'sh-root',
            'status' => '8517006',
            'begdate' => '20200101',
            'enddate' => '20240121',
            'value' => 'Ex-smoker (finding)',
        ], $this->getFieldRecord('social_history', 0)['smoking']);
        $this->assertSame(0, $this->getEntryId('social_history', 0));
    }

    public function testFetchSocialHistoryDataReadsEveryTemplateIdAndAPointInTime(): void
    {
        // Synthea and C-CDA R2.1: the template with and without its version, and an effectiveTime value.
        $this->parser->fetchSocialHistoryData(self::smokingStatus(
            [['root' => '2.16.840.1.113883.10.20.22.4.78'], ['root' => '2.16.840.1.113883.10.20.22.4.78', 'extension' => '2014-06-09']],
            ['value' => '20240121152238'],
        ));

        $smoking = $this->getFieldRecord('social_history', 0)['smoking'];
        $this->assertIsArray($smoking);
        $this->assertSame('8517006', $smoking['status']);
        $this->assertSame('20240121152238', $smoking['begdate']);
        $this->assertNull($smoking['enddate']);
    }

    public function testFetchSocialHistoryDataGivesASecondSmokingStatusItsOwnEntry(): void
    {
        $this->parser->fetchSocialHistoryData(self::smokingStatus(['root' => '2.16.840.1.113883.10.20.22.4.78'], ['value' => '20200101']));
        $this->parser->fetchSocialHistoryData(self::smokingStatus(['root' => '2.16.840.1.113883.10.20.22.4.78'], ['value' => '20240121'], '266919005'));

        $this->assertEntityCount('social_history', 2);
        $first = $this->getFieldRecord('social_history', 0)['smoking'];
        $second = $this->getFieldRecord('social_history', 2)['smoking'];
        $this->assertIsArray($first);
        $this->assertIsArray($second);
        $this->assertSame('8517006', $first['status']);
        $this->assertSame('266919005', $second['status']);
    }

    public function testFetchSocialHistoryDataSkipsTemplatesItDoesNotMap(): void
    {
        // Tobacco Use (4.85) and an entry with no templateId are not imported.
        $this->parser->fetchSocialHistoryData(self::smokingStatus(['root' => '2.16.840.1.113883.10.20.22.4.85'], ['value' => '20200101']));
        $this->parser->fetchSocialHistoryData(self::smokingStatus([], ['value' => '20200101']));

        $this->assertSame([], $this->getTemplateData());
    }

    public function testFetchSocialHistoryDataSkipsAnEntryWithNoValueCode(): void
    {
        $entry = self::smokingStatus(['root' => '2.16.840.1.113883.10.20.22.4.78'], ['value' => '20200101']);
        $observation = $entry['observation'];
        $this->assertIsArray($observation);
        $observation['value'] = ['nullFlavor' => 'UNK'];
        $this->parser->fetchSocialHistoryData(['observation' => $observation]);

        $this->assertSame([], $this->getTemplateData());
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    // ──────────────────────────────────────────────────────────────────────
    // fetchLabResultData
    // ──────────────────────────────────────────────────────────────────────

    /**
     * A unit that isn't UCUM comes as nullFlavor="OTH", with the number and the unit's
     * text in a translation (HL7's "Results Unit Non-UCUM" example).
     */
    public function testFetchLabResultDataReadsANonUcumQuantityFromItsTranslation(): void
    {
        $nonUcum = fn(string $number): array => [
            'nullFlavor' => 'OTH',
            'translation' => ['value' => $number, 'nullFlavor' => 'OTH', 'originalText' => 'THOUS/MCL'],
        ];
        $this->parser->fetchLabResultData($this->labResultEntry(
            ['type' => 'PQ'] + $nonUcum('152'),
            ['low' => $nonUcum('150'), 'high' => $nonUcum('400'), 'type' => 'IVL_PQ'],
        ));

        $item = $this->getFieldRecord('procedure_result', 1);
        $this->assertSame('152', $item['results_value']);
        $this->assertSame('THOUS/MCL', $item['results_unit']);
        $this->assertSame('150 400 THOUS/MCL', $item['results_range']);
    }

    public function testFetchLabResultDataReadsTheTranslationThatHasTheNumber(): void
    {
        $this->parser->fetchLabResultData($this->labResultEntry(
            [
                'type' => 'PQ',
                'nullFlavor' => 'OTH',
                'translation' => [
                    ['code' => 'THOUS/MCL', 'codeSystem' => '2.16.840.1.113883.19'],
                    ['value' => '152', 'nullFlavor' => 'OTH', 'originalText' => 'THOUS/MCL'],
                ],
            ],
            ['type' => 'IVL_PQ'],
        ));

        $item = $this->getFieldRecord('procedure_result', 1);
        $this->assertSame('152', $item['results_value']);
        $this->assertSame('THOUS/MCL', $item['results_unit']);
    }

    public function testFetchLabResultDataPrefersTheUcumQuantityOverItsTranslation(): void
    {
        $this->parser->fetchLabResultData($this->labResultEntry(
            [
                'type' => 'PQ',
                'value' => '7.2',
                'unit' => '10*3/uL',
                'translation' => ['value' => '7.2', 'code' => 'K/uL', 'codeSystem' => '2.16.840.1.113883.19'],
            ],
            ['low' => ['value' => '4.5', 'unit' => '10*3/uL'], 'high' => ['value' => '11.0', 'unit' => '10*3/uL'], 'type' => 'IVL_PQ'],
        ));

        $item = $this->getFieldRecord('procedure_result', 1);
        $this->assertSame('7.2', $item['results_value']);
        $this->assertSame('10*3/uL', $item['results_unit']);
        $this->assertSame('4.5 11.0 10*3/uL', $item['results_range']);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function roundTripUnitProvider(): array
    {
        return [
            'UCUM' => ['mg/dL'],
            'not UCUM' => ['Thous/CU.MM'],
        ];
    }

    /**
     * A lab result OpenEMR exports comes back with its number, unit and range, whether
     * or not its unit is UCUM.
     */
    #[DataProvider('roundTripUnitProvider')]
    public function testFetchLabResultDataReadsWhatOpenEmrExports(string $unit): void
    {
        $cda = (new InternalToCdaConverter())->convert(<<<XML
            <CCDA>
                <results>
                    <result>
                        <extension>RES-1</extension>
                        <test_code>58410-2</test_code>
                        <test_name>CBC panel</test_name>
                        <subtest>
                            <result_code>6690-2</result_code>
                            <result_desc>WBC</result_desc>
                            <result_value>7.2</result_value>
                            <unit>{$unit}</unit>
                            <low>4.5</low>
                            <high>11.0</high>
                        </subtest>
                    </result>
                </results>
            </CCDA>
            XML);
        $organizer = $this->findOrganizer((new XmlExtended())->fromString($cda), '2.16.840.1.113883.10.20.22.4.1');
        $this->assertNotNull($organizer, 'The export must hold a Result Organizer');

        $this->parser->fetchLabResultData(['organizer' => $organizer]);

        $item = $this->getFieldRecord('procedure_result', 1);
        $this->assertSame('7.2', $item['results_value']);
        $this->assertSame($unit, $item['results_unit']);
        $this->assertSame('4.5 11.0 ' . $unit, $item['results_range']);
    }

    /**
     * One Result Organizer entry holding one result observation.
     *
     * @param array<string, mixed> $value
     * @param array<string, mixed> $rangeValue
     * @return array<string, mixed>
     */
    private function labResultEntry(array $value, array $rangeValue): array
    {
        return [
            'organizer' => [
                'id' => ['root' => 'org-root', 'extension' => 'org-ext'],
                'code' => ['code' => '58410-2', 'codeSystemName' => 'LOINC', 'displayName' => 'CBC panel'],
                'statusCode' => ['code' => 'completed'],
                'component' => [[
                    'observation' => [
                        'id' => ['root' => 'obs-root', 'extension' => 'obs-ext'],
                        'code' => ['code' => '6690-2', 'codeSystemName' => 'LOINC', 'displayName' => 'WBC'],
                        'effectiveTime' => ['value' => '20261029'],
                        'value' => $value,
                        'referenceRange' => ['observationRange' => ['value' => $rangeValue]],
                    ],
                ]],
            ],
        ];
    }

    /**
     * The first organizer in a parsed document carrying the given templateId.
     *
     * @return array<mixed>|null
     */
    private function findOrganizer(mixed $node, string $templateId): ?array
    {
        if (!is_array($node)) {
            return null;
        }
        $organizer = $node['organizer'] ?? null;
        if (is_array($organizer)) {
            $ids = $organizer['templateId'] ?? [];
            $ids = is_array($ids) && array_is_list($ids) ? $ids : [$ids];
            foreach ($ids as $id) {
                if (is_array($id) && ($id['root'] ?? null) === $templateId) {
                    return $organizer;
                }
            }
        }
        foreach ($node as $child) {
            $found = $this->findOrganizer($child, $templateId);
            if ($found !== null) {
                return $found;
            }
        }
        return null;
    }

    private function setProperty(string $name, mixed $value): void
    {
        $prop = $this->reflection->getProperty($name);
        $prop->setValue($this->parser, $value);
    }

    /**
     * @return array<string, mixed>
     */
    private function getTemplateData(): array
    {
        $prop = $this->reflection->getProperty('templateData');
        $value = $prop->getValue($this->parser);
        $this->assertIsArray($value);
        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * Get a specific entity record from field_name_value_array.
     *
     * @return array<string, mixed>
     */
    private function getFieldRecord(string $entity, int $index): array
    {
        $td = $this->getTemplateData();
        $this->assertArrayHasKey('field_name_value_array', $td);
        $fields = $td['field_name_value_array'];
        $this->assertIsArray($fields);
        $this->assertArrayHasKey($entity, $fields);
        $entityData = $fields[$entity];
        $this->assertIsArray($entityData);
        $this->assertArrayHasKey($index, $entityData);
        $record = $entityData[$index];
        $this->assertIsArray($record);
        /** @var array<string, mixed> $record */
        return $record;
    }

    /**
     * Get the entry_identification_array value for an entity at index.
     */
    private function getEntryId(string $entity, int $index): int
    {
        $td = $this->getTemplateData();
        $this->assertArrayHasKey('entry_identification_array', $td);
        $ids = $td['entry_identification_array'];
        $this->assertIsArray($ids);
        $this->assertArrayHasKey($entity, $ids);
        $entityIds = $ids[$entity];
        $this->assertIsArray($entityIds);
        $this->assertArrayHasKey($index, $entityIds);
        $value = $entityIds[$index];
        $this->assertIsInt($value);
        return $value;
    }

    /**
     * Assert that the field_name_value_array has a specific count for an entity.
     */
    private function assertEntityCount(string $entity, int $expectedCount): void
    {
        $td = $this->getTemplateData();
        $this->assertArrayHasKey('field_name_value_array', $td);
        $fields = $td['field_name_value_array'];
        $this->assertIsArray($fields);
        $this->assertArrayHasKey($entity, $fields);
        $entityData = $fields[$entity];
        $this->assertIsArray($entityData);
        $this->assertCount($expectedCount, $entityData);
    }
}
