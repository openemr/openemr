<?php

/**
 * Orchestrates the OpenID Connect authorization-code login for staff.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman
 * @copyright Copyright (c) 2026 Tamir Suliman
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class OidcLoginService
{
    public function __construct(
        private readonly OidcRpSettings $settings,
        private readonly OidcRpClient $client,
        private readonly OidcIdTokenValidator $validator,
        private readonly OidcStaffAuthenticator $authenticator,
        private readonly SessionInterface $session,
    ) {
    }

    public static function fromContainer(OEGlobalsBag $globals, SessionInterface $session): self
    {
        $settings = OidcRpSettings::fromGlobals($globals, ServiceContainer::getCrypto());
        $client = new OidcRpClient(
            ServiceContainer::getHttpClient(),
            ServiceContainer::getRequestFactory(),
            ServiceContainer::getStreamFactory(),
            ServiceContainer::getLogger(),
        );
        $validator = new OidcIdTokenValidator(ServiceContainer::getHttpClient(), ServiceContainer::getClock());
        $authenticator = new OidcStaffAuthenticator(
            $settings,
            new OidcIdentityRepository(ServiceContainer::getClock()),
        );
        return new self($settings, $client, $validator, $authenticator, $session);
    }

    public function settings(): OidcRpSettings
    {
        return $this->settings;
    }

    public function startUrl(string $siteAddress, string $webRoot): string
    {
        if (!$this->settings->isReady()) {
            throw new OidcRpException('OIDC staff login is not configured');
        }
        $metadata = $this->client->discover($this->settings);
        $redirectUri = $this->settings->resolveRedirectUri($this->siteAddress($siteAddress), $webRoot);
        $request = $this->client->buildAuthorizationRequest($this->settings, $metadata, $redirectUri);
        $this->session->set(OidcRpSettings::SESSION_STATE, $request['state']);
        $this->session->set(OidcRpSettings::SESSION_NONCE, $request['nonce']);
        $this->session->set(OidcRpSettings::SESSION_VERIFIER, $request['code_verifier']);
        return $request['authorization_url'];
    }

    public function complete(string $code, string $state, string $siteAddress, string $webRoot): void
    {
        if (!$this->settings->isReady()) {
            throw new OidcRpException('OIDC staff login is not configured');
        }
        $expectedState = $this->session->get(OidcRpSettings::SESSION_STATE);
        $nonce = $this->session->get(OidcRpSettings::SESSION_NONCE);
        $verifier = $this->session->get(OidcRpSettings::SESSION_VERIFIER);
        $this->clearAuthorizationSession();
        if (!is_string($expectedState) || !is_string($nonce) || !is_string($verifier) || $expectedState === '' || $nonce === '' || $verifier === '') {
            throw new OidcRpException('OIDC login session is missing');
        }
        if ($state === '' || !hash_equals($expectedState, $state)) {
            throw new OidcRpException('OIDC login state is invalid');
        }
        if ($code === '') {
            throw new OidcRpException('OIDC authorization code is missing');
        }

        $metadata = $this->client->discover($this->settings);
        $redirectUri = $this->settings->resolveRedirectUri($this->siteAddress($siteAddress), $webRoot);
        $tokens = $this->client->exchangeAuthorizationCode($this->settings, $metadata, $code, $redirectUri, $verifier);
        $claims = $this->validator->validate($tokens['id_token'], $this->settings, $metadata, $nonce);
        $this->authenticator->login($claims);
        $this->session->set(OidcRpSettings::SESSION_LOGIN, true);
        $this->session->set(OidcRpSettings::SESSION_ISSUER, $this->settings->issuer);
    }

    public function logoutRedirect(string $postLogoutRedirectUri): ?string
    {
        if ($this->session->get(OidcRpSettings::SESSION_LOGIN) !== true || !$this->settings->isReady()) {
            return null;
        }
        try {
            $metadata = $this->client->discover($this->settings);
        } catch (OidcRpException) {
            return null;
        }
        if ($metadata->endSessionEndpoint === '') {
            return null;
        }
        return $metadata->endSessionEndpoint . '?' . http_build_query([
            'client_id' => $this->settings->clientId,
            'post_logout_redirect_uri' => $postLogoutRedirectUri,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    private function siteAddress(string $configured): string
    {
        if ($configured !== '') {
            return $configured;
        }

        return CurrentRequest::get()->getSchemeAndHttpHost();
    }

    private function clearAuthorizationSession(): void
    {
        $this->session->remove(OidcRpSettings::SESSION_STATE);
        $this->session->remove(OidcRpSettings::SESSION_NONCE);
        $this->session->remove(OidcRpSettings::SESSION_VERIFIER);
    }
}
