<?php

/**
 * EntityHelper is a mustache helper trait for checking if various objects are a kind of entity.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Qrda\Util;

use Mustache_Context;

trait EntityHelper
{
    public function practitioner_entity(Mustache_Context $context): bool
    {
        return $this->checkOid($context, '2.16.840.1.113883.10.20.28.4.137');
    }

    public function care_partner_entity(Mustache_Context $context): bool
    {
        return $this->checkOid($context, '2.16.840.1.113883.10.20.28.4.134');
    }

    public function location_entity(Mustache_Context $context): bool
    {
        return $this->checkOid($context, '2.16.840.1.113883.10.20.28.4.142');
    }

    public function organization_entity(Mustache_Context $context): bool
    {
        return $this->checkOid($context, '2.16.840.1.113883.10.20.28.4.135');
    }

    public function patient_entity(Mustache_Context $context): bool
    {
        return $this->checkOid($context, '2.16.840.1.113883.10.20.28.4.136');
    }

    private function check_oid(Mustache_Context $context, $oid): bool
    {
        $hqmfOid = $context->get('hqmfOid');
        return $hqmfOid == $oid;
    }
}
