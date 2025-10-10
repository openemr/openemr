<?php

/**
 * GeneratedCcdaResult holds the generated ccda content.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

class GeneratedCcdaResult
{
    /**
     * @param int $id The database id from the ccda table for the generated ccda.
     * @param string $uuid The database uuid from the ccda table for the generated ccda.
     * @param string $filename The human readable file name for the generated ccda
     * @param string $content The xml content for the generated ccda
     */
    public function __construct(
        private int $id,
        private string $uuid,
        private string $filename,
        private string $content
    ) {
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return GeneratedCcdaResult
     */
    public function setId(int $id): GeneratedCcdaResult
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return GeneratedCcdaResult
     */
    public function setUuid(string $uuid): GeneratedCcdaResult
    {
        $this->uuid = $uuid;
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
     * @return GeneratedCcdaResult
     */
    public function setContent(string $content): GeneratedCcdaResult
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return GeneratedCcdaResult
     */
    public function setFilename(string $filename): GeneratedCcdaResult
    {
        $this->filename = $filename;
        return $this;
    }
}
