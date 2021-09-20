<?php
/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Modules\LifeMesh;

require_once "Database.php";
require_once "AppDispatch.php";


/**
 * Class Container
 * @package OpenEMR\Modules\LifeMesh
 */
class Container
{
    /**
     * @var
     */
    private $database;

    /**
     * @var
     */
    private $appdispatch;

    /**
     * @var
     */
    private $applistener;

    public function __construct()
    {
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        if ($this->database === null) {
            $this->database = new Database();
        }
        return $this->database;
    }

    /**
     * @return AppDispatch
     */
    public function getAppDispatch()
    {
        if ($this->appdispatch === null) {
            $this->appdispatch = new AppDispatch();
        }
        return $this->appdispatch;
    }

    public function getAppListener()
    {
        if ($this->applistener === null) {
            $this->applistener = new AppointmentSubscriber();
        }
        return $this->applistener;
    }
}
