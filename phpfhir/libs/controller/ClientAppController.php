<?php

/**
 * clientController class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("oeDispatcher.php");
require_once(dirname(__FILE__) . "/../../../_rest_config.php");

use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Common\Http\oeHttp;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FhirResourcesService;

class clientController extends oeDispatchController
{
    private $fhirBase;
    private $apiBase;
    private $apifhirBase;
    private $credentials;

    public function __construct()
    {
        //$fhir_base = trim($GLOBALS['fhir_base_url']);
        //$this->fhirBase = substr($fhir_base, -1) == '/' ? $fhir_base : $fhir_base . '/';
        $this->apiBase = $_SERVER['HTTP_HOST'] . $GLOBALS['web_root'] . '/apis/fhir/';
        $this->fhirBase = "https://" . $_SERVER['HTTP_HOST'] . $GLOBALS['web_root'] . '/apis/fhir/';
        // password hardcoded for now. need to do lookup once App credentials are developed
        $this->credentials = array("grant_type" => "password", "username" => $_SESSION['authUser'], "password" => 'pass', "scope" => "default");
        // When oAuth is called in this manner it will save return auth token in cache
        // and reAuth() will auto send stored tokens with Http calls
        // @todo add status 200 check
        $jC = json_encode($this->credentials);
        $auth = oeHttp::withOAuth($this->credentials, $this->fhirBase . 'auth', 'password')
            ->asJson()
            ->post($this->fhirBase, $this->credential);
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
        $pid = $this->getRequest('pid');
        $oeid = $this->getRequest('oeid');
        $type = $this->getRequest('type');

        $this->encounterService = new EncounterService();
        $this->fhirService = new FhirResourcesService();
        $oept = $this->encounterService->getEncountersForPatient($pid);

        $notify = '';
        foreach ($oept as $e) {
            $resource = $this->fhirService->createEncounterResource($e['id'], $e, 0);
            $fhir_uri = 'Encounter/' . $e['id'];
            $response = oeHttp::reAuth()->asJson()->usingBaseUri($this->fhirBase)->put($fhir_uri, $resource);
            $notify .= "<strong>Sent: $fhir_uri</strong><br />" . $this->checkErrors($response) . $response->body() . '<br />';
        }

        return $notify;
    }

    public function createAction()
    {
        $oeid = $this->getRequest('oeid');
        $type = $this->getRequest('type');

        $fhir_uri = $this->fhirBase . $type . '/' . $oeid;
        $api = '/fhir/' . $type . '/' . $oeid;

        // get resource from api
        $gbl = RestConfig::GetInstance();
        $gbl::setNotRestCall();
        $resource = HttpRestRouteHandler::dispatch($gbl::$FHIR_ROUTE_MAP, $api, 'GET', 'direct');

        // create resource on Fhir server.
        $returned = oeHttp::reAuth()->asJson()->put($fhir_uri, $resource);
        $reply = $returned->body();

        $head = '<strong>Transaction Status: ' . $returned->getStatusCode() . ' ' . $returned->getReasonPhrase() . '</strong><br/>';
        foreach ($returned->getHeaders() as $name => $values) {
            $head .= $name . ': ' . implode(', ', $values) . "<br/>";
        }

        return $head . '<strong>Replied Resource:</strong><br />' . print_r(json_decode($reply, true)) . '<br />';
    }

    public function historyAction()
    {
        $oeid = $this->getRequest('oeid');
        $type = $this->getRequest('type');

        $id = $oeid;
        $uri = $type . '/' . $id . '/_history';
        $response = oeHttp::reAuth()->usingBaseUri($this->fhirBase)->get($uri);
        return $this->checkErrors($response) . $response->body();
    }

    public function readAction()
    {
        $oeid = $this->getRequest('oeid');
        $type = $this->getRequest('type');
        $api = $this->getRequest('api');

        if (!empty($api)) {
            $uri = $type . '/' . $oeid;
            $response = oeHttp::reAuth()->usingBaseUri($this->fhirBase)->get($uri);
        } else {
            $uri = $type . '/' . $oeid;
            $response = oeHttp::withOAuth($this->credentials, $endpoint = $this->fhirBase . 'auth', $grant_type = 'password')
                ->usingBaseUri($this->fhirBase)->get($uri);
        }

        return $this->checkErrors($response) . $response->body();
    }

    public function searchAction()
    {
        $pid = $this->getRequest('pid');
        $type = $this->getRequest('type');
        if ($type === 'Patient') {
            return xlt('Patient Search Not Available');
        }

        $id = $pid;
        $query = [
            'patient' => $id,
            '_format' => 'json',
            '_pretty' => 'true'
        ];
        $response = oeHttp::reAuth()->usingBaseUri($this->fhirBase)->get($type, $query);

        return $this->checkErrors($response) . $response->body();
    }

    private function checkErrors($response)
    {
        $check = '';
        if ($response->status() !== 200) {
            $check = "<br /><strong>Error:" . $response->status() . " : " . $response->getReasonPhrase() . "</strong><br />";
        }

        return $check;
    }

    private function parseId($id)
    {
        return preg_replace('/[^0-9]/', '', $id);
    }
}
