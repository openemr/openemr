# CCDA PHP Migration Plan (Pragmatic Approach)

## Overview

Replace `CcdaGenerator::socket_get()` (Node.js call) with `InternalToCdaConverter::convert()` (pure PHP). TDD against existing test vectors.

### Current State
- PHP (`EncounterccdadispatchTable`) fetches data and formats as proprietary XML
- XML sent via TCP socket to Node.js service on port 6661
- Node.js parses XML, restructures to JSON, uses `oe-blue-button-generate` templates
- Node.js returns CDA-compliant XML

### Target State
- PHP fetches data and formats as proprietary XML (unchanged)
- `InternalToCdaConverter` transforms proprietary XML directly to CDA XML
- No external processes, sockets, or Node.js dependencies

### Success Criteria

1. `CcdaServiceDocumentRequestorTest` passes with PHP converter instead of Node
2. All existing CCDA generation entry points continue to work
3. Node.js service and dependencies deleted

---

## Test Vectors

Existing fixtures in `tests/Tests/data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/`:

| File | Size | Content |
|------|------|---------|
| `ccda-example-input1.xml` | 384KB | Proprietary `<CCDA>` format (PHP sends this) |
| `ccda-example-response1.xml` | 321KB | CDA `<ClinicalDocument>` (Node returns this) |

### Test Vector Limitations

- Single patient with synthetic data
- Many specialty sections are empty (operative, discharge, consultation)
- No edge cases: empty allergies "NKA", multiple entries, special characters
- Timestamps and some UUIDs are dynamic (normalization helpers exist in test)

---

## Gap Analysis

### Sections That Work (19 total)

| Input Element | Output Section |
|---------------|----------------|
| `allergies` | Allergies Section (2.16.840.1.113883.10.20.22.2.6.1) |
| `medications` | Medications Section (2.16.840.1.113883.10.20.22.2.1.1) |
| `problem_lists` | Problems Section (2.16.840.1.113883.10.20.22.2.5.1) |
| `procedures` | Procedures Section (2.16.840.1.113883.10.20.22.2.7.1) |
| `results` | Results Section (2.16.840.1.113883.10.20.22.2.3.1) |
| `encounter_list` | Encounters Section (2.16.840.1.113883.10.20.22.2.22.1) |
| `immunizations` | Immunizations Section (2.16.840.1.113883.10.20.22.2.2.1) |
| `vitals` | Vital Signs Section (2.16.840.1.113883.10.20.22.2.4.1) |
| `social_history` | Social History Section (2.16.840.1.113883.10.20.22.2.17) |
| `payers` | Payers Section (2.16.840.1.113883.10.20.22.2.18) |
| `medical_devices` | Medical Equipment Section (2.16.840.1.113883.10.20.22.2.23) |
| `advance_directives` | Advance Directives Section (2.16.840.1.113883.10.20.22.2.21.1) |
| `care_team` | Care Team Section (2.16.840.1.113883.10.20.22.2.500) |
| `functional_status` | Functional Status Section (2.16.840.1.113883.10.20.22.2.14) |
| `mental_status` | Mental Status Section (2.16.840.1.113883.10.20.22.2.56) |
| `goals` | Goals Section (2.16.840.1.113883.10.20.22.2.60) |
| `health_concerns` | Health Concerns Section (2.16.840.1.113883.10.20.22.2.58) |
| `planofcare` | Plan of Care Section (2.16.840.1.113883.10.20.22.2.10) |
| `clinical_notes` | Various Note Sections |
| `clinical_notes/evaluation_note` | Assessment Section (2.16.840.1.113883.10.20.22.2.8) |

### Known Dropped Data (Pre-existing Bug)

These elements exist in PHP input but Node silently ignores them:

| Element | Risk | Impact |
|---------|------|--------|
| `dischargediagnosis` | HIGH | Discharge summary diagnoses lost |
| `dischargemedication` | HIGH | Discharge summary meds lost |
| `chief_complaint` | MEDIUM | H&P chief complaint lost |
| `history_past_illness` | MEDIUM | H&P past illness lost |
| `anesthesia` | MEDIUM | Operative note data |
| `blood_loss` | MEDIUM | Operative note data |
| `complications` | MEDIUM | Procedure note data |
| `pre_operative_diag` | MEDIUM | Operative note data |
| `post_operative_diag` | MEDIUM | Operative note data |

