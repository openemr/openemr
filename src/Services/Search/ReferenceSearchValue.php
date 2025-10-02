<?php

/**
 * Reference Search Value contains a reference id and
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Common\Uuid\UuidRegistry;

class ReferenceSearchValue implements \Stringable
{
    /**
     * @var string|number
     */
    private $id;


    /**
     * @param mixed $id
     * @param string|null $resource
     * @param bool $isUuid Tracks if the reference search value is a unique user id (stored in binary) and needs to be converted. All reference values should be uuids but this gives us the option in the future to have non-uuids if this changes
     */
    public function __construct(
        $id,
        private $resource = null,
        private $isUuid = false
    ) {
        if ($this->isUuid) {
            if (UuidRegistry::isValidStringUUID($id)) {
                $this->id = UuidRegistry::uuidToBytes($id);
            } else {
                throw new \InvalidArgumentException("UUID columns must be a valid UUID string");
            }
        } else {
            $this->id = $id;
        }
    }

    /**
     * Parses a relative URL to create the reference search value.  For example for a relative url such as Patient/23
     * return a reference search value with Patient as the resource and 23 as the id.
     * @param $relativeUri string the URI to parse
     * @return ReferenceSearchValue
     */
    public static function createFromRelativeUri($relativeUri, $isUuid = false)
    {
        $id = $relativeUri;
        $resource = null;
        if (str_contains((string) $relativeUri, "/")) {
            $parts = explode("/", (string) $relativeUri);
            $resource = $parts[0];
            $id = end($parts);
        }
        $reference = new ReferenceSearchValue($id, $resource, $isUuid);
        return $reference;
    }

    /**
     * @return string
     */
    public function getResource(): ?string
    {
        return $this->resource;
    }

    /**
     * @return number|string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getHumanReadableId()
    {
        $id = $this->getId();
        if ($this->isUuid && !empty($id)) {
            return UuidRegistry::uuidToString($id);
        } else {
            return $id;
        }
    }

    public function __toString(): string
    {
        if ($this->getResource()) {
            return $this->getResource() . "/" . $this->getHumanReadableId();
        } else {
            return (string) $this->getHumanReadableId();
        }
    }
}
