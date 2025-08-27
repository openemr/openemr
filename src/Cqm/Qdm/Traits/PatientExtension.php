<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Cqm\Qdm\Traits;

use OpenEMR\Cqm\Qdm\BaseTypes\Address;
use OpenEMR\Cqm\Qdm\BaseTypes\Telcom;
use OpenEMR\Cqm\Qdm\Identifier;

trait PatientExtension
{
    public $patientName;

    /**
     * @var Identifier Used to store our OpenEMR PID
     */
    public $id;

    /**
     * @var Identifier BSON representation that is preserved through cqm-execution
     */
    public $_id;

    public $addresses = [];
    public $telcoms = [];

    // These are the "data criteria", or QDM datatype elements that exist on a
    // patient.
    public $dataElements = [];

    // This field is for application specific information only. If both Bonnie
    // Cypress use a common field, it should be made a field on this model,
    // and not put into extendedData.
    public $extendedData = [];

    // Add a data element to the dataElements array
    public function add_data_element($dataElement)
    {
        $this->dataElements[] = $dataElement;
    }

    /**
     * @param ?string $category
     * @param ?string $status
     * @return mixed
     *
     * Return the first actual code for this data element code. This is used for building a hash count of codes
     */
    public function extract_first_code($category = null, $status = null)
    {
        $code = null;
        $data_elements = $this->get_data_elements($category, $status);
        if (count($data_elements) > 0) {
            $first_element = $data_elements[0];
            if (count($first_element->dataElementCodes) > 0) {
                $first_code = $first_element->dataElementCodes[0];
                $code = $first_code->code;
            }
        }
        return $code;
    }

    // Returns an array of elements that exist on this patient. Optionally
    // takes a category and/or, which returns all elements of that QDM
    // category. Example: patient.get_data_elements('encounters')
    // will return all Encounter QDM data types active on the patient.
    public function get_data_elements($category = null, $status = null)
    {
        $categoryElements = [];
        foreach ($this->dataElements as $dataElement) {
            if ($dataElement->qdmCategory === $category) {
                if (
                    (
                        $status !== null &&
                        $status == $dataElement->qdmStatus
                    ) ||
                    $status === null
                ) {
                    $categoryElements[] = $dataElement;
                }
            }
        }

        return $categoryElements;
    }

    public function get_by_hqmf_oid($hqmfOid)
    {
        $element = false;
        foreach ($this->dataElements as $dataElement) {
            if ($dataElement->hqmfOid === $hqmfOid) {
                $element = $dataElement;
                break;
            }
        }

        return $element;
    }

    public function conditions()
    {
        return $this->get_data_elements('conditions');
    }

    public function addAddress(Address $address)
    {
        $this->addresses[] = $address;
    }

    public function addTelcom(Telcom $telcom)
    {
        $this->telcoms[]= $telcom;
    }
}
