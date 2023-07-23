<?php

/**
 * PatientDocumentViewCCDAEvent is fired when a ccda document (CCD/CCR/etc) is being viewed in the system.  Consumers
 * of the event can transform the content of the CCDA before it is displayed to the end user or downloaded.
 *
 * The format field can be used to determine whether the output will be a xml, or html document allowing consumers to
 * modify the contents.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientDocuments;

class PatientDocumentViewCCDAEvent
{
    /**
     * Name of the event
     */
    const EVENT_NAME = "patient.ccda.view";

    /**
     * @var int The database identifier for the generated ccda as stored in the ccda table
     */
    private $ccdaId;

    /**
     * @var int The database identifier for the ccda document stored in the database if it exists
     */
    private $documentId;

    /**
     * @var string The type of ccda document.  Valid options are currently CCD/Referral/CCDA
     */
    private $ccdaType;

    /**
     * @var string The CCDA document content that is to be viewed / output to the screen by the final event consumer
     */
    private $content;

    /**
     * @var string The path to the stylesheet that was last used to transform the content (empty if no stylesheet was used)
     */
    private $stylesheetPath;

    /**
     * @var string xml|html The output format we want to view the ccda in.  Default is html for the human readable format
     */
    private $format;

    /**
     * @var boolean True if the ccda should ignore the user preferences (trimming, sort order, etc).  Default false
     */
    private $ignoreUserPreferences;

    public function __construct()
    {
        $this->format = "html";
        $this->ignoreUserPreferences = false;
        $this->stylesheetPath = "";
        $this->ccdaId = 0;
        $this->documentId = 0;
    }

    /**
     * @return int
     */
    public function getCcdaId(): int
    {
        return $this->ccdaId;
    }

    /**
     * @param int $ccdaId
     * @return PatientDocumentViewCCDAEvent
     */
    public function setCcdaId(int $ccdaId): PatientDocumentViewCCDAEvent
    {
        $this->ccdaId = $ccdaId;
        return $this;
    }

    /**
     * @return int
     */
    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    /**
     * @param int $documentId
     * @return PatientDocumentViewCCDAEvent
     */
    public function setDocumentId(int $documentId): PatientDocumentViewCCDAEvent
    {
        $this->documentId = $documentId;
        return $this;
    }

    /**
     * @return string
     */
    public function getStylesheetPath(): string
    {
        return $this->stylesheetPath;
    }

    /**
     * @param string $stylesheetPath
     * @return PatientDocumentViewCCDAEvent
     */
    public function setStylesheetPath(string $stylesheetPath): PatientDocumentViewCCDAEvent
    {
        $this->stylesheetPath = $stylesheetPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getCcdaType(): string
    {
        return $this->ccdaType;
    }

    /**
     * @param string $ccdaType
     * @return PatientDocumentViewCCDAEvent
     */
    public function setCcdaType(string $ccdaType): PatientDocumentViewCCDAEvent
    {
        $this->ccdaType = $ccdaType;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return PatientDocumentViewCCDAEvent
     */
    public function setContent(string $content): PatientDocumentViewCCDAEvent
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return PatientDocumentViewCCDAEvent
     */
    public function setFormat(string $format): PatientDocumentViewCCDAEvent
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return bool
     */
    public function shouldIgnoreUserPreferences(): bool
    {
        return $this->ignoreUserPreferences;
    }

    /**
     * @param bool $ignoreUserPreferences
     * @return PatientDocumentViewCCDAEvent
     */
    public function setIgnoreUserPreferences(bool $ignoreUserPreferences): PatientDocumentViewCCDAEvent
    {
        $this->ignoreUserPreferences = $ignoreUserPreferences;
        return $this;
    }
}
