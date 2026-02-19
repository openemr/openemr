<?php

/**
 * Connectivity information for the ClaimRev module.
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

class ConnectivityInfo
{
    public string $client_authority;
    public string $clientId;
    public string $client_scope;
    public string $client_secret;
    public string $api_server;
    public string $hasToken;
    public string $defaultAccount;

    public function __construct()
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();

        $this->client_authority = $globalsConfig->getClientAuthority();
        /** @var string $clientId */
        $clientId = $globalsConfig->getClientId();
        $this->clientId = $clientId;
        $this->client_scope = $globalsConfig->getClientScope();
        $clientSecret = $globalsConfig->getClientSecret();
        $this->client_secret = is_string($clientSecret) ? $clientSecret : '';
        $this->api_server = $globalsConfig->getApiServer();

        try {
            $api = ClaimRevApi::makeFromGlobals();
            $this->hasToken = $api->canConnect() ? 'Yes' : 'No';
            $this->defaultAccount = json_encode($api->getDefaultAccount(), JSON_THROW_ON_ERROR);
        } catch (ClaimRevAuthenticationException) {
            $this->hasToken = 'No';
            $this->defaultAccount = '';
        } catch (ClaimRevApiException) {
            $this->hasToken = 'Yes';
            $this->defaultAccount = '';
        }
    }
}
