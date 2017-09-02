<?php
/** @package    Openemr::Model */

/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

/** import supporting libraries */
require_once("DAO/PatientCriteriaDAO.php");

/**
 * The PatientCriteria class extends PatientDAOCriteria and is used
 * to query the database for objects and collections
 *
 * @inheritdocs
 * @package Openemr::Model
 * @author ClassBuilder
 * @version 1.0
 */
class PatientCriteria extends PatientCriteriaDAO
{

    /**
     * GetFieldFromProp returns the DB column for a given class property
     *
     * If any fields that are not part of the table need to be supported
     * by this Criteria class, they can be added inside the switch statement
     * in this method
     *
     * @see Criteria::GetFieldFromProp()
     */
    /*
	public function GetFieldFromProp($propname)
	{
		switch($propname)
		{
			 case 'CustomProp1':
			 	return 'my_db_column_1';
			 case 'CustomProp2':
			 	return 'my_db_column_2';
			default:
				return parent::GetFieldFromProp($propname);
		}
	}
	*/

    /**
     * For custom query logic, you may override OnPrepare and set the $this->_where to whatever
     * sql code is necessary.  If you choose to manually set _where then Phreeze will not touch
     * your where clause at all and so any of the standard property names will be ignored
     *
     * @see Criteria::OnPrepare()
     */
    /*
	function OnPrepare()
	{
		if ($this->MyCustomField == "special value")
		{
			// _where must begin with "where"
			$this->_where = "where db_field ....";
		}
	}
	*/
}
