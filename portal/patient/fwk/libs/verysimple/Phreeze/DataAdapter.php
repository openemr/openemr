<?php

/** @package    verysimple::Phreeze */

/**
 * import supporting libraries
 */
require_once("IObservable.php");
require_once("ConnectionSetting.php");
require_once("verysimple/DB/DataDriver/IDataDriver.php");

/**
 * DataAdapter abstracts and provides access to the data store
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc. <noreply@verysimple.com>
 * @copyright 1997-2005 VerySimple Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.2
 */
class DataAdapter implements IObservable
{
    /**
     *
     * @var ConnectionSetting
     */
    public $ConnectionSetting;
    private $_observers = array ();
    private $_dbconn;
    private $_dbopen;
    private $_driver;
    private $_label;
    private $_transactionInProgress;
    private $_masterAdapter;

    /** @var used internally to keep track of communication error re-tries */
    private $_num_retries = 0;

    /** @var static singleton instance of the data adapter */
    static $ADAPTER_INSTANCE = null;

    /** @var instance of the driver class, used for escaping */
    static $DRIVER_INSTANCE = null;

    /** @var bool if true the data adapter attempt one retry when a communication error occurs */
    static $RETRY_ON_COMMUNICATION_ERROR = false;

    /**
     * Contructor initializes the object
     *
     * @access public
     * @param ConnectionSetting $csetting
     * @param Observable $listener
     * @param
     *          IDataDriver (optional) if not provided, then DataAdapter will attempt to instantiate one based on ConnectionSetting->Type
     * @param
     *          string (optional) a label for the DataAdapter used in debug messages (if empty a random label will be generated)
     */
    function __construct($csetting, $listener = null, IDataDriver $driver = null, $label = null)
    {
        $this->_driver = $driver;
        if ($this->_driver) {
            DataAdapter::$DRIVER_INSTANCE = $this->_driver;
        }

        $this->_label = $label ? $label : 'db-' . mt_rand(10000, 99999);

        $this->ConnectionSetting = & $csetting;

        if ($listener) {
            $this->AttachObserver($listener);
        }

        $this->Observe("DataAdapter ($this->_label) Instantiated", OBSERVE_DEBUG);

        // set the singleton reference
        DataAdapter::$ADAPTER_INSTANCE = $this;
    }

    /**
     * Destructor closes the db connection.
     *
     * @access public
     */
    function __destruct()
    {
        $this->Observe("DataAdapter ($this->_label) Destructor Firing...", OBSERVE_DEBUG);
        $this->Close();
    }

    /**
     * Load the data driver
     *
     * @throws Exception
     */
    public function LoadDriver()
    {
        if ($this->_driver == null) {
            require_once("verysimple/IO/Includer.php");

            // the driver was not explicitly provided so we will try to create one from
            // the connection setting based on the database types that we do know about
            switch ($this->ConnectionSetting->Type) {
                case "mysql":
                    include_once("verysimple/DB/DataDriver/MySQL.php");
                    $this->_driver = new DataDriverMySQL();
                    break;
                case "mysqli":
                    include_once("verysimple/DB/DataDriver/MySQLi.php");
                    $this->_driver = new DataDriverMySQLi();
                    break;
                case "sqlite":
                    include_once("verysimple/DB/DataDriver/SQLite.php");
                    $this->_driver = new DataDriverSQLite();
                    break;
                default:
                    try {
                        Includer::IncludeFile("verysimple/DB/DataDriver/" . $this->ConnectionSetting->Type . ".php");
                        $classname = "DataDriver" . $this->ConnectionSetting->Type;
                        $this->_driver = new $classname();
                    } catch (IncludeException $ex) {
                        throw new Exception('Unknown DataDriver "' . $this->ConnectionSetting->Type . '" specified in connection settings');
                    }
                    break;
            }

            DataAdapter::$DRIVER_INSTANCE = $this->_driver;
        }
    }

    /**
     * Returns name of the DB currently in use
     *
     * @access public
     * @return string
     */
    function GetDBName()
    {
        return $this->ConnectionSetting->DBName;
    }