**Note:** In the test vector, these are all empty. This migration replicates current behavior; fixing dropped data is a separate enhancement.

---

## Phase 1: Foundation

### Task 1.1: Create Test Infrastructure

**File:** `tests/Tests/Isolated/Cda/InternalToCdaConverterTest.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda;

use DOMDocument;
use DOMXPath;
use OpenEMR\Cda\InternalToCdaConverter;
use PHPUnit\Framework\TestCase;

class InternalToCdaConverterTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../../../data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/';

    public function testConvertProducesValidCda(): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . 'ccda-example-input1.xml');
        $expected = file_get_contents(self::FIXTURE_DIR . 'ccda-example-response1.xml');

        $converter = new InternalToCdaConverter();
        $actual = $converter->convert($input);

        $this->assertCdaEquals($expected, $actual);
    }

    private function assertCdaEquals(string $expected, string $actual): void
    {
        $expectedDom = $this->loadDom($expected);
        $actualDom = $this->loadDom($actual);

        // Normalize expected
        $expectedDom = $this->cleanWhitespace($expectedDom);

        // Normalize actual: timestamps and dynamic IDs
        $fixtureDate = '20251215';
        $currentDate = date('Ymd');
        $actualDom = $this->replaceLatestTimeStamp($actualDom, $currentDate, $fixtureDate);
        $actualDom = $this->updateRootIds($actualDom, $expectedDom);
        $actualDom = $this->cleanWhitespace($actualDom);

        $this->assertXmlStringEqualsXmlString(
            $expectedDom->C14N(),
            $actualDom->C14N(),
            'Generated CDA does not match expected output'
        );
    }

    private function loadDom(string $xml): DOMDocument
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        if (!$dom->loadXML($xml, LIBXML_NOBLANKS)) {
            throw new \RuntimeException('Invalid XML');
        }
        return $dom;
    }

    private function replaceLatestTimeStamp(DOMDocument $xml, string $currentTimestamp, string $newTimeStamp): DOMDocument
    {
        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        // Replace @value attributes with current date
        $expr = '//*[@value="' . $currentTimestamp . '"]';
        $timestampValues = $xpath->query($expr);
        foreach ($timestampValues as $timestamp) {
            if ($timestamp instanceof \DOMElement) {
                $timestamp->setAttribute('value', $newTimeStamp);
            }
        }

        // Also replace Y-m-d formatted dates in table cells
        $dateTime = \DateTimeImmutable::createFromFormat("Ymd", $currentTimestamp);
        $dateTimeNewFormat = \DateTimeImmutable::createFromFormat("Ymd", $newTimeStamp);
        $expr = "//hl7:tr/hl7:td/text()[normalize-space(.) = '" . $dateTime->format("Y-m-d") . "']";
        $timestampTextNodes = $xpath->query($expr);
        foreach ($timestampTextNodes as $textNode) {
            $textNode->nodeValue = $dateTimeNewFormat->format("Y-m-d");
        }

        return $xml;
    }

    private function updateRootIds(DOMDocument $actual, DOMDocument $expected): DOMDocument
    {
        // These specific observation codes generate new UUIDs each run:
        // - 76691-5 (Gender Identity)
        // - 46098-0 (Sex)
        // - 76690-7 (Sexual Orientation)
        // - 86744-0 (Care Team organizer)
        // - 85847-2 (Patient Care Team Information)

        $xpath = new DOMXPath($actual);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $xpathExpected = new DOMXPath($expected);
        $xpathExpected->registerNamespace('hl7', 'urn:hl7-org:v3');

        $this->replaceRootIdForXpathQuery("//hl7:observation/hl7:code[@code='76691-5']", $xpath, $xpathExpected);
        $this->replaceRootIdForXpathQuery("//hl7:observation/hl7:code[@code='46098-0']", $xpath, $xpathExpected);
        $this->replaceRootIdForXpathQuery("//hl7:observation/hl7:code[@code='76690-7']", $xpath, $xpathExpected);
        $this->replaceRootIdForXpathQuery("//hl7:section/hl7:entry/hl7:organizer/hl7:code[@code='86744-0']", $xpath, $xpathExpected);
        $this->replaceRootIdForXpathQuery("//hl7:component/hl7:act/hl7:code[@code='85847-2']", $xpath, $xpathExpected);

        return $actual;
    }

    private function replaceRootIdForXpathQuery(string $query, DOMXPath $path, DOMXPath $expectedXpath): void
    {
        $currentList = $path->query($query);
        $expectedList = $expectedXpath->query($query);

        $count = $currentList->count();
        if ($currentList->count() !== $expectedList->count()) {
            throw new \RuntimeException('Node lists have different counts for query: ' . $query);
        }

        for ($i = 0; $i < $count; $i++) {
            $currentNode = $currentList->item($i)->parentElement;
            $expectedNode = $expectedList->item($i)->parentElement;

            $currentNodeId = $path->query(".//hl7:id", $currentNode)->item(0);
            $expectedNodeId = $expectedXpath->query(".//hl7:id", $expectedNode)->item(0);

            if ($currentNodeId instanceof \DOMElement && $expectedNodeId instanceof \DOMElement) {
                $currentNodeId->setAttribute('root', $expectedNodeId->getAttribute('root'));
            }
        }
    }

    private function cleanWhitespace(DOMDocument $dom): DOMDocument
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

        $nodes = $xpath->query('//hl7:text//text() | //xhtml:td//text() | //hl7:value//text()');
        foreach ($nodes as $node) {
            $node->nodeValue = trim((string) preg_replace('/\s+/u', ' ', (string) $node->nodeValue));
        }

        return $dom;
    }
}
```

