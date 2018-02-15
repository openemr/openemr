<?php
/**
 * clientController class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

require_once("oeDispatcher.php");

use oeFHIR\oeFHIRHttpClient;
use oeFHIR\oeFHIRResource;

class clientController extends oeDispatchController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function indexAction()
    {
        if (!$this->getSession('pid', '')) {
            $pid = $this->getRequest('patient_id');
            $this->setSession('pid', $pid);
        } else {
            $pid = $this->getSession('pid', '');
        }

        return null;
    }

    public function createAction()
    {
        $pid = $this->getRequest(pid);
        $type = $this->getRequest(type);
        $client = new oeFHIRHttpClient();
        $id = 'oe-' . $pid;
        $rs = new oeFHIRResource();
        $r = $rs->createPatientResource($pid);
        $pt = $client->sendResource($type, $id, $r);

        return $pt;
    }

    public function historyAction()
    {
        $pid = $this->getRequest(pid);
        $type = $this->getRequest(type);
        $client = new oeFHIRHttpClient();
        $id = 'oe-' . $pid;
        $pt = $client->requestResource($type, $id, 'history');
        return $pt;
    }

    public function readAction()
    {
        $pid = $this->getRequest(pid);
        $type = $this->getRequest(type);
        $client = new oeFHIRHttpClient();
        $id = 'oe-' . $pid;
        $pt = $client->requestResource($type, $id, ''); // gets latest version.
        return $pt;
    }

    public function searchAction()
    {
        $pid = $this->getRequest(pid);
        $type = $this->getRequest(type);
        $client = new oeFHIRHttpClient();
        $id = 'oe-' . $pid;
        $pt = $client->requestResource($type, $id, 'search'); // gets latest version.
        return $pt;
    }
}
