<?php
/**
 *
 * AMC 302m STAGE2 Denominator
 *
 * Copyright (C) 2015 Ensoftek, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ensoftek
 * @link    http://www.open-emr.org
 */

class AMC_302m_STG2_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302m_STG2 Denominator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        $off_visits = sqlQuery("select count(encounter) as cnt from form_encounter e inner join openemr_postcalendar_categories opc on opc.pc_catid = e.pc_catid where e.pid = ? and e.date >= ? and e.date <= ? and opc.pc_catname = 'office visit'", array($patient->id,$beginDate,$endDate));
        if ($off_visits['cnt'] >= 1) {
            return true;
        }

        return false;
    }
}
