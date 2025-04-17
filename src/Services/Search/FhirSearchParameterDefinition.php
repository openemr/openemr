<?php

/**
 * FhirSearchParameterDefinition represents a field in FHIR that searches can be conducted against.  It defines what fields
 * that FHIR search field maps onto, whether that is a single OpenEMR data field or many fields (composite field).  The
 * type of field is represented in the definition.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

class FhirSearchParameterDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;
    /**
     * @var string[]
     */
    private $mappedFields;

    /**
     * @var string[]
     */
    private $options;

    public function __construct($name, $type, $mappedFields, $options = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->mappedFields = $mappedFields;
        $this->options = $options;
    }

    /**
     * @return string|ServiceField[]
     */
    public function getMappedFields()
    {
        return $this->mappedFields;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
