<?php

/**
 * CSRF helper that wires the active session into CsrfUtils.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class CsrfHelper
{
    public static function collectCsrfToken(string $subject = 'default'): string
    {
        return CsrfUtils::collectCsrfToken(self::getSession(), $subject);
    }

    public static function verifyCsrfToken(string $token, string $subject = 'default'): bool
    {
        return CsrfUtils::verifyCsrfToken($token, self::getSession(), $subject);
    }

    private static function getSession(): SessionInterface
    {
        return SessionWrapperFactory::getInstance()->getActiveSession();
    }
}
