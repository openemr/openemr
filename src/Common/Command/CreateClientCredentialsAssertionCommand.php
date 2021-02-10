<?php

/**
 * CreateClientCredentialsAssertion Is a helper utility to create a Client Credentials Grant assertion statement as
 * well as print out the Public JSON Web Key Set that can be used for a test System App.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Command\Runner\CommandContext;
use Ramsey\Uuid\Uuid;

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
        $opts = getopt('c:i:a:hk');

        $keyLocation = $context->getRootPath() . "tests" . DIRECTORY_SEPARATOR . "Tests" . DIRECTORY_SEPARATOR
            . "data" . DIRECTORY_SEPARATOR . "Unit" . DIRECTORY_SEPARATOR . "Common" . DIRECTORY_SEPARATOR . "Auth"
            . DIRECTORY_SEPARATOR . "Grant" . DIRECTORY_SEPARATOR;

        if (isset($opts['k'])) {
            $jwks = file_get_contents($keyLocation . "jwk-public-valid.json");
            echo "JSON Web Key Set (Public Key)\n";
            echo "WARNING - THIS IS FOR TESTING PURPOSES ONLY!\n";
            echo "DO NOT USE THIS IN PRODUCTION AS THE PRIVATE KEYS FOR THIS JWKS IS COMMITED TO THE SOURCE CODE\n\n";
            echo $jwks . "\n\n";
            return;
        }

        if (!(isset($opts['i']) && isset($opts['a']))) {
            echo "Missing required arguments.\n";
            $this->printUsage($context);
            return;
        }


        $configuration = Configuration::forAsymmetricSigner(
        // You may use RSA or ECDSA and all their variations (256, 384, and 512)
            new Sha384(),
            LocalFileReference::file($keyLocation . "openemr-rsa384-private.key"),
            LocalFileReference::file($keyLocation . "openemr-rsa384-public.pem")
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );

        $jti = Uuid::uuid4();

        $now   = new \DateTimeImmutable();
        $oauthTokenUrl = $opts['a'];
        $clientId = $opts['i'];
        $token = $configuration->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($clientId)
            // Configures the audience (aud claim)
            ->permittedFor($oauthTokenUrl)
            // Configures the id (jti claim)
            ->identifiedBy($jti)
            // Configures the time that the token was issue (iat claim)
            ->issuedAt($now)
            // Configures the time that the token can be used (nbf claim)
            ->canOnlyBeUsedAfter($now)
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($now->modify('+60 seconds'))
            ->relatedTo($clientId)
            ->getToken($configuration->signer(), $configuration->signingKey());
        $assertion = $token->toString(); // The string representation of the object is a JWT string
        echo "Generated Client Credentials Assertion\n";
        echo $assertion . "\n";

        echo "\n\nSample CURL request using assertion: \n";
        $assertionType = CustomClientCredentialsGrant::OAUTH_JWT_CLIENT_ASSERTION_TYPE;
        $scope = 'system/*.\$export system/*.\$bulkdata-status system/Group.\$export system/Patient.\$export '
        . 'system/Encounter.read system/Document.read';
        echo "--> curl -k -X POST --data-urlencode \"client_assertion_type=$assertionType\" \\\n"
        . "  --data-urlencode \"client_assertion=$assertion\" \\\n"
        . "  --data-urlencode \"grant_type=client_credentials\" \\\n"
        . "  --data-urlencode \"scope=$scope\" $oauthTokenUrl\n";
    }
}
