<?php

/**
 * Abstract implementation of SignableIF which represents a signable row
 * in the database.
 *
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    http://www.open-emr.org
 **/

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/SignableIF.php';
require_once $GLOBALS['srcdir'] . '/ESign/Signature.php';
require_once $GLOBALS['srcdir'] . '/ESign/Utils/Verification.php';

abstract class DbRow_Signable implements SignableIF
{
    private $_signatures = array();
    private $_tableId = null;
    private $_tableName = null;
    private $_verification = null;

    public function __construct($tableId, $tableName)
    {
        $this->_tableId = $tableId;
        $this->_tableName = $tableName;
        $this->_verification = new Utils_Verification();
    }

    public function getSignatures()
    {
        $this->_signatures = array();

        $statement = "SELECT E.id, E.tid, E.table, E.uid, U.fname, U.lname, E.datetime, E.is_lock, E.amendment, E.hash, E.signature_hash FROM esign_signatures E ";
        $statement .= "JOIN users U ON E.uid = U.id ";
        $statement .= "WHERE E.tid = ? AND E.table = ? ";
        $statement .= "ORDER BY E.datetime ASC";
        $result = sqlStatement($statement, array( $this->_tableId, $this->_tableName ));

        while ($row = sqlFetchArray($result)) {
            $signature = new Signature(
                $row['id'],
                $row['tid'],
                $row['table'],
                $row['is_lock'],
                $row['uid'],
                $row['fname'],
                $row['lname'],
                $row['datetime'],
                $row['hash'],
                $row['amendment'],
                $row['signature_hash']
            );
            $this->_signatures[] = $signature;
        }

        return $this->_signatures;
    }

    /**
     * Get the hash of the last signature of type LOCK.
     *
     * This is used for comparison with a current hash to
     * verify data integrity.
     *
     * @return sha1(or sha3-512)|empty string
     */
    protected function getLastLockHash()
    {
        $statement = "SELECT E.tid, E.table, E.hash FROM esign_signatures E ";
        $statement .= "WHERE E.tid = ? AND E.table = ? AND E.is_lock = ? ";
        $statement .= "ORDER BY E.datetime DESC LIMIT 1";
        $row = sqlQuery($statement, array( $this->_tableId, $this->_tableName, SignatureIF::ESIGN_LOCK ));
        $hash = null;
        if ($row && isset($row['hash'])) {
            $hash = $row['hash'];
        }

        return $hash;
    }

    public function getTableId()
    {
        return $this->_tableId;
    }

    public function renderForm()
    {
        include 'views/esign_signature_log.php';
    }

    public function isLocked()
    {
        $statement = "SELECT E.is_lock FROM esign_signatures E ";
        $statement .= "WHERE E.tid = ? AND E.table = ? AND is_lock = ? ";
        $statement .= "ORDER BY E.datetime DESC LIMIT 1 ";
        $row = sqlQuery($statement, array( $this->_tableId, $this->_tableName, SignatureIF::ESIGN_LOCK ));
        if ($row && $row['is_lock'] == SignatureIF::ESIGN_LOCK) {
            return true;
        }

        return false;
    }

    public function sign($userId, $lock = false, $amendment = null)
    {
        $statement = "INSERT INTO `esign_signatures` ( `tid`, `table`, `uid`, `datetime`, `is_lock`, `hash`, `amendment`, `signature_hash` ) ";
        $statement .= "VALUES ( ?, ?, ?, NOW(), ?, ?, ?, ? ) ";

        // Make type string
        $isLock = SignatureIF::ESIGN_NOLOCK;
        if ($lock) {
            $isLock = SignatureIF::ESIGN_LOCK;
        }

        // Create a hash of the signable object so we can verify it's integrity
        $hash = $this->_verification->hash($this->getData());

        // Crate a hash of the signature data itself. This is the same data as Signature::getData() method
        $signature = array(
            $this->_tableId,
            $this->_tableName,
            $userId,
            $isLock,
            $hash,
            $amendment );
        $signatureHash = $this->_verification->hash($signature);

        // Append the hash of the signature data to the insert array before we insert
        $signature[] = $signatureHash;
        $id = sqlInsert($statement, $signature);

        if ($id === false) {
            throw new \Exception("Error occured while attempting to insert a signature into the database.");
        }

        return $id;
    }

    public function verify()
    {
        $valid = true;
        // Verify the signable data integrity
        // Check to see if this SignableIF is locked
        if ($this->isLocked()) {
            $signatures = $this->getSignatures();

            // SignableIF is locked, so if it has any signatures, make sure it hasn't been edited since lock
            if (count($signatures)) {
                // Verify the data of the SignableIF object
                $lastLockHash = $this->getLastLockHash();
                $valid = $this->_verification->verify($this->getData(), $lastLockHash);

                if ($valid === true) {
                    // If still vlaid, verify each signatures' integrity
                    foreach ($signatures as $signature) {
                        if ($signature instanceof SignatureIF) {
                            $valid = $signature->verify();
                            if ($valid === false) {
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $valid;
    }
}
