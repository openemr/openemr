<?php

/**
 * CreateClientCredentialsAssertion Is a helper utility to create a Client Credentials Grant assertion statement as
 * well as print out the Public JSON Web Key Set that can be used for a test System App.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Command\Runner\CommandContext;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateClientCredentialsAssertionSymfonyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('openemr:create-client-credentials-assertion')
            ->setDescription("Utility class to help test and use the client credentials grant assertion")
            ->addUsage('--site=default')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('issuer', 'i', InputOption::VALUE_REQUIRED, 'JSON Web Token (JWT) Issuer.  This should be the The Client ID received from the OpenEMR registration. Used as the issuer and subject for the JWT'),
                    new InputOption('oauth-token-url', 'a', InputOption::VALUE_REQUIRED, 'OpenEMR OAuth2 Token URL is the audience of the JWT', 'https://localhost:9300/default/oauth2/token'),
                    new InputOption('print-jwks', 'k', InputOption::VALUE_NONE, 'Prints out the JSON Web Key Set public key that can be registered with OpenEMR for the client application'),
                ])
            );
    }

    /**
     * Runs the command
     * @param CommandContext $context
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rootPath = $GLOBALS['fileroot'];

        $keyLocation = $rootPath . DIRECTORY_SEPARATOR . "tests" . DIRECTORY_SEPARATOR . "Tests" . DIRECTORY_SEPARATOR
            . "data" . DIRECTORY_SEPARATOR . "Unit" . DIRECTORY_SEPARATOR . "Common" . DIRECTORY_SEPARATOR . "Auth"
            . DIRECTORY_SEPARATOR . "Grant" . DIRECTORY_SEPARATOR;

        if ($input->getOption('print-jwks')) {
            $jwks = file_get_contents($keyLocation . "jwk-public-valid.json");
            $output->writeln("JSON Web Key Set (Public Key)");
            $output->writeln("WARNING - THIS IS FOR TESTING PURPOSES ONLY!");
            $output->writeln("DO NOT USE THIS IN PRODUCTION AS THE PRIVATE KEYS FOR THIS JWKS IS COMMITED TO THE SOURCE CODE\n");
            $output->writeln($jwks . "\n");
            return Command::SUCCESS;
        }
        $clientId = $input->getOption('issuer');
        $oauthTokenUrl = $input->getOption('oauth-token-url');
        if (empty($clientId) || empty($oauthTokenUrl)) {
            $output->writeln("Missing required arguments.");
            $output->writeln($this->getSynopsis());
            return Command::FAILURE;
        }

        $configuration = Configuration::forAsymmetricSigner(
        // You may use RSA or ECDSA and all their variations (256, 384, and 512)
            new Sha384(),
            InMemory::file($keyLocation . "openemr-rsa384-private.key"),
            InMemory::file($keyLocation . "openemr-rsa384-public.pem")
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );

        $jti = Uuid::uuid4();

        $now   = new \DateTimeImmutable();
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
        $output->writeln("Generated Client Credentials Assertion");
        $output->writeln($assertion);

        $output->writeln("\n\nSample CURL request using assertion: ");
        $assertionType = CustomClientCredentialsGrant::OAUTH_JWT_CLIENT_ASSERTION_TYPE;
        $scope = 'system/*.\$export system/*.\$bulkdata-status system/Group.\$export system/Patient.\$export '
        . 'system/Encounter.read system/Binary.read';
        $output->writeln("--> curl -k -X POST --data-urlencode \"client_assertion_type=$assertionType\" \\\n"
        . "  --data-urlencode \"client_assertion=$assertion\" \\\n"
        . "  --data-urlencode \"grant_type=client_credentials\" \\\n"
        . "  --data-urlencode \"scope=$scope\" $oauthTokenUrl");
        return Command::SUCCESS;
    }
}
