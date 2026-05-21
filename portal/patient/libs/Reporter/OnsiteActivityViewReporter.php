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
    // the properties in this class must match the columns returned by GetCustomQuery()
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
        $sql = <<<'SQL'
        SELECT
            `onsite_portal_activity`.`id` AS Id,
            `onsite_portal_activity`.`date` AS Date,
            `onsite_portal_activity`.`patient_id` AS PatientId,
            `onsite_portal_activity`.`activity` AS Activity,
            `onsite_portal_activity`.`require_audit` AS RequireAudit,
            `onsite_portal_activity`.`pending_action` AS PendingAction,
            `onsite_portal_activity`.`action_taken` AS ActionTaken,
            `onsite_portal_activity`.`status` AS Status,
            `onsite_portal_activity`.`narrative` AS Narrative,
            `onsite_portal_activity`.`table_action` AS TableAction,
            `onsite_portal_activity`.`table_args` AS TableArgs,
            `onsite_portal_activity`.`action_user` AS ActionUser,
            `onsite_portal_activity`.`action_taken_time` AS ActionTakenTime,
            `onsite_portal_activity`.`checksum` AS Checksum,
            `patient_data`.`title` AS Title,
            `patient_data`.`fname` AS Fname,
            `patient_data`.`lname` AS Lname,
            `patient_data`.`mname` AS Mname,
            `patient_data`.`DOB` AS Dob,
            `patient_data`.`ss` AS Ss,
            `patient_data`.`street` AS Street,
            `patient_data`.`postal_code` AS PostalCode,
            `patient_data`.`city` AS City,
            `patient_data`.`state` AS State,
            `patient_data`.`referrerID` AS Referrerid,
            `patient_data`.`providerID` AS Providerid,
            `patient_data`.`ref_providerID` AS RefProviderid,
            `patient_data`.`pubpid` AS Pubpid,
            `patient_data`.`care_team_provider` AS CareTeam,
            `users`.`username` AS Username,
            `users`.`authorized` AS Authorized,
            `users`.`fname` AS Ufname,
            `users`.`mname` AS Umname,
            `users`.`lname` AS Ulname,
            `users`.`facility` AS Facility,
            `users`.`active` AS Active,
            `users`.`title` AS Utitle,
            `users`.`physician_type` AS PhysicianType
        FROM `onsite_portal_activity`
        LEFT JOIN `patient_data` ON `onsite_portal_activity`.`patient_id` = `patient_data`.`pid`
        LEFT JOIN `users` ON `patient_data`.`providerID` = `users`.`id`
        SQL;

        // the criteria can be used or you can write your own custom logic.
        // be sure to escape any user input with $criteria->Escape()
        $where = $criteria->GetWhere();
        $sql .= is_string($where) ? $where : '';
        $order = $criteria->GetOrder();
        $sql .= is_string($order) ? $order : '';

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
        $sql = <<<'SQL'
        SELECT count(1) AS counter
        FROM `onsite_portal_activity`
        LEFT JOIN `patient_data` ON `onsite_portal_activity`.`patient_id` = `patient_data`.`pid`
        LEFT JOIN `users` ON `patient_data`.`providerID` = `users`.`id`
        SQL;

        // the criteria can be used or you can write your own custom logic.
        // be sure to escape any user input with $criteria->Escape()
        $where = $criteria->GetWhere();
        $sql .= is_string($where) ? $where : '';

        return $sql;
    }
}
