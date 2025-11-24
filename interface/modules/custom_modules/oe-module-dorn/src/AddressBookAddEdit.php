<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\Dorn;

class AddressBookAddEdit
{
    public static function createOrUpdateRecordInAddressBook($uid, $organization, $street, $streetb, $city, $state, $zip, $url, $phone, $fax, $notes)
    {
        // Check if the record with the given 'uid' already exists
        if ($uid > 0) {
            $existingRecord = sqlQuery("SELECT * FROM users WHERE id = ?", [$uid]);
        } else {
            $existingRecord = sqlQuery("SELECT * FROM users WHERE organization = ? AND notes LIKE CONCAT('%', ?, '%') LIMIT 1", [$organization, $notes]);
        }

        if ($existingRecord) {
            AddressBookAddEdit::updateRecordInAddressBook($uid, $organization, $street, $streetb, $city, $state, $zip, $url, $phone, $fax, $notes);
        } else {
            // Insert a new record
            $uid = AddressBookAddEdit::createRecordInAddressBook($organization, $street, $streetb, $city, $state, $zip, $url, $phone, $fax, $notes);
        }
        return $uid;
    }

    public static function updateRecordInAddressBook($uid, $organization, $street, $streetb, $city, $state, $zip, $url, $phone, $fax, $notes)
    {
        $sql = "UPDATE users SET organization = ?, street = ?, streetb = ?, city = ?,
            state = ?, zip = ?, url = ?, phone = ?, fax = ?, notes = ?
            WHERE id = ?";
        $sqlarr = [$organization, $street, $streetb, $city,
        $state, $zip, $url, $phone, $fax, $notes,
        $uid];
        sqlStatement($sql, $sqlarr);
    }

    public static function createRecordInAddressBook($organization, $street, $streetb, $city, $state, $zip, $url, $phone, $fax, $notes)
    {
        $abook_type     = "ord_lab";
        $see_auth       = 0;
        $active         = 1;
        $authorized     = 0;

        $npi            = "";
        $userName       = "";
        $password       = "";
        $fname          = "";
        $mname          = "";
        $lname          = "";
        $suffix         = "";
        $federaltaxid   = "";
        $federaldrugid  = "";
        $info           = "";
        $source         = null;
        $title          = "";
        $upin           = "";
        $facility       = "";
        $billname       = "";
        $taxonomy       = "";
        $cpoe           = "";
        $specialty      = "";
        $valedictory    = "";
        $assistant      = "";
        $email          = "";
        $email_direct   = "";
        $street2        = "";
        $streetb2       = "";
        $city2          = "";
        $state2         = "";
        $zip2           = "";
        $phonew1        = "";
        $phonew2        = "";
        $phonecell      = "";

        $sqlArr = [${$userName}, $password, $authorized, $info, $source
            ,$title, $fname, $lname, $mname, $suffix
            ,$federaltaxid, $federaldrugid,$upin,$facility,$see_auth,$active,$npi,$taxonomy,$cpoe
            ,$specialty,$organization,$valedictory,$assistant,$billname,$email,$email_direct,$url
            ,$street,$streetb,$city,$state,$zip,$street2,$streetb2,$city2,$state2,$zip2,$phone,$phonew1
            ,$phonew2,$phonecell,$fax,$notes,$abook_type];


        $userid = sqlInsert(
            "INSERT INTO users (
        username, password, authorized, info, source,
        title, fname, lname, mname, suffix,
        federaltaxid, federaldrugid, upin, facility, see_auth, active, npi, taxonomy, cpoe,
        specialty, organization, valedictory, assistant, billname, email, email_direct, url,
        street, streetb, city, state, zip, street2, streetb2, city2, state2, zip2, phone, phonew1,
        phonew2, phonecell, fax, notes, abook_type)
        VALUES (?, ?, ?, ?, ?
        ,?, ?, ?, ?, ?
        ,?, ?,?,?,?,?,?,?,?
        ,?,?,?,?,?,?,?,?
        ,?,?,?,?,?,?,?,?,?,?,?,?
        ,?,?,?,?,?)",
            $sqlArr
        );

        return $userid;
    }
}
