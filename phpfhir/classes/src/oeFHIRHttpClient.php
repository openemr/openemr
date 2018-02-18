<?php
/**
 * oeFHIRHttpClient class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

namespace oeFHIR;

use GuzzleHttp\Client;

class oeFHIRHttpClient
{
    private $client;
    private $settings = [];

// @TODO Create exceptions catch for recovery and display.

    public function setSettings()
    {
        //$url = 'http://localhost:8076/dstu3/open/'; //Smart on FHIR (multi-tenant stu3, port 8075 is for dstu2)
        //$url = 'http://localhost:8080/hapi-fhir-jpaserver-example/baseStu3/';
        $url = trim($GLOBALS['fhir_base_url']);
        $url = substr($url, -1) == '/' ? $url : $url . '/';
        $this->settings = array(
            'base_uri' => $url, // http/https ssl cert verify is currently off
            'verify' => false, // @TODO force/add client cert check and/or endpoint cert verify
            'http_errors' => false);
    }

    public function __construct()
    {
        $this->setSettings();
        $this->client = new Client($this->settings);
    }

    public function sendResource($type = 'Patient', $id = '', $data = '')
    {
        $uri = $type . '/' . $id;
        $returned = $this->client->request('PUT', $uri, ['body' => $data]);
        $head = '<strong>Transaction Status: ' . $returned->getStatusCode() . ' ' . $returned->getReasonPhrase() . '</strong><br/>';
        foreach ($returned->getHeaders() as $name => $values) {
            $head .= $name . ': ' . implode(', ', $values) . "<br/>";
        }

        return $head;
    }

    public function requestResource($type = 'Patient', $id = '', $action = '')
    {
        $actionIs = $action == 'history' ? '/_history' : ''; // no action = read latest version
        $uri = $type . '/' . $id . $actionIs . '?_format=json';
        $returned = $this->client->request('GET', $uri);
        $body = $returned->getBody()->getContents();

        return $body;
    }

    // @todo for now search for type by patient
    public function searchResource($type = 'Patient', $id = '', $search = '')
    {
        $uri = $type . '?patient=' . $id . '&_format=json&_pretty=true';
        $returned = $this->client->request('GET', $uri);
        $body = $returned->getBody()->getContents();

        return $body;
    }
}
