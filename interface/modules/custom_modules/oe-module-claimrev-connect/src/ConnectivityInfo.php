<?php

/**
 * Connectivity information for ClaimRev debug page
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

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ClaimRevConnector\Bootstrap;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;
use OpenEMR\Modules\ClaimRevConnector\Exception\ClaimRevApiException;

class ConnectivityInfo
{
    public $client_authority;
    public $clientId;
    public $client_scope;
    public $client_secret;
    public $api_server;
    public $hasToken;
    public $defaultAccount;

    public function __construct()
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();
        $this->client_authority = $globalsConfig->getClientAuthority();
        $this->clientId = $globalsConfig->getClientId();
        $this->client_scope = $globalsConfig->getClientScope();
        $this->client_secret = $globalsConfig->getClientSecret();
        $this->api_server = $globalsConfig->getApiServer();
        $this->hasToken = ClaimRevApi::canConnectToClaimRev();

        try {
            $token = ClaimRevApi::getAccessToken();
            $defaultAccount = ClaimRevApi::getDefaultAccount($token);
        } catch (ClaimRevApiException) {
            $token = '';
            $defaultAccount = '';
        }
        $this->token = $token;
        $this->defaultAccount = $defaultAccount;
    }
}
