<?php

/**
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Cda;

use DOMDocument;
use DOMXPath;

class ClinicalNoteParser
{
    private $xml;

    public function __construct()
    {
    }

    private function fetchPlanTypeByCode($code)
    {
        // TODO fix imaging_narrative duplicate with procedure_note code in list then use the list to look up.
        $options = [
            '28570-0' => 'procedure_note',
            '11506-3' => 'progress_note',
            '34746-8' => 'nurse_note',
            '34117-2' => 'history_physical',
            '34109-9' => 'general_note',
            '18842-5' => 'discharge_summary',
            '18748-4' => 'imaging_narrative',
            '81222-2' => 'consultation_note',
        ];
        return $options[$code] ?? null;
    }

    /**
     * Parse the clinical note section with templateId OID.
     *
     * @param \DOMNode $node
     * @return string
     */
    private function innerXML($node): string
    {
        $xml = '';
        foreach ($node->childNodes as $child) {
            $xml .= $child->C14N();
        }
        return "<text>" . $xml . "</text>";
    }

    /**
     * Parse the clinical note section by template OID and note type code.
     *
     * @param string $xmlContent XML content of the entire document.
     * @param string $code       The code that identifies the note type (e.g. "28570-0").
     * @return array Parsed anf formatted text notes with metadata.
     */
    public function parseClinicalNotesByCode(string $xmlContent, string $code = "", $batchFlag = 0): array
    {
        $textXML = "";

        // Note that this is somewhat inefficient as we are loading the XML twice.
        // Could pass in just section XML instead of entire document.
        // Since the parser is written to parse XML, I need to convert xpath back to
        // string XML that includes the found section from xpath search.
        // This is done by using the C14N() method which returns a canonical XML string.
        // TODO: explore if we can use the xpath to get the section and then pass xpath to parser.

        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);
        $xpath = new DOMXPath($dom);
        $this->xml = $dom;
        // Register the default namespace as "ns"
        $ns = $dom->documentElement->lookupNamespaceURI(null);
        if ($ns) {
            $xpath->registerNamespace('ns', $ns);
        }

        $clinicalNotes = [];

        // Locate the <section> using the OID and the <code> for note type.
        $section_path = "//ns:component/ns:section[ns:templateId[@root='2.16.840.1.113883.10.20.22.2.65' and @extension='2016-11-01'] and ns:code[@code='$code']]";
        $section = $xpath->query($section_path)->item(0);
        // Check if the section is empty
        if (!$section) {
            return [];
        }
        // Extract section metadata.
        $sectionMetadata = $this->extractSectionMetadata($xpath, $section);

        $textNode = $xpath->query(".//ns:text", $section)->item(0);
        if ($textNode) {
            $textXML = $textNode->C14N();
            // todo: explore why can't recover namespace with this method!
            // todo: this method ensures all nested content is included. Generally, this is not needed.
            //$textXML = $this->innerXML($textNode);
        } else {
            $narrativeNotes = [];
        }

        // Extract narrative notes from the <text> section.
        $items = $xpath->query(".//ns:text/ns:list/ns:item", $section);
        foreach ($items as $item) {
            $id = $item->getAttribute("ID");
            if ($id) {
                if (!$batchFlag) {
                    $narrativeNotes["#" . $id] = $this->extractNoteFromTextSection($textXML, $id);
                }
            }
        }
        // If batchFlag is set, extract all narrative notes from the <text> section.
        // This is used for the batch export of clinical notes. Currently not used but available.
        // Method called without item id is flag to batch
        if ($batchFlag) {
            $narrativeNotes = $this->extractNoteFromTextSection($textXML);
        }

        // Process <entry> nodes
        $entries = $xpath->query(".//ns:entry", $section);
        foreach ($entries as $entry) {
            $referenceNode = $xpath->query(".//ns:text/ns:reference", $entry)->item(0);
            $referenceId = $referenceNode ? $referenceNode->getAttribute("value") : "";
            $effectiveTime = $xpath->query(".//ns:effectiveTime", $entry)->item(0)?->getAttribute("value") ?? "Unknown";
            $authorDetails = $this->extractAuthorDetails($xpath, $entry);
            $encounterNode = $xpath->query(".//ns:entryRelationship/ns:encounter/ns:id", $entry)->item(0);
            $encounter_root = $encounterNode?->getAttribute("root");
            $encounter_extension = $encounterNode?->getAttribute("extension");

            // Look up the note content from the narrative notes array.
            $noteContent = $narrativeNotes[$referenceId] ?? "Referenced by $referenceId content not found.";

            $type = $this->fetchPlanTypeByCode($sectionMetadata['code']) ?? null;
            $clinicalNotes[] = [
                'plan_type' => $type,
                'plan_code' => $sectionMetadata['code'],
                'plan_code_system_name' => $sectionMetadata['codeSystemName'],
                'plan_display_name' => $sectionMetadata['displayName'],
                'reference_id' => $referenceId,
                'effective_time' => $effectiveTime,
                'author_details' => $authorDetails,
                'encounter_root' => $encounter_root,
                'encounter_extension' => $encounter_extension,
                'content' => $noteContent,
            ];
        }

        return [
            'section_metadata' => $sectionMetadata,
            'clinical_notes' => $clinicalNotes,
        ];
    }

    /**
     * Extract metadata from the section.
     *
     * @param DOMXPath    $xpath
     * @param \DOMElement $section
     * @return array Section metadata.
     */
    private function extractSectionMetadata(DOMXPath $xpath, $section): array
    {
        $codeNode = $xpath->query(".//ns:code", $section)->item(0);
        return [
            'code' => $codeNode?->getAttribute("code"),
            'codeSystemName' => $codeNode?->getAttribute("codeSystemName"),
            'displayName' => $codeNode?->getAttribute("displayName"),
        ];
    }

    /**
     * Extract text content from an <item>.
     *
     * @param \DOMElement $item
     * @return string Extracted content.
     */
    private function extractItemContent($item): string
    {
        $contentLines = [];
        foreach ($item->childNodes as $child) {
            $child->textContent = str_replace(["\n\n\n\n", "\n\n", "\r\r", "\r\r\r\r"], "\n", $child->textContent);
            $text = trim($child->textContent);
            if ($text) {
                $contentLines[] = $text;
            }
        }
        return implode("\n", $contentLines);
    }

    /**
     * Extract author details from an <entry>.
     *
     * @param DOMXPath    $xpath
     * @param \DOMElement $entry
     * @return array
     */
    private function extractAuthorDetails(DOMXPath $xpath, $entry): array
    {
        $authorDetails = [
            'name' => 'Unknown Author',
            'wp_address' => [
                'street' => null,
                'city' => null,
                'state' => null,
                'postalCode' => null,
                'country' => null,
            ],
            'wp_phone' => 'Unknown Phone',
        ];
        // Extract author name
        $authorNode = $xpath->query(".//ns:author/ns:assignedAuthor/ns:assignedPerson/ns:name", $entry)->item(0);
        if ($authorNode) {
            $given = $xpath->query(".//ns:given", $authorNode)->item(0)?->nodeValue ?? "";
            $family = $xpath->query(".//ns:family", $authorNode)->item(0)?->nodeValue ?? "";
            $authorDetails['name'] = trim("$given $family");
            $authorDetails['given'] = trim("$given");
            $authorDetails['family'] = trim("$family");
        }
        // Extract WP address
        $wpAddressNode = $xpath->query(".//ns:author/ns:assignedAuthor/ns:addr[@use='WP']", $entry)->item(0);
        if ($wpAddressNode) {
            $authorDetails['wp_address'] = [
                'street' => $xpath->query(".//ns:streetAddressLine", $wpAddressNode)->item(0)?->textContent ?? null,
                'city' => $xpath->query(".//ns:city", $wpAddressNode)->item(0)?->textContent ?? null,
                'state' => $xpath->query(".//ns:state", $wpAddressNode)->item(0)?->textContent ?? null,
                'postalCode' => $xpath->query(".//ns:postalCode", $wpAddressNode)->item(0)?->textContent ?? null,
                'country' => $xpath->query(".//ns:country", $wpAddressNode)->item(0)?->textContent ?? null,
            ];
        }
        // Extract WP phone
        $wpPhoneNode = $xpath->query(".//ns:author/ns:assignedAuthor/ns:telecom[@use='WP']", $entry)->item(0);
        if ($wpPhoneNode) {
            $rawPhone = $wpPhoneNode->getAttribute("value");
            $cleanPhone = str_replace("tel:", "", $rawPhone);
            $authorDetails['wp_phone'] = $cleanPhone;
        }

        return $authorDetails;
    }

    /**
     * Given the XML for a <text> section fetch human‑readable narrative paragraph.
     *
     * @param string $textXML The XML string representing the <text> element.
     * @param string $itemId  The ID of the <item> to process (from the item’s ID attribute).
     * @return string|array The narrative paragraph or an error message.
     */
    public function extractNoteFromTextSection(string $textXML, string $itemId = ''): string|array
    {
        libxml_use_internal_errors(true);
        // Remove extraneous whitespace between tags
        $textXML = trim((string) preg_replace('/>\s+</', '><', $textXML));
        $dom = new DOMDocument();
        if (!$dom->loadXML($textXML)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            return "Error loading XML: " . implode("; ", array_map(fn($err): string => trim($err->message), $errors));
        }
        $xpath = new DOMXPath($dom);
        // Look up the default namespace and register it (if needed).
        $ns = $dom->documentElement->lookupNamespaceURI(null);
        if ($ns) {
            $xpath->registerNamespace('ns', $ns);
        }

        // Instead of a static path can search for all <item> elements with ID or id attributes.
        // TODO: narrow the search to handle those text sections that don't use item to id but parts of a table.
        if ($itemId) {
            $items = $xpath->query("//*[local-name()='item' and (@ID='$itemId' or @id='$itemId')]");
        } else {
            $items = $xpath->query("//*[local-name()='item' and (@ID or @id)]");
        }

        if ($items->length === 0) {
            return "Item with ID '$itemId' not found.";
        }

        $fullNarrative = [];
        foreach ($items as $item) {
            // Use uppercase or lowercase attribute
            $id = $item->getAttribute("ID") ?: $item->getAttribute("id");
            // Get the introductory paragraph (if present)
            $paraText = "";
            $paragraphNodes = $xpath->query("ns:paragraph", $item);
            $captionNodes = $xpath->query("ns:caption", $item);
            if ($captionNodes->length > 0) {
                $paraText = trim((string) $captionNodes->item(0)->nodeValue) . "\n";
            }
            if ($paragraphNodes->length > 0) {
                $paraText .= trim((string) $paragraphNodes->item(0)->nodeValue);
            }
            // First table in the item
            $tableNodes = $xpath->query(".//ns:table", $item);
            if ($tableNodes->length === 0) {
                // No table found, so just extract the text content.
                // Most likely a narrative note enclosed with in content and/or paragraph tags.
                $note = $this->extractItemContent($item);
                $fullNarrative["#" . $id] = trim($note);
                continue;
            }
            $table = $tableNodes->item(0);

            $headers = [];
            $theadRows = $xpath->query("ns:thead/ns:tr", $table);
            if ($theadRows->length > 0) {
                foreach ($xpath->query("ns:th", $theadRows->item(0)) as $th) {
                    $headers[] = trim((string) $th->nodeValue);
                }
            }
            if (empty($headers)) {
                $firstRowCells = $xpath->query("ns:tbody/ns:tr[1]/*", $table);
                $colCount = $firstRowCells->length;
                for ($i = 1; $i <= $colCount; $i++) {
                    $headers[] = "Column $i";
                }
            }

            $narrativeRows = [];
            foreach ($xpath->query("ns:tbody/ns:tr", $table) as $tr) {
                $cellParts = [];
                $colIndex = 0;
                foreach ($xpath->query("*", $tr) as $cell) {
                    $header = $headers[$colIndex] ?? "Column " . ($colIndex + 1);
                    $cellValue = trim(str_replace("\n", "", $cell->nodeValue));
                    if ($cellValue !== "") {
                        $cellParts[] = "$cellValue";
                    }
                    $colIndex++;
                }
                if (!empty($cellParts)) {
                    $narrativeRows[] = implode(";\n", $cellParts) . ".\n";
                }
            }

            $note = trim($paraText . " " . implode(" ", $narrativeRows));
            // match syntax of other narrative notes
            $fullNarrative["#" . $id] = $note;
        }

        return $itemId ? ($fullNarrative["#" . $itemId] ?? "Item with ID '$itemId' not found.") : $fullNarrative;
    }
}
