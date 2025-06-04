<?php

/**
 * OnsiteActivityViewMap-query.php
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
            self::$FM["Id"] = new FieldMap("Id", "onsite_portal_activity", "id", true, FM_TYPE_BIGINT, 20, null, false);
            self::$FM["Date"] = new FieldMap("Date", "onsite_portal_activity", "date", false, FM_TYPE_DATETIME, null, null, false);
            self::$FM["PatientId"] = new FieldMap("PatientId", "onsite_portal_activity", "patient_id", false, FM_TYPE_BIGINT, 20, null, false);
            self::$FM["Activity"] = new FieldMap("Activity", "onsite_portal_activity", "activity", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["RequireAudit"] = new FieldMap("RequireAudit", "onsite_portal_activity", "require_audit", false, FM_TYPE_TINYINT, 1, "1", false);
            self::$FM["PendingAction"] = new FieldMap("PendingAction", "onsite_portal_activity", "pending_action", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["ActionTaken"] = new FieldMap("ActionTaken", "onsite_portal_activity", "action_taken", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Status"] = new FieldMap("Status", "onsite_portal_activity", "status", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Narrative"] = new FieldMap("Narrative", "onsite_portal_activity", "narrative", false, FM_TYPE_LONGTEXT, null, null, false);
            self::$FM["TableAction"] = new FieldMap("TableAction", "onsite_portal_activity", "table_action", false, FM_TYPE_LONGTEXT, null, null, false);
            self::$FM["TableArgs"] = new FieldMap("TableArgs", "onsite_portal_activity", "table_args", false, FM_TYPE_LONGTEXT, null, null, false);
            self::$FM["ActionUser"] = new FieldMap("ActionUser", "onsite_portal_activity", "action_user", false, FM_TYPE_INT, 11, null, false);
            self::$FM["ActionTakenTime"] = new FieldMap("ActionTakenTime", "onsite_portal_activity", "action_taken_time", false, FM_TYPE_DATETIME, null, null, false);
            self::$FM["Checksum"] = new FieldMap("Checksum", "onsite_portal_activity", "checksum", false, FM_TYPE_LONGTEXT, null, null, false);
            self::$FM["Title"] = new FieldMap("Title", "patient_data", "title", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Fname"] = new FieldMap("Fname", "patient_data", "fname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Lname"] = new FieldMap("Lname", "patient_data", "lname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Mname"] = new FieldMap("Mname", "patient_data", "mname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Dob"] = new FieldMap("Dob", "patient_data", "DOB", false, FM_TYPE_DATE, null, null, false);
            self::$FM["Ss"] = new FieldMap("Ss", "patient_data", "ss", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Street"] = new FieldMap("Street", "patient_data", "street", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["PostalCode"] = new FieldMap("PostalCode", "patient_data", "postal_code", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["City"] = new FieldMap("City", "patient_data", "city", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["State"] = new FieldMap("State", "patient_data", "state", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Referrerid"] = new FieldMap("Referrerid", "patient_data", "referrerID", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Providerid"] = new FieldMap("Providerid", "patient_data", "providerID", false, FM_TYPE_INT, 11, null, false);
            self::$FM["RefProviderid"] = new FieldMap("RefProviderid", "patient_data", "ref_providerID", false, FM_TYPE_INT, 11, null, false);
            self::$FM["Pubpid"] = new FieldMap("Pubpid", "patient_data", "pubpid", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["CareTeam"] = new FieldMap("CareTeam", "patient_data", "care_team_provider", false, FM_TYPE_INT, 11, null, false);
            self::$FM["Username"] = new FieldMap("Username", "users", "username", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Authorized"] = new FieldMap("Authorized", "users", "authorized", false, FM_TYPE_TINYINT, 4, null, false);
            self::$FM["Ufname"] = new FieldMap("Ufname", "users", "ufname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Umname"] = new FieldMap("Umname", "users", "umname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Ulname"] = new FieldMap("Ulname", "users", "ulname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Facility"] = new FieldMap("Facility", "users", "facility", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Active"] = new FieldMap("Active", "users", "active", false, FM_TYPE_TINYINT, 1, "1", false);
            self::$FM["Utitle"] = new FieldMap("Utitle", "users", "utitle", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["PhysicianType"] = new FieldMap("PhysicianType", "users", "physician_type", false, FM_TYPE_VARCHAR, 50, null, false);
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