    /**
     * Opens a connection to the data server and selects the specified database
     *
     * @access public
     */
    function Open()
    {
        $this->Observe("DataAdapter ($this->_label) Opening Connection...", OBSERVE_DEBUG);

        if ($this->_dbopen) {
            $this->Observe("DataAdapter ($this->_label) Connection Already Open", OBSERVE_WARN);
        } else {
            if (! $this->_driver) {
                $this->LoadDriver();
            }

            try {
                $this->_dbconn = $this->_driver->Open($this->ConnectionSetting->ConnectionString, $this->ConnectionSetting->DBName, $this->ConnectionSetting->Username, $this->ConnectionSetting->Password, $this->ConnectionSetting->Charset, $this->ConnectionSetting->BootstrapSQL);

                $this->_num_retries = 0;
            } catch (Exception $ex) {
                // retry one time a communication error occurs
                if ($this->_num_retries == 0 && DataAdapter::$RETRY_ON_COMMUNICATION_ERROR && $this->IsCommunicationError($ex)) {
                    $this->_num_retries++;
                    $this->Observe("DataAdapter ($this->_label) Communication error.  Retry attempt " . $this->_num_retries, OBSERVE_WARN);
                    sleep(2); // slight delay to prevent throttling
                    return $this->Open();
                }

                $msg = "DataAdapter ($this->_label) Error Opening DB: " . $ex->getMessage() . ' (retry attempts: ' . $this->_num_retries . ')';

                $this->Observe($msg, OBSERVE_FATAL);
                throw new Exception($msg, $ex->getCode());
            }

            $this->_dbopen = true;
            $this->Observe("DataAdapter ($this->_label) Connection Open", OBSERVE_DEBUG);
        }
    }

    /**
     * Closing the connection to the data Server
     *
     * @access public
     */
    function Close()
    {
        $this->Observe("DataAdapter ($this->_label) Closing Connection...", OBSERVE_DEBUG);

        if ($this->_dbopen) {
            $this->_driver->Close($this->_dbconn); // ignore warnings
            $this->_dbopen = false;
            $this->Observe("DataAdapter ($this->_label) Connection Closed", OBSERVE_DEBUG);
        } else {
            $this->Observe("DataAdapter ($this->_label) Connection Not Open", OBSERVE_DEBUG);
        }
    }

    /**
     * Checks that the connection is open and if not, crashes
     *
     * @access public
     * @param bool $auto
     *          Automatically try to connect if connection isn't already open
     */
    private function RequireConnection($auto = false)
    {
        if ($this->_dbopen) {
            // $this->_driver->Ping($this->_dbconn);
        } else {
            if ($auto) {
                $this->Open();
            } else {
                $this->Observe("DataAdapter ($this->_label) DB is not connected.  Please call DBConnection->Open() first.", OBSERVE_FATAL);
                throw new Exception("DataAdapter ($this->_label) DB is not connected.  Please call DBConnection->Open() first.");
            }
        }
    }

    /**
     * Executes a SQL select statement and returns a resultset that can be read
     * using Fetch
     *
     * @access public
     * @param string $sql
     * @return resultset (dependent on the type of driver used)
     */
    function Select($sql)
    {
        $this->RequireConnection(true);
        $this->Observe("DataAdapter ($this->_label) (DataAdapter.Select) " . $sql, OBSERVE_QUERY);

        try {
            $rs = $this->_driver->Query($this->_dbconn, $sql);
            $this->_num_retries = 0;
        } catch (Exception $ex) {
            // retry one time a communication error occurs
            if ($this->_num_retries == 0 && DataAdapter::$RETRY_ON_COMMUNICATION_ERROR && $this->IsCommunicationError($ex)) {
                $this->_num_retries++;
                $this->Observe("DataAdapter ($this->_label) Communication error.  Retry attempt " . $this->_num_retries, OBSERVE_WARN);
                sleep(2); // slight delay to prevent throttling
                return $this->Select($sql);
            }

            $msg = "DataAdapter ($this->_label)" . ' Error Selecting SQL: ' . $ex->getMessage() . ' (retry attempts: ' . $this->_num_retries . ')';

            $this->Observe($msg, OBSERVE_FATAL);
            throw new Exception($msg, $ex->getCode());
        }

        return $rs;
    }

