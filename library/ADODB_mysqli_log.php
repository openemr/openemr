<?php

/**
 * ADODB custom wrapper class to support ssl option in main.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Logging\EventAuditLogger;

class ADODB_mysqli_log extends ADODB_mysqli
{
    /**
     * ADODB Execute function wrapper to ensure proper auditing in OpenEMR.
     *
     * @param  string  $sql         query
     * @param  array   $inputarr    binded variables array (optional)
     * @return boolean              returns false if error
     */
    function Execute($sql, $inputarr = false, $insertNeedReturn = false)
    {
        $retval = parent::Execute($sql, $inputarr);
        if ($retval === false) {
            $outcome = false;
            // Stash the error into last_mysql_error so it doesn't get clobbered when
            // we insert into the audit log.
            $GLOBALS['last_mysql_error'] = $this->ErrorMsg();

            // Last error no
            $GLOBALS['last_mysql_error_no'] = $this->ErrorNo();
        } else {
            $outcome = true;
        }

        // Stash the insert ID into lastidado so it doesn't get clobbered when
        // we insert into the audit log.
        if ($insertNeedReturn) {
            $GLOBALS['lastidado'] = $this->Insert_ID();
        }
        EventAuditLogger::instance()->auditSQLEvent($sql, $outcome, $inputarr);
        return $retval;
    }

    /**
     * ADODB Execute function wrapper to skip auditing in OpenEMR.
     *
     * Bypasses the OpenEMR auditing engine.
     *
     * @param  string  $sql         query
     * @param  array   $inputarr    binded variables array (optional)
     * @return boolean              returns false if error
     */
    function ExecuteNoLog($sql, $inputarr = false)
    {
        return parent::Execute($sql, $inputarr);
    }

    /*
    * ADODB GenID function wrapper to work with OpenEMR.
    *
    * Need to override to fix a bug where call to GenID was updating
    * sequences table but always returning a zero with the OpenEMR audit
    * engine both on and off. Note this bug only appears to occur in recent
    * php versions on windows. The fix is to use the ExecuteNoLog() function
    * rather than the Execute() functions within this function (otherwise,
    * there are no other changes from the original ADODB GenID function).
    *
    * @param  string  $seqname     table name containing sequence (default is adodbseq)
    * @param  integer $startID     id to start with for a new sequence (default is 1)
    * @return integer              returns the sequence integer
    */
    function GenID($seqname = 'adodbseq', $startID = 1)
    {
        // post-nuke sets hasGenID to false
        if (!$this->hasGenID) {
            return false;
        }

        $getnext = sprintf($this->_genIDSQL, $seqname);
        $holdtransOK = $this->_transOK; // save the current status
        $rs = @$this->ExecuteNoLog($getnext);
        if (!$rs) {
            if ($holdtransOK) {
                $this->_transOK = true; //if the status was ok before reset
            }

            $u = strtoupper($seqname);
            $this->ExecuteNoLog(sprintf($this->_genSeqSQL, $seqname));
            $cnt = $this->GetOne(sprintf($this->_genSeqCountSQL, $seqname));
            if (!$cnt) {
                $this->ExecuteNoLog(sprintf($this->_genSeq2SQL, $seqname, $startID - 1));
            }

            $rs = $this->ExecuteNoLog($getnext);
        }

        if ($rs) {
            $this->genID = mysqli_insert_id($this->_connectionID);
            $rs->Close();
        } else {
            $this->genID = 0;
        }

        return $this->genID;
    }

    // ADODB _connect function wrapper to work with OpenEMR
    //  Needed to do this to add support for mysql ssl.
    //  (so only have added the mysqli_ssl_set stuff)
    // returns true or false
    // To add: parameter int $port,
    //         parameter string $socket
    function _connect(
        $argHostname = null,
        $argUsername = null,
        $argPassword = null,
        $argDatabasename = null,
        $persist = false
    ) {
        if (!extension_loaded("mysqli")) {
            return null;
        }
        $this->_connectionID = @mysqli_init();

        if (is_null($this->_connectionID)) {
            // mysqli_init only fails if insufficient memory
            if ($this->debug) {
                ADOConnection::outp("mysqli_init() failed : "  . $this->ErrorMsg());
            }
            return false;
        }
        /*
        I suggest a simple fix which would enable adodb and mysqli driver to
        read connection options from the standard mysql configuration file
        /etc/my.cnf - "Bastien Duclaux" <bduclaux#yahoo.com>
        */
        foreach ($this->optionFlags as $arr) {
            mysqli_options($this->_connectionID, $arr[0], $arr[1]);
        }

        //http ://php.net/manual/en/mysqli.persistconns.php
        if ($persist && PHP_VERSION > 5.2 && strncmp($argHostname, 'p:', 2) != 0) {
            $argHostname = 'p:' . $argHostname;
        }

        //Below was added by OpenEMR to support mysql ssl
        // Note there is really weird behavior where the paths to certificates do not work if within a variable.
        //  (super odd which is why have 2 different mysqli_ssl_set commands as a work around)
        if (defined('MYSQLI_CLIENT_SSL') && $this->clientFlags == MYSQLI_CLIENT_SSL) {
            if (
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") &&
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert")
            ) {
                // with client side certificate/key
                mysqli_ssl_set(
                    $this->_connectionID,
                    "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-key",
                    "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-cert",
                    "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-ca",
                    null,
                    null
                );
            } else {
                // without client side certificate/key
                mysqli_ssl_set(
                    $this->_connectionID,
                    null,
                    null,
                    "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-ca",
                    null,
                    null
                );
            }
        }

        #if (!empty($this->port)) $argHostname .= ":".$this->port;
        $ok = mysqli_real_connect(
            $this->_connectionID,
            $argHostname,
            $argUsername,
            $argPassword,
            $argDatabasename,
            # PHP7 compat: port must be int. Use default port if cast yields zero
            (int)$this->port != 0 ? (int)$this->port : 3306,
            $this->socket,
            $this->clientFlags
        );

        if ($ok) {
            if ($argDatabasename) {
                return $this->SelectDB($argDatabasename);
            }
            return true;
        } else {
            if ($this->debug) {
                ADOConnection::outp("Could't connect : "  . $this->ErrorMsg());
            }
            $this->_connectionID = null;
            return false;
        }
    }
}
