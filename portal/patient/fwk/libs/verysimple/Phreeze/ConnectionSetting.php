<?php

/** @package    verysimple::Phreeze */

/**
 * ConnectionSetting object contains information about the data store used for object persistence.
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.0
 */
class ConnectionSetting
{
    /** @var string database type, for example mysql, mysqli, sqlite */
    public $Type = "mysql";

    /** @var string connection string used to connect to the database, for example  localhost:3306 */
    public $ConnectionString;

    /** @var string name of the database/schema */
    public $DBName;

    /** @var string database username used to connect */
    public $Username;

    /** @var string database password used to connect */
    public $Password;

    /** @var string if all tables share a common prefix, this can be used so object names do not include the prefix */
    public $TablePrefix;

    /** @var string any arbitrary SQL that should be run upon first opening the connection, for example SET SQL_BIG_SELECTS=1 */
    public $BootstrapSQL;

    /** @var string characterset used for the database, for example 'utf8' */
    public $Charset;

    /** @var boolean set to true and multi-byte functions will be used when evaluating strings */
    public $Multibyte = false;

    /** @var boolean set to true and write operations will not be allowed */
    public $IsReadOnlySlave = false;

    /** @var ConnectionSetting if this is a slave connection, this is key of the master server */
    public $MasterConnectionDelegate = '';

    /**
     * Constructor
     */
    function __construct($connection_code = "")
    {
        if ($connection_code != "") {
            $this->Unserialize($connection_code);
        }
    }

    /**
     * Returns an DSN array compatible with PEAR::DB
     */
    function GetDSN()
    {
        return  [
                'phptype' => $this->Type,
                'username' => $this->Username,
                'password' => $this->Password,
                'hostspec' => $this->ConnectionString,
                'database' => $this->DBName
        ];
    }

    /**
     * Returns an options array compatible with PEAR::DB
     */
    function GetOptions()
    {
        return  [
                'debug' => 2
        ]
        // 'portability' => DB_PORTABILITY_NONE,
        ;
    }

    /**
     * Serialize to string
     */
    function Serialize()
    {
        return base64_encode(serialize($this));
    }

    /**
     * Populate info from serialized string
     */
    function Unserialize(&$serialized)
    {
        // load the util from the serialized code
        $tmp = unserialize(base64_decode((string) $serialized));
        $this->Type = $tmp->Type;
        $this->Username = $tmp->Username;
        $this->Password = $tmp->Password;
        $this->ConnectionString = $tmp->ConnectionString;
        $this->DBName = $tmp->DBName;
        $this->Type = $tmp->Type;
        $this->TablePrefix = $tmp->TablePrefix;
        $this->Charset = $tmp->Charset;
        $this->BootstrapSQL = $tmp->BootstrapSQL;
        $this->Multibyte = $tmp->Multibyte;
    }
}