**Acceptance Criteria:**
- Test exists and fails (converter not implemented yet)
- Normalization helpers working

### Incremental Testing Strategy

Since only one end-to-end fixture exists, use section-level comparison during development:

```php
private function assertSectionMatches(string $actual, string $expected, string $templateId): void
{
    $actualDom = $this->loadDom($actual);
    $expectedDom = $this->loadDom($expected);

    $actualSection = $this->extractSection($actualDom, $templateId);
    $expectedSection = $this->extractSection($expectedDom, $templateId);

    $this->assertXmlStringEqualsXmlString(
        $expectedSection,
        $actualSection,
        "Section $templateId mismatch"
    );
}

private function extractSection(DOMDocument $dom, string $templateId): string
{
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
    $section = $xpath->query("//hl7:section[hl7:templateId[@root='$templateId']]")->item(0);
    return $section ? $dom->saveXML($section) : '';
}
```

**Test progression:**
1. Implement header → test header elements via XPath assertions (no section extraction)
2. Implement each body section → test with `assertSectionMatches()` for that templateId
3. Final integration → full document comparison with `assertCdaEquals()`

This provides immediate feedback per-section rather than waiting for full implementation.

---

### Task 1.2: Create Converter Skeleton

**File:** `src/Cda/InternalToCdaConverter.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;

class InternalToCdaConverter
{
    private const NS_CDA = 'urn:hl7-org:v3';
    private const NS_SDTC = 'urn:hl7-org:sdtc';
    private const NS_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    private DOMDocument $output;
    private DOMDocument $input;
    private DOMXPath $inputXpath;

    public function convert(string $internalXml): string
    {
        $this->input = new DOMDocument();
        $this->input->loadXML($internalXml);
        $this->inputXpath = new DOMXPath($this->input);

        $this->output = new DOMDocument('1.0', 'UTF-8');
        $this->output->formatOutput = true;

        // Add XML stylesheet PI
        $xsl = $this->output->createProcessingInstruction(
            'xml-stylesheet',
            'type="text/xsl" href="CDA.xsl"'
        );
        $this->output->appendChild($xsl);

        // Build document
        $root = $this->createRootElement();
        $this->output->appendChild($root);

        $this->renderHeader($root);
        $this->renderBody($root);

        return $this->output->saveXML();
    }

    private function createRootElement(): DOMElement
    {
        $root = $this->output->createElementNS(self::NS_CDA, 'ClinicalDocument');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', self::NS_XSI);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:voc', 'urn:hl7-org:v3/voc');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sdtc', self::NS_SDTC);
        return $root;
    }

    private function renderHeader(DOMElement $root): void
    {
        // TODO: Implement header rendering
    }

    private function renderBody(DOMElement $root): void
    {
        // TODO: Implement body rendering
    }

    // --- Utility Methods ---

    private function xpath(string $query, ?DOMElement $context = null): DOMNodeList
    {
        return $this->inputXpath->query($query, $context ?? $this->input->documentElement);
    }

    private function xpathValue(string $query, ?DOMElement $context = null): string
    {
        $nodes = $this->xpath($query, $context);
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }

    private function createElement(string $name, ?string $text = null): DOMElement
    {
        $el = $this->output->createElement($name);
        if ($text !== null) {
            $el->textContent = $text;
        }
        return $el;
    }

    private function appendTemplateId(DOMElement $parent, string $root, ?string $extension = null): void
    {
        $el = $this->createElement('templateId');
        $el->setAttribute('root', $root);
        if ($extension !== null) {
            $el->setAttribute('extension', $extension);
        }
        $parent->appendChild($el);
    }

    private function appendId(DOMElement $parent, string $root, string $extension = ''): void
    {
        $el = $this->createElement('id');
        $el->setAttribute('root', $root);
        if ($extension !== '') {
            $el->setAttribute('extension', $extension);
        }
        $parent->appendChild($el);
    }

    private function appendCode(
        DOMElement $parent,
        string $elementName,
        string $code,
        string $codeSystem,
        string $displayName = '',
        string $codeSystemName = ''
    ): void {
        $el = $this->createElement($elementName);
        $el->setAttribute('code', $code);
        $el->setAttribute('codeSystem', $codeSystem);
        if ($displayName !== '') {
            $el->setAttribute('displayName', $displayName);
        }
        if ($codeSystemName !== '') {
            $el->setAttribute('codeSystemName', $codeSystemName);
        }
        $parent->appendChild($el);
    }

    private function formatDate(string $input): string
    {
        $input = trim($input);
        if ($input === '' || $input === '0000-00-00') {
            return '';
        }
        // Handle various input formats, normalize to CDA format
        // TODO: Port date formatting logic from Node
        return $input;
    }

    private function cleanCode(string $code): string
    {
        $code = trim($code);
        if ($code === '') {
            return 'null_flavor';
        }
        return preg_replace('/[.#]/', '', $code);
    }
}
```

