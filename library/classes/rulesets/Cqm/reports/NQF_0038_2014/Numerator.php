<?php

/**
 *
 * CQM NQF 0038(2014) Numerator
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

class NQF_0038_2014_Numerator implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        if (
            (Immunizations::checkDtap($patient, $beginDate, $endDate) ) ||
              ( Immunizations::checkIpv($patient, $beginDate, $endDate) ) ||
              ( Immunizations::checkMmr($patient, $beginDate, $endDate) ) ||
              ( Immunizations::checkHib($patient, $beginDate, $endDate) ) ||
              ( Immunizations::checkHepB($patient, $beginDate, $endDate) ) ||
              ( Immunizations::checkVzv($patient, $beginDate, $endDate) )  ||
              ( Immunizations::checkPheumococcal($patient, $beginDate, $endDate) ) ||
              ( Immunizations::checkHepA($patient, $beginDate, $endDate) ) ||
              ( Immunizations::checkRotavirus_2014($patient, $beginDate, $endDate) ) ||
              ( Immunizations::checkInfluenza($patient, $beginDate, $endDate) )
        ) {
            return true;
        }

        return false;
    }
}
