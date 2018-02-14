<?php

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