**Acceptance Criteria:**
- Class exists, instantiable
- Test runs (fails on assertion, not error)

---

## Phase 2: Header

### Task 2.1: CDA Document Root and Metadata

Implement in `renderHeader()`:
- `<realmCode code="US"/>`
- `<typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>`
- Template IDs for US Realm Header and CCD
- `<id>`, `<code>`, `<title>`, `<effectiveTime>`
- `<confidentialityCode>`, `<languageCode>`, `<setId>`, `<versionNumber>`

**Input elements:** `serverRoot`, `doc_type`, `created_time_timezone`, `timezone_local_offset`

---

### Task 2.2: Record Target (Patient)

**Input element:** `<patient>`

Implement:
- `<recordTarget>/<patientRole>`
- Patient identifiers, addresses, telecoms
- Patient demographics (name, gender, DOB, race, ethnicity, language)
- Guardian/related persons

---

### Task 2.3: Author

**Input element:** `<author>` (nested in `<patient>` and `<encounter_provider>`)

Implement:
- `<author>/<assignedAuthor>`
- Author identifiers, name, address, telecom
- Represented organization

---

### Task 2.4: Custodian

**Input element:** `<custodian>`

Implement `<custodian>/<assignedCustodian>/<representedCustodianOrganization>`

---

### Task 2.5: Other Header Participants

**Input elements:** `<informer>`, `<information_recipient>`, `<legal_authenticator>`, `<authenticator>`, `<document_participants>`

Implement:
- `<informant>`
- `<informationRecipient>`
- `<legalAuthenticator>`
- `<authenticator>`
- `<participant>`

---

### Task 2.6: Documentation Of / Service Event

**Input elements:** `<encounter_provider>`, `<care_team>`, `<primary_care_provider>`

Implement:
- `<documentationOf>/<serviceEvent>`
- Performers

---

## Phase 3: Body Sections (Core)

Each section task follows the same pattern:
1. Add private method `renderXxxSection(DOMElement $structuredBody): void`
2. Extract data from input XML using `$this->xpath()`
3. Build section with templateId, code, title, narrative text (`<text>`), entries
4. Run test, iterate until section matches expected output

---

### Task 3.1: Allergies Section

**Input element:** `<allergies>/<allergy>`

