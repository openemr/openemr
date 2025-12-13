<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\Dorn;

use DateTime;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;
use OpenEMR\Modules\Dorn\models\AckViewModel;
use OpenEMR\Modules\Dorn\models\ApiResponseViewModel;
use OpenEMR\Modules\Dorn\models\CompendiumInstallDateViewModel;
use OpenEMR\Modules\Dorn\models\LabOrderViewModel;

class ConnectorApi
{
    public static function searchOrderStatus($originalOrderNumber, $primaryId, $startDateTime, $endDateTime)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Orders/v1/SearchOrderStatus";
        $params = [];
        // Initialize an empty params array

        if (!empty($originalOrderNumber)) {
            $params['originalOrderNumber'] = $originalOrderNumber;
        }
        if (!empty($primaryId)) {
            $params['primaryId'] = $primaryId;
        }
        if (!empty($startDateTime)) {
            $params['startDateTime'] = $startDateTime;
        }
        if (!empty($endDateTime)) {
            $params['endDateTime'] = $endDateTime;
        }

        $url = $url . '?' . http_build_query($params);
        $returnData = ConnectorApi::getData($url);
        return $returnData;
    }

    public static function sendAck($resultsGuid, $isRejected, $msgs)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Orders/v1/AcknowledgeResult";
        $data = new AckViewModel();
        $data->resultsGuid = $resultsGuid;
        $data->isRejected = $isRejected;
        if (is_array($msgs) && !empty($msgs)) {
            $data->errorMessages = $msgs;
        }
        return ConnectorApi::postData($url, $data);
    }

    public static function setCompendiumLastUpdate($labGuid)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Labs/v1/SetCompendiumInstallDate";
        $data = new CompendiumInstallDateViewModel();
        $data->installDate = (new DateTime())->format('Y-m-d\TH:i:s');
        $data->labGuid = $labGuid;
        return ConnectorApi::putData($url, $data);
    }

    public static function searchPendingLabResults($labAccountNumber, $startDateTime, $endDateTime)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Orders/v1/GetPendingResults";
        $params = [];

        if (!empty($labAccountNumber)) {
            $params['labAccountNumber'] = $labAccountNumber;
        }
        if (!empty($startDateTime)) {
            $params['startDateTime'] = $startDateTime;
        }
        if (!empty($endDateTime)) {
            $params['endDateTime'] = $endDateTime;
        }

        $url = $url . '?' . http_build_query($params);
        $returnData = ConnectorApi::getData($url);
        return $returnData;
    }

    public static function getLabResults($resultsGuid)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Orders/v1/GetResults/" . $resultsGuid;
        $returnData = ConnectorApi::getData($url);
        return $returnData;
    }


    public static function sendOrder($labGuid, $labAccountNumber, $orderNumber, $patientId, $hl7)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Orders/v1/SendLabOrder";
        $base64 = base64_encode((string) $hl7);
        $data = new LabOrderViewModel();
        $data->labGuid = $labGuid . '';
        $data->orderNumber = $orderNumber . '';
        $data->patientId = $patientId . '';
        $data->hl7Base64 = $base64;
        $data->labAccountNumber = $labAccountNumber . '';
        return ConnectorApi::postData($url, $data);
    }

    public static function getCompendium($labGuid)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Labs/v1/" . $labGuid . "/Compendium";
        $returnData = ConnectorApi::getData($url);
        return $returnData;
    }

    public static function createRoute($data)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Route/v1/CreateRoute";
        return ConnectorApi::postData($url, $data);
    }
    public static function getRoutesFromDorn()
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Route/v1/GetRoutesFromDorn";
        $returnData = ConnectorApi::getData($url);
        return $returnData;
    }

    public static function deleteRoutesFromDorn($labGuid, $labAccountNumber)
    {
        $payload = [
            'LabGuid'      => $labGuid,
            'AccountNumber' => $labAccountNumber
        ];
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Route/v1/DeleteRoute";
        $returnData = ConnectorApi::postData($url, $payload);
        return $returnData;
    }
    public static function getLab($labGuid)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Labs/v1/" . $labGuid;
        $returnData = ConnectorApi::getData($url);
        return $returnData;
    }

    public static function searchLabs($labName, $phoneNumber, $faxNumber, $city, $state, $zipCode, $isActive, $isConnected)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Labs/v1/SearchLabs";
        $params = [];
