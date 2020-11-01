<?php

/**
 *
 * CQM NQF 0101 Population Criteria
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

class NQF_0101_PopulationCriteria implements CqmPopulationCrtiteriaFactory
{
    public function getTitle()
    {
        return "Population Criteria";
    }

    public function createInitialPatientPopulation()
    {
        return new NQF_0101_InitialPatientPopulation();
    }

    public function createNumerators()
    {
        return new NQF_0101_Numerator();
    }

    public function createDenominator()
    {
        return new NQF_0101_Denominator();
    }

    public function createExclusion()
    {
        return new ExclusionsNone();
    }

    public function createDenominatorException()
    {
        return new NQF_0101_DenominatorException();
    }
}
