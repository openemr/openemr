<?php

/**
 * Cat1View is a mustache helper trait with helper methods specific to the Cat1 report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Qrda\Helpers;

use Mustache_Context;
use OpenEMR\Services\CodeTypesService;

trait Cat1View
{
    public function negation_ind(Mustache_Context $context): string
    {
        $negationRationale = $context->find('negationRationale');
        return empty($negationRationale) ? "" : " negationInd=\"true\"";
    }

    public function negated(Mustache_Context $context): bool
    {
        return !empty($context->find('negationRationale'));
    }

    public function multiple_codes(Mustache_Context $context): bool
    {
        $codes = $context->find('dataElementCodes');
        if (!empty($codes)) {
            return count($codes) > 1;
        }
        // if its empty there are not multiple codes
        return false;
    }

    public function display_author_dispenser_id(Mustache_Context $context): bool
    {
        $category = $context->find('qdmCategory');
        $status = $context->find('qdmStatus');
        return $category == 'medication' && $status == 'dispensed';
    }

    public function display_author_prescriber_id(Mustache_Context $context): bool
    {
        $category = $context->find('qdmCategory');
        $status = $context->find('qdmStatus');
        return $category == 'medication' && $status == 'order';
    }

    public function id_or_null_flavor(Mustache_Context $context): bool
    {
        $namingSystem = $context->find('namingSystem');
        $value = $context->find('value');

        if (empty($namingSystem) && empty($value)) {
            return "<id nullFlavor=\"NA\"/>";
        } else {
            return "<id root=\"" . $namingSystem . "\" extension=\"" . $value . "\"/>";
        }
    }

    public function code_and_codesystem(Mustache_Context $context)
    {
        $oid = $context->find('system');
        $code = $context->find('code');
        if (empty($oid) && !empty($code)) {
            $codeService = new CodeTypesService();
            $code_tmp = $codeService->resolveCode($code, '', '');
            $code = $code_tmp['code'] ?? null;
            $oid = $code_tmp['system_oid'] ?? null;
        }
        if (empty($oid)) {
            return "nullFlavor=\"NA\" sdtc:valueSet=\"$code\"";
        } else {
            $codeSystem = $this->get_code_system_for_oid($oid);
            return "code=\"" . $code . "\" codeSystem=\"" . $oid . "\" codeSystemName=\"" . $codeSystem . "\"";
        }
    }

    public function primary_code_and_codesystem(Mustache_Context $context)
    {
        $codes = $context->find('dataElementCodes');
        $oid = $codes[0]['system'] || $codes[0]['codeSystem'];
        $code = $codes[0]['code'];
        $system = $this->get_code_system_for_oid($oid);
        return "code=\"" . $code . "\" codeSystem=\"" . $oid . "\" codeSystemName=\"" . $system . "\"";
    }

    public function translation_codes_and_codesystem_list(Mustache_Context $context)
    {
        $translation_list = "";
        $codes = $context->find('dataElementCodes');
        $count = count($codes);
        for ($i = 0; $i < $count; $i++) {
            // this skip was from the original ruby code.  Why do we skip the first one?
            if ($i == 0) {
                continue;
            }
            $oid = $codes[$i]['system'] ?? $codes[$i]['codeSystem'];
            $code = $codes[$i]['code'];
            $system = $this->get_code_system_for_oid($oid);
            $translation_list += "<translation code=\"" . $code . "\" codeSystem=\"" . $oid
                . "\" codeSystemName=\"" . $system . "\"/>";
        }
        return $translation_list;
    }

    public function value_as_float(Mustache_Context $context)
    {
        $value = $context->find('value');
        return floatval($value);
    }

    public function dose_quantity_value(Mustache_Context $context)
    {
        $value = $this->value_as_float($context);
        $unit = $context->find('unit');
        if (!empty($unit)) {
            return "<doseQuantity value=\"" . $value . "\" unit=\"" . $unit . "\"/>";
        } else {
            return "<doseQuantity value=\"" . $value . "\" />";
        }
    }

    public function result_value(Mustache_Context $context)
    {
        $result = $context->find('result');
        if (empty($result)) {
            return "<value xsi:type=\"CD\" nullFlavor=\"UNK\"/>";
        }
        if (is_array($result)) {
            // indexed array
            if (array_key_exists(0, $result)) {
                $result_string = $this->result_value_as_string($result[0]);
            } else { // hashmap
                $result_string = $this->result_value_as_string($result);
            }
            // string
        } elseif (is_string($result)) {
            $result_string = "<value xsi:type=\"ST\">" . $result . "</value>";
            // non-null value
        } else {
            if (is_numeric($result ?? null)) {
                $result_string = "<value xsi:type=\"PQ\" value=\"" . $result . "\" unit=\"1\"/>";
            } else {
                return "<value xsi:type=\"CD\" nullFlavor=\"UNK\"/>";
            }
        }
        return $result_string;
    }

    public function result_value_as_string($result)
    {
        // Result could be a code or value, but if we have neither, we return null/UNK
        if (empty($result['code']) && empty($result['value'])) {
            return "<value xsi:type=\"CD\" nullFlavor=\"UNK\"/>";
        }
        // Not all results will have code
        $oid = $result['system'] ?? $result['codeSystem'] ?? '';
        if (!empty($result['code']) && !empty($oid)) {
            $system = $this->get_code_system_for_oid($oid) ?: $result['codeSystem'];
            return "<value xsi:type=\"CD\" code=\"" . $result['code'] . "\" codeSystem=\"" . $oid
                . "\" codeSystemName=\"" . $system . "\"/>";
        } elseif (is_numeric($result['value'])) {
            // Such as value 10.2 unit ml/??
            return "<value xsi:type=\"PQ\" value=\"" . $result['value'] . "\" unit=\"" . ($result['unit'] ?: "UNK") . "\"/>";
        } elseif (is_string($result['value'])) {
            // Such as urine color YELLOW
            return "<value xsi:type=\"ST\" value=\"" . $result['value'] . "\"/>";
        }
        return "";
    }

    public function authordatetime_or_dispenserid(Mustache_Context $context): bool
    {
        $authorDateTime = $context->find('authorDatetime');
        $dispenserId = $context->find('dispenserId');
        return !empty($authorDateTime) || !empty($dispenserId);
    }

    private function get_code_system_for_oid($oid)
    {
        $codesService = new CodeTypesService();
        return $codesService->getCodeSystemNameFromSystem($oid);
    }
}
