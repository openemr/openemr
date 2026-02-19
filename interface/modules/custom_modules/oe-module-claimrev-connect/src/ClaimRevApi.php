<?php

/**
 * ClaimRev API client
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ClaimRevConnector\Exception\ClaimRevApiException;
use OpenEMR\Modules\ClaimRevConnector\UploadEdiFileContentModel;
use OpenEMR\Modules\ClaimRevConnector\Bootstrap;

class ClaimRevApi
{
    public static function canConnectToClaimRev(): string
    {
        try {
            ClaimRevApi::GetAccessToken();
            return "Yes";
        } catch (ClaimRevApiException) {
            return "No";
        }
    }
    /**
     * @throws ClaimRevApiException
     */
    public static function getAccessToken()
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $authority);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            (new SystemLogger())->error('ClaimRev: cURL error in ' . __FUNCTION__, ['error' => $error]);
            throw new ClaimRevApiException('Failed to connect to ClaimRev authentication service: ' . $error);
        }
        curl_close($ch);

        $data = json_decode((string) $result);
        if ($data === null) {
            (new SystemLogger())->error('ClaimRev: Invalid JSON response in ' . __FUNCTION__);
            throw new ClaimRevApiException('Invalid JSON response from ClaimRev authentication service');
        }

        if (!property_exists($data, 'access_token')) {
            (new SystemLogger())->error('ClaimRev: Missing access_token in ' . __FUNCTION__);
            throw new ClaimRevApiException('ClaimRev authentication failed: no access token returned');
        }
        return $data->access_token;
    }

    public static function uploadClaimFile($ediContents, $fileName, $token)
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $api_server = $globalsConfig->getApiServer();

        $content = 'content-type: application/json';
        $bearer = 'authorization: Bearer ' . $token;
        $headers = [
            $content,
            $bearer
         ];

        $url = $api_server . "/api/InputFile/v1";

        $model = new UploadEdiFileContentModel("", $ediContents, $fileName);
        $payload = json_encode($model, JSON_UNESCAPED_SLASHES);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            (new SystemLogger())->error('ClaimRev: cURL error in ' . __FUNCTION__, ['error' => $error]);
            throw new ClaimRevApiException('Failed to upload claim file: ' . $error);
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            (new SystemLogger())->error('ClaimRev: HTTP error in ' . __FUNCTION__, ['httpcode' => $httpcode]);
            throw new ClaimRevApiException('Failed to upload claim file: HTTP ' . $httpcode, $httpcode);
        }

        $data = json_decode((string) $result);
        if (!is_object($data)) {
            (new SystemLogger())->error('ClaimRev: Invalid JSON response in ' . __FUNCTION__);
            throw new ClaimRevApiException('Invalid JSON response when uploading claim file');
        }

        if (property_exists($data, 'isError') && $data->isError) {
            $errorMsg = property_exists($data, 'errorMessage') && is_string($data->errorMessage)
                ? $data->errorMessage
                : 'Unknown error';
            (new SystemLogger())->error('ClaimRev: API error in ' . __FUNCTION__, ['error' => $errorMsg]);
            throw new ClaimRevApiException('ClaimRev API error: ' . $errorMsg);
        }

        return true;
    }

    public static function getReportFiles($reportType, $token)
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $api_server = $globalsConfig->getApiServer();

        $content = 'content-type: application/json';
        $bearer = 'authorization: Bearer ' . $token;
        $headers = [
            $content,
            $bearer
         ];

        $params = ['ediType' => $reportType];

        $endpoint = $api_server . "/api/EdiResponseFile/v1/GetReport";
        $url = $endpoint . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            (new SystemLogger())->error('ClaimRev: cURL error in ' . __FUNCTION__, ['error' => $error]);
            throw new ClaimRevApiException('Failed to get report files: ' . $error);
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            (new SystemLogger())->error('ClaimRev: HTTP error in ' . __FUNCTION__, ['httpcode' => $httpcode]);
            throw new ClaimRevApiException('Failed to get report files: HTTP ' . $httpcode, $httpcode);
        }

        $data = json_decode((string) $result);
        if ($data === null) {
            (new SystemLogger())->error('ClaimRev: Invalid JSON response in ' . __FUNCTION__);
            throw new ClaimRevApiException('Invalid JSON response when getting report files');
        }

        return $data;
    }

    public static function getDefaultAccount($token)
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $api_server = $globalsConfig->getApiServer();

        $content = 'content-type: application/json';
        $bearer = 'authorization: Bearer ' . $token;
        $headers = [
            $content,
            $bearer
         ];


        $url = $api_server . "/api/UserProfile/v1/GetDefaultAccount";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            (new SystemLogger())->error('ClaimRev: cURL error in ' . __FUNCTION__, ['error' => $error]);
            throw new ClaimRevApiException('Failed to get default account: ' . $error);
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            (new SystemLogger())->error('ClaimRev: HTTP error in ' . __FUNCTION__, ['httpcode' => $httpcode]);
            throw new ClaimRevApiException('Failed to get default account: HTTP ' . $httpcode, $httpcode);
        }

        return $result;
    }

    public static function searchClaims($claimSearch, $token)
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $api_server = $globalsConfig->getApiServer();

        $content = 'content-type: application/json';
        $bearer = 'authorization: Bearer ' . $token;
        $headers = [
            $content,
            $bearer
         ];

        $url = $api_server . "/api/ClaimView/v1/SearchClaims";

        $payload = json_encode($claimSearch, JSON_UNESCAPED_SLASHES);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            (new SystemLogger())->error('ClaimRev: cURL error in ' . __FUNCTION__, ['error' => $error]);
            throw new ClaimRevApiException('Failed to search claims: ' . $error);
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            (new SystemLogger())->error('ClaimRev: HTTP error in ' . __FUNCTION__, ['httpcode' => $httpcode]);
            throw new ClaimRevApiException('Failed to search claims: HTTP ' . $httpcode, $httpcode);
        }

        $data = json_decode((string) $result);
        if ($data === null) {
            (new SystemLogger())->error('ClaimRev: Invalid JSON response in ' . __FUNCTION__);
            throw new ClaimRevApiException('Invalid JSON response when searching claims');
        }

        return $data;
    }

    public static function searchDownloadableFiles($downloadSearch, $token)
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $api_server = $globalsConfig->getApiServer();

        $content = 'content-type: application/json';
        $bearer = 'authorization: Bearer ' . $token;
        $headers = [
            $content,
            $bearer
         ];

        $url = $api_server . "/FileManagement/SearchOutboundClientFiles";

        $payload = json_encode($downloadSearch, JSON_UNESCAPED_SLASHES);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            (new SystemLogger())->error('ClaimRev: cURL error in ' . __FUNCTION__, ['error' => $error]);
            throw new ClaimRevApiException('Failed to search downloadable files: ' . $error);
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            (new SystemLogger())->error('ClaimRev: HTTP error in ' . __FUNCTION__, ['httpcode' => $httpcode]);
            throw new ClaimRevApiException('Failed to search downloadable files: HTTP ' . $httpcode, $httpcode);
        }

        $data = json_decode((string) $result);
        if ($data === null) {
            (new SystemLogger())->error('ClaimRev: Invalid JSON response in ' . __FUNCTION__);
            throw new ClaimRevApiException('Invalid JSON response when searching downloadable files');
        }

        return $data;
    }

    /**
     * @throws ClaimRevApiException
     */
    public static function getFileForDownload($objectId, $token)
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $api_server = $globalsConfig->getApiServer();

        $content = 'content-type: application/json';
        $bearer = 'authorization: Bearer ' . $token;
        $headers = [
            $content,
            $bearer
         ];

        $endpoint = $api_server . "/FileManagement/GetFileForDownload";
        $params = ['id' => $objectId];
        $url = $endpoint . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            (new SystemLogger())->error('ClaimRev: cURL error in ' . __FUNCTION__, ['error' => $error]);
            throw new ClaimRevApiException('Failed to download file: ' . $error);
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            (new SystemLogger())->error('ClaimRev: HTTP error in ' . __FUNCTION__, ['httpcode' => $httpcode]);
            throw new ClaimRevApiException('Failed to download file: HTTP ' . $httpcode, $httpcode);
        }

        $data = json_decode((string) $result);
        if ($data === null) {
            (new SystemLogger())->error('ClaimRev: Invalid JSON response in ' . __FUNCTION__);
            throw new ClaimRevApiException('Invalid JSON response when downloading file');
        }

        return $data;
    }

    public static function getEligibilityResult($originatingSystemId, $token)
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $api_server = $globalsConfig->getApiServer();

        $content = 'content-type: application/json';
        $bearer = 'authorization: Bearer ' . $token;
        $headers = [
            $content,
            $bearer
         ];

        $endpoint = $api_server . "/api/Eligibility/v1/GetEligibilityRequest";
        $params = ['originatingSystemId' => $originatingSystemId];
        $url = $endpoint . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            (new SystemLogger())->error('ClaimRev: cURL error in ' . __FUNCTION__, ['error' => $error]);
            throw new ClaimRevApiException('Failed to get eligibility result: ' . $error);
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            (new SystemLogger())->error('ClaimRev: HTTP error in ' . __FUNCTION__, ['httpcode' => $httpcode]);
            throw new ClaimRevApiException('Failed to get eligibility result: HTTP ' . $httpcode, $httpcode);
        }

        $data = json_decode((string) $result);
        if ($data === null) {
            (new SystemLogger())->error('ClaimRev: Invalid JSON response in ' . __FUNCTION__);
            throw new ClaimRevApiException('Invalid JSON response when getting eligibility result');
        }

        return $data;
    }

    public static function uploadEligibility($eligibility, $token)
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $api_server = $globalsConfig->getApiServer();

        $content = 'content-type: application/json';
        $bearer = 'authorization: Bearer ' . $token;
        $headers = [
            $content,
            $bearer
         ];


        $url = $api_server . "/api/SharpRevenue/v1/RunSharpRevenue";
        $payload = json_encode($eligibility, JSON_UNESCAPED_SLASHES);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            (new SystemLogger())->error('ClaimRev: cURL error in ' . __FUNCTION__, ['error' => $error]);
            throw new ClaimRevApiException('Failed to upload eligibility: ' . $error);
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            (new SystemLogger())->error('ClaimRev: HTTP error in ' . __FUNCTION__, ['httpcode' => $httpcode]);
            throw new ClaimRevApiException('Failed to upload eligibility: HTTP ' . $httpcode, $httpcode);
        }

        $data = json_decode((string) $result);
        if ($data === null) {
            (new SystemLogger())->error('ClaimRev: Invalid JSON response in ' . __FUNCTION__);
            throw new ClaimRevApiException('Invalid JSON response when uploading eligibility');
        }

        return $data;
    }
}
