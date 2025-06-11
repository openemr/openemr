<?php

/**
 * OnsiteActivityViewReporter.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * import supporting libraries
 */
require_once("verysimple/Phreeze/Reporter.php");

/**
 * This is an example Reporter based on the OnsiteActivityView object.
 * The reporter object
 * allows you to run arbitrary queries that return data which may or may not fit within
 * the data access API. This can include aggregate data or subsets of data.
 *
 * Note that Reporters are read-only and cannot be used for saving data.
 *
 * @package Openemr::Model::DAO
 * @author ClassBuilder
 * @version 1.0
 */
class OnsiteActivityViewReporter extends Reporter
{
    // the properties in this class must match the columns returned by GetCustomQuery().
    // 'CustomFieldExample' is an example that is not part of the `onsite_activity_view` table
    public $Id;
    public $Date;
    public $PatientId;
    public $Activity;
    public $RequireAudit;
    public $PendingAction;
    public $ActionTaken;
    public $Status;
    public $Narrative;
    public $TableAction;
    public $TableArgs;
    public $ActionUser;
    public $ActionTakenTime;
    public $Checksum;
    public $Title;
    public $Fname;
    public $Lname;
    public $Mname;
    public $Dob;
    public $Ss;
    public $Street;
    public $PostalCode;
    public $City;
    public $State;
    public $Referrerid;
    public $Providerid;
    public $RefProviderid;
    public $Pubpid;
    public $CareTeam;
    public $Username;
    public $Authorized;
    public $Ufname;
    public $Umname;
    public $Ulname;
    public $Facility;
    public $Active;
    public $Utitle;
    public $PhysicianType;

    /*
     * GetCustomQuery returns a fully formed SQL statement. The result columns
     * must match with the properties of this reporter object.
     *
     * @see Reporter::GetCustomQuery
     * @param Criteria $criteria
     * @return string SQL statement
     */
    static function GetCustomQuery($criteria)
    {

        $sql = "select
			`onsite_activity_view`.`id` as Id
			,`onsite_activity_view`.`date` as Date
			,`onsite_activity_view`.`patient_id` as PatientId
			,`onsite_activity_view`.`activity` as Activity
			,`onsite_activity_view`.`require_audit` as RequireAudit
			,`onsite_activity_view`.`pending_action` as PendingAction
			,`onsite_activity_view`.`action_taken` as ActionTaken
			,`onsite_activity_view`.`status` as Status
			,`onsite_activity_view`.`narrative` as Narrative
			,`onsite_activity_view`.`table_action` as TableAction
			,`onsite_activity_view`.`table_args` as TableArgs
			,`onsite_activity_view`.`action_user` as ActionUser
			,`onsite_activity_view`.`action_taken_time` as ActionTakenTime
			,`onsite_activity_view`.`checksum` as Checksum
			,`onsite_activity_view`.`title` as Title
			,`onsite_activity_view`.`fname` as Fname
			,`onsite_activity_view`.`lname` as Lname
			,`onsite_activity_view`.`mname` as Mname
			,`onsite_activity_view`.`DOB` as Dob
			,`onsite_activity_view`.`ss` as Ss
			,`onsite_activity_view`.`street` as Street
			,`onsite_activity_view`.`postal_code` as PostalCode
			,`onsite_activity_view`.`city` as City
			,`onsite_activity_view`.`state` as State
			,`onsite_activity_view`.`referrerID` as Referrerid
			,`onsite_activity_view`.`providerID` as Providerid
			,`onsite_activity_view`.`ref_providerID` as RefProviderid
			,`onsite_activity_view`.`pubpid` as Pubpid
			,`onsite_activity_view`.`care_team_provider` as CareTeam
			,`onsite_activity_view`.`username` as Username
			,`onsite_activity_view`.`authorized` as Authorized
			,`onsite_activity_view`.`ufname` as Ufname
			,`onsite_activity_view`.`umname` as Umname
			,`onsite_activity_view`.`ulname` as Ulname
			,`onsite_activity_view`.`facility` as Facility
			,`onsite_activity_view`.`active` as Active
			,`onsite_activity_view`.`utitle` as Utitle
			,`onsite_activity_view`.`physician_type` as PhysicianType
		from `onsite_activity_view`";

        // the criteria can be used or you can write your own custom logic.
        // be sure to escape any user input with $criteria->Escape()
        $sql .= $criteria->GetWhere();
        $sql .= $criteria->GetOrder();

        return $sql;
    }

    /*
     * GetCustomCountQuery returns a fully formed SQL statement that will count
     * the results. This query must return the correct number of results that
     * GetCustomQuery would, given the same criteria
     *
     * @see Reporter::GetCustomCountQuery
     * @param Criteria $criteria
     * @return string SQL statement
     */
    static function GetCustomCountQuery($criteria)
    {
        $sql = "select count(1) as counter from `onsite_activity_view`";

        // the criteria can be used or you can write your own custom logic.
        // be sure to escape any user input with $criteria->Escape()
        $sql .= $criteria->GetWhere();

        return $sql;
    }
}
