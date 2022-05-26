<?php

/**
 * CcdaUserPreferencesTransformer transforms a ccda xml document using the user preferences provided.  It will truncate
 * and sort the ccda xml according to the preferences provided.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

class CcdaUserPreferencesTransformer
{
    /**
     * @var array the ccda section order to display items in.  Each section is identified by its oid
     */
    private $sortPreferences;

    /**
     * @var int the maximum number of clinical section contents to display in the ccda
     */
    private $maxSections;

    public function __construct($maxSections = null, $sortPreferences = array())
    {
        // TODO: @adunsulag remove these defaults
        $this->maxSections = intval($maxSections ?? 0);

        $this->sortPreferences = $sortPreferences ?? array();

        /*[
            '2.16.840.1.113883.10.20.22.2.1.1' // Medications
            ,'2.16.840.1.113883.10.20.22.2.6.1' // Allergies
        ];
        */
    }

    public function transform($content)
    {
        $ccdaDoc = new \DOMDocument();
        $ccdaDoc->loadXML($content);

        $xpath = new \DOMXPath($ccdaDoc);
        $maxChildren = $this->maxSections;

        // first we do our sort order here
        $sortedSections = $this->sortPreferences;

        // reverse sort as we are going to be prepending these
        $sortedSections = array_reverse($sortedSections);

        $sortNodes = [];

        $xpath->registerNamespace('n1', "urn:hl7-org:v3");

        // first off we need to grab our structured bodies here... should only be one
        $query = "/n1:ClinicalDocument/n1:component/n1:structuredBody";
        $structuredBodies = $xpath->query($query);

        foreach ($structuredBodies as $body) {
            if (!empty($sortedSections)) {
                foreach ($sortedSections as $section) {
                    $foundSectionNodes = $xpath->query("n1:component[n1:section/n1:templateId/@root = '" . $section . "']", $body);
                    if ($foundSectionNodes !== false && $foundSectionNodes->length > 0) {
                        foreach ($foundSectionNodes as $node) {
                            // if our found node is already the first child we will just leave it alone and skip over.
                            if ($node !== $body->firstChild) {
                                // if firstChild is empty it will just append
                                $body->insertBefore($node, $body->firstChild);
                            }
                        }
                    }
                }
            }
            // anything 0 or less is treated as no limit.
            if ($maxChildren > 0) {
                // now that we've sorted everything, start at the end of our node list and just truncate our
                // component sections until we get to our max number of nodes
                if ($body->childNodes && $body->childNodes->length > $maxChildren) {
                    for ($i = $body->childNodes->length; $i > $maxChildren; --$i) {
                        $body->removeChild($body->lastChild);
                    }
                }
            }
        }

        return $ccdaDoc->saveXML();
    }

    /**
     * @return array
     */
    public function getSortPreferences(): array
    {
        return $this->sortPreferences;
    }

    /**
     * @param array $sortPreferences
     * @return CcdaUserPreferencesTransformer
     */
    public function setSortPreferences(array $sortPreferences): CcdaUserPreferencesTransformer
    {
        $this->sortPreferences = $sortPreferences;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxSections(): int
    {
        return $this->maxSections;
    }

    /**
     * @param int $maxSections
     * @return CcdaUserPreferencesTransformer
     */
    public function setMaxSections(int $maxSections): CcdaUserPreferencesTransformer
    {
        $this->maxSections = $maxSections;
        return $this;
    }
}
