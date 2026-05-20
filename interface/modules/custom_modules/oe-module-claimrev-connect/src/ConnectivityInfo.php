<?php

/**
 * Connectivity information for the ClaimRev module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Core\OEGlobalsBag;

class ConnectivityInfo
{
    public string $client_authority;
    public string $clientId;
    public string $client_scope;
    public string $client_secret;
    public string $api_server;
    public bool $hasToken;
    public string $defaultAccount;

    public function __construct()
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();

        $clientIdRaw = $globalsConfig->getClientId();
        $this->clientId = is_string($clientIdRaw) ? $clientIdRaw : '';

        try {
            $this->client_authority = $globalsConfig->getClientAuthority();
            $this->client_scope = $globalsConfig->getClientScope();
            $this->api_server = $globalsConfig->getApiServer();
        } catch (ModuleNotConfiguredException) {
            $this->client_authority = '';
            $this->client_scope = '';
            $this->api_server = '';
            $this->client_secret = '';
            $this->hasToken = false;
            $this->defaultAccount = '';
            return;
        }

        $clientSecret = $globalsConfig->getClientSecret();
        $this->client_secret = is_string($clientSecret) ? $clientSecret : '';

        try {
            $api = ClaimRevApi::makeFromGlobals();
            $this->hasToken = $api->canConnect();
            $account = $api->getDefaultAccount();
            $accountValue = $account['value'] ?? null;
            $this->defaultAccount = is_string($accountValue)
                ? $accountValue
                : json_encode($account, JSON_THROW_ON_ERROR);
        } catch (ClaimRevAuthenticationException) {
            $this->hasToken = false;
            $this->defaultAccount = '';
        } catch (ClaimRevApiException) {
            $this->hasToken = true;
            $this->defaultAccount = '';
        } catch (ModuleNotConfiguredException) {
            $this->hasToken = false;
            $this->defaultAccount = '';
        }
    }
}
