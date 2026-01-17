<?php

/**
 * Generic OIDC Provider - For any OIDC-compliant identity provider
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Providers;

class GenericOidcProvider extends AbstractOidcProvider
{
    public function getId(): string
    {
        return 'generic_oidc';
    }

    public function getName(): string
    {
        return $this->config['display_name'] ?? 'SSO';
    }

    public function getIcon(): string
    {
        // Use custom icon URL if configured
        if (!empty($this->config['icon_url'])) {
            return '<img src="' . attr($this->config['icon_url']) . '" alt="" width="18" height="18" style="vertical-align: middle;">';
        }

        // Default: generic lock icon
        return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
            . '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>'
            . '<path d="M7 11V7a5 5 0 0 1 10 0v4"/>'
            . '</svg>';
    }

    public function isConfigured(): bool
    {
        return parent::isConfigured() && !empty($this->config['discovery_url']);
    }

    protected function getDiscoveryUrl(): string
    {
        return $this->config['discovery_url'] ?? '';
    }

    public function getDefaultScopes(): array
    {
        // Allow custom scopes if configured
        if (!empty($this->config['scopes'])) {
            $scopes = array_map('trim', explode(' ', $this->config['scopes']));
            // Ensure openid is always included
            if (!in_array('openid', $scopes)) {
                array_unshift($scopes, 'openid');
            }
            return $scopes;
        }

        return parent::getDefaultScopes();
    }

    public function supportsGroupClaims(): bool
    {
        // Allow configuration to specify if groups are supported
        return !empty($this->config['supports_groups']);
    }

    public function extractUserInfo(array $claims): array
    {
        $userInfo = parent::extractUserInfo($claims);

        // Allow custom claim mappings
        if (!empty($this->config['email_claim'])) {
            $userInfo['email'] = $claims[$this->config['email_claim']] ?? $userInfo['email'];
        }

        if (!empty($this->config['name_claim'])) {
            $userInfo['name'] = $claims[$this->config['name_claim']] ?? $userInfo['name'];
        }

        if (!empty($this->config['groups_claim'])) {
            $groups = $claims[$this->config['groups_claim']] ?? [];
            $userInfo['groups'] = is_array($groups) ? $groups : [];
        }

        return $userInfo;
    }

    public function getConfigFields(): array
    {
        return array_merge(parent::getConfigFields(), [
            'discovery_url' => [
                'type' => 'url',
                'label' => 'Discovery URL',
                'required' => true,
                'description' => 'OIDC Discovery URL (e.g., https://idp.example.com/.well-known/openid-configuration)',
            ],
            'display_name' => [
                'type' => 'text',
                'label' => 'Display Name',
                'required' => false,
                'description' => 'Name shown on the login button (defaults to "SSO")',
            ],
            'scopes' => [
                'type' => 'text',
                'label' => 'Scopes',
                'required' => false,
                'description' => 'Space-separated list of OAuth scopes (defaults to "openid email profile")',
            ],
            'email_claim' => [
                'type' => 'text',
                'label' => 'Email Claim',
                'required' => false,
                'description' => 'Name of the claim containing the user email (defaults to "email")',
            ],
            'name_claim' => [
                'type' => 'text',
                'label' => 'Name Claim',
                'required' => false,
                'description' => 'Name of the claim containing the user name (defaults to "name")',
            ],
            'groups_claim' => [
                'type' => 'text',
                'label' => 'Groups Claim',
                'required' => false,
                'description' => 'Name of the claim containing user groups (leave empty if not supported)',
            ],
            'supports_groups' => [
                'type' => 'checkbox',
                'label' => 'Supports Group Claims',
                'required' => false,
                'description' => 'Check if this IdP includes group membership in tokens',
            ],
        ]);
    }
}
