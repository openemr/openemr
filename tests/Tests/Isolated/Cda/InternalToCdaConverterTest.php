<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda;

use DOMDocument;
use DOMXPath;
use OpenEMR\Cda\InternalToCdaConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InternalToCdaConverterTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../../data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/';

    private ?string $actualOutput = null;
    private ?string $expectedOutput = null;

    public function testConvertProducesValidCda(): void
    {
        [$actual, $expected] = $this->getConvertedAndExpected();
        $this->assertCdaEquals($expected, $actual);
    }

    /**
     * The Functional Status Organizer (4.66) must contain the Functional Status
     * Observation (4.67) and Self Care Activities (4.128) as two separate member
     * observations. Regression guard for the 4.128 templateId being incorrectly
     * nested inside the 4.67 observation.
     */
    public function testFunctionalStatusSelfCareIsSeparateObservation(): void
    {
        $input = <<<'XML'
            <CCDA>
                <functional_status>
                    <item>
                        <extension>FS-1</extension>
                        <date>2021-07-23</date>
                        <code>3298001</code>
                        <code_text>Amnestic disorder</code_text>
                        <code_type>SNOMED CT</code_type>
                    </item>
                </functional_status>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $organizers = $xpath->query("//hl7:organizer[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.66']]");
        self::assertNotFalse($organizers, 'Organizer query must be valid');
        self::assertSame(1, $organizers->length, 'Expected exactly one Functional Status Organizer');
        $organizer = $organizers->item(0);
        self::assertInstanceOf(\DOMElement::class, $organizer, 'Organizer node must be an element');

        $allObs = $xpath->query('hl7:component/hl7:observation', $organizer);
        self::assertNotFalse($allObs, 'Component observation query must be valid');
        self::assertSame(2, $allObs->length, 'Organizer must contain two separate member observations');

        $funcObs = $xpath->query("hl7:component/hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.67']]", $organizer);
        self::assertNotFalse($funcObs, 'Functional Status Observation query must be valid');
        self::assertSame(1, $funcObs->length, 'Functional Status Observation (4.67) must be its own component');

        $selfCareObs = $xpath->query("hl7:component/hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.128']]", $organizer);
        self::assertNotFalse($selfCareObs, 'Self Care Activities query must be valid');
        self::assertSame(1, $selfCareObs->length, 'Self Care Activities (4.128) must be its own component');

        $misplaced = $xpath->query(
            "hl7:component/hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.67']]"
            . "/hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.128']",
            $organizer
        );
        self::assertNotFalse($misplaced, 'Misplaced-templateId query must be valid');
        self::assertSame(0, $misplaced->length, 'Self Care Activities templateId must not be nested in the Functional Status Observation');
    }

    /**
     * Input-contract regression guard.
     *
     * The converter reads the internal /CCDA/ XML by literal xpath. Several of
     * those paths did not match the document the request model actually emits
     * (e.g. /CCDA/patient/occupation/... where the document has /CCDA/occupation/...,
     * and /CCDA/goals/goal where the document uses /CCDA/goals/item). Every one of
     * those renderers returns early on a missing path, so the demo fixtures -- whose
     * SDOH, goals, occupation and functional status elements are empty -- passed
     * while the sections were silently dropped. The failures only surfaced in ONC
     * scenario testing.
     *
     * This fixture is a full scenario patient with those elements populated. Each
     * assertion below corresponds to a path that was previously wrong; a renamed or
     * re-parented element in the internal XML will fail here rather than in a
     * certification run.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function scenarioTemplateProvider(): array
    {
        return [
            'goal observation' => ['2.16.840.1.113883.10.20.22.4.121', '/CCDA/goals/item'],
            'basic occupation observation' => ['2.16.840.1.113883.10.20.22.4.503', '/CCDA/occupation/occupation_code'],
            'disability status observation' => ['2.16.840.1.113883.10.20.22.4.505', '/CCDA/sdoh_data/disability_assessment'],
        ];
    }

    #[DataProvider('scenarioTemplateProvider')]
    public function testScenarioInputProducesExpectedTemplates(string $templateId, string $sourcePath): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . 'ccda-input-scenario-uscdi.xml');
        self::assertIsString($input, 'Scenario fixture must be readable');

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $nodes = $xpath->query("//hl7:templateId[@root='" . $templateId . "']");
        self::assertNotFalse($nodes, 'templateId query must be valid');
        self::assertGreaterThan(
            0,
            $nodes->length,
            $templateId . ' missing; check the converter xpath against ' . $sourcePath
        );
    }

    /**
     * @return array<string, array{0: string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function scenarioEntryTemplateProvider(): array
    {
        return [
            'Care Team Organizer' => ['2.16.840.1.113883.10.20.22.4.500'],
            'Care Team Member Act' => ['2.16.840.1.113883.10.20.22.4.500.1'],
            'Note Activity' => ['2.16.840.1.113883.10.20.22.4.202'],
            'Health Concern' => ['2.16.840.1.113883.10.20.22.4.132'],
            'Planned Encounter' => ['2.16.840.1.113883.10.20.22.4.40'],
            'Planned Procedure' => ['2.16.840.1.113883.10.20.22.4.41'],
            'Planned Substance Administration' => ['2.16.840.1.113883.10.20.22.4.42'],
            'Planned Observation' => ['2.16.840.1.113883.10.20.22.4.44'],
            'Mental Status Observation' => ['2.16.840.1.113883.10.20.22.4.74'],
            'Smoking Status Observation' => ['2.16.840.1.113883.10.20.22.4.78'],
            'Hunger Vital Signs' => ['2.16.840.1.113883.10.20.22.4.69'],
            'Occupation Industry Observation' => ['2.16.840.1.113883.10.20.22.4.504'],
            'Encounter Diagnosis' => ['2.16.840.1.113883.10.20.22.4.80'],
            'Product Instance' => ['2.16.840.1.113883.10.20.22.4.37'],
            'Indication' => ['2.16.840.1.113883.10.20.22.4.19'],
            'Notes Section' => ['2.16.840.1.113883.10.20.22.2.65'],
        ];
    }

    /**
     * Entry templates reached only by the scenario fixture.
     *
     * The golden fixtures exercise most of the converter, but seventeen entry
     * templates appear in no expected-output fixture and in no targeted test, so
     * a renderer could stop emitting one and nothing would fail. Each template
     * below has source data in the scenario fixture, which is the patient that
     * passes the ONC scenarios, so each should be emitted.
     *
     * This is a presence guard, not a shape assertion: it catches a renderer
     * going silent, which is the failure mode that produced the goals,
     * occupation, pregnancy and disability findings.
     */
    #[DataProvider('scenarioEntryTemplateProvider')]
    public function testScenarioFixtureEmitsEntryTemplate(string $templateId): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . 'ccda-input-scenario-uscdi.xml');
        self::assertIsString($input, 'Scenario fixture must be readable');

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $nodes = $xpath->query("//hl7:templateId[@root='" . $templateId . "']");
        self::assertNotFalse($nodes, 'templateId query must be valid');
        self::assertGreaterThan(0, $nodes->length, $templateId . ' is not emitted from the scenario fixture');
    }

    /**
     * Advance Directive Observation (4.48).
     *
     * The scenario fixture carries an empty <advance_directives/>, so this
     * renderer is reached by no fixture. Driven directly here instead.
     */
    public function testAdvanceDirectiveObservationIsEmitted(): void
    {
        $input = <<<'XML'
            <CCDA>
                <patient>
                    <fname>Happy</fname>
                    <lname>Kid</lname>
                </patient>
                <advance_directives>
                    <directive>
                        <extension>AD-1</extension>
                        <observation>
                            <code>75320-2</code>
                            <code_system>2.16.840.1.113883.6.1</code_system>
                            <display>Advance directive</display>
                        </observation>
                    </directive>
                </advance_directives>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $observation = $xpath->query(
            "//hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.48']]"
        );
        self::assertNotFalse($observation, 'Advance directive query must be valid');
        self::assertSame(1, $observation->length, 'Advance Directive Observation is emitted');

        $node = $observation->item(0);
        self::assertInstanceOf(\DOMElement::class, $node, 'Observation must be an element');

        $code = $xpath->query("hl7:code[@code='75320-2']", $node);
        self::assertNotFalse($code, 'Code query must be valid');
        self::assertSame(1, $code->length, 'The observation code comes from the directive');
    }

    /**
     * Immunization Refusal Reason (4.53).
     *
     * No fixture has a refused immunization, so this renderer is reached by no
     * fixture. It is called only when the immunization status is "refused", and
     * then returns early unless a refusal reason code or name is present.
     */
    public function testImmunizationRefusalReasonIsEmitted(): void
    {
        $input = <<<'XML'
            <CCDA>
                <patient>
                    <fname>Happy</fname>
                    <lname>Kid</lname>
                </patient>
                <immunizations>
                    <immunization>
                        <extension>IMM-1</extension>
                        <cvx_code>140</cvx_code>
                        <code_text>Influenza</code_text>
                        <administered_on>2015-07-22</administered_on>
                        <status>refused</status>
                        <refusal_reason_code>PATOBJ</refusal_reason_code>
                        <refusal_reason>Patient objection</refusal_reason>
                    </immunization>
                </immunizations>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $observation = $xpath->query(
            "//hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.53']]"
        );
        self::assertNotFalse($observation, 'Refusal reason query must be valid');
        self::assertSame(1, $observation->length, 'Immunization Refusal Reason is emitted');

        $node = $observation->item(0);
        self::assertInstanceOf(\DOMElement::class, $node, 'Observation must be an element');

        $code = $xpath->query("hl7:code[@code='PATOBJ']", $node);
        self::assertNotFalse($code, 'Code query must be valid');
        self::assertSame(1, $code->length, 'The refusal reason code is carried through');
    }

    /**
     * Document-wide structural invariants, asserted as rules rather than per
     * section.
     *
     * Both classes below reached ONC validation as findings before being caught
     * here: a narrative table with a thead and no tbody
     * (cvc-complex-type.2.4.b), and a section with no entries and no nullFlavor
     * (entries-required conformance). Asserting them over the whole document
     * catches the next renderer that grows the same defect.
     *
     * A section that trips the entries rule is either a real conformance bug or
     * a narrative-only section missing from the exemption list below. Check the
     * IG for that section before adding it to the list.
     */
    public function testScenarioDocumentMeetsStructuralInvariants(): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . 'ccda-input-scenario-uscdi.xml');
        self::assertIsString($input, 'Scenario fixture must be readable');

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $tables = $xpath->query('//hl7:table');
        self::assertNotFalse($tables, 'Table query must be valid');
        foreach ($tables as $table) {
            self::assertInstanceOf(\DOMElement::class, $table, 'Table must be an element');
            $bodies = $xpath->query('hl7:tbody | hl7:tfoot', $table);
            self::assertNotFalse($bodies, 'Table body query must be valid');
            self::assertGreaterThan(
                0,
                $bodies->length,
                'Every narrative table needs a tbody or tfoot, never a thead alone'
            );
        }

        $sections = $xpath->query('//hl7:structuredBody/hl7:component/hl7:section');
        self::assertNotFalse($sections, 'Section query must be valid');
        self::assertGreaterThan(0, $sections->length, 'The document has sections');

        // Narrative-only sections carry no entries by design, so the rule below
        // does not apply to them. Assessment Section is the IG's conclusions
        // narrative and defines no entry templates at all.
        $narrativeOnly = [
            '2.16.840.1.113883.10.20.22.2.8',
        ];

        foreach ($sections as $section) {
            self::assertInstanceOf(\DOMElement::class, $section, 'Section must be an element');
            if ($section->hasAttribute('nullFlavor')) {
                continue;
            }

            $isNarrativeOnly = false;
            foreach ($narrativeOnly as $narrativeOnlyId) {
                $match = $xpath->query("hl7:templateId[@root='" . $narrativeOnlyId . "']", $section);
                self::assertNotFalse($match, 'Narrative-only templateId query must be valid');
                if ($match->length > 0) {
                    $isNarrativeOnly = true;
                    break;
                }
            }
            if ($isNarrativeOnly) {
                continue;
            }

            $entries = $xpath->query('hl7:entry', $section);
            self::assertNotFalse($entries, 'Entry query must be valid');

            $title = $xpath->query('hl7:title', $section);
            self::assertNotFalse($title, 'Title query must be valid');
            $titleNode = $title->item(0);
            $label = $titleNode instanceof \DOMElement ? $titleNode->textContent : 'untitled section';

            self::assertGreaterThan(
                0,
                $entries->length,
                'Section "' . $label . '" has no entries and no nullFlavor'
            );
        }
    }

    /**
     * providerOrganization name and telecom are SHALL 1..* (CONF:5419,
     * CONF:5420). Node omits the telecom when the facility has no phone and
     * emits an empty <name/>; both fail validation.
     */
    public function testProviderOrganizationCarriesNameAndTelecom(): void
    {
        $input = <<<'XML'
            <CCDA>
                <patient>
                    <fname>Happy</fname>
                    <lname>Kid</lname>
                </patient>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $base = '/hl7:ClinicalDocument/hl7:recordTarget/hl7:patientRole/hl7:providerOrganization';

        foreach (['name', 'telecom'] as $part) {
            $node = $xpath->query($base . '/hl7:' . $part);
            self::assertNotFalse($node, $part . ' query must be valid');
            self::assertSame(1, $node->length, 'providerOrganization must carry a ' . $part);

            $element = $node->item(0);
            self::assertInstanceOf(\DOMElement::class, $element, $part . ' must be an element');
            self::assertSame(
                'UNK',
                $element->getAttribute('nullFlavor'),
                'An unknown ' . $part . ' is nullFlavor, never omitted or empty'
            );
        }
    }

    /**
     * representedCustodianOrganization name is SHALL 1..1 and telecom is
     * SHALL 1..*. An empty <name/> fails validateST and a bare "tel:" with no
     * number is not a usable TEL value, so both carry nullFlavor when unknown.
     */
    public function testCustodianOrganizationCarriesNameAndTelecom(): void
    {
        $input = <<<'XML'
            <CCDA>
                <patient>
                    <fname>Happy</fname>
                    <lname>Kid</lname>
                </patient>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $base = '/hl7:ClinicalDocument/hl7:custodian/hl7:assignedCustodian'
            . '/hl7:representedCustodianOrganization';

        foreach (['name', 'telecom'] as $part) {
            $node = $xpath->query($base . '/hl7:' . $part);
            self::assertNotFalse($node, $part . ' query must be valid');
            self::assertSame(1, $node->length, 'The custodian organization carries a ' . $part);

            $element = $node->item(0);
            self::assertInstanceOf(\DOMElement::class, $element, $part . ' must be an element');
            self::assertSame(
                'UNK',
                $element->getAttribute('nullFlavor'),
                'An unknown custodian ' . $part . ' is nullFlavor, never empty'
            );
        }
    }

    /**
     * A clinical statement never carries a bare UUID root as its id.
     *
     * Five renderers hardcoded a UUID root and emitted it with no extension -
     * allergy reactions, functional status, self care, mental status and the
     * notes section - so every element of that kind in the document carried the
     * same id. A root-only II means "this exact identifier".
     *
     * Scoped deliberately, after three over-broad versions of this test:
     *   - entity identifiers are excluded. A provider NPI or an organization OID
     *     repeats because it names one real-world thing, and a root-only OID is
     *     a perfectly good identifier.
     *   - OID roots are excluded for the same reason; only generated UUID roots
     *     need a local identifier alongside them.
     *   - global uniqueness is not asserted. The same clinical statement
     *     legitimately appears under more than one section with the same id.
     */
    public function testClinicalStatementsHaveNoBareUuidRootId(): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . 'ccda-input-scenario-uscdi.xml');
        self::assertIsString($input, 'Scenario fixture must be readable');

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $statements = ['act', 'observation', 'procedure', 'substanceAdministration', 'encounter', 'organizer', 'supply', 'section'];
        $query = implode(' | ', array_map(
            static fn(string $name): string => '//hl7:' . $name . '/hl7:id[@root][not(@extension)]',
            $statements
        ));

        $ids = $xpath->query($query);
        self::assertNotFalse($ids, 'Id query must be valid');

        $uuid = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';
        $byRoot = [];
        foreach ($ids as $id) {
            self::assertInstanceOf(\DOMElement::class, $id, 'Id must be an element');
            $root = $id->getAttribute('root');
            if (preg_match($uuid, $root) !== 1) {
                continue;
            }
            $parent = $id->parentNode;
            $byRoot[$root][] = $parent instanceof \DOMElement ? $parent->localName : 'unknown';
        }

        foreach ($byRoot as $root => $owners) {
            self::assertCount(
                1,
                $owners,
                'UUID root ' . $root . ' is emitted ' . count($owners) . ' times with no extension (on '
                . implode(', ', array_unique($owners))
                . '); a hardcoded UUID root needs a local identifier alongside it'
            );
        }
    }

    /**
     * Every II/@root is an OID or a UUID.
     *
     * The Notes Section id was ported from node as
     * "16C8G888-10D9-23E6-H141-0080055B0002", which contains G and H and is
     * therefore neither. No validator checks @root syntax, so this guards a
     * class of defect nothing else would catch.
     */
    public function testEveryIdRootIsAnOidOrUuid(): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . 'ccda-input-scenario-uscdi.xml');
        self::assertIsString($input, 'Scenario fixture must be readable');

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $nodes = $xpath->query('//*[@root]');
        self::assertNotFalse($nodes, 'Root query must be valid');
        self::assertGreaterThan(0, $nodes->length, 'The document carries id roots');

        $oid = '/^[0-2](\.(0|[1-9]\d*))+$/';
        $uuid = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';

        foreach ($nodes as $node) {
            self::assertInstanceOf(\DOMElement::class, $node, 'Node must be an element');
            $root = $node->getAttribute('root');
            self::assertTrue(
                preg_match($oid, $root) === 1 || preg_match($uuid, $root) === 1,
                'II/@root must be an OID or a UUID, got "' . $root . '" on <' . $node->localName . '>'
            );
        }
    }

    /**
     * A narrative table with a thead and no tbody is schema-invalid
     * (cvc-complex-type.2.4.b). The Social History section can be non-empty on
     * its USCDI observations alone while carrying no smoking or tobacco history
     * element, which is the only kind of row its table holds.
     */
    public function testSocialHistoryOmitsEmptyNarrativeTable(): void
    {
        $input = <<<'XML'
            <CCDA>
                <patient>
                    <fname>Happy</fname>
                    <lname>Kid</lname>
                    <tribal_code>65</tribal_code>
                    <tribal_title>Coquille Indian Tribe</tribal_title>
                </patient>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $tables = $xpath->query(
            "//hl7:section[hl7:templateId[@root='2.16.840.1.113883.10.20.22.2.17']]//hl7:table"
        );
        self::assertNotFalse($tables, 'Table query must be valid');

        foreach ($tables as $table) {
            self::assertInstanceOf(\DOMElement::class, $table, 'Table must be an element');
            $bodies = $xpath->query('hl7:tbody | hl7:tfoot', $table);
            self::assertNotFalse($bodies, 'Body query must be valid');
            self::assertGreaterThan(
                0,
                $bodies->length,
                'A narrative table must have a tbody or tfoot, never a thead alone'
            );
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function entriesRequiredSectionProvider(): array
    {
        return [
            'Vital Signs' => ['2.16.840.1.113883.10.20.22.2.4.1', 'Vital Signs'],
            'Problems' => ['2.16.840.1.113883.10.20.22.2.5.1', 'Problem List'],
            'Encounters' => ['2.16.840.1.113883.10.20.22.2.22.1', 'Encounters'],
        ];
    }

    /**
     * An entries-required section with no data must carry nullFlavor and must
     * not emit a narrative table. Without the nullFlavor it fails the
     * entries-required conformance, and the table would carry a thead with no
     * tbody (cvc-complex-type.2.4.b).
     */
    #[DataProvider('entriesRequiredSectionProvider')]
    public function testEmptyEntriesRequiredSectionIsNullFlavored(string $templateId, string $title): void
    {
        $input = <<<'XML'
            <CCDA>
                <patient>
                    <fname>Happy</fname>
                    <lname>Kid</lname>
                </patient>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $sections = $xpath->query("//hl7:section[hl7:templateId[@root='" . $templateId . "']]");
        self::assertNotFalse($sections, 'Section query must be valid');
        self::assertSame(1, $sections->length, $title . ' section is present');

        $section = $sections->item(0);
        self::assertInstanceOf(\DOMElement::class, $section, 'Section must be an element');
        self::assertSame(
            'NI',
            $section->getAttribute('nullFlavor'),
            $title . ' carries nullFlavor when it has no entries'
        );

        $tables = $xpath->query('hl7:text//hl7:table', $section);
        self::assertNotFalse($tables, 'Table query must be valid');
        self::assertSame(0, $tables->length, $title . ' emits no narrative table when empty');
    }

    /**
     * The Goal Observation code must carry a codeSystem OID.
     *
     * The internal XML names the code system (code_type, e.g. "LOINC") without
     * an OID. Node resolves it from the name in translate.js; the converter
     * emitted codeSystemName alone, so the CD had no codeSystem and the ONC
     * scenario code-system comparison failed.
     */
    public function testGoalObservationCodeCarriesCodeSystem(): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . 'ccda-input-scenario-uscdi.xml');
        self::assertIsString($input, 'Scenario fixture must be readable');

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $codes = $xpath->query(
            "//hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.121']]/hl7:code"
        );
        self::assertNotFalse($codes, 'Goal code query must be valid');
        self::assertGreaterThan(0, $codes->length, 'The scenario fixture carries goal observations');

        foreach ($codes as $code) {
            self::assertInstanceOf(\DOMElement::class, $code, 'Goal code must be an element');
            if ($code->hasAttribute('nullFlavor')) {
                continue;
            }
            self::assertSame(
                '2.16.840.1.113883.6.1',
                $code->getAttribute('codeSystem'),
                'LOINC goal codes resolve code_type to the LOINC OID'
            );
            self::assertSame(
                'LOINC',
                $code->getAttribute('codeSystemName'),
                'codeSystemName accompanies the resolved OID'
            );
        }
    }

    /**
     * Related persons must appear as header participants.
     *
     * Node merges patient.related_persons.participant into the header
     * participant list alongside document_participants.participant
     * (serveccda.js populateHeader). The converter read only the latter, so
     * related persons were dropped and the ONC scenarios reported their
     * relationship codes as missing.
     */
    public function testRelatedPersonsAppearAsHeaderParticipants(): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . 'ccda-input-scenario-uscdi.xml');
        self::assertIsString($input, 'Scenario fixture must be readable');

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        foreach (['GRPRN' => 'Holler', 'SPS' => 'Newman'] as $relationship => $family) {
            $entity = $xpath->query(
                "/hl7:ClinicalDocument/hl7:participant/hl7:associatedEntity"
                . "[hl7:code/@code='" . $relationship . "']"
            );
            self::assertNotFalse($entity, 'Participant query must be valid');
            self::assertSame(
                1,
                $entity->length,
                'Related person with relationship ' . $relationship . ' must be a header participant'
            );

            $node = $entity->item(0);
            self::assertInstanceOf(\DOMElement::class, $node, 'associatedEntity must be an element');
            self::assertSame(
                'PRS',
                $node->getAttribute('classCode'),
                'Related person associatedEntity carries class_code from the input'
            );

            $code = $xpath->query("hl7:code", $node);
            self::assertNotFalse($code, 'Code query must be valid');
            $codeEl = $code->item(0);
            self::assertInstanceOf(\DOMElement::class, $codeEl, 'Code must be an element');
            self::assertSame(
                '2.16.840.1.113883.1.11.19563',
                $codeEl->getAttribute('codeSystem'),
                'Relationship code uses the Personal Relationship Role Type value set'
            );

            $name = $xpath->query("hl7:associatedPerson/hl7:name/hl7:family", $node);
            self::assertNotFalse($name, 'Name query must be valid');
            self::assertSame(1, $name->length, 'Related person carries a family name');
            $familyNode = $name->item(0);
            self::assertInstanceOf(\DOMElement::class, $familyNode, 'Family name must be an element');
            self::assertSame($family, $familyNode->textContent, 'Family name matches the input');
        }
    }

    /**
     * Tribal affiliation must carry the numeric TribalEntityUS code, not the
     * internal slug. The internal XML holds tribal_code ("65"), tribal_title
     * ("Coquille Indian Tribe") and tribal ("coquille") as siblings; only the
     * first is a valid @code.
     */
    public function testTribalAffiliationUsesCodedValue(): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . 'ccda-input-scenario-uscdi.xml');
        self::assertIsString($input, 'Scenario fixture must be readable');

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $value = $xpath->query(
            "//hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.506']]/hl7:value"
        );
        self::assertNotFalse($value, 'Tribal value query must be valid');
        self::assertSame(1, $value->length, 'Tribal Affiliation Observation must emit one value');

        $element = $value->item(0);
        self::assertInstanceOf(\DOMElement::class, $element, 'Value node must be an element');
        self::assertSame('65', $element->getAttribute('code'), 'Tribal @code is the TribalEntityUS code');
        self::assertSame(
            'Coquille Indian Tribe',
            $element->getAttribute('displayName'),
            'Tribal @displayName is the tribe title, not the internal slug'
        );
    }

    /**
     * Disability Status Observation (4.505) belongs in the Functional Status
     * section, not Social History. The ONC Edge Test Tool enforces this template
     * under Functional Status content validation for the USCDI v3 b(1) ToC
     * scenarios, and the Node service renders it there via the
     * functionalStatusSection "disability_status" entry.
     *
     * The Social History Observation (4.38) shape previously used for disability
     * has no Node counterpart -- disabilityAssessmentObservation is exported but
     * never referenced by a section tree -- so it must not appear.
     */
    public function testDisabilityStatusObservationIsInFunctionalStatusSection(): void
    {
        $input = <<<'XML'
            <CCDA>
                <sdoh_data>
                    <disability_assessment>
                            <overall_status>
                                <code>89571-4</code>
                                <code_system>2.16.840.1.113883.6.1</code_system>
                                <code_system_name>LOINC</code_system_name>
                                <display>Disability Status [CUBS]</display>
                                <answer_code>LA29243-5</answer_code>
                                <answer_display>I'm Vulnerable</answer_display>
                            </overall_status>
                            <disability_questions>
                                <question>
                                    <code>69858-3</code>
                                    <display>Hearing difficulty</display>
                                    <answer_code>LA33-6</answer_code>
                                    <answer_display>No</answer_display>
                                </question>
                            </disability_questions>
                    </disability_assessment>
                </sdoh_data>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $functionalSection = "//hl7:section[hl7:templateId[@root='2.16.840.1.113883.10.20.22.2.14']]";
        $socialSection = "//hl7:section[hl7:templateId[@root='2.16.840.1.113883.10.20.22.2.17']]";

        $inFunctional = $xpath->query(
            $functionalSection . "//hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.505']]"
        );
        self::assertNotFalse($inFunctional, 'Disability Status query must be valid');
        self::assertSame(1, $inFunctional->length, 'Disability Status Observation must be in the Functional Status section');

        $inSocial = $xpath->query(
            $socialSection . "//hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.505']]"
        );
        self::assertNotFalse($inSocial, 'Social History query must be valid');
        self::assertSame(0, $inSocial->length, 'Disability Status Observation must not appear in Social History');

        $observation = $inFunctional->item(0);
        self::assertInstanceOf(\DOMElement::class, $observation, 'Observation node must be an element');

        $versioned = $xpath->query(
            "hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.505'][@extension='2023-05-01']",
            $observation
        );
        self::assertNotFalse($versioned, 'Versioned templateId query must be valid');
        self::assertSame(1, $versioned->length, 'Disability Status Observation carries the 2023-05-01 extension');

        $value = $xpath->query("hl7:value[@code='LA29243-5']", $observation);
        self::assertNotFalse($value, 'Value query must be valid');
        self::assertSame(1, $value->length, 'value carries the LOINC answer set code');

        $question = $xpath->query(
            "hl7:entryRelationship[@typeCode='COMP']"
            . "/hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.86']]",
            $observation
        );
        self::assertNotFalse($question, 'Question observation query must be valid');
        self::assertSame(1, $question->length, 'Each disability question is a COMP entryRelationship');

        // id is SHALL 1..* on both templates (CONF:16724). The fixture has no
        // facility OID, which previously suppressed the id entirely.
        $obsId = $xpath->query("hl7:id", $observation);
        self::assertNotFalse($obsId, 'Observation id query must be valid');
        self::assertGreaterThan(0, $obsId->length, 'Disability Status Observation must carry an id');

        $supporting = $question->item(0);
        self::assertInstanceOf(\DOMElement::class, $supporting, 'Supporting observation must be an element');
        $supportingId = $xpath->query("hl7:id", $supporting);
        self::assertNotFalse($supportingId, 'Supporting id query must be valid');
        self::assertGreaterThan(
            0,
            $supportingId->length,
            'Assessment Scale Supporting Observation must carry an id'
        );

        $plain = $xpath->query(
            "hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.505'][not(@extension)]",
            $observation
        );
        self::assertNotFalse($plain, 'Plain templateId query must be valid');
        self::assertSame(1, $plain->length, 'The unversioned templateId must not be duplicated');
    }

    /**
     * Document provenance is the encounter the document is about.
     *
     * getEncounterHistory() sorts ORDER BY fe.date ascending, so the first
     * entry is the patient's OLDEST encounter. Taking it by position stamped
     * every document with that date; the scenarios only passed because their
     * earliest encounter happened to be the one being summarised.
     *
     * /CCDA/patient/encounter names the requested encounter, and each list
     * entry carries its own encounter_id, so the two are matched directly.
     */
    public function testDocumentAuthorTimeMatchesTheDocumentEncounter(): void
    {
        $input = <<<'XML'
            <CCDA>
                <created_time_timezone>20261005123026-0400</created_time_timezone>
                <encounter_list>
                    <encounter>
                        <encounter_id>2</encounter_id>
                        <date>2015-07-22 00:00:00-0400</date>
                    </encounter>
                    <encounter>
                        <encounter_id>9</encounter_id>
                        <date>2026-07-08 22:11:00-0400</date>
                    </encounter>
                </encounter_list>
                <patient>
                    <fname>Jeremy</fname>
                    <lname>Bates</lname>
                    <encounter>9</encounter>
                </patient>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $time = $xpath->query('/hl7:ClinicalDocument/hl7:author/hl7:time');
        self::assertNotFalse($time, 'Author time query must be valid');
        self::assertSame(1, $time->length, 'Document author carries one time');

        $element = $time->item(0);
        self::assertInstanceOf(\DOMElement::class, $element, 'Time must be an element');
        self::assertStringStartsWith(
            '20260708',
            $element->getAttribute('value'),
            'Provenance follows the requested encounter, not the first in the list'
        );
    }

    /**
     * A patient-level document names no encounter, so the most recent one is
     * used. The list is sorted ascending, so that is the last entry.
     */
    public function testDocumentAuthorTimeUsesMostRecentEncounterWhenPatientLevel(): void
    {
        $input = <<<'XML'
            <CCDA>
                <created_time_timezone>20261005123026-0400</created_time_timezone>
                <encounter_list>
                    <encounter>
                        <encounter_id>2</encounter_id>
                        <date>2015-07-22 00:00:00-0400</date>
                    </encounter>
                    <encounter>
                        <encounter_id>9</encounter_id>
                        <date>2026-07-08 22:11:00-0400</date>
                    </encounter>
                </encounter_list>
                <patient>
                    <fname>Jeremy</fname>
                    <lname>Bates</lname>
                    <encounter></encounter>
                </patient>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $time = $xpath->query('/hl7:ClinicalDocument/hl7:author/hl7:time');
        self::assertNotFalse($time, 'Author time query must be valid');
        $element = $time->item(0);
        self::assertInstanceOf(\DOMElement::class, $element, 'Time must be an element');
        self::assertStringStartsWith(
            '20260708',
            $element->getAttribute('value'),
            'A patient-level document uses the most recent encounter, not the oldest'
        );
    }

    /**
     * With no encounter the generation timestamp remains the fallback, so a
     * document without an encounter still carries a provenance time.
     */
    public function testDocumentAuthorTimeFallsBackToCreatedTime(): void
    {
        $input = <<<'XML'
            <CCDA>
                <created_time_timezone>20261003123026-0400</created_time_timezone>
                <patient>
                    <fname>Jeremy</fname>
                    <lname>Bates</lname>
                </patient>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $time = $xpath->query('/hl7:ClinicalDocument/hl7:author/hl7:time');
        self::assertNotFalse($time, 'Author time query must be valid');
        self::assertSame(1, $time->length, 'Document author carries one time');

        $element = $time->item(0);
        self::assertInstanceOf(\DOMElement::class, $element, 'Time must be an element');
        self::assertStringStartsWith(
            '20261003',
            $element->getAttribute('value'),
            'Without an encounter the generation timestamp is used'
        );
    }

    /**
     * The patient name must carry prefix and suffix when the record has them.
     * createPersonName() had no suffix parameter, so a patient suffix was
     * dropped and the ONC scenario reported it missing.
     */
    public function testPatientNameIncludesPrefixAndSuffix(): void
    {
        $input = <<<'XML'
            <CCDA>
                <patient>
                    <fname>Jeremy</fname>
                    <mname>V</mname>
                    <lname>Bates</lname>
                    <prefix>Mr.</prefix>
                    <suffix>Jr.</suffix>
                </patient>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $base = "/hl7:ClinicalDocument/hl7:recordTarget/hl7:patientRole/hl7:patient/hl7:name";

        foreach (['prefix' => 'Mr.', 'suffix' => 'Jr.', 'family' => 'Bates'] as $part => $expected) {
            $node = $xpath->query($base . "/hl7:" . $part);
            self::assertNotFalse($node, $part . ' query must be valid');
            self::assertSame(1, $node->length, 'Patient name must carry a ' . $part);
            $partNode = $node->item(0);
            self::assertInstanceOf(\DOMElement::class, $partNode, $part . ' must be an element');
            self::assertSame($expected, $partNode->textContent, $part . ' matches the input');
        }
    }

    /**
     * A procedure with a code but no code type must omit the codeSystemName
     * attribute rather than emit codeSystemName="", which is an empty st value
     * the C-CDA IG rejects. Mirrors Node's translate.code omit-empty behavior.
     */
    public function testProcedureCodeOmitsEmptyCodeSystemName(): void
    {
        $input = <<<'XML'
            <CCDA>
                <procedures>
                    <procedure>
                        <extension>PROC-1</extension>
                        <date>2021-07-23</date>
                        <code>73761001</code>
                        <description>Colonoscopy</description>
                        <code_type></code_type>
                    </procedure>
                </procedures>
            </CCDA>
            XML;

        $code = $this->firstProcedureCode($input);
        self::assertSame('73761001', $code->getAttribute('code'), 'Code value must be preserved');
        self::assertFalse($code->hasAttribute('codeSystemName'), 'Empty code type must not emit codeSystemName');
        self::assertFalse($code->hasAttribute('codeSystem'), 'Empty code type must not emit codeSystem');
        self::assertFalse($code->hasAttribute('nullFlavor'), 'A present code must not be nullFlavored');
    }

    /**
     * A procedure with no code at all must collapse to nullFlavor="UNK" rather
     * than emit an empty code="" attribute. Mirrors Node's translate.code.
     */
    public function testProcedureWithEmptyCodeUsesNullFlavor(): void
    {
        $input = <<<'XML'
            <CCDA>
                <procedures>
                    <procedure>
                        <extension>PROC-1</extension>
                        <date>2021-07-23</date>
                        <code></code>
                        <description>Unknown procedure</description>
                        <code_type>SNOMED CT</code_type>
                    </procedure>
                </procedures>
            </CCDA>
            XML;

        $code = $this->firstProcedureCode($input);
        self::assertSame('UNK', $code->getAttribute('nullFlavor'), 'Missing code must be nullFlavor UNK');
        self::assertFalse($code->hasAttribute('code'), 'nullFlavor code must not carry an empty code attribute');
        self::assertFalse($code->hasAttribute('codeSystemName'), 'nullFlavor code must not carry codeSystemName');
    }

    /**
     * The author code element is guarded by existsWhen propertyNotEmpty('code')
     * in Node, so an unknown physician type must omit the whole code element
     * rather than emit empty coded attributes.
     */
    public function testDocumentAuthorOmitsCodeWhenTypeEmpty(): void
    {
        $input = <<<'XML'
            <CCDA>
                <created_time_timezone>20210723</created_time_timezone>
                <author>
                    <npi>1234567890</npi>
                    <physician_type_code></physician_type_code>
                    <physician_type></physician_type>
                    <physician_type_system></physician_type_system>
                    <physician_type_system_name></physician_type_system_name>
                </author>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('/hl7:ClinicalDocument/hl7:author/hl7:assignedAuthor/hl7:code');
        self::assertNotFalse($codes, 'Author code query must be valid');
        self::assertSame(0, $codes->length, 'Empty author type code must omit the code element');
    }

    public function testDocumentAuthorEmitsCodeWhenTypePresent(): void
    {
        $input = <<<'XML'
            <CCDA>
                <created_time_timezone>20210723</created_time_timezone>
                <author>
                    <npi>1234567890</npi>
                    <physician_type_code>207Q00000X</physician_type_code>
                    <physician_type>Family Medicine</physician_type>
                    <physician_type_system>2.16.840.1.113883.6.101</physician_type_system>
                    <physician_type_system_name>NUCC</physician_type_system_name>
                </author>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('/hl7:ClinicalDocument/hl7:author/hl7:assignedAuthor/hl7:code');
        self::assertNotFalse($codes, 'Author code query must be valid');
        self::assertSame(1, $codes->length, 'Present author type code must emit the code element');
    }

    /**
     * assignedAuthor telecom is SHALL 1..* (CONF:1198-5428). Node omits it when
     * the author has no phone; the converter carries the unknown number as
     * nullFlavor instead.
     */
    public function testDocumentAuthorTelecomUsesNullFlavorWhenPhoneEmpty(): void
    {
        $input = <<<'XML'
            <CCDA>
                <created_time_timezone>20210723</created_time_timezone>
                <author>
                    <npi>1234567890</npi>
                </author>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $telecoms = $xpath->query('/hl7:ClinicalDocument/hl7:author/hl7:assignedAuthor/hl7:telecom');
        self::assertNotFalse($telecoms, 'Author telecom query must be valid');
        self::assertSame(1, $telecoms->length, 'Author must carry exactly one telecom');
        $telecom = $telecoms->item(0);
        self::assertInstanceOf(\DOMElement::class, $telecom);
        self::assertSame('UNK', $telecom->getAttribute('nullFlavor'));
        self::assertFalse($telecom->hasAttribute('value'), 'A nullFlavor telecom must not carry a value');
    }

    /**
     * patientRole telecom is SHALL 1..* (CONF:1198-5280). A patient with no
     * phone or email gets one nullFlavor telecom rather than none.
     */
    public function testPatientTelecomUsesNullFlavorWhenNoContact(): void
    {
        $input = <<<'XML'
            <CCDA>
                <created_time_timezone>20210723</created_time_timezone>
                <patient>
                    <fname>Test</fname>
                    <lname>Patient</lname>
                </patient>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $telecoms = $xpath->query('/hl7:ClinicalDocument/hl7:recordTarget/hl7:patientRole/hl7:telecom');
        self::assertNotFalse($telecoms, 'Patient telecom query must be valid');
        self::assertSame(1, $telecoms->length, 'Patient must carry exactly one telecom');
        $telecom = $telecoms->item(0);
        self::assertInstanceOf(\DOMElement::class, $telecom);
        self::assertSame('UNK', $telecom->getAttribute('nullFlavor'));
    }

    public function testPatientTelecomOmitsNullFlavorWhenPhonePresent(): void
    {
        $input = <<<'XML'
            <CCDA>
                <created_time_timezone>20210723</created_time_timezone>
                <patient>
                    <fname>Test</fname>
                    <lname>Patient</lname>
                    <phone_home>555-555-1234</phone_home>
                </patient>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $telecoms = $xpath->query('/hl7:ClinicalDocument/hl7:recordTarget/hl7:patientRole/hl7:telecom');
        self::assertNotFalse($telecoms, 'Patient telecom query must be valid');
        self::assertSame(1, $telecoms->length, 'A known phone must be the only telecom');
        $telecom = $telecoms->item(0);
        self::assertInstanceOf(\DOMElement::class, $telecom);
        self::assertSame('tel:555-555-1234', $telecom->getAttribute('value'));
        self::assertFalse($telecom->hasAttribute('nullFlavor'));
    }

    /**
     * Severity Observation value is SHALL 1..1 with xsi:type="CD"
     * (CONF:1098-7356), and CDA's ANY type is abstract, so an untyped value
     * fails the schema. Node drops the xsi:type when the severity is unknown;
     * demo1 has two such allergies.
     */
    public function testUnknownSeverityValueKeepsCdType(): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . 'ccda-input-demo1.xml');
        self::assertIsString($input, 'Demo fixture must be readable');

        $xpath = $this->convertToXPath($input);
        $xpath->registerNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $severity = "//hl7:observation[hl7:templateId/@root='2.16.840.1.113883.10.20.22.4.8']/hl7:value";

        $unknown = $xpath->query($severity . "[@nullFlavor='UNK']");
        self::assertNotFalse($unknown, 'Severity value query must be valid');
        self::assertGreaterThan(0, $unknown->length, 'demo1 must exercise the unknown-severity branch');

        $untyped = $xpath->query($severity . '[not(@xsi:type)]');
        self::assertNotFalse($untyped, 'Untyped severity value query must be valid');
        self::assertSame(0, $untyped->length, 'Every severity value must carry xsi:type');
    }

    /**
     * The relationship to the subscriber arrives as attributes on
     * participant/code. The converter read child elements there, so the code
     * never matched and every covered party was emitted as SELF.
     */
    public function testPayerCoveredPartyUsesRecordedRelationship(): void
    {
        $xpath = $this->convertToXPath($this->payerInput(
            '<code code="512" code_system="2.16.840.1.113883.3.221.5" code_system_name="Source of Payment Typology" name="" />',
            '<code name="family dependent" code="FAMDEP" code_system="2.16.840.1.113883.5.111" code_system_name="HL7 RoleCode" />',
        ));

        $code = $this->singleElement($xpath, "//hl7:participant[@typeCode='COV']/hl7:participantRole/hl7:code");
        self::assertSame('FAMDEP', $code->getAttribute('code'));
        self::assertSame('family dependent', $code->getAttribute('displayName'));
        self::assertSame('2.16.840.1.113883.5.111', $code->getAttribute('codeSystem'));

        $policyCode = $this->singleElement($xpath, "//hl7:act[hl7:templateId/@root='2.16.840.1.113883.10.20.22.4.61']/hl7:code");
        self::assertSame('512', $policyCode->getAttribute('code'));
        self::assertFalse($policyCode->hasAttribute('displayName'), 'An unnamed coverage type must not be labelled');
    }

    /**
     * An unknown relationship or coverage type is nullFlavor, not the SELF
     * and 72 ("PPO") defaults Node substitutes.
     */
    public function testPayerUnknownCodesUseNullFlavor(): void
    {
        $xpath = $this->convertToXPath($this->payerInput(
            '<code code="" code_system="" code_system_name="" name="" />',
            '<code name="" code="" code_system="" code_system_name="" />',
        ));

        $code = $this->singleElement($xpath, "//hl7:participant[@typeCode='COV']/hl7:participantRole/hl7:code");
        self::assertSame('UNK', $code->getAttribute('nullFlavor'));
        self::assertFalse($code->hasAttribute('code'));

        $policyCode = $this->singleElement($xpath, "//hl7:act[hl7:templateId/@root='2.16.840.1.113883.10.20.22.4.61']/hl7:code");
        self::assertSame('UNK', $policyCode->getAttribute('nullFlavor'));
        self::assertFalse($policyCode->hasAttribute('code'));
    }

    private function payerInput(string $policyCode, string $participantCode): string
    {
        return <<<XML
            <CCDA>
                <created_time_timezone>20210723</created_time_timezone>
                <payers>
                    <payer>
                        <identifiers><identifier>2.16.840.1.113883.19.5</identifier></identifiers>
                        <policy>
                            <identifiers><identifier>2.16.840.1.113883.19.5.1</identifier><extension>GRP-1</extension></identifiers>
                            {$policyCode}
                        </policy>
                        <participant>
                            <time_low>2024-08-01</time_low>
                            <time_high>2026-08-01</time_high>
                            {$participantCode}
                            <performer>
                                <identifiers><identifier>2.16.840.1.113883.19.5.2</identifier><extension>POL-1</extension></identifiers>
                            </performer>
                        </participant>
                    </payer>
                </payers>
            </CCDA>
            XML;
    }

    private function singleElement(DOMXPath $xpath, string $query): \DOMElement
    {
        $nodes = $xpath->query($query);
        self::assertNotFalse($nodes, 'Query must be valid: ' . $query);
        self::assertSame(1, $nodes->length, 'Expected exactly one match: ' . $query);
        $node = $nodes->item(0);
        self::assertInstanceOf(\DOMElement::class, $node);
        return $node;
    }

    /**
     * The medication manufacturedMaterial code uses Node's leafLevel.code
     * (no existsWhen), so a missing RxNorm code collapses to nullFlavor="UNK"
     * rather than emitting code="null_flavor" or an empty codeSystemName.
     */
    public function testMedicationCodeUsesNullFlavorWhenRxnormEmpty(): void
    {
        $input = <<<'XML'
            <CCDA>
                <medications>
                    <medication>
                        <extension>MED-1</extension>
                        <drug>Aspirin</drug>
                        <rxnorm></rxnorm>
                    </medication>
                </medications>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('//hl7:manufacturedMaterial/hl7:code');
        self::assertNotFalse($codes, 'Material code query must be valid');
        $code = $codes->item(0);
        self::assertInstanceOf(\DOMElement::class, $code, 'Material code element must exist');
        self::assertSame('UNK', $code->getAttribute('nullFlavor'), 'Missing RxNorm must be nullFlavor UNK');
        self::assertFalse($code->hasAttribute('code'), 'nullFlavor code must not carry the null_flavor sentinel');
        self::assertFalse($code->hasAttribute('codeSystemName'), 'nullFlavor code must not carry codeSystemName');
    }

    /**
     * The immunization manufacturedMaterial code uses Node's leafLevel.code, so
     * a missing CVX code collapses to nullFlavor="UNK" rather than emit empty
     * coded attributes.
     */
    public function testImmunizationCodeUsesNullFlavorWhenCvxEmpty(): void
    {
        $input = <<<'XML'
            <CCDA>
                <immunizations>
                    <immunization>
                        <extension>IMM-1</extension>
                        <cvx_code></cvx_code>
                        <code_text>Influenza</code_text>
                    </immunization>
                </immunizations>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('//hl7:manufacturedMaterial/hl7:code');
        self::assertNotFalse($codes, 'Material code query must be valid');
        $code = $codes->item(0);
        self::assertInstanceOf(\DOMElement::class, $code, 'Material code element must exist');
        self::assertSame('UNK', $code->getAttribute('nullFlavor'), 'Missing CVX code must be nullFlavor UNK');
        self::assertFalse($code->hasAttribute('codeSystemName'), 'nullFlavor code must not carry codeSystemName');
    }

    /**
     * The results organizer code and each result observation code use Node's
     * leafLevel.code, so missing LOINC codes collapse to nullFlavor="UNK"
     * rather than emit empty coded attributes.
     */
    public function testResultCodesUseNullFlavorWhenCodeEmpty(): void
    {
        $input = <<<'XML'
            <CCDA>
                <results>
                    <result>
                        <extension>RES-1</extension>
                        <test_code></test_code>
                        <test_name>Metabolic Panel</test_name>
                        <subtest>
                            <result_code></result_code>
                            <result_desc>Glucose</result_desc>
                        </subtest>
                    </result>
                </results>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);

        $organizerCodes = $xpath->query('//hl7:organizer/hl7:code');
        self::assertNotFalse($organizerCodes, 'Organizer code query must be valid');
        $organizerCode = $organizerCodes->item(0);
        self::assertInstanceOf(\DOMElement::class, $organizerCode, 'Organizer code element must exist');
        self::assertSame('UNK', $organizerCode->getAttribute('nullFlavor'), 'Missing organizer code must be nullFlavor UNK');
        self::assertFalse($organizerCode->hasAttribute('codeSystemName'), 'nullFlavor organizer code must not carry codeSystemName');

        $obsCodes = $xpath->query('//hl7:organizer/hl7:component/hl7:observation/hl7:code');
        self::assertNotFalse($obsCodes, 'Observation code query must be valid');
        $obsCode = $obsCodes->item(0);
        self::assertInstanceOf(\DOMElement::class, $obsCode, 'Observation code element must exist');
        self::assertSame('UNK', $obsCode->getAttribute('nullFlavor'), 'Missing observation code must be nullFlavor UNK');
    }

    /**
     * The encounter performer assignedEntity code uses Node's leafLevel.code:
     * a missing physician type code collapses to nullFlavor="UNK", and a present
     * code with an unknown code system omits the empty codeSystemName attribute.
     */
    public function testEncounterPerformerCodeMatchesNode(): void
    {
        $input = <<<'XML'
            <CCDA>
                <encounter_list>
                    <encounter>
                        <extension>ENC-1</extension>
                        <physician_type_code></physician_type_code>
                    </encounter>
                    <encounter>
                        <extension>ENC-2</extension>
                        <physician_type_code>207Q00000X</physician_type_code>
                        <physician_type></physician_type>
                        <physician_code_type></physician_code_type>
                    </encounter>
                </encounter_list>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('//hl7:encounter/hl7:performer/hl7:assignedEntity/hl7:code');
        self::assertNotFalse($codes, 'Performer code query must be valid');
        self::assertSame(2, $codes->length, 'Expected one performer code per encounter');

        $missing = $codes->item(0);
        self::assertInstanceOf(\DOMElement::class, $missing, 'First performer code must exist');
        self::assertSame('UNK', $missing->getAttribute('nullFlavor'), 'Missing physician type code must be nullFlavor UNK');

        $present = $codes->item(1);
        self::assertInstanceOf(\DOMElement::class, $present, 'Second performer code must exist');
        self::assertSame('207Q00000X', $present->getAttribute('code'), 'Present code must be preserved');
        self::assertFalse($present->hasAttribute('codeSystemName'), 'Empty code system must omit codeSystemName');
        self::assertFalse($present->hasAttribute('displayName'), 'Empty physician type must omit displayName');
    }

    /**
     * A problem observation value with a code but no title must omit the empty
     * displayName attribute rather than emit displayName="", matching Node's
     * leafLevel.code omit-empty behavior.
     */
    public function testProblemValueOmitsEmptyDisplayName(): void
    {
        $input = <<<'XML'
            <CCDA>
                <problem_lists>
                    <problem>
                        <extension>PROB-1</extension>
                        <code>38341003</code>
                        <code_type>SNOMED CT</code_type>
                        <title></title>
                        <start_date>2021-07-23</start_date>
                    </problem>
                </problem_lists>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $values = $xpath->query("//hl7:value[@code='38341003']");
        self::assertNotFalse($values, 'Problem value query must be valid');
        $value = $values->item(0);
        self::assertInstanceOf(\DOMElement::class, $value, 'Problem value element must exist');
        self::assertFalse($value->hasAttribute('displayName'), 'Empty title must omit displayName');
        self::assertSame('2.16.840.1.113883.6.96', $value->getAttribute('codeSystem'), 'SNOMED codeSystem must be preserved');
    }

    /**
     * The allergen code and allergy status value use Node's leafLevel.code, so a
     * present code with a missing display name omits the empty displayName
     * attribute rather than emit displayName="".
     */
    public function testAllergyCodesOmitEmptyDisplayName(): void
    {
        $input = <<<'XML'
            <CCDA>
                <allergies>
                    <allergy>
                        <extension>ALG-1</extension>
                        <rxnorm_code>7980</rxnorm_code>
                        <rxnorm_code_text>Penicillin</rxnorm_code_text>
                        <title></title>
                        <status_code>55561003</status_code>
                        <status_table></status_table>
                    </allergy>
                </allergies>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);

        $allergenCodes = $xpath->query('//hl7:participant/hl7:participantRole/hl7:playingEntity/hl7:code');
        self::assertNotFalse($allergenCodes, 'Allergen code query must be valid');
        $allergenCode = $allergenCodes->item(0);
        self::assertInstanceOf(\DOMElement::class, $allergenCode, 'Allergen code element must exist');
        self::assertSame('7980', $allergenCode->getAttribute('code'), 'Allergen code must be preserved');
        self::assertFalse($allergenCode->hasAttribute('displayName'), 'Empty title must omit allergen displayName');

        $statusValues = $xpath->query("//hl7:value[@code='55561003']");
        self::assertNotFalse($statusValues, 'Status value query must be valid');
        $statusValue = $statusValues->item(0);
        self::assertInstanceOf(\DOMElement::class, $statusValue, 'Status value element must exist');
        self::assertFalse($statusValue->hasAttribute('displayName'), 'Empty status table must omit displayName');
    }

    /**
     * The encounter diagnosis observation value uses Node's leafLevel.code, so a
     * diagnosis with a code but no text must omit the empty displayName
     * attribute rather than emit displayName="".
     */
    public function testEncounterDiagnosisValueOmitsEmptyDisplayName(): void
    {
        $input = <<<'XML'
            <CCDA>
                <encounter_list>
                    <encounter>
                        <extension>ENC-1</extension>
                        <encounter_problems>
                            <problem>
                                <code>38341003</code>
                                <code_type>SNOMED CT</code_type>
                                <text></text>
                            </problem>
                        </encounter_problems>
                    </encounter>
                </encounter_list>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $values = $xpath->query("//hl7:value[@code='38341003']");
        self::assertNotFalse($values, 'Diagnosis value query must be valid');
        $value = $values->item(0);
        self::assertInstanceOf(\DOMElement::class, $value, 'Diagnosis value element must exist');
        self::assertFalse($value->hasAttribute('displayName'), 'Empty diagnosis text must omit displayName');
        self::assertSame('2.16.840.1.113883.6.96', $value->getAttribute('codeSystem'), 'SNOMED codeSystem must be preserved');
    }

    private function firstProcedureCode(string $input): \DOMElement
    {
        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('//hl7:procedure/hl7:code');
        self::assertNotFalse($codes, 'Procedure code query must be valid');
        $code = $codes->item(0);
        self::assertInstanceOf(\DOMElement::class, $code, 'Procedure code element must exist');
        return $code;
    }

    private function convertToXPath(string $input): DOMXPath
    {
        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        return $xpath;
    }

    #[DataProvider('demoFixtureProvider')]
    public function testDemoFixture(string $inputFile, string $expectedFile): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . $inputFile);
        self::assertNotFalse($input, "Failed to read input fixture: $inputFile");
        $expected = file_get_contents(self::FIXTURE_DIR . $expectedFile);
        self::assertNotFalse($expected, "Failed to read expected fixture: $expectedFile");

        $converter = new InternalToCdaConverter();
        $actual = $converter->convert(trim($input));

        $this->assertCdaEquals($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function demoFixtureProvider(): array
    {
        return [
            'demo1' => ['ccda-input-demo1.xml', 'ccda-output-demo1.xml'],
            'demo2' => ['ccda-input-demo2.xml', 'ccda-output-demo2.xml'],
        ];
    }

    #[DataProvider('sectionTemplateIdProvider')]
    public function testSection(string $name, string $templateId): void
    {
        [$actual, $expected] = $this->getConvertedAndExpected();
        $this->assertSectionMatches($actual, $expected, $templateId, $name);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sectionTemplateIdProvider(): array
    {
        return [
            'Care Team' => ['Care Team', '2.16.840.1.113883.10.20.22.2.500'],
            'Allergies' => ['Allergies', '2.16.840.1.113883.10.20.22.2.6.1'],
            'Medications' => ['Medications', '2.16.840.1.113883.10.20.22.2.1.1'],
            'Problems' => ['Problems', '2.16.840.1.113883.10.20.22.2.5.1'],
            'Procedures' => ['Procedures', '2.16.840.1.113883.10.20.22.2.7.1'],
            'Results' => ['Results', '2.16.840.1.113883.10.20.22.2.3.1'],
            'Encounters' => ['Encounters', '2.16.840.1.113883.10.20.22.2.22.1'],
            'Immunizations' => ['Immunizations', '2.16.840.1.113883.10.20.22.2.2.1'],
            'Vital Signs' => ['Vital Signs', '2.16.840.1.113883.10.20.22.2.4.1'],
            'Social History' => ['Social History', '2.16.840.1.113883.10.20.22.2.17'],
            'Payers' => ['Payers', '2.16.840.1.113883.10.20.22.2.18'],
            'Medical Equipment' => ['Medical Equipment', '2.16.840.1.113883.10.20.22.2.23'],
            'Functional Status' => ['Functional Status', '2.16.840.1.113883.10.20.22.2.14'],
            'Mental Status' => ['Mental Status', '2.16.840.1.113883.10.20.22.2.56'],
            'Plan of Care' => ['Plan of Care', '2.16.840.1.113883.10.20.22.2.10'],
            'Goals' => ['Goals', '2.16.840.1.113883.10.20.22.2.60'],
            'Health Concerns' => ['Health Concerns', '2.16.840.1.113883.10.20.22.2.58'],
            'Assessment' => ['Assessment', '2.16.840.1.113883.10.20.22.2.8'],
        ];
    }

    /**
     * @return array{string, string}
     */
    private function getConvertedAndExpected(): array
    {
        if ($this->actualOutput === null) {
            $input = file_get_contents(self::FIXTURE_DIR . 'ccda-example-input1.xml');
            self::assertNotFalse($input, 'Failed to read input fixture');
            $expected = file_get_contents(self::FIXTURE_DIR . 'ccda-example-response1.xml');
            self::assertNotFalse($expected, 'Failed to read expected fixture');
            $this->expectedOutput = $expected;

            $converter = new InternalToCdaConverter();
            $this->actualOutput = $converter->convert(trim($input));
        }
        self::assertNotNull($this->expectedOutput, 'Expected output not initialized');
        return [$this->actualOutput, $this->expectedOutput];
    }

    private function assertCdaEquals(string $expected, string $actual): void
    {
        $expectedDom = $this->loadDom($expected);
        $actualDom = $this->loadDom($actual);

        $expectedDom = $this->cleanWhitespace($expectedDom);

        // Extract fixture date from expected document's effectiveTime
        $fixtureDate = $this->extractFixtureDate($expectedDom);
        $currentDate = date('Ymd');
        $actualDom = $this->replaceTimestamps($actualDom, $currentDate, $fixtureDate);
        $actualDom = $this->normalizeDynamicIds($actualDom, $expectedDom);
        $actualDom = $this->cleanWhitespace($actualDom);

        self::assertXmlStringEqualsXmlString(
            $expectedDom->C14N(),
            $actualDom->C14N(),
            'Generated CDA does not match expected output'
        );
    }

    private function assertSectionMatches(string $actual, string $expected, string $templateId, string $name = ''): void
    {
        $actualDom = $this->loadDom($actual);
        $expectedDom = $this->loadDom($expected);

        // Extract fixture date from the full expected document before extracting sections
        $fixtureDate = $this->extractFixtureDate($expectedDom);

        $actualSection = $this->extractSection($actualDom, $templateId);
        $expectedSection = $this->extractSection($expectedDom, $templateId);

        $label = $name !== '' ? "$name ($templateId)" : $templateId;

        if ($expectedSection === '') {
            self::markTestSkipped("Section $label not found in expected output");
        }

        self::assertNotSame('', $actualSection, "Section $label missing from actual output");

        $actualDom = $this->loadDom($actualSection);
        $expectedDom = $this->loadDom($expectedSection);

        $currentDate = date('Ymd');
        $actualDom = $this->replaceTimestamps($actualDom, $currentDate, $fixtureDate);
        $actualDom = $this->cleanWhitespace($actualDom);
        $expectedDom = $this->cleanWhitespace($expectedDom);

        self::assertXmlStringEqualsXmlString(
            $expectedDom->C14N(),
            $actualDom->C14N(),
            "Section $label mismatch"
        );
    }

    private function extractSection(DOMDocument $dom, string $templateId): string
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $result = $xpath->query("//hl7:section[hl7:templateId[@root='$templateId']]");
        if ($result === false) {
            return '';
        }
        $section = $result->item(0);
        if (!$section instanceof \DOMElement) {
            return '';
        }

        $newDoc = new DOMDocument();
        $newDoc->preserveWhiteSpace = false;
        $imported = $newDoc->importNode($section, true);
        $newDoc->appendChild($imported);
        return $newDoc->saveXML() ?: '';
    }

    private function loadDom(string $xml): DOMDocument
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $loaded = $dom->loadXML($xml, LIBXML_NOBLANKS);
        if ($loaded === false) {
            throw new \RuntimeException('Invalid XML');
        }
        return $dom;
    }

    private function replaceTimestamps(DOMDocument $xml, string $currentTimestamp, string $newTimestamp): DOMDocument
    {
        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $expr = '//*[@value="' . $currentTimestamp . '"]';
        $timestampValues = $xpath->query($expr);
        if ($timestampValues !== false) {
            foreach ($timestampValues as $timestamp) {
                if ($timestamp instanceof \DOMElement) {
                    $timestamp->setAttribute('value', $newTimestamp);
                }
            }
        }

        $dateTime = \DateTimeImmutable::createFromFormat('Ymd', $currentTimestamp);
        $dateTimeNew = \DateTimeImmutable::createFromFormat('Ymd', $newTimestamp);
        if ($dateTime !== false && $dateTimeNew !== false) {
            $expr = "//hl7:tr/hl7:td/text()[normalize-space(.) = '" . $dateTime->format('Y-m-d') . "']";
            $timestampTextNodes = $xpath->query($expr);
            if ($timestampTextNodes !== false) {
                foreach ($timestampTextNodes as $textNode) {
                    $textNode->nodeValue = $dateTimeNew->format('Y-m-d');
                }
            }
        }

        return $xml;
    }

    private function normalizeDynamicIds(DOMDocument $actual, DOMDocument $expected): DOMDocument
    {
        $xpath = new DOMXPath($actual);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $xpathExpected = new DOMXPath($expected);
        $xpathExpected->registerNamespace('hl7', 'urn:hl7-org:v3');

        $this->replaceRootIdForQuery("//hl7:observation/hl7:code[@code='76691-5']", $xpath, $xpathExpected);
        $this->replaceRootIdForQuery("//hl7:observation/hl7:code[@code='46098-0']", $xpath, $xpathExpected);
        $this->replaceRootIdForQuery("//hl7:observation/hl7:code[@code='76690-7']", $xpath, $xpathExpected);
        $this->replaceRootIdForQuery("//hl7:section/hl7:entry/hl7:organizer/hl7:code[@code='86744-0']", $xpath, $xpathExpected);
        $this->replaceRootIdForQuery("//hl7:component/hl7:act/hl7:code[@code='85847-2']", $xpath, $xpathExpected);

        return $actual;
    }

    private function replaceRootIdForQuery(string $query, DOMXPath $actual, DOMXPath $expected): void
    {
        $actualList = $actual->query($query);
        $expectedList = $expected->query($query);

        if ($actualList === false || $expectedList === false) {
            return;
        }

        $count = $actualList->count();
        if ($count !== $expectedList->count()) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $actualNode = $actualList->item($i)?->parentNode;
            $expectedNode = $expectedList->item($i)?->parentNode;

            if (!$actualNode instanceof \DOMElement || !$expectedNode instanceof \DOMElement) {
                continue;
            }

            $actualIdList = $actual->query('.//hl7:id', $actualNode);
            $expectedIdList = $expected->query('.//hl7:id', $expectedNode);
            if ($actualIdList === false || $expectedIdList === false) {
                continue;
            }

            $actualId = $actualIdList->item(0);
            $expectedId = $expectedIdList->item(0);
            if ($actualId instanceof \DOMElement && $expectedId instanceof \DOMElement) {
                $actualId->setAttribute('root', $expectedId->getAttribute('root'));
            }
        }
    }

    private function extractFixtureDate(DOMDocument $dom): string
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        // Get the effectiveTime from the document header (ClinicalDocument/effectiveTime)
        $effectiveTime = $xpath->query('/hl7:ClinicalDocument/hl7:effectiveTime/@value');
        if ($effectiveTime !== false && $effectiveTime->length > 0) {
            $value = $effectiveTime->item(0)->nodeValue ?? '';
            // Extract just the date portion (first 8 chars: YYYYMMDD)
            if (strlen($value) >= 8) {
                return substr($value, 0, 8);
            }
        }

        // Fallback to a default if not found
        return '20251215';
    }

    private function cleanWhitespace(DOMDocument $dom): DOMDocument
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

        $nodes = $xpath->query('//hl7:text//text() | //xhtml:td//text() | //hl7:value//text()');
        if ($nodes !== false) {
            foreach ($nodes as $node) {
                $node->nodeValue = trim((string) preg_replace('/\s+/u', ' ', (string) $node->nodeValue));
            }
        }

        return $dom;
    }
}
