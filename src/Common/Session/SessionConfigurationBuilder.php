<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session;

class SessionConfigurationBuilder
{
    public static function forCore(string $webRoot = '', bool $readOnly = true): SessionConfiguration
    {
        return new SessionConfiguration(
            name: SessionUtil::CORE_SESSION_ID,
            cookiePath: $webRoot . '/',
            cookieHttpOnly: false,
            readAndClose: $readOnly,
        );
    }

    public static function forOAuth(string $webRoot = ''): SessionConfiguration
    {
        return new SessionConfiguration(
            name: SessionUtil::OAUTH_SESSION_ID,
            cookiePath: $webRoot . SessionUtil::OAUTH_WEBROOT,
            cookieSameSite: 'None',
            cookieSecure: true,
        );
    }

    public static function forApi(string $webRoot = ''): SessionConfiguration
    {
        return new SessionConfiguration(
            name: SessionUtil::API_SESSION_ID,
            cookiePath: $webRoot . SessionUtil::API_WEBROOT,
            cookieSecure: true,
        );
    }

    public static function forPortal(string $webRoot = '', bool $readOnly = true): SessionConfiguration
    {
        return new SessionConfiguration(
            name: SessionUtil::PORTAL_SESSION_ID,
            cookiePath: $webRoot . '/',
            readAndClose: $readOnly,
        );
    }

    public static function forSetup(): SessionConfiguration
    {
        return new SessionConfiguration(
            name: SessionUtil::SETUP_SESSION_ID,
            cookiePath: '/',
        );
    }
}
