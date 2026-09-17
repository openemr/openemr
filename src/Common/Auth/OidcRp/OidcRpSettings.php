<?php

/**
 * Staff SSO settings sourced from Administration → Globals → Security.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Core\OEGlobalsBag;

final readonly class OidcRpSettings
{
    public const SESSION_STATE = 'oidc_rp_state';
    public const SESSION_NONCE = 'oidc_rp_nonce';
    public const SESSION_VERIFIER = 'oidc_rp_code_verifier';
    public const SESSION_LOGIN = 'oidc_rp_login';
    public const SESSION_ISSUER = 'oidc_rp_issuer';
    public const SESSION_NEW_LOGIN = 'oidc_rp_new_login';

    /**
     * phpGACL group title used when creating a missing OpenEMR user.
     * Administrators and Emergency Login cannot be assigned through SSO.
     */
    public string $newUserAcl;

    /**
     * Name stored in the OpenEMR `groups` table (auth provider), often Default.
     */
    public string $newUserGroup;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public bool $enabled,
        public string $issuer,
        public string $clientId,
        public string $clientSecret,
        public string $buttonLabel,
        public string $usernameClaim,
        public bool $autoRedirect,
        public bool $hideLocalLogin,
        public bool $matchEmail,
        public bool $createUsers,
        string $newUserAcl,
        string $newUserGroup,
        public bool $allowHttp,
        public string $redirectUriOverride,
        public array $scopes,
    ) {
        $this->newUserAcl = self::normalizeAccessControlGroup($newUserAcl);
        $trimmedGroup = trim($newUserGroup);
        $this->newUserGroup = $trimmedGroup !== '' ? $trimmedGroup : 'Default';
    }

    public static function fromGlobals(OEGlobalsBag $globals, CryptoInterface $crypto): self
    {
        $secret = '';
        $storedSecret = $globals->getString('oidc_rp_client_secret');
        if ($storedSecret !== '') {
            try {
                $secret = $crypto->decryptFromDatabase($storedSecret);
            } catch (CryptoGenException $exception) {
                throw new OidcRpException('OIDC client secret could not be decrypted', 0, $exception);
            }
        }

        $scopes = self::normalizeScopes($globals->getString('oidc_rp_scopes'));

        $issuer = trim($globals->getString('oidc_rp_issuer'));
        $acl = trim($globals->getString('oidc_rp_new_user_acl'));
        $group = trim($globals->getString('oidc_rp_new_user_group'));

        $claim = trim($globals->getString('oidc_rp_username_claim'));
        if ($claim === '') {
            $claim = 'preferred_username';
        }

        $label = trim($globals->getString('oidc_rp_button_label'));
        if ($label === '') {
            $label = 'Sign in with SSO';
        }

        return new self(
            enabled: $globals->getBoolean('oidc_rp_enabled'),
            issuer: $issuer,
            clientId: trim($globals->getString('oidc_rp_client_id')),
            clientSecret: $secret,
            buttonLabel: $label,
            usernameClaim: $claim,
            autoRedirect: $globals->getBoolean('oidc_rp_auto_redirect'),
            hideLocalLogin: $globals->getBoolean('oidc_rp_hide_local_login'),
            matchEmail: $globals->getBoolean('oidc_rp_match_email'),
            createUsers: $globals->getBoolean('oidc_rp_create_users'),
            newUserAcl: $acl,
            newUserGroup: $group,
            allowHttp: $globals->getBoolean('oidc_rp_allow_http'),
            redirectUriOverride: trim($globals->getString('oidc_rp_redirect_uri')),
            scopes: $scopes,
        );
    }

    /**
     * Keep optional user creation inside existing OpenEMR ACL groups.
     * Administrators and Emergency Login must be granted in OpenEMR, not by SSO.
     */
    public static function normalizeAccessControlGroup(string $acl): string
    {
        $acl = trim($acl);
        if ($acl === '' || strcasecmp($acl, 'Administrators') === 0 || strcasecmp($acl, 'Emergency Login') === 0) {
            return 'Clinicians';
        }

        return $acl;
    }

    /**
     * @return list<string>
     */
    public static function normalizeScopes(string $raw): array
    {
        $scopes = preg_split('/\s+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY);
        if ($scopes === false || $scopes === []) {
            return ['openid', 'profile', 'email'];
        }

        $normalized = [];
        foreach ($scopes as $scope) {
            if (!in_array($scope, $normalized, true)) {
                $normalized[] = $scope;
            }
        }
        if (!in_array('openid', $normalized, true)) {
            array_unshift($normalized, 'openid');
        }

        return $normalized;
    }

    public function isReady(): bool
    {
        return $this->enabled && $this->issuer !== '' && $this->clientId !== '';
    }

    /**
     * @return non-empty-string
     */
    public function discoveryUrl(): string
    {
        return rtrim($this->issuer, '/') . '/.well-known/openid-configuration';
    }

    public function resolveRedirectUri(string $siteAddress, string $webRoot): string
    {
        if ($this->redirectUriOverride !== '') {
            return $this->redirectUriOverride;
        }

        return rtrim($siteAddress, '/') . rtrim($webRoot, '/') . '/interface/login/oidc_callback.php';
    }
}
