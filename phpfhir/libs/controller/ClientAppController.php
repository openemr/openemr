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

use oeFHIR\FetchLiveData;
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

    public function createEncounterAllAction()
    {
        $pid = $this->getRequest(pid);
        $oeid = $this->getRequest(oeid);
        $type = $this->getRequest(type);
        $fs = new FetchLiveData();
        $oept = $fs->getEncounterIdList($pid);
        $notify = '';
        foreach ($oept as $e) {
            $client = new oeFHIRHttpClient();
            $id = 'encounter-' . $e['encounter'];
            $rs = new oeFHIRResource();
            $r = $rs->createEncounterResource($pid, $id, $e['encounter']);
            $pt = $client->sendResource($type, $id, $r);
            $notify .= '<strong>Sent:</strong></br>' . $r . '</br>' . $pt;
        }
        return $notify;
    }

    public function createAction()
    {
        $pid = $this->getRequest(pid);
        $oeid = $this->getRequest(oeid);
        $type = $this->getRequest(type);
        $client = new oeFHIRHttpClient();
        $id = strtolower($type) . '-' . $oeid;
        $method = 'create' . $type . 'Resource';
        $rs = new oeFHIRResource();
        $r = $rs->$method($pid, $id);
        $pt = $client->sendResource($type, $id, $r);

        return '<strong>Sent:</strong></br>' . $r . '</br>' . $pt;
    }

    public function historyAction()
    {
        $pid = $this->getRequest(pid);
        $oeid = $this->getRequest(oeid);
        $type = $this->getRequest(type);
        $client = new oeFHIRHttpClient();
        $id = strtolower($type) . '-' . $oeid;
        $pt = $client->requestResource($type, $id, 'history');
        return $pt;
    }

    public function readAction()
    {
        $pid = $this->getRequest(pid);
        $oeid = $this->getRequest(oeid);
        $type = $this->getRequest(type);
        $client = new oeFHIRHttpClient();
        $id = strtolower($type) . '-' . $oeid;
        $pt = $client->requestResource($type, $id, ''); // gets latest version.
        return $pt;
    }

    public function searchAction()
    {
        $pid = $this->getRequest(pid);
        $type = $this->getRequest(type);
        if ($type === 'Patient') {
            return xlt('Search Not Available');
        }
        $client = new oeFHIRHttpClient();
        $id = 'patient-' . $pid;
        $pt = $client->searchResource($type, $id, 'search');
        return $pt;
    }
}