**Output structure:**
```xml
<component>
  <section>
    <templateId root="2.16.840.1.113883.10.20.22.2.6.1" extension="2015-08-01"/>
    <templateId root="2.16.840.1.113883.10.20.22.2.6.1"/>
    <code code="48765-2" codeSystem="2.16.840.1.113883.6.1" displayName="Allergies and adverse reactions Document"/>
    <title>ALLERGIES AND ADVERSE REACTIONS</title>
    <text><!-- narrative table --></text>
    <entry typeCode="DRIV">
      <act classCode="ACT" moodCode="EVN">
        <!-- Allergy Problem Act -->
      </act>
    </entry>
  </section>
</component>
```

---

### Task 3.2: Medications Section

**Input element:** `<medications>/<medication>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.1.1`

---

### Task 3.3: Problems Section

**Input element:** `<problem_lists>/<problem>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.5.1`

---

### Task 3.4: Procedures Section

**Input element:** `<procedures>/<procedure>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.7.1`

---

### Task 3.5: Results Section

**Input element:** `<results>/<result>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.3.1`

---

### Task 3.6: Encounters Section

**Input element:** `<encounter_list>/<encounter>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.22.1`

---

### Task 3.7: Immunizations Section

**Input element:** `<immunizations>/<immunization>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.2.1`

---

### Task 3.8: Vital Signs Section

**Input element:** `<history_physical>/<vitals_list>/<vitals>` (no top-level `<vitals>` element exists)
**Output templateId:** `2.16.840.1.113883.10.20.22.2.4.1`

---

### Task 3.9: Social History Section

**Input element:** `<history_physical>/<social_history>` (no top-level `<social_history>` element exists)
**Output templateId:** `2.16.840.1.113883.10.20.22.2.17`

---

### Task 3.10: Payers Section

**Input element:** `<payers>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.18`

---

## Phase 4: Body Sections (Extended)

### Task 4.1: Medical Equipment Section

**Input element:** `<medical_devices>/<device>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.23`

---

### Task 4.2: Advance Directives Section

**Input element:** `<advance_directives>/<directive>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.21.1`

---

### Task 4.3: Care Team Section

**Input element:** `<care_team>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.500`

---

### Task 4.4: Functional Status Section

**Input element:** `<functional_status>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.14`

---

### Task 4.5: Mental Status Section

**Input element:** `<mental_status>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.56`

---

### Task 4.6: Plan of Care Section

**Input element:** `<planofcare>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.10`

---

### Task 4.7: Goals Section

**Input element:** `<goals>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.60`

---

### Task 4.8: Health Concerns Section

**Input element:** `<health_concerns>`
**Output templateId:** `2.16.840.1.113883.10.20.22.2.58`

---

### Task 4.9: Clinical Notes Section

**Input element:** `<clinical_notes>`

Each note entry has a `clinical_notes_type` field that determines the output section:

| `clinical_notes_type` | Output Section |
|-----------------------|----------------|
| `evaluation_note` | Skipped in note loop; generates Assessment Section separately |
| `progress_note` | Progress Note Section |
| `history_physical` | History and Physical Section (`code_text` = "History and Physical") |
| `nurse_note` | Nurse Note Section |
| `general_note` | General Note Section |
| `discharge_summary` | Discharge Summary Section |
| `procedure_note` | Procedure Note Section |
| `consultation_note` | Consultation Note Section |
| `imaging_narrative` | Imaging Narrative Section |
| `laboratory_report_narrative` | Laboratory Report Section |
| `pathology_report_narrative` | Pathology Report Section |

**Note:** The Assessment Section (templateId `2.16.840.1.113883.10.20.22.2.8`) is generated from `clinical_notes/evaluation_note` data but is handled separately in the Node code (see `getAssessments()` function in `serveccda.js`).

---

## Phase 5: Integration

### Task 5.1: Wire Converter into CcdaGenerator

**File:** `interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaGenerator.php`

```php
// Before:
private function socket_get($data): string
{
    $serviceRequestor = new CcdaServiceDocumentRequestor();
    return $serviceRequestor->socket_get($data);
}

// After:
private function generateCdaXml(string $internalXml): string
{
    $converter = new \OpenEMR\Cda\InternalToCdaConverter();
    return $converter->convert($internalXml);
}
```

Update all callers of `socket_get()` to use the renamed method in the same commit.

