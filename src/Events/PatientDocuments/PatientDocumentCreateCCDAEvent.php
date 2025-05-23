<?php

/**
 * PatientDocumentCreateCCDAEvent is fired when the dispatcher wants to generate a ccda document in the system for a patient.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientDocuments;

use OpenEMR\Services\Search\DateSearchField;
use DateTime;

class PatientDocumentCreateCCDAEvent
{
    const EVENT_NAME_CCDA_CREATE = "patient.ccda.create";

    /**
     * @var int
     */
    private $pid;

    /**
     * @var string[] The individual components to include in the ccda, empty if all components are to be generated
     */
    private $components;

    /**
     * @var string[] The sections to include in the ccda, empty if all sections are to be generated
     */
    private $sections;

    /**
     * The output format
     * @var xml|html|zip
     */
    private $format;

    /**
     * @var string The recipient of the CCDA
     */
    private $recipient;

    /**
     * @var int The database primary key to the ccda that was generated in the system.
     */
    private $ccdaId;

    /**
     * @var string the URL to the file that was generated.
     */
    private $fileUrl;

    /**
     * @var DateTime The start date from which to include clinically relevant data for the generated CCDA.  Null if all data from the start of the system is relevant.
     */
    private $dateFrom;

    /**
     * If a dateFromSearchField is populated it will take precedence over the dateFrom field
     * @var DateSearchField Complex date search field - The start date from which to include clinically relevant data for the generated CCDA.  Null if all data from the start of the system is relevant.
     */
    private $dateFromSearchField;

    /**
     * @var DateTime The end date to include clinically relevant data for the generated CCDA.  Null if there is no end date.
     */
    private $dateTo;

    /**
     * @var "ccd"|"referral"|"careplan"|"toc"
     */
    private $documentType;

    public function __construct($pid)
    {
        $this->setPid($pid);
        $this->setComponents(array());
        $this->setFormat("xml");
        $this->setRecipient("self");
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->documentType = "ccd";
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     * @return PatientDocumentCreateCCDAEvent
     */
    public function setPid(int $pid): PatientDocumentCreateCCDAEvent
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @param string $component
     */
    public function addComponent(string $component)
    {
        $this->components[] = $component;
    }

    /**
     * @return string[]
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * @param string[] $components
     * @return PatientDocumentCreateCCDAEvent
     */
    public function setComponents(array $components): PatientDocumentCreateCCDAEvent
    {
        $this->components = array_filter($components, 'is_string');
        return $this;
    }

    /**
     * @return html|xml|zip
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param html|xml|zip $format
     * @return PatientDocumentCreateCCDAEvent
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipient(): string
    {
        return $this->recipient;
    }

    /**
     * @param string $recipient
     * @return PatientDocumentCreateCCDAEvent
     */
    public function setRecipient(string $recipient): PatientDocumentCreateCCDAEvent
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCcdaId(): ?int
    {
        return $this->ccdaId;
    }

    /**
     * @param int $ccdaId
     * @return PatientDocumentCreateCCDAEvent
     */
    public function setCcdaId(int $ccdaId): PatientDocumentCreateCCDAEvent
    {
        $this->ccdaId = $ccdaId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileUrl(): string
    {
        return $this->fileUrl;
    }

    /**
     * @param string $fileUrl
     * @return PatientDocumentCreateCCDAEvent
     */
    public function setFileUrl(string $fileUrl): PatientDocumentCreateCCDAEvent
    {
        $this->fileUrl = $fileUrl;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateFrom(): ?DateTime
    {
        return $this->dateFrom;
    }

    /**
     * @param DateTime $dateFrom
     * @return PatientDocumentCreateCCDAEvent
     */
    public function setDateFrom(?DateTime $dateFrom): PatientDocumentCreateCCDAEvent
    {
        $this->dateFrom = $dateFrom;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateTo(): ?DateTime
    {
        return $this->dateTo;
    }

    /**
     * @param DateTime|null $dateTo
     * @return PatientDocumentCreateCCDAEvent
     */
    public function setDateTo(?DateTime $dateTo): PatientDocumentCreateCCDAEvent
    {
        $this->dateTo = $dateTo;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @param string[] $sections
     * @return PatientDocumentCreateCCDAEvent
     */
    public function setSections(array $sections): PatientDocumentCreateCCDAEvent
    {
        $this->sections = $sections;
        return $this;
    }


    public function addSection(string $section)
    {
        $this->sections[] = $section;
    }


    public function getSectionsAsString(): string
    {
        return $this->getCcdaStringFormat($this->sections ?? []);
    }

    public function getComponentsAsString(): string
    {
        return $this->getCcdaStringFormat($this->components ?? []);
    }

    private function getCcdaStringFormat(array $arr)
    {
        return implode("|", $arr);
    }

    /**
     * @return string
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * @param string $documentType The c-cda document type to generate options are "ccd", "careplan", "toc", "referral"
     * @return PatientDocumentCreateCCDAEvent
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;
        return $this;
    }
}