    /**
     * Executes a SQL query that does not return a resultset
     *
     * @access public
     * @param string $sql
     * @return int number of records affected
     */
    function Execute($sql)
    {
        $result = null;

        if ($this->ConnectionSetting->IsReadOnlySlave) {
            // this is a read-only slave connection attempting a write operation. we
            // will only proceed if the connection specifies a "master" delegate connection
            if (! $this->_masterAdapter) {
                if ($this->ConnectionSetting->MasterConnectionDelegate) {
                    $this->Observe("DataAdapter ($this->_label) (DataAdapter.Execute) Delegating write operation from Slave to Master Connection", OBSERVE_INFO);

                    $this->_masterAdapter = new DataAdapter($this->ConnectionSetting->MasterConnectionDelegate);

                    foreach ($this->_observers as $observer) {
                        $this->_masterAdapter->AttachObserver($observer);
                    }
                } else {
                    throw new Exception('DB Write operation was attempted on a read-only slave connection');
                }
            }

            // we have a master connection initialized and ready to use
            $result = $this->_masterAdapter->Execute($sql);
        } else {
            $this->RequireConnection(true);
            $this->Observe("DataAdapter ($this->_label) (DataAdapter.Execute) " . $sql, OBSERVE_QUERY);
            $result = - 1;

            try {
                $result = $this->_driver->Execute($this->_dbconn, $sql);
                $this->_num_retries = 0;
            } catch (Exception $ex) {
                // retry one time a communication error occurs
                if ($this->_num_retries == 0 && DataAdapter::$RETRY_ON_COMMUNICATION_ERROR && $this->IsCommunicationError($ex)) {
                    $this->_num_retries++;
                    $this->Observe("DataAdapter ($this->_label) Communication error.  Retry attempt " . $this->_num_retries, OBSERVE_WARN);
                    sleep(2); // slight delay to prevent throttling
                    return $this->Execute($sql);
                }

                $msg = "DataAdapter ($this->_label)" . ' Error Executing SQL: ' . $ex->getMessage() . ' (retry attempts: ' . $this->_num_retries . ')';

                $this->Observe($msg, OBSERVE_FATAL);
                throw new Exception($msg, $ex->getCode());
            }
        }

        return $result;
    }

    /**
     * Return true if a transaction is in progress
     *
     * @return boolean
     */
    function IsTransactionInProgress()
    {
        return $this->_transactionInProgress;
    }

    /**
     * Start a DB transaction, disabling auto-commit if necessar)
     *
     * @access public
     */
    function StartTransaction()
    {
        if ($this->IsTransactionInProgress()) {
            throw new Exception('Transaction is already in progress.  Commit or rollback must be called before beginning a new transaction');
        }

        if ($this->ConnectionSetting->IsReadOnlySlave) {
            throw new Exception('Transactions are not allowed on a read-only slave');
        }

        $this->RequireConnection(true);
        $this->Observe("DataAdapter ($this->_label) (DataAdapter.StartTransaction)", OBSERVE_QUERY);
        $this->_transactionInProgress = true;
        return $this->_driver->StartTransaction($this->_dbconn);
    }

    /**
     * Commit the current DB transaction and re-enable auto-commit if necessary
     *
     * @access public
     */
    function CommitTransaction()
    {
        if ($this->ConnectionSetting->IsReadOnlySlave) {
            throw new Exception('Transactions are not allowed on a read-only slave');
        }

        $this->RequireConnection(true);
        $this->Observe("DataAdapter ($this->_label) (DataAdapter.CommitTransaction)", OBSERVE_QUERY);
        $this->_transactionInProgress = false;
        return $this->_driver->CommitTransaction($this->_dbconn);
    }

    /**
     * Rollback the current DB transaction and re-enable auto-commit if necessary
     *
     * @access public
     */
    function RollbackTransaction()
    {
        if ($this->ConnectionSetting->IsReadOnlySlave) {
            throw new Exception('Transactions are not allowed on a read-only slave');
        }

        $this->RequireConnection(true);
        $this->Observe("DataAdapter ($this->_label) (DataAdapter.RollbackTransaction)", OBSERVE_QUERY);
        $this->_transactionInProgress = false;
        return $this->_driver->RollbackTransaction($this->_dbconn);
    }

    /**
     * Return true if the error with the given message is a communication/network error
     *
     * @param
     *          variant string or Exception $msg
     * @return bool
     */
    public function IsCommunicationError($error)
    {
        $msg = is_a($error, 'Exception') ? $error->getMessage() : $error;
        return strpos(strtolower($msg), 'lost connection') !== false;
    }

    /**
     * Returns an array of all table names in the current database
     *
     * @param
     *          bool true to ommit tables that are empty (default = false)
     * @return array
     */
    public function GetTableNames($ommitEmptyTables = false)
    {
        return $this->_driver->GetTableName($this->_dbconn, $this->GetDBName(), $ommitEmptyTables);
    }

