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

class ProgressNoteParser
{
    private $xml;

    public function __construct()
    {
    }

    /**
     * Parse the progress note section with templateId OID.
     *
     * @param string $xmlContent XML content.
     * @return array Parsed notes with metadata.
     */
    public function parseProgressNotes($xmlContent): array
    {
        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);
        $xpath = new DOMXPath($dom);
        $this->xml = $dom;

        $namespaces = $this->xml->documentElement->lookupNamespaceURI(null);
        if ($namespaces) {
            $xpath->registerNamespace('ns', $namespaces);
        }

        $progressNotes = [];
        // Locate the <section> using the OID
        $section = $xpath->query("//ns:component/ns:section[ns:templateId[@root='2.16.840.1.113883.10.20.22.2.65']]")->item(0);

        if (!$section) {
            return [];
        }
        // Extract section metadata
        $sectionMetadata = $this->extractSectionMetadata($xpath, $section);
        // Extract <item> nodes and map them by ID
        $itemMap = [];
        $items = $xpath->query(".//ns:text/ns:list/ns:item", $section);
        foreach ($items as $item) {
            $id = $item->getAttribute("ID");
            if ($id) {
                $itemMap["#" . $id] = $this->extractItemContent($item);
            }
        }

        // Process <entry> nodes
        $entries = $xpath->query(".//ns:entry", $section);
        foreach ($entries as $entry) {
            $referenceNode = $xpath->query(".//ns:text/ns:reference", $entry)->item(0);
            $referenceId = $referenceNode?->getAttribute("value");
            $effectiveTime = $xpath->query(".//ns:effectiveTime", $entry)->item(0)?->getAttribute("value") ?? "Unknown";
            $authorDetails = $this->extractAuthorDetails($xpath, $entry);

            $noteContent = $itemMap[$referenceId] ?? "Referenced content not found.";
            $progressNotes[] = [
                'plan_type' => 'progress_note',
                'reference_id' => $referenceId,
                'effective_time' => $effectiveTime,
                'author_details' => $authorDetails,
                'content' => $noteContent,
            ];
        }

        return [
            'section_metadata' => $sectionMetadata,
            'progress_notes' => $progressNotes,
        ];
    }

    /**
     * Extract metadata from the section.
     *
     * @param DOMXPath $xpath
     * @param \DOMElement $section
     * @return array Section metadata.
     */
    private function extractSectionMetadata(DOMXPath $xpath, $section): array
    {
        return [
            'code' => $xpath->query(".//ns:code", $section)->item(0)?->getAttribute("code") ?? null,
            'codeSystemName' => $xpath->query(".//ns:code", $section)->item(0)?->getAttribute("codeSystemName") ?? null,
            'displayName' => $xpath->query(".//ns:code", $section)->item(0)?->getAttribute("displayName") ?? null,
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
            $child->textContent = (string)str_replace(array("\n\n\n\n", "\n\n", "\r\r", "\r\r\r\r"), "\n", $child->textContent);
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
     * @param DOMXPath $xpath
     * @param \DOMElement $entry
     * @return array Author details including WP address and phone number.
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
}
