<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Utils\HttpUtils;
use OpenEMR\Common\Utils\RandomGenUtils;
use Psr\Log\LoggerInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $cryptoGen;

    public function __construct()
    {
        $this->logger = new SystemLogger();
        $this->cryptoGen = new CryptoGen();
    }

    /**
     * @return CryptoGen
     */
    public function getCryptoGen(): CryptoGen
    {
        return $this->cryptoGen;
    }

    /**
     * @param CryptoGen $cryptoGen
     * @return ClientRepository
     */
    public function setCryptoGen(CryptoGen $cryptoGen): ClientRepository
    {
        $this->cryptoGen = $cryptoGen;
        return $this;
    }

    public function insertNewClient($clientId, $info, $site): bool
    {
        $user = $_SESSION['authUserID'] ?? null; // future use for provider client.
        $is_confidential_client = empty($info['client_secret']) ? 0 : 1;
        $skip_ehr_launch_authorization_flow = $info['skip_ehr_launch_authorization_flow'] == true ? 1 : 0;

        $contacts = $info['contacts'];
        $redirects = $info['redirect_uris'];
        if (is_array($redirects)) {
            // need to combine our redirects if we are an array... this is due to the legacy implementation of this data
            $redirects = implode("|", $redirects);
        }
        $logout_redirect_uris = $info['post_logout_redirect_uris'] ?? null;
        $info['client_secret'] = $info['client_secret'] ?? null; // just to be sure empty is null;
        // set our list of default scopes for the registration if our scope is empty
        // This is how a client can set if they support SMART apps and other stuff by passing in the 'launch'
        // scope to the dynamic client registration.
        // per RFC 7591 @see https://tools.ietf.org/html/rfc7591#section-2
        // TODO: adunsulag do we need to reject the registration if there are certain scopes here we do not support
        // TODO: adunsulag should we check these scopes against our '$this->supportedScopes'?
        $info['scope'] = $info['scope'] ?? 'openid email phone address api:oemr api:fhir api:port';

        $scopes = explode(" ", $info['scope']);
        $scopeRepo = new ScopeRepository();

        if ($scopeRepo->hasScopesThatRequireManualApproval($is_confidential_client == 1, $scopes)) {
            $is_client_enabled = 0; // disabled
        } else {
            $is_client_enabled = 1; // enabled
        }

        // encrypt the client secret
        if (!empty($info['client_secret'])) {
            $cryptoGen = $this->getCryptoGen();
            $info['client_secret'] = $cryptoGen->encryptStandard($info['client_secret']);
        }

        // TODO: @adunsulag why do we skip over request_uris when we have it in the outer function?
        $sql = "INSERT INTO `oauth_clients` (`client_id`, `client_role`, `client_name`, `client_secret`, `registration_token`, `registration_uri_path`, `register_date`, `revoke_date`, `contacts`, `redirect_uri`, `grant_types`, `scope`, `user_id`, `site_id`, `is_confidential`, `logout_redirect_uris`, `jwks_uri`, `jwks`, `initiate_login_uri`, `endorsements`, `policy_uri`, `tos_uri`, `is_enabled`, `skip_ehr_launch_authorization_flow`) VALUES (?, ?, ?, ?, ?, ?, NOW(), NULL, ?, ?, 'authorization_code', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $i_vals = array(
            $clientId,
            $info['client_role'],
            $info['client_name'],
            $info['client_secret'],
            $info['registration_access_token'],
            $info['registration_client_uri_path'],
            $contacts,
            $redirects,
            $info['scope'],
            $user,
            $site,
            $is_confidential_client,
            $logout_redirect_uris,
            ($info['jwks_uri'] ?? null),
            ($info['jwks'] ?? null),
            ($info['initiate_login_uri'] ?? null),
            ($info['endorsements'] ?? null),
            ($info['policy_uri'] ?? null),
            ($info['tos_uri'] ?? null),
            $is_client_enabled,
            $skip_ehr_launch_authorization_flow
        );

        return sqlQueryNoLog($sql, $i_vals, true); // throw an exception if it fails
    }

    public function generateClientId()
    {
        return HttpUtils::base64url_encode(RandomGenUtils::produceRandomBytes(32));
    }

    public function generateClientSecret()
    {
        return HttpUtils::base64url_encode(RandomGenUtils::produceRandomBytes(64));
    }

    /**
     * @return ClientEntity[]
     */
    public function listClientEntities(): array
    {
        $clients = sqlStatementNoLog("Select * From oauth_clients ORDER BY is_enabled DESC, register_date DESC");
        $list = [];
        if (!empty($clients)) {
            while ($client = $clients->FetchRow()) {
                $list[] = $this->hydrateClientEntityFromArray($client);
            }
        }
        return $list;
    }

    public function getClientEntity($clientIdentifier)
    {
        $clients = sqlQueryNoLog("Select * From oauth_clients Where client_id=?", array($clientIdentifier));

        // Check if client is registered
        if ($clients === false) {
            $this->logger->error(
                "ClientRepository->getClientEntity() no client found for identifier ",
                ["client" => $clientIdentifier]
            );
            return false;
        }

        $this->logger->debug(
            "ClientRepository->getClientEntity() client found",
            [
                "client" => [
                    "client_name" => $clients['client_name'],
                    "redirect_uri" => $clients['redirect_uri'],
                    "is_confidential" => $clients['is_confidential']
                ]
            ]
        );
        return $this->hydrateClientEntityFromArray($clients);
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $this->logger->debug(
            "ClientRepository->validateClient() checking client validation",
            ["client" => $clientIdentifier, "grantType" => $grantType]
        );
        if ($grantType == 'authorization_code') {
            $client = sqlQueryNoLog("SELECT `client_secret`, `is_confidential` FROM `oauth_clients` WHERE `client_id` = ?", [$clientIdentifier]);

            // Check if client is registered
            if ($client === false) {
                $this->logger->error(
                    "ClientRepository->validateClient() no client found for identifier ",
                    ["client" => $clientIdentifier]
                );
                return false;
            }

            // Validate client if is_confidential
            if (!empty($clientSecret) && !empty($client['is_confidential'])) {
                $secret = (new CryptoGen())->decryptStandard($client['client_secret']);
                if (empty($secret)) {
                    return false;
                }
                $secretMatches = hash_equals($clientSecret, $secret);
                if (!$secretMatches) {
                    $this->logger->error(
                        "ClientRepository->validateClient() Confidential client sent invalid client secret.  Validation failed",
                        ["client" => $clientIdentifier, "grantType" => $grantType]
                    );
                }
                return $secretMatches;
            }

            return true;
        } else {
            // password and refresh grant
            return true;
        }
    }

    /**
     * Set a client in the database to be enabled if $isEnabled is true or disabled if $isEnabled is false.
     * @param ClientEntity $client
     * @param $isEnabled
     * @return bool True if it succeeded
     * @throws \RuntimeException If there is a database error in saving.
     */
    public function saveIsEnabled(ClientEntity $client, $isEnabled)
    {
        // TODO: adunsulag do we want to eventually just have a save() method.. it would be very handy but not sure
        // we want any oauth2 values being overwritten.
        $isEnabledSaveValue = $isEnabled === true ? 1 : 0;
        $clientId = $client->getIdentifier();
        $params = [$isEnabledSaveValue, $clientId];
        $res = sqlStatement("UPDATE oauth_clients SET is_enabled=? WHERE client_id = ?", $params);
        if ($res === false) {
            // TODO: adunsulag is there a better exception to throw here in OpenEMR than runtime?
            throw new \RuntimeException("Failed to save oauth_clients is_enabled flag.  Check logs for sql error");
        }
        return true;
    }

    /**
     * @param $client_record
     * @return ClientEntity
     */
    private function hydrateClientEntityFromArray($client_record): ClientEntity
    {
        // note redirect_uris in the database is actually named redirect_uri
        $pipedValues = array('contacts', 'redirect_uri', 'request_uri', 'post_logout_redirect_uris', 'grant_types', 'response_types', 'default_acr_values');
        foreach ($pipedValues as $value) {
            if (!empty($client_record[$value])) {
                $client_record[$value] = explode('|', $client_record[$value]);
            }
        }
        $client = new ClientEntity();
        $client->setIdentifier($client_record['client_id']);
        $client->setName($client_record['client_name']);
        $client->setRedirectUri($client_record['redirect_uri']);
        $client->setIsConfidential($client_record['is_confidential']);
        $client->setScopes($client_record['scope']);
        $client->setClientRole($client_record['client_role']);
        // launch uri is the same as the initiate_login_uri SMART uses launchUri
        // so we will refer to it that way.
        $client->setLaunchUri($client_record['initiate_login_uri']);
        $client->setIsEnabled($client_record['is_enabled'] == "1");
        $client->setJwks($client_record['jwks']);
        $client->setJwksUri($client_record['jwks_uri']);
        $client->setLogoutRedirectUris($client_record['logout_redirect_uris']);
        $client->setContacts($client_record['contacts']);
        $client->setRegistrationDate($client_record['register_date']);
        $client->setSkipEHRLaunchAuthorizationFlow($client_record['skip_ehr_launch_authorization_flow'] == "1");
        return $client;
    }

    public function generateRegistrationAccessToken()
    {
        return HttpUtils::base64url_encode(RandomGenUtils::produceRandomBytes(32));
    }

    public function generateRegistrationClientUriPath()
    {
        return HttpUtils::base64url_encode(RandomGenUtils::produceRandomBytes(16));
    }

    public function saveSkipEHRLaunchFlow(ClientEntity $client, bool $skipFlow)
    {
        // TODO: adunsulag do we want to eventually just have a save() method.. it would be very handy but not sure
        // we want any oauth2 values being overwritten.
        $skipFlowValue = $skipFlow === true ? 1 : 0;
        $clientId = $client->getIdentifier();
        $params = [$skipFlowValue, $clientId];
        $res = sqlStatement("UPDATE oauth_clients SET skip_ehr_launch_authorization_flow=? WHERE client_id = ?", $params);
        if ($res === false) {
            // TODO: adunsulag is there a better exception to throw here in OpenEMR than runtime?
            throw new \RuntimeException("Failed to save oauth_clients skip_ehr_launch_authorization_flow flag.  Check logs for sql error");
        }
        return true;
    }
}