**Acceptance Criteria:**
- `CcdaServiceDocumentRequestorTest` updated to test `InternalToCdaConverter` directly
- Manual test: generate CCDA through UI, view renders correctly

---

### Task 5.2: End-to-End Testing

- Generate CCDA for test patient via UI
- Verify XSL transformation displays correctly
- Run through healthit.gov validator (manual)
- Test download functionality
- Test CCM (Care Coordination Module) workflows

---

## Phase 6: Cleanup

### Task 6.1: Remove Node.js Dependencies

**Note:** `node_modules/` is committed to this repo (unusual). Verify no other code depends on these modules before removing. The deletion will be a large commit (~384 directories).

```bash
rm -rf ccdaservice/serveccda.js
rm -rf ccdaservice/oe-blue-button-generate/
rm -rf ccdaservice/oe-blue-button-meta/
rm -rf ccdaservice/oe-blue-button-util/
rm -rf ccdaservice/utils/
rm -rf ccdaservice/data-stack/
rm -rf ccdaservice/node_modules/
rm ccdaservice/package.json
rm ccdaservice/package-lock.json
```

---

### Task 6.2: Remove PHP Socket Code

- Delete `CcdaServiceDocumentRequestor.php`
- Delete `CcdaServiceConnectionException.php`
- Update `CcdaGenerator::socket_get()` → rename to `generateCda()` or inline

---

### Task 6.3: Remove Global Setting

Remove or deprecate `ccda_alt_service_enable` global setting (no longer needed).

---

### Task 6.4: Update Documentation

- Update `ccdaservice/README.md` to note PHP-only architecture
- Remove Node.js setup instructions from any documentation

---

## Appendix A: Reference Files

### Key Files to Reference

| Purpose | File |
|---------|------|
| Input XML structure | `tests/Tests/data/.../ccda-example-input1.xml` |
| Expected output | `tests/Tests/data/.../ccda-example-response1.xml` |
| Existing test with normalization | `tests/Tests/Services/.../CcdaServiceDocumentRequestorTest.php` |
| Node transformation logic | `ccdaservice/serveccda.js` (search for `populateCCDA` function) |
| Node populate functions | `ccdaservice/serveccda.js` (search for `populate*` functions) |
| Blue-button section templates | `ccdaservice/oe-blue-button-generate/lib/sectionLevel2.js` |
| Blue-button entry templates | `ccdaservice/oe-blue-button-generate/lib/entryLevel/*.js` |

### Call Stack (for debugging)

```
EncounterccdadispatchController
  → CcdaGenerator::generate()
    → CcdaGenerator::create_data()
      → CcdaServiceRequestModelGenerator::create_data()
        → EncounterccdadispatchTable::get*() methods
    → CcdaGenerator::socket_get()  ← REPLACE THIS
      → InternalToCdaConverter::convert()  ← NEW
```

---

## Appendix B: Estimated Effort

| Phase | Tasks | Estimate |
|-------|-------|----------|
| 1. Foundation | 1.1-1.2 | 0.5 day |
| 2. Header | 2.1-2.6 | 1-2 days |
| 3. Core Sections | 3.1-3.10 | 3-4 days |
| 4. Extended Sections | 4.1-4.9 | 2-3 days |
| 5. Integration | 5.1-5.2 | 0.5 day |
| 6. Cleanup | 6.1-6.4 | 0.5 day |

**Total: ~8-11 days**

---

## Appendix C: Comparison to Original Plan

| Original Plan | This Plan |
|---------------|-----------|
| Data classes, hydrators, renderers | Direct XML→XML transformation |
| ~50 new PHP files | ~1-3 new PHP files |
| Abstracted architecture | Pragmatic single-class approach |
| Port blue-button-generate | Inline DOM manipulation |
| Refactor EncounterccdadispatchTable | Leave unchanged |
| ~25-35 days | ~8-11 days |

---

## Appendix D: Future Enhancements (Out of Scope)

These are known gaps or improvements not addressed by this migration:

1. **Fix dropped data:** dischargediagnosis, dischargemedication, operative note fields
2. **Refactor data layer:** Replace EncounterccdadispatchTable with Doctrine-based repositories
3. **Add more test vectors:** NKA cases, empty sections, special characters
4. **Schema validation:** Add XSD validation to test suite
5. **Schematron validation:** Add C-CDA Schematron validation
