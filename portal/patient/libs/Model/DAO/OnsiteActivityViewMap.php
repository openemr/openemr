<?php

/**
 * OnsiteActivityViewMap.php
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
require_once("verysimple/Phreeze/IDaoMap.php");
require_once("verysimple/Phreeze/IDaoMap2.php");

/**
 *
 * @package Openemr::Model::DAO
 * @author ClassBuilder
 * @version 1.0
 */
class OnsiteActivityViewMap implements IDaoMap, IDaoMap2
{
    private static $KM;
    private static $FM;

    /**
     *
     * {@inheritdoc}
     *
     */
    public static function AddMap($property, FieldMap $map)
    {
        self::GetFieldMaps();
        self::$FM[$property] = $map;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public static function SetFetchingStrategy($property, $loadType)
    {
        self::GetKeyMaps();
        self::$KM[$property]->LoadType = $loadType;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public static function GetFieldMaps()
    {
        if (self::$FM == null) {
            self::$FM = array ();
            self::$FM["Id"] = new FieldMap("Id", "onsite_activity_view", "id", true, FM_TYPE_BIGINT, 20, null, false);
            self::$FM["Date"] = new FieldMap("Date", "onsite_activity_view", "date", false, FM_TYPE_DATETIME, null, null, false);
            self::$FM["PatientId"] = new FieldMap("PatientId", "onsite_activity_view", "patient_id", false, FM_TYPE_BIGINT, 20, null, false);
            self::$FM["Activity"] = new FieldMap("Activity", "onsite_activity_view", "activity", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["RequireAudit"] = new FieldMap("RequireAudit", "onsite_activity_view", "require_audit", false, FM_TYPE_TINYINT, 1, "1", false);
            self::$FM["PendingAction"] = new FieldMap("PendingAction", "onsite_activity_view", "pending_action", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["ActionTaken"] = new FieldMap("ActionTaken", "onsite_activity_view", "action_taken", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Status"] = new FieldMap("Status", "onsite_activity_view", "status", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Narrative"] = new FieldMap("Narrative", "onsite_activity_view", "narrative", false, FM_TYPE_LONGTEXT, null, null, false);
            self::$FM["TableAction"] = new FieldMap("TableAction", "onsite_activity_view", "table_action", false, FM_TYPE_LONGTEXT, null, null, false);
            self::$FM["TableArgs"] = new FieldMap("TableArgs", "onsite_activity_view", "table_args", false, FM_TYPE_LONGTEXT, null, null, false);
            self::$FM["ActionUser"] = new FieldMap("ActionUser", "onsite_activity_view", "action_user", false, FM_TYPE_INT, 11, null, false);
            self::$FM["ActionTakenTime"] = new FieldMap("ActionTakenTime", "onsite_activity_view", "action_taken_time", false, FM_TYPE_DATETIME, null, null, false);
            self::$FM["Checksum"] = new FieldMap("Checksum", "onsite_activity_view", "checksum", false, FM_TYPE_LONGTEXT, null, null, false);
            self::$FM["Title"] = new FieldMap("Title", "onsite_activity_view", "title", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Fname"] = new FieldMap("Fname", "onsite_activity_view", "fname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Lname"] = new FieldMap("Lname", "onsite_activity_view", "lname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Mname"] = new FieldMap("Mname", "onsite_activity_view", "mname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Dob"] = new FieldMap("Dob", "onsite_activity_view", "DOB", false, FM_TYPE_DATE, null, null, false);
            self::$FM["Ss"] = new FieldMap("Ss", "onsite_activity_view", "ss", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Street"] = new FieldMap("Street", "onsite_activity_view", "street", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["PostalCode"] = new FieldMap("PostalCode", "onsite_activity_view", "postal_code", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["City"] = new FieldMap("City", "onsite_activity_view", "city", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["State"] = new FieldMap("State", "onsite_activity_view", "state", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Referrerid"] = new FieldMap("Referrerid", "onsite_activity_view", "referrerID", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Providerid"] = new FieldMap("Providerid", "onsite_activity_view", "providerID", false, FM_TYPE_INT, 11, null, false);
            self::$FM["RefProviderid"] = new FieldMap("RefProviderid", "onsite_activity_view", "ref_providerID", false, FM_TYPE_INT, 11, null, false);
            self::$FM["Pubpid"] = new FieldMap("Pubpid", "onsite_activity_view", "pubpid", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["CareTeam"] = new FieldMap("CareTeam", "onsite_activity_view", "care_team_provider", false, FM_TYPE_INT, 11, null, false);
            self::$FM["Username"] = new FieldMap("Username", "onsite_activity_view", "username", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Authorized"] = new FieldMap("Authorized", "onsite_activity_view", "authorized", false, FM_TYPE_TINYINT, 4, null, false);
            self::$FM["Ufname"] = new FieldMap("Ufname", "onsite_activity_view", "ufname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Umname"] = new FieldMap("Umname", "onsite_activity_view", "umname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Ulname"] = new FieldMap("Ulname", "onsite_activity_view", "ulname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Facility"] = new FieldMap("Facility", "onsite_activity_view", "facility", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Active"] = new FieldMap("Active", "onsite_activity_view", "active", false, FM_TYPE_TINYINT, 1, "1", false);
            self::$FM["Utitle"] = new FieldMap("Utitle", "onsite_activity_view", "utitle", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["PhysicianType"] = new FieldMap("PhysicianType", "onsite_activity_view", "physician_type", false, FM_TYPE_VARCHAR, 50, null, false);
        }

        return self::$FM;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public static function GetKeyMaps()
    {
        if (self::$KM == null) {
            self::$KM = array ();
        }

        return self::$KM;
    }
}
