<?php

/**
 * UserMap.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/** import supporting libraries */
require_once("verysimple/Phreeze/IDaoMap.php");
require_once("verysimple/Phreeze/IDaoMap2.php");

/**
 * UserMap is a static class with functions used to get FieldMap and KeyMap information that
 * is used by Phreeze to map the UserDAO to the users datastore.
 *
 * WARNING: THIS IS AN AUTO-GENERATED FILE
 *
 * This file should generally not be edited by hand except in special circumstances.
 * You can override the default fetching strategies for KeyMaps in _config.php.
 * Leaving this file alone will allow easy re-generation of all DAOs in the event of schema changes
 *
 * @package Openemr::Model::DAO
 * @author ClassBuilder
 * @version 1.0
 */
class UserMap implements IDaoMap, IDaoMap2
{

    private static $KM;
    private static $FM;

    /**
     * {@inheritdoc}
     */
    public static function AddMap($property, FieldMap $map)
    {
        self::GetFieldMaps();
        self::$FM[$property] = $map;
    }

    /**
     * {@inheritdoc}
     */
    public static function SetFetchingStrategy($property, $loadType)
    {
        self::GetKeyMaps();
        self::$KM[$property]->LoadType = $loadType;
    }

    /**
     * {@inheritdoc}
     */
    public static function GetFieldMaps()
    {
        if (self::$FM == null) {
            self::$FM = array();
            self::$FM["Id"] = new FieldMap("Id", "users", "id", true, FM_TYPE_BIGINT, 20, null, true);
            self::$FM["Username"] = new FieldMap("Username", "users", "username", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Password"] = new FieldMap("Password", "users", "password", false, FM_TYPE_LONGTEXT, null, null, false);
            self::$FM["Authorized"] = new FieldMap("Authorized", "users", "authorized", false, FM_TYPE_TINYINT, 4, null, false);
            self::$FM["Info"] = new FieldMap("Info", "users", "info", false, FM_TYPE_LONGTEXT, null, null, false);
            self::$FM["Source"] = new FieldMap("Source", "users", "source", false, FM_TYPE_TINYINT, 4, null, false);
            self::$FM["Fname"] = new FieldMap("Fname", "users", "fname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Mname"] = new FieldMap("Mname", "users", "mname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Lname"] = new FieldMap("Lname", "users", "lname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Federaltaxid"] = new FieldMap("Federaltaxid", "users", "federaltaxid", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Federaldrugid"] = new FieldMap("Federaldrugid", "users", "federaldrugid", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Upin"] = new FieldMap("Upin", "users", "upin", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Facility"] = new FieldMap("Facility", "users", "facility", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["FacilityId"] = new FieldMap("FacilityId", "users", "facility_id", false, FM_TYPE_INT, 11, null, false);
            self::$FM["SeeAuth"] = new FieldMap("SeeAuth", "users", "see_auth", false, FM_TYPE_INT, 11, "1", false);
            self::$FM["Active"] = new FieldMap("Active", "users", "active", false, FM_TYPE_TINYINT, 1, "1", false);
            self::$FM["Npi"] = new FieldMap("Npi", "users", "npi", false, FM_TYPE_VARCHAR, 15, null, false);
            self::$FM["Title"] = new FieldMap("Title", "users", "title", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["Specialty"] = new FieldMap("Specialty", "users", "specialty", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Billname"] = new FieldMap("Billname", "users", "billname", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Email"] = new FieldMap("Email", "users", "email", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["EmailDirect"] = new FieldMap("EmailDirect", "users", "email_direct", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["EserUrl"] = new FieldMap("EserUrl", "users", "url", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Assistant"] = new FieldMap("Assistant", "users", "assistant", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Organization"] = new FieldMap("Organization", "users", "organization", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Valedictory"] = new FieldMap("Valedictory", "users", "valedictory", false, FM_TYPE_VARCHAR, 255, null, false);
            self::$FM["Street"] = new FieldMap("Street", "users", "street", false, FM_TYPE_VARCHAR, 60, null, false);
            self::$FM["Streetb"] = new FieldMap("Streetb", "users", "streetb", false, FM_TYPE_VARCHAR, 60, null, false);
            self::$FM["City"] = new FieldMap("City", "users", "city", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["State"] = new FieldMap("State", "users", "state", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["Zip"] = new FieldMap("Zip", "users", "zip", false, FM_TYPE_VARCHAR, 20, null, false);
            self::$FM["Street2"] = new FieldMap("Street2", "users", "street2", false, FM_TYPE_VARCHAR, 60, null, false);
            self::$FM["Streetb2"] = new FieldMap("Streetb2", "users", "streetb2", false, FM_TYPE_VARCHAR, 60, null, false);
            self::$FM["City2"] = new FieldMap("City2", "users", "city2", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["State2"] = new FieldMap("State2", "users", "state2", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["Zip2"] = new FieldMap("Zip2", "users", "zip2", false, FM_TYPE_VARCHAR, 20, null, false);
            self::$FM["Phone"] = new FieldMap("Phone", "users", "phone", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["Fax"] = new FieldMap("Fax", "users", "fax", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["Phonew1"] = new FieldMap("Phonew1", "users", "phonew1", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["Phonew2"] = new FieldMap("Phonew2", "users", "phonew2", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["Phonecell"] = new FieldMap("Phonecell", "users", "phonecell", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["Notes"] = new FieldMap("Notes", "users", "notes", false, FM_TYPE_TEXT, null, null, false);
            self::$FM["CalUi"] = new FieldMap("CalUi", "users", "cal_ui", false, FM_TYPE_TINYINT, 4, "1", false);
            self::$FM["Taxonomy"] = new FieldMap("Taxonomy", "users", "taxonomy", false, FM_TYPE_VARCHAR, 30, "207Q00000X", false);
            //self::$FM["SsiRelayhealth"] = new FieldMap("SsiRelayhealth","users","ssi_relayhealth",false,FM_TYPE_VARCHAR,64,null,false);
            self::$FM["Calendar"] = new FieldMap("Calendar", "users", "calendar", false, FM_TYPE_TINYINT, 1, null, false);
            self::$FM["AbookType"] = new FieldMap("AbookType", "users", "abook_type", false, FM_TYPE_VARCHAR, 31, null, false);
            self::$FM["DefaultWarehouse"] = new FieldMap("DefaultWarehouse", "users", "default_warehouse", false, FM_TYPE_VARCHAR, 31, null, false);
            self::$FM["Irnpool"] = new FieldMap("Irnpool", "users", "irnpool", false, FM_TYPE_VARCHAR, 31, null, false);
            self::$FM["StateLicenseNumber"] = new FieldMap("StateLicenseNumber", "users", "state_license_number", false, FM_TYPE_VARCHAR, 25, null, false);
            self::$FM["NewcropUserRole"] = new FieldMap("NewcropUserRole", "users", "newcrop_user_role", false, FM_TYPE_VARCHAR, 30, null, false);
            self::$FM["Cpoe"] = new FieldMap("Cpoe", "users", "cpoe", false, FM_TYPE_TINYINT, 1, null, false);
            self::$FM["PhysicianType"] = new FieldMap("PhysicianType", "users", "physician_type", false, FM_TYPE_VARCHAR, 50, null, false);
            self::$FM["PortalUser"] = new FieldMap("PortalUser", "users", "portal_user", false, FM_TYPE_TINYINT, 1, null, false);
        }

        return self::$FM;
    }

    /**
     * {@inheritdoc}
     */
    public static function GetKeyMaps()
    {
        if (self::$KM == null) {
            self::$KM = array();
            self::$KM["examinerlkup"] = new KeyMap("examinerlkup", "Id", "FormHearing", "ExaminerId", KM_TYPE_ONETOMANY, KM_LOAD_LAZY);  // use KM_LOAD_EAGER with caution here (one-to-one relationships only)
            self::$KM["reviewerlkup"] = new KeyMap("reviewerlkup", "Id", "FormHearing", "ReviewerId", KM_TYPE_ONETOMANY, KM_LOAD_LAZY);  // use KM_LOAD_EAGER with caution here (one-to-one relationships only)
        }

        return self::$KM;
    }
}
