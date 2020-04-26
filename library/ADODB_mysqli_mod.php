<?php

/**
 * ADODB custom wrapper class to support ssl option in gacl and calendar.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

class ADODB_mysqli_mod extends ADODB_mysqli
{
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