    /**
     * Runs OPTIMIZE TABLE on all tables in the current database
     *
     * @return array results for each table
     */
    public function OptimizeTables()
    {
        if ($this->ConnectionSetting->IsReadOnlySlave) {
            throw new Exception('Optimizing tables is allowed on a read-only slave');
        }

        $results = array ();
        $table_names = $this->_driver->GetTableNames($this->_dbconn, $this->GetDBName());

        foreach ($table_names as $table_name) {
            $results [$table_name] = $this->_driver->Optimize($this->_dbconn, $table_name);
        }

        return $results;
    }
    /**
     * Returns last auto-inserted Id.
     * If this is a read-only slave and a write operation was made to a Master Delegate
     * then the last insert id from that connection will be returned
     *
     * @access public
     * @return int
     */
    function GetLastInsertId()
    {
        $id = null;

        if ($this->ConnectionSetting->IsReadOnlySlave && $this->_masterAdapter) {
            $id = $this->_masterAdapter->GetLastInsertId();
        } else {
            $this->RequireConnection();
            $this->Observe("DataAdapter ($this->_label) GetLastInsertId", OBSERVE_QUERY);
            $id = $this->_driver->GetLastInsertId($this->_dbconn);
        }

        return $id;
    }

    /**
     * Moves the database curser forward and returns the current row as an associative array
     * the resultset passed in must have been created by the same database driver that
     * was connected when Select was called
     *
     * @access public
     * @param resultset $rs
     * @return Array
     */
    function Fetch($rs)
    {
        $this->RequireConnection();

        $this->Observe("DataAdapter ($this->_label) Fetching next result as array", OBSERVE_DEBUG);
        return $this->_driver->Fetch($this->_dbconn, $rs);
    }

    /**
     * Releases the resources for the given resultset.
     * the resultset must have
     * been created by the same database driver
     *
     * @access public
     * @param resultset $rs
     */
    function Release($rs)
    {
        $this->RequireConnection();

        $this->Observe("DataAdapter ($this->_label) Releasing result resources", OBSERVE_DEBUG);
        $this->_driver->Release($this->_dbconn, $rs);
    }

    /**
     * Removes any illegal chars from a value to prepare it for use in SQL
     *
     * @access public
     * @param string $val
     * @return string
     */
    public static function Escape($val)
    {
        if (DataAdapter::$ADAPTER_INSTANCE) {
            DataAdapter::$ADAPTER_INSTANCE->LoadDriver();
        }

            // this is an unfortunate leftover from poor design of making this function static
            // we cannon use the driver's escape method without a static reference
        if (! DataAdapter::$DRIVER_INSTANCE) {
            throw new Exception("DataAdapter must be instantiated before Escape can be called");
        }

            // $driver->RequireConnection(true);
        return DataAdapter::$DRIVER_INSTANCE->Escape($val);
    }

    /**
     * Quote and escape value to prepare it for use in SQL
     *
     * @access public
     * @param string $val
     * @return string
     */
    public static function GetQuotedSql($val)
    {
        if (DataAdapter::$ADAPTER_INSTANCE) {
            DataAdapter::$ADAPTER_INSTANCE->LoadDriver();
        }

            // this is an unfortunate leftover from poor design of making this function static
            // we cannon use the driver's escape method without a static reference
        if (! DataAdapter::$DRIVER_INSTANCE) {
            throw new Exception("DataAdapter must be instantiated before Escape can be called");
        }

            // $driver->RequireConnection(true);
        return DataAdapter::$DRIVER_INSTANCE->GetQuotedSql($val);
    }

    /**
     * Registers/attaches an IObserver to this object
     *
     * @access public
     * @param IObserver $observer
     */
    public function AttachObserver($listener)
    {
        if ($listener) {
            $this->_observers [] = & $listener;
            if ($this->_masterAdapter) {
                $this->_masterAdapter->AttachObserver($listener);
            }
        }
    }

    /**
     * Fires the Observe event on all registered observers
     *
     * @access public
     * @param variant $obj
     *          the $obj or message that you want to log/listen to, etc.
     * @param int $ltype
     *          the type/level
     */
    public function Observe($obj, $ltype = OBSERVE_INFO)
    {
        foreach ($this->_observers as $observer) {
            @$observer->Observe($obj, $ltype);
        }
    }
}
