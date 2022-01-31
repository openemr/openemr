<?php

/**
 * PatientCriteria.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
