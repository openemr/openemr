<?php

/**
 * PatientView is a mustache helper trait with various helper methods dealing specifically with the patient entity.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Qrda\Helpers;

use Mustache_Context;
use OpenEMR\Cqm\Qdm\Patient;

trait PatientView
{
    protected $provider;

    /**
     * @var Patient
     */
    protected $patient;

    public function provider()
    {
        if (!empty($this->provider)) {
            return json_decode(json_encode($this->provider));
        }
        return null;
    }

    public function patient_addresses()
    {
        return json_decode(json_encode($this->patient->addresses));
    }

    public function patient_telecoms()
    {
        return json_decode(json_encode($this->patient->telcoms));
    }

    public function patient()
    {
        if (!empty($this->patient)) {
            return json_decode(json_encode($this->patient));
        }
        return null;
    }

    public function provider_street(Mustache_Context $context)
    {
        $street = $context->find('street');
        if (!empty($street)) {
            return implode('', $street);
        }
    }

    public function provider_npi(Mustache_Context $context)
    {
        $ids = $context->find('ids');
        // the assumption is that this oid is for npi...
        return $this->get_ids_for_oids($ids, '2.16.840.1.113883.4.6');
    }

    public function provider_tin(Mustache_Context $context)
    {
        $ids = $context->find('ids');
        // the assumption is that this oid is for tin...
        return $this->get_ids_for_oids($ids, '2.16.840.1.113883.4.2');
    }

    public function provider_ccn(Mustache_Context $context)
    {
        $ids = $context->find('ids');
        // the assumption is that this oid is for ccn...
        return $this->get_ids_for_oids($ids, '2.16.840.1.113883.4.336');
    }

    public function provider_type_code(Mustache_Context $context)
    {
        return $context->find('specialty');
    }

    public function mrn(Mustache_Context $context)
    {
        // TODO: We will need to check here to see what we are supposed to be doing here... the Ruby id.to_s method
        // may be the value, or it could be some structured data element here
        if (!empty($this->patient->id)) {
            return $this->patient->id->value;
        }
        return null;
    }

    public function given_name(Mustache_Context $context)
    {
        $names = $context->find('patientName');
        if (!empty($names)) {
            return trim($names->given . ' ' . $names->middle);
        }
    }

    public function familyName(Mustache_Context $context)
    {
        $family = $context->find('patientName');
        if (is_object($family)) {
            return trim($family->family);
        }
        return "";
    }

    public function gender()
    {
        $gender_elements = array_filter(
            $this->patient->dataElements,
            function ($de) {
                return $de->_type == "QDM::PatientCharacteristicSex";
            }
        );
        if (empty($gender_elements)) {
            return false;
        } else if (empty($gender_elements[0]->dataElementCodes)) {
            return false;
        } else {
            return $gender_elements[0]->dataElementCodes[0]['code'];
        }
    }

    public function birthdate()
    {
        $birthdate_elements = array_filter(
            $this->patient->dataElements,
            function ($de) {
                return $de->_type == "QDM::PatientCharacteristicBirthdate";
            }
        );
        if (empty($birthdate_elements)) {
            return "None";
        } else {
            return $birthdate_elements[0]['expiredDateTime'];
        }
    }

    public function expiration()
    {
        $elements = array_filter(
            $this->patient->dataElements,
            function ($de) {
                return $de->_type == "QDM::PatientCharacteristicExpired";
            }
        );
        if (empty($elements)) {
            return "None";
        } else {
            return $elements[0]['expiredDatetime'];
        }
    }

    public function race()
    {
        $elements = array_filter(
            $this->patient->dataElements,
            function ($de) {
                return $de->_type == "QDM::PatientCharacteristicRace";
            }
        );
        if (empty($elements)) {
            return false;
        } else if (empty($elements[0]->dataElementCodes)) {
            return false;
        } else {
            return $elements[0]->dataElementCodes[0]['code'];
        }
    }

    public function ethnic_group()
    {
        $elements = array_filter(
            $this->patient->dataElements,
            function ($de) {
                return $de->_type == "QDM::PatientCharacteristicEthnicity";
            }
        );
        if (empty($elements)) {
            return false;
        } else if (empty($elements[0]->dataElementCodes)) {
            return false;
        } else {
            return $elements[0]->dataElementCodes[0]['code'];
        }
    }

    public function payer()
    {
        $elements = array_filter(
            $this->patient->dataElements,
            function ($de) {
                return $de->_type == "QDM::PatientCharacteristicPayer";
            }
        );
        if (empty($elements)) {
            return false;
        } else if (empty($elements[0]->dataElementCodes)) {
            return false;
        } else {
            return $elements[0]->dataElementCodes[0]['code'];
        }
    }

    /**
     * Given a list of ids and an oid to filter on, return all of the ids that match this filter
     *
     * @param  $ids
     * @param  $oid
     * @return array|null
     */
    private function get_ids_for_oids($ids, $oid)
    {
        if (!empty($ids)) {
            $mappedIds = [];
            foreach ($ids as $id) {
                if ($id['namingSystem'] == $oid) {
                    $mappedIds[] = $id;
                }
            }
            if (!empty($mappedIds)) {
                return $mappedIds;
            }
        }
        return null;
    }
}
