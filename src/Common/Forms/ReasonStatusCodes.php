<?php

/**
 * ReasonStatusCodes represents the statii that an observation,procedure, and other ccda/cql reportable item's reason code
 * can be.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms;

class ReasonStatusCodes
{
    const COMPLETED = "completed";
    const NEGATED = "negated";
    const PENDING = "pending";
    const NONE = "";

    public static function getCodesWithDescriptions()
    {
        return [
            self::NONE => [
                'code' => self::NONE
                ,'description' => ""
            ]
            ,self::PENDING => [
                'code' => self::PENDING
                ,'description' => xl("Pending")
            ]
            ,self::COMPLETED => [
                'code' => self::COMPLETED
                ,'description' => xl("Completed")
            ]
            ,self::NEGATED => [
                'code' => self::NEGATED
                ,'description' => xl("Negated")
            ]
        ];
    }
}
