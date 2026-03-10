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
use DOMElement;
use DOMXPath;

class CdaTextParser
{
    private readonly DOMDocument $xml;

    public function __construct(string $xmlContent, private readonly string $title = "Imported CarePlan Notes.")
    {
        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);
        $this->xml = $dom;
    }

    /**
     * Parse the section with a specific code to extract notes.
     *
     * @param string $sectionCode Section code to search for.
     * @return array<int, array{id: string, caption: string, content: string}> Extracted notes.
     */
    public function parseSectionByCode($sectionCode): array
    {
        $notes = [];
        $xpath = new DOMXPath($this->xml);

        // Register namespaces
        $namespaces = $this->xml->documentElement?->lookupNamespaceURI(null);
        if ($namespaces !== null) {
            $xpath->registerNamespace('ns', $namespaces); // 'ns' is a generic prefix
        }

        // use namespace prefix if present
        $query = $namespaces !== null ? "//ns:section[ns:code[@code='{$sectionCode}']]" : "//section[code[@code='{$sectionCode}']]";
        $sections = $xpath->query($query);

        if ($sections === false) {
            return $notes;
        }

        foreach ($sections as $section) {
            if (!$section instanceof DOMElement) {
                continue;
            }
            $listResult = $xpath->query(".//ns:list | .//list", $section);
            $list = $listResult !== false ? $listResult->item(0) : null;
            if ($list instanceof DOMElement) {
                foreach ($list->getElementsByTagName("item") as $item) {
                    $id = $item->getAttribute("ID") ?: "";
                    $caption = $item->getElementsByTagName("caption")->item(0)?->textContent ?: "";
                    $content = $this->extractItemContent($item);

                    $notes[] = [
                        'id' => $id,
                        'caption' => $caption,
                        'content' => $content,
                    ];
                }
            }
        }

        return $notes;
    }

    /**
     * Extract all text content from an <item>, including nested lists.
     *
     * @param DOMElement $item
     * @return string Extracted content.
     */
    private function extractItemContent(DOMElement $item, int $level = 0): string
    {
        $contentLines = [];
        $indent = str_repeat("    ", $level); // Indentation for nested content

        foreach ($item->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = trim((string) preg_replace('/\s+/', ' ', (string) $child->nodeValue)); // Normalize spaces
                if ($text !== '') {
                    $contentLines[] = $indent . $text;
                }
            } elseif ($child instanceof DOMElement) {
                if ($child->tagName === 'list') {
                    // Recursive parsing for nested lists
                    foreach ($child->getElementsByTagName("item") as $nestedItem) {
                        $nestedContent = $this->extractItemContent($nestedItem, $level + 1);
                        if ($nestedContent) {
                            $contentLines[] = $nestedContent;
                        }
                    }
                } else {
                    $text = trim((string) preg_replace('/\s+/', ' ', $child->textContent)); // Normalize spaces
                    if ($text !== '') {
                        $contentLines[] = $indent . $text;
                    }
                }
            }
        }

        return implode("\n", array_filter($contentLines));
    }

    /**
     * Generate textareas from parsed notes.
     *
     * @param array<int, array{id: string, caption: string, content: string}> $notes Parsed notes.
     * @return string Generated HTML.
     */
    public function generateConsolidatedTextNote(array $notes): string
    {
        if (count($notes) === 0) {
            return '';
        }
        $text = "\n{$this->title}\n";
        foreach ($notes as $index => $note) {
            $index++;
            $text .= "Item {$index} {$note['id']} {$note['caption']}\n";
            $text .= $note['content'];
            $text .= "\n";
        }
        return $text;
    }
}
