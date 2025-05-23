<?php

/**
 * CcdaDocumentTemplateOids contains all of the CCD-A oids for the document template types we support in OpenEMR.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

// @see http://www.hl7.org/ccdasearch/ for a listing of this oids (Last accessed on June 14th 2022)
class CcdaDocumentTemplateOids
{
    const CCD = "2.16.840.1.113883.10.20.22.1.2";
    const REFERRAL = "2.16.840.1.113883.10.20.22.1.14";
    const TRANSFER_SUMMARY = "2.16.840.1.113883.10.20.22.1.13";
    const CAREPLAN  = "2.16.840.1.113883.10.20.22.1.15";
    const CCDA_DOCUMENT_TEMPLATE_OIDS = [self::CCD, self::REFERRAL, self::TRANSFER_SUMMARY, self::CAREPLAN];

    public static function isValidDocumentTemplateOid($oid)
    {
        return in_array($oid, self::CCDA_DOCUMENT_TEMPLATE_OIDS);
    }
}
