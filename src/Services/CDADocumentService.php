<?php

/**
 * Document Service for CCDA
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use CouchDB;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Uuid\UuidRegistry;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Class CDADocumentService
 *
 * @package OpenEMR\Services
 *
 * See interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php
 * indexAction() and interface/modules/zend_modules/public/index.php
 */
class CDADocumentService extends BaseService
{
    const TABLE_NAME = "ccda";
    protected $serverUrl;

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
        UuidRegistry::createMissingUuidsForTables([self::TABLE_NAME]);
        $this->serverUrl = $GLOBALS['qualified_site_addr'];
    }

    /**
     * @param $pid
     * @return array|false|null
     */
    public function getLastCdaMeta($pid)
    {
        $query = "SELECT cc.uuid, cc.date, pd.fname, pd.lname, pd.pid FROM ccda AS cc
		    LEFT JOIN patient_data AS pd ON pd.pid=cc.pid
		    WHERE cc.pid = ?
		    ORDER BY cc.id DESC LIMIT 1";

        return sqlQuery($query, array($pid));
    }

    /**
     * @param $id
     * @return false|string
     */
    public function getFile($id)
    {
        $query = "select couch_docid, couch_revid, ccda_data, encrypted from ccda where uuid=?";
        $row = sqlQuery($query, array($id));
        $content = '';
        if (!empty($row)) {
            if (!empty($row['couch_docid'])) {
                $couch = new CouchDB();
                $resp = $couch->retrieve_doc($row['couch_docid']);
                if ($row['encrypted']) {
                    $cryptoGen = new CryptoGen();
                    $content = $cryptoGen->decryptStandard($resp->data, null, 'database');
                } else {
                    $content = base64_decode($resp->data);
                }
            } elseif (!empty($row['ccda_data'])) {
                $fccda = fopen($row['ccda_data'], "r");
                if ($row['encrypted']) {
                    $cryptoGen = new CryptoGen();
                    $content = $cryptoGen->decryptStandard(fread($fccda, filesize($row['ccda_data'])), null, 'database');
                } else {
                    $content = fread($fccda, filesize($row['ccda_data']));
                }
                fclose($fccda);
            }
        }

        return $content;
    }

    /**
     * @param $pid
     * @return string
     */
    public function generateCCDHtml($pid): string
    {
        $url = $this->serverUrl . "/interface/modules/zend_modules/public/encounterccdadispatch";
        $httpClient = HttpClient::create([
            "verify_peer" => false,
            "verify_host" => false
        ]);
        $response = $httpClient->request('GET', $url, [
            'query' => [
                'combination' => $pid,
                'recipient' => 'self',
                'view' => '1',
                'site' => $_SESSION ['site_id'],
                'sent_by_app' => 'core_api',
                'me' => session_id()
            ]
        ]);

        $status = $response->getStatusCode(); // @todo validate

        return $response->getContent();
    }

    /**
     * @param $pid
     * @return string
     */
    public function generateCCDXml($pid): string
    {
        $url = $this->serverUrl . "/interface/modules/zend_modules/public/encounterccdadispatch";
        $httpClient = HttpClient::create([
            "verify_peer" => false,
            "verify_host" => false
        ]);
        $response = $httpClient->request('GET', $url, [
            'query' => [
                'combination' => $pid,
                'recipient' => 'patient',
                'view' => '0',
                'hiehook' => '1',
                'sent_by_app' => 'core_api',
                'me' => session_id()
            ]
        ]);

        $status = $response->getStatusCode(); // @todo validate

        return $response->getContent();
    }

    /**
     * @param $pid
     * @return string
     */
    public function portalGenerateCCD($pid): string
    {
        $url = $this->serverUrl . "/interface/modules/zend_modules/public/encounterccdadispatch";
        $httpClient = HttpClient::create([
            "verify_peer" => false,
            "verify_host" => false
        ]);
        $response = $httpClient->request('GET', $url, [
            'query' => [
                'combination' => $pid,
                'recipient' => 'patient',
                'view' => '1',
                'me' => session_id(),// to authenticate in CCM. Portal only.
                'site' => $_SESSION ['site_id']
            ]
        ]);

        $status = $response->getStatusCode(); // @todo validate

        return $response->getContent();
    }

    /**
     * @param $pid
     * @return string
     */
    public function portalGenerateCCDZip($pid): string
    {
        $parameterArray = array(
            'combination' => $pid,
            'components' => 'allergies|medications|problems|immunizations|procedures|results|plan_of_care|vitals|social_history|encounters|functional_status|referral|instructions|medical_devices|goals',
            'downloadccda' => 'download_ccda',
            'latestccda' => '0',
            'send_to' => 'download_all',
            'sent_by_app' => 'portal',
            'ccda_pid' => [0 => $pid],
            'view' => 0,
            'recipient' => 'patient',
            'site' => $_SESSION ['site_id'],
        );
        $url = $this->serverUrl . "/interface/modules/zend_modules/public/encounterccdadispatch";
        $httpClient = HttpClient::create([
            "verify_peer" => false,
            "verify_host" => false
        ]);
        $response = $httpClient->request('POST', $url, [
            'query' => ['me' => session_id()], // to authenticate in CCM. Portal only.
            'body' => $parameterArray
        ]);

        $status = $response->getStatusCode(); // @todo validate

        return $response->getContent();
    }

    /**
     * Complete zip of xml, html version
     * when called within an openemr authorized session.
     *
     * @param $pid
     * @return string
     */
    public function generateCCDZip($pid): string
    {
        $parameterArray = array(
            'combination' => $pid,
            'components' => 'allergies|medications|problems|immunizations|procedures|results|plan_of_care|vitals|social_history|encounters|functional_status|referral|instructions|medical_devices|goals',
            'downloadccda' => 'download_ccda',
            'latestccda' => '0',
            'send_to' => 'download_all',
            'sent_by_app' => 'core_api',
            'ccda_pid' => [0 => $pid],
            'view' => 0,
            'recipient' => 'self',
            'site' => $_SESSION['site_id'],
        );
        $url = $this->serverUrl . "/interface/modules/zend_modules/public/encounterccdadispatch"; // add for debug ?XDEBUG_SESSION=PHPSTORM
        $httpClient = HttpClient::create([
            "verify_peer" => false,
            "verify_host" => false
        ]);
        $response = $httpClient->request('POST', $url, [
            'query' => ['me' => session_id()],
            'body' => $parameterArray
        ]);

        $status = $response->getStatusCode(); // @todo validate

        return $response->getContent();
    }
}
