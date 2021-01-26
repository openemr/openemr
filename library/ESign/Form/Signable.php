<?php

/**
 * Form implementation of SignableIF interface, which represents an
 * object that can be signed, locked and/or amended.
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

require_once $GLOBALS['srcdir'] . '/ESign/DbRow/Signable.php';
require_once $GLOBALS['srcdir'] . '/ESign/SignableIF.php';

class Form_Signable extends DbRow_Signable implements SignableIF
{
    protected $_encounterId = null;
    protected $_formId = null;
    protected $_formDir = null;

    public function __construct($formId, $formDir, $encounterId)
    {
        $this->_formId = $formId;
        $this->_formDir = $formDir;
        $this->_encounterId = $encounterId;
        parent::__construct($formId, 'forms');
    }

    protected function getLastLockHash()
    {
        $hash = null;
        if ($this->isLocked()) {
            // Check to see if there was an explicit lock hash
            $hash = parent::getLastLockHash();

            // If there was no explicit lock hash, then we must have been locked because
            // our encounter was locked, so get our last hash
            if ($hash === null) {
                $statement = "SELECT E.tid, E.table, E.hash FROM esign_signatures E ";
                $statement .= "WHERE E.tid = ? AND E.table = ? ";
                $statement .= "ORDER BY E.datetime DESC LIMIT 1";
                $row = sqlQuery($statement, array( $this->_tableId, $this->_tableName ));
                $hash = null;
                if ($row && isset($row['hash'])) {
                    $hash = $row['hash'];
                }
            }
        }

        return $hash;
    }

    /**
     * Check to see if this table is locked (read-only)
     *
     * A form is locked if it has a lock entry in the esign_signatures
     * table, or if it's encounter is locked.
     *
     * @see \ESign\DbRow_Signable::isLocked()
     */
    public function isLocked()
    {
        // Initialize to false and check individual form
        $locked = false;
        if ($GLOBALS['lock_esign_individual']) {
            $locked = parent::isLocked();
        }

        // Check the "parent" encounter if signing is allowed at encounter level
        if (!$locked && $GLOBALS['lock_esign_all']) {
            $statement = "SELECT E.is_lock FROM esign_signatures E ";
            $statement .= "WHERE E.tid = ? AND E.table = ? AND E.is_lock = ? ";
            $statement .= "ORDER BY E.datetime DESC LIMIT 1";
            $row = sqlQuery($statement, array( $this->_encounterId, 'form_encounter', SignatureIF::ESIGN_LOCK ));
            if ($row && $row['is_lock'] == SignatureIF::ESIGN_LOCK) {
                $locked = true;
            }
        }

        return $locked;
    }

    /**
     * Get the data in an array for this form.
     *
     * First, we check the forms table to get the row id in the
     * specific table. Then we get the row of data from the specific
     * form_* table.
     *
     * @see \ESign\SignableIF::getData()
     */
    public function getData()
    {
      // Use default standards based on formdir value
      // Exceptions are specified in formdir_keys list
        $row = sqlQuery(
            "SELECT title FROM list_options WHERE list_id = ? AND option_id = ? AND activity = 1",
            array('formdir_keys', $this->_formDir)
        );
        if (isset($row['title'])) {
            $excp = json_decode("{" . $row['title'] . "}");
        }

        $tbl = (isset($excp->tbl) ? $excp->tbl : "form_" . $this->_formDir);

        // eye form fix
        if ($tbl == 'form_eye_mag') {
            $tbl = 'form_eye_base';
        }

        $id = (isset($excp->id) ? $excp->id : 'id');
        $limit = (isset($excp->limit) ? $excp->limit : 1);

      // Get form data based on key from forms table
        $sql = sprintf(
            "SELECT fd.* FROM %s fd
      		INNER JOIN forms f ON fd.%s = f.form_id
      		WHERE f.id = ?",
            escape_table_name($tbl),
            escape_sql_column_name($id, array($tbl))
        );
        if ($limit <> '*') {
            $sql .= ' LIMIT ' . escape_limit($limit);
        }

        $rs = sqlStatement($sql, array( $this->_formId ));
        if (sqlNumRows($rs) == 1) { // maintain legacy hash
            $frs = sqlFetchArray($rs);
        } else {
            $frs = array();
            while ($fr = sqlFetchArray($rs)) {
                array_push($frs, $fr);
            }
        }

        return $frs;
    }
}
