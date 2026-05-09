<?php

/**
 * CreateClientCredentialsAssertion Is a helper utility to create a Client Credentials Grant assertion statement as
 * well as print out the Public JSON Web Key Set that can be used for a test System App.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use Lcobucci\JWT\Signer\Key\InMemory;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Command\Runner\CommandContext;
use OpenEMR\Tools\OAuth2\ClientCredentialsAssertionGenerator;

class CreateClientCredentialsAssertionCommand implements IOpenEMRCommand
{
    /**
     * Returns the summary description of this command.
     * @param CommandContext $context
     * @return string
     */
    public function getDescription(CommandContext $context): string
    {
        return "utility class to help test and use the client credentials grant assertion";
    }

    /**
     * Prints out how to use this command.
     * @param CommandContext $context
     */
    public function printUsage(CommandContext $context)
    {
        echo "Command Usage: " . $context->getScriptName() . " -c CreateClientCredentialsAssertion -i <JWT_Issuer> "
            . " -a <OpenEMR_OAUTH2_Token_URL>\n";
        echo "-i <CLIENT_ID> The Client ID received from the OpenEMR registration. Used as the issuer and subject for the JWT\n";
        echo "-a <OpenEMR_OAUTH2_Token_URL> is the audience of the JWT which should be the OpenEMR Oauth2 Token URL Endpoint\n";
        echo "-h Prints out this help message\n";
        echo "-k Prints out the JSON Web Key Set public key that can be registered with OpenEMR for the client application\n\n";
    }

    /**
     * Runs the command
     * @param CommandContext $context
     * @throws \Exception
     */
    public function execute(CommandContext $context)
    {
        echo "Executing command 'CreateClientCredentialsAssertion'\n";
        $opts = getopt('c:i:a:hk');

        $keyLocation = $context->getRootPath() . "tests" . DIRECTORY_SEPARATOR . "Tests" . DIRECTORY_SEPARATOR
            . "data" . DIRECTORY_SEPARATOR . "Unit" . DIRECTORY_SEPARATOR . "Common" . DIRECTORY_SEPARATOR . "Auth"
            . DIRECTORY_SEPARATOR . "Grant" . DIRECTORY_SEPARATOR;

        if (isset($opts['k'])) {
            $jwks = file_get_contents($keyLocation . "jwk-public-valid.json");
            echo "JSON Web Key Set (Public Key)\n";
            echo "WARNING - THIS IS FOR TESTING PURPOSES ONLY!\n";
            echo "DO NOT USE THIS IN PRODUCTION AS THE PRIVATE KEYS FOR THIS JWKS IS COMMITTED TO THE SOURCE CODE\n\n";
            echo $jwks . "\n\n";
            return;
        }

        if (!(isset($opts['i']) && isset($opts['a']))) {
            echo "Missing required arguments.\n";
            $this->printUsage($context);
            return;
        }

        $oauthTokenUrl = $opts['a'];
        $clientId = $opts['i'];
        if (!is_string($oauthTokenUrl) || $oauthTokenUrl === '' || !is_string($clientId) || $clientId === '') {
            echo "Arguments -a and -i must be non-empty strings.\n";
            $this->printUsage($context);
            return;
        }
        // SMART Backend Services / RFC 7515 §4.1.4: the OpenEMR OAuth
        // server's JWT validator requires a `kid` header. Pull it from
        // the same fixture JWKS the public/private keys come from so
        // the generated assertion is server-acceptable out of the box.
        $kid = null;
        $jwksRaw = @file_get_contents($keyLocation . "jwk-public-valid.json");
        if (is_string($jwksRaw)) {
            $decoded = json_decode($jwksRaw, true);
            // Step through the structure with intermediate variables so
            // PHPStan can narrow `mixed` at each level — chained
            // subscripts on `mixed` trip `offsetAccess.nonOffsetAccessible`.
            $keys = is_array($decoded) && isset($decoded['keys']) && is_array($decoded['keys'])
                ? $decoded['keys']
                : null;
            $firstKey = $keys !== null && isset($keys[0]) && is_array($keys[0])
                ? $keys[0]
                : null;
            if (
                $firstKey !== null
                && isset($firstKey['kid'])
                && is_string($firstKey['kid'])
                && $firstKey['kid'] !== ''
            ) {
                $kid = $firstKey['kid'];
            }
        }

        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            InMemory::file($keyLocation . "openemr-rsa384-private.key"),
            InMemory::file($keyLocation . "openemr-rsa384-public.pem"),
            $oauthTokenUrl,
            $clientId,
            $kid,
        );
        echo "Generated Client Credentials Assertion\n";
        echo $assertion . "\n";

        echo "\n\nSample CURL request using assertion: \n";
        $assertionType = CustomClientCredentialsGrant::OAUTH_JWT_CLIENT_ASSERTION_TYPE;
        $scope = 'system/*.\$export system/*.\$bulkdata-status system/Group.\$export system/Patient.\$export '
        . 'system/Encounter.read system/Binary.read';
        echo "--> curl -k -X POST --data-urlencode \"client_assertion_type=$assertionType\" \\\n"
        . "  --data-urlencode \"client_assertion=$assertion\" \\\n"
        . "  --data-urlencode \"grant_type=client_credentials\" \\\n"
        . "  --data-urlencode \"scope=$scope\" $oauthTokenUrl\n";
    }
}
