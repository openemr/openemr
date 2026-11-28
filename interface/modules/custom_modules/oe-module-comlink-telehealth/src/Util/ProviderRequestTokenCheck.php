<?php

/**
 * Decides whether a provider-side telehealth request carries the request token it needs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Util;

use OpenEMR\Common\Csrf\CsrfUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class ProviderRequestTokenCheck
{
    /**
     * The settings script is loaded by a script tag, which cannot send headers, and it is
     * what hands the token to the page, so it is the one action that runs without it.
     */
    private const ACTIONS_WITHOUT_TOKEN = ['get_telehealth_settings'];

    public function __construct(private SessionInterface $session)
    {
    }

    public function allows(mixed $action, mixed $token): bool
    {
        if (in_array($action, self::ACTIONS_WITHOUT_TOKEN, true)) {
            return true;
        }
        if (!is_string($token) || $token === '') {
            return false;
        }
        return CsrfUtils::verifyCsrfToken($token, $this->session, 'api');
    }
}
