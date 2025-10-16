<?php

/**
 * GenerateAccessTokenTestCommand is a command that generates an access token for a test client
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Auth\OAuth2KeyConfig;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\AccessTokenEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\RefreshTokenEntity;
//use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\RefreshTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Command\Trait\GlobalInterfaceCommandTrait;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use Random\RandomException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Exception;
use DateTimeImmutable;
use DateInterval;
use RuntimeException;

class GenerateAccessTokenTestCommand extends Command implements IGlobalsAwareCommand
{
    use GlobalInterfaceCommandTrait;

    const MAX_GENERATION_ATTEMPTS = 5;

    const ACCESS_TOKEN_IDENTIFIER_RANDOM_BYTES_LENGTH = 40;

    protected function configure(): void
    {
        $this
            ->setName('openemr-dev:api-generate-access-token')
            ->setDescription("Utility class to help test api clients by generating an access token for a test client")
            ->addUsage('--site=default')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('site', 's', InputOption::VALUE_REQUIRED, 'Name of site', 'default'),
                    new InputOption('client-id', 'c', InputOption::VALUE_REQUIRED, 'The client identifier to generate an access token using the password grant'),
                    new InputOption('resources', 'r', InputOption::VALUE_REQUIRED, 'Fhir resources to allow access to (comma separated list)', ''),
                ])
            );
    }
    /**
     * Execute the command and spit any output to STDOUT and errors to STDERR
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // going to hit the github api endpoint for the milestone given in the api
        $clientId = $input->getOption('client-id');
        $symfonyStyler = new SymfonyStyle($input, $output);

        try {
            if (empty($clientId)) {
                $symfonyStyler->error("Client ID is required to generate an access token.");
                return Command::FAILURE;
            }

            $username = $symfonyStyler->ask("Enter username to use for access token generation (leave blank for default):", 'admin');
            $password = $symfonyStyler->askHidden("Enter password to use for access token generation:");
            if (empty($username) || empty($password)) {
                $symfonyStyler->error("Username and password are required to generate an access token.");
                return Command::FAILURE;
            }
            // REMOTE_ADDR is required for AuthUtils::confirmPassword
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1'; // Set a default IP address for the session
            $auth = new AuthUtils();
            if (!$auth->confirmPassword($username, $password)) {
                $symfonyStyler->error("Invalid username or password.");
                unset($password);
                unset($username);
                return Command::FAILURE;
            } else {
                unset($password);
            }
            $userService = new UserService();
            $user = $userService->getUserByUsername($username);

            $clientRepository = new ClientRepository();
            $client = $clientRepository->getClientEntity($clientId);
            if (empty($client)) {
                $symfonyStyler->error("Client with ID $clientId not found.");
                return Command::FAILURE;
            }
            if (!$client->isEnabled()) {
                $symfonyStyler->error("Client with ID $clientId is not enabled.");
                return Command::FAILURE;
            }

            $scopeIdentifiers = $client->getScopes();
            // if we have been given specific resources then we will limit the scopes to those resources
            if (!empty($input->getOption('resources'))) {
                $requestedResources = array_map('trim', explode(',', (string) $input->getOption('resources')));
                $fhirScopes = array_map(fn($resource): string => "user/{$resource}.rs", $requestedResources);
                $scopeList = new ServerScopeListEntity();

                $scopeIdentifiers = array_unique(array_merge($fhirScopes, $scopeList->requiredSmartOnFhirScopes()));
            }

            $hasOfflineScope = in_array('offline_access', $scopeIdentifiers, true);
            $scopes = array_map(fn($scope): ScopeEntity => ScopeEntity::createFromString($scope), $scopeIdentifiers);
//            $scopes = array_map(function($scope): ScopeEntity { $entity = new ScopeEntity(); $entity->setIdentifier($scope); return $entity; }
//            , $scopeIdentifiers);
            $session = new Session(new MockFileSessionStorage());
            $session->set('trusted', 1); // just to have something in the cache
            $accessTokenRepository = new AccessTokenRepository($this->getGlobalsBag(), $session);
//            $accessTokenRepository = new AccessTokenRepository();
            $token = $accessTokenRepository->getNewToken($client, $scopes);
            // we could allow the cli to determine the access token expiration time, but for now we will set it to 1 hour
            $token->setExpiryDateTime((new DateTimeImmutable())->add(new DateInterval("PT1H"))); // 1 hour expiration
            $oauth2KeyConfig = new OAuth2KeyConfig($this->globalsBag->get('OE_SITE_DIR'));
//            $oauth2KeyConfig = new OAuth2KeyConfig();
            $oauth2KeyConfig->configKeyPairs();
            $keyLocation = $oauth2KeyConfig->getPrivateKeyLocation();
            $privateKey = new CryptKey($keyLocation, $oauth2KeyConfig->getPassPhrase());
            $token->setPrivateKey($privateKey);
            $token->setUserIdentifier($user['uuid']);
            $psrResponse = (new Psr17Factory())->createResponse();
            $bearerTokenResponse = new  BearerTokenResponse();
            $bearerTokenResponse->setEncryptionKey($oauth2KeyConfig->getEncryptionKey());
            $maxGenerationAttempts = self::MAX_GENERATION_ATTEMPTS;

            while ($maxGenerationAttempts-- > 0) {
                $token->setIdentifier($this->generateUniqueIdentifier());
                try {
                    $accessTokenRepository->persistNewAccessToken($token);
                } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                    if ($maxGenerationAttempts === 0) {
                        throw $e;
                    }
                }
            }
            $symfonyStyler->success("Access token successfully generated.");
            $symfonyStyler->table([
                'Access Token ID',
                'Expires At',
                'Client ID'
            ], [
                [
                    $token->getIdentifier(),
                    $token->getExpiryDateTime()->format('Y-m-d H:i:s'),
                    $clientId,
                ]
            ]);
            $bearerTokenResponse->setAccessToken($token);
            if ($hasOfflineScope) {
                $refreshToken = $this->generateRefreshToken($token, $client, $scopeIdentifiers, $session);
                $symfonyStyler->success("Refresh token successfully generated.");
                $bearerTokenResponse->setRefreshToken($refreshToken);
            }
            $bearerTokenResponse->setPrivateKey($privateKey);
            $response = $bearerTokenResponse->generateHttpResponse($psrResponse);
            $response->getBody()->rewind();
            $jsonResponse = $response->getBody()->getContents();
            $symfonyStyler->info("scopes");
            $symfonyStyler->info($scopeIdentifiers);
            $symfonyStyler->info("Bearer Token Response");
            $symfonyStyler->text($jsonResponse);
            return Command::SUCCESS;
        } catch (Exception $e) {
            $symfonyStyler->error("Error creating access token : " . $e->getMessage());
            $symfonyStyler->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * @param AccessTokenEntity $token
     * @param ClientEntity $client
     * @param array $scopeIdentifiers
     * @param SessionInterface $session
     * @return RefreshTokenEntity|RefreshTokenEntityInterface
     * @throws UniqueTokenIdentifierConstraintViolationException
     * @throws RandomException
     */
    private function generateRefreshToken(AccessTokenEntity $token, ClientEntity $client, array $scopeIdentifiers, SessionInterface $session): RefreshTokenEntity|RefreshTokenEntityInterface
    {
        $refreshRepository = new RefreshTokenRepository(true);

        $refreshToken = $refreshRepository->getNewRefreshToken();
        $refreshToken->setAccessToken($token);
        $refreshToken->setExpiryDateTime((new DateTimeImmutable())->add(new DateInterval("P3M"))); // 3 months expiration for refresh token
        $maxGenerationAttempts = self::MAX_GENERATION_ATTEMPTS;
        while ($maxGenerationAttempts-- > 0) {
            $refreshToken->setIdentifier($this->generateUniqueIdentifier());
            try {
                $refreshRepository->persistNewRefreshToken($refreshToken);

                // we will save our trusted user
                $trustedUserService = new TrustedUserService();
                $sessionCache = json_encode($session->all());
                $trustedUserService->saveTrustedUser($client->getIdentifier(), $token->getUserIdentifier(), $scopeIdentifiers, 1, '', $sessionCache, 'password_grant');
                return $refreshToken;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if ($maxGenerationAttempts === 0) {
                    throw $e;
                }
            }
        }
        throw new RuntimeException("Failed to generate unique refresh token after " . self::MAX_GENERATION_ATTEMPTS . " attempts");
    }

    /**
     * @return string
     * @throws RandomException
     */
    private function generateUniqueIdentifier(): string
    {
        return bin2hex(random_bytes(self::ACCESS_TOKEN_IDENTIFIER_RANDOM_BYTES_LENGTH));
    }
}
