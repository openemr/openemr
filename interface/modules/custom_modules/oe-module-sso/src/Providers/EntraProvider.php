<?php

/**
 * Microsoft Entra ID (Azure AD) Provider
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Providers;

class EntraProvider extends AbstractOidcProvider
{
    public function getId(): string
    {
        return 'entra';
    }

    public function getName(): string
    {
        return 'Microsoft Entra ID';
    }

    public function getIcon(): string
    {
        // Microsoft logo SVG
        return '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21">'
            . '<rect x="1" y="1" width="9" height="9" fill="#f25022"/>'
            . '<rect x="11" y="1" width="9" height="9" fill="#7fba00"/>'
            . '<rect x="1" y="11" width="9" height="9" fill="#00a4ef"/>'
            . '<rect x="11" y="11" width="9" height="9" fill="#ffb900"/>'
            . '</svg>';
    }

    public function isConfigured(): bool
    {
        return parent::isConfigured() && !empty($this->config['tenant_id']);
    }

    protected function getDiscoveryUrl(): string
    {
        $tenant = $this->config['tenant_id'] ?? 'common';
        return "https://login.microsoftonline.com/{$tenant}/v2.0/.well-known/openid-configuration";
    }

    protected function getAdditionalAuthParams(): array
    {
        $params = [
            'prompt' => 'select_account', // Allow account selection
        ];

        // Add domain hint if configured
        if (!empty($this->config['domain_hint'])) {
            $params['domain_hint'] = $this->config['domain_hint'];
        }

        return $params;
    }

    public function getDefaultScopes(): array
    {
        $scopes = ['openid', 'email', 'profile'];

        // Add offline_access for refresh tokens
        $scopes[] = 'offline_access';

        return $scopes;
    }

    public function supportsGroupClaims(): bool
    {
        // Entra ID supports group claims, but requires Azure AD Premium
        // or application role assignment in the Azure portal
        return true;
    }

    public function extractUserInfo(array $claims): array
    {
        $userInfo = parent::extractUserInfo($claims);

        // Entra ID may use 'preferred_username' or 'upn' for email
        if (empty($userInfo['email'])) {
            $userInfo['email'] = $claims['upn'] ?? $claims['preferred_username'] ?? '';
        }

        // Extract groups if present
        if (isset($claims['groups']) && is_array($claims['groups'])) {
            $userInfo['groups'] = $claims['groups'];
        }

        // Entra ID may include roles
        if (isset($claims['roles']) && is_array($claims['roles'])) {
            $userInfo['roles'] = $claims['roles'];
        }

        return $userInfo;
    }

    public function getConfigFields(): array
    {
        return array_merge(parent::getConfigFields(), [
            'tenant_id' => [
                'type' => 'text',
                'label' => 'Tenant ID',
                'required' => true,
                'description' => 'Azure AD Tenant ID (GUID) or domain name. Use "common" for multi-tenant.',
            ],
            'domain_hint' => [
                'type' => 'text',
                'label' => 'Domain Hint',
                'required' => false,
                'description' => 'Optional domain hint to skip account selection (e.g., contoso.com)',
            ],
        ]);
    }
}
