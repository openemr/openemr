<?php

/**
 * Google Workspace Provider
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Providers;

class GoogleProvider extends AbstractOidcProvider
{
    public function getId(): string
    {
        return 'google';
    }

    public function getName(): string
    {
        return 'Google';
    }

    public function getIcon(): string
    {
        // Google "G" logo SVG
        return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48">'
            . '<path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>'
            . '<path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>'
            . '<path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>'
            . '<path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>'
            . '</svg>';
    }

    protected function getDiscoveryUrl(): string
    {
        return 'https://accounts.google.com/.well-known/openid-configuration';
    }

    protected function getAdditionalAuthParams(): array
    {
        $params = [
            'access_type' => 'offline', // Get refresh token
            'prompt' => 'select_account',
        ];

        // Restrict to specific Google Workspace domain if configured
        if (!empty($this->config['hosted_domain'])) {
            $params['hd'] = $this->config['hosted_domain'];
        }

        return $params;
    }

    public function getDefaultScopes(): array
    {
        return ['openid', 'email', 'profile'];
    }

    public function supportsGroupClaims(): bool
    {
        // Google Workspace groups require Admin SDK API calls
        // Not available directly in ID token
        return false;
    }

    public function extractUserInfo(array $claims): array
    {
        $userInfo = parent::extractUserInfo($claims);

        // Google uses 'hd' (hosted domain) for Workspace accounts
        if (isset($claims['hd'])) {
            $userInfo['hosted_domain'] = $claims['hd'];
        }

        // Google uses 'picture' for profile photo
        if (isset($claims['picture'])) {
            $userInfo['picture'] = $claims['picture'];
        }

        return $userInfo;
    }

    public function validateIdToken(string $idToken, string $nonce): array
    {
        $claims = parent::validateIdToken($idToken, $nonce);

        // Validate hosted domain if configured
        if (!empty($this->config['hosted_domain'])) {
            $tokenDomain = $claims['hd'] ?? null;
            if ($tokenDomain !== $this->config['hosted_domain']) {
                throw new \RuntimeException(
                    "User is not from the configured Google Workspace domain"
                );
            }
        }

        return $claims;
    }

    public function getConfigFields(): array
    {
        return array_merge(parent::getConfigFields(), [
            'hosted_domain' => [
                'type' => 'text',
                'label' => 'Hosted Domain',
                'required' => false,
                'description' => 'Restrict to a specific Google Workspace domain (e.g., example.com)',
            ],
        ]);
    }
}
