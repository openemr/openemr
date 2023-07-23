<?php

/**
 *
 * AMC 304i STAGE2
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


class AMC_304i_STG2 extends AbstractAmcReport
{
    public function getTitle()
    {
        return "AMC_304i_STG2";
    }

    public function getObjectToCount()
    {
        // @see AbstractAmcReport for how transitions-out is retrieved and calculated
        return "transitions-out";
    }

    /**
     * 2015 Rule:  DENOMINATOR Number of transitions of care and referrals during the performance period for which the MIPS eligible
     * clinician was the transferring or referring clinician.
     * @return AMC_304i_STG2_Denominator
     */
    public function createDenominator()
    {
        return new AMC_304i_STG2_Denominator();
    }

    /**
     * 2015 Rule: NUMERATOR: The number of transitions of care and referrals in the denominator where a
     * summary of care record was created using CEHRT and exchanged electronically.
     * @return AMC_304i_STG2_Numerator
     */
    public function createNumerator()
    {
        return new AMC_304i_STG2_Numerator();
    }
}