// Initialize an empty params array

        if (!empty($labName)) {
            $params['labName'] = $labName;
        }
        if (!empty($phoneNumber)) {
            $params['phoneNumber'] = $phoneNumber;
        }
        if (!empty($faxNumber)) {
            $params['faxNumber'] = $faxNumber;
        }
        if (!empty($city)) {
            $params['city'] = $city;
        }
        if (!empty($state)) {
            $params['state'] = $state;
        }
        if (!empty($zipCode)) {
            $params['zipCode'] = $zipCode;
        }
        if (!empty($isActive)) {
            if ($isActive == "yes") {
                $params['isActive'] = "true";
            } elseif ($isActive == "no") {
                $params['isActive'] = "false";
            }
        }
        if (!empty($isConnected)) {
            if ($isConnected == "yes") {
                $params['isConnected'] = "true";
            } elseif ($isConnected == "no") {
                $params['isConnected'] = "false";
            }
        }

        $url = $url . '?' . http_build_query($params);
        $returnData = ConnectorApi::getData($url);
        return $returnData;
    }

    public static function savePrimaryInfo($data)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Customer/v1/SaveCustomerPrimaryInfo";
        return ConnectorApi::postData($url, $data);
    }

    public static function getPrimaryInfoByNpi($npi)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Customer/v1/GetPrimaryInfoByNpi";
        if ($npi) {
            $params = ['npi' => $npi];
            $url = $url . '?' . http_build_query($params);
        }

        $returnData = ConnectorApi::getData($url);
        return $returnData;
    }

    public static function getPrimaryInfos($npi)
    {
        $api_server = ConnectorApi::getServerInfo();
        $url = $api_server . "/api/Customer/v1/SearchPrimaryInfo";
        if ($npi) {
            $params = ['npi' => $npi];
            $url = $url . '?' . http_build_query($params);
        }

        $returnData = ConnectorApi::getData($url);
        return $returnData;
    }

    public static function getData($url)
    {
        $headers = ConnectorApi::buildHeader();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result === false) {
            error_log('cURL error: ' . curl_error($ch));
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode == 200 || $httpcode == 400) {
            $responseJsonData = json_decode($result);
            return $responseJsonData;
        }
        error_log("Error " . "Status Code" . text($httpcode) . " sending in api " . text($url) . " Message " . text($result));
        return "";
    }

    public static function putData($url, $sendData)
    {
        $headers = ConnectorApi::buildHeader();
        $payload = json_encode($sendData, JSON_UNESCAPED_SLASHES);
        error_log("putting");
        error_log(text($payload));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
// Use PUT method
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result === false) {
            error_log('cURL error: ' . curl_error($ch));
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode == 200 || $httpcode == 400) {
            $responseJsonData = json_decode($result);
            return $responseJsonData;
        }

        error_log("Error " . "Status Code" . text($httpcode) . " sending in api " . text($url) . " Message " . text($result));
        $response = new ApiResponseViewModel();
        $response->isSuccess = false;
        $response->responseMessage = "Error Putting Data!";
        return $response;
    }

    public static function postData($url, $sendData)
    {
        $error = "";
        $headers = ConnectorApi::buildHeader();
        $payload = json_encode($sendData, JSON_UNESCAPED_SLASHES);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result === false) {
            $error = curl_error($ch);
            error_log('cURL error: ' . curl_error($ch));
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode == 200 || $httpcode == 400) {
            $responseJsonData = json_decode($result);
            return $responseJsonData;
        }
        error_log("Error " . "Status Code" . text($httpcode) . " sending in api " . text($url) . " Message " . text($result));
        $response = new ApiResponseViewModel();
        $response->isSuccess = false;
        $response->responseMessage = "Error Posting Data! " . $error;
        return $response;
    }

    public static function getServerInfo()
    {
        $bootstrap = new Bootstrap($GLOBALS['kernel']->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $api_server = $globalsConfig->getApiServer();
        return $api_server;
    }

    public static function buildHeader()
    {
        $token = ConnectorApi::getAccessToken();
        $content = 'content-type: application/json';
        $bearer = 'authorization: Bearer ' . $token;
        $headers = [
            $content,
            $bearer
        ];
        return $headers;
    }


    public static function canConnectToClaimRev()
    {
        $token = ClaimRevApi::GetAccessToken();
        if ($token == "") {
            return "No";
        }
        return "Yes";
    }

    public static function getAccessToken()
    {
        $bootstrap = new Bootstrap($GLOBALS['kernel']->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $authority = $globalsConfig->getClientAuthority();
        $clientId = $globalsConfig->getClientId();
        $scope = $globalsConfig->getClientScope();
        $client_secret = $globalsConfig->getClientSecret();
        $api_server = $globalsConfig->getApiServer();
        $headers = [
            'content-type: application/x-www-form-urlencoded'
        ];
        $payload = "client_id=" . $clientId . "&scope=" . $scope . "&client_secret=" . $client_secret . "&grant_type=client_credentials";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $authority);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result === false) {
            error_log('cURL error: ' . curl_error($ch));
        }
        $data = json_decode($result);
        $token = "";
        if (property_exists($data, 'access_token')) {
            $token = $data->access_token;
        }

        return $token;
    }
}
