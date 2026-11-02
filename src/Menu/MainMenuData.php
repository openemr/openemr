<?php

/**
 * The logged-in user's main menu: what interface/main/tabs/main.php renders
 * when the page loads, and what interface/main/tabs/menu_json.php returns when
 * the page asks for it again (top.refreshMainMenu()).
 *
 * The menu's restrictions read a few globals that have to be set first. They
 * were set inline in main.php; both callers now set them here, so a refreshed
 * menu is built exactly like the first one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Menu;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class MainMenuData
{
    /**
     * @return array<mixed> the menu entries, as MainMenuRole::getMenu() returns them
     */
    public static function forUser(EventDispatcherInterface $dispatcher, SessionInterface $session): array
    {
        self::prepareGlobals($session);
        return (new MainMenuRole($dispatcher))->getMenu();
    }

    private static function prepareGlobals(SessionInterface $session): void
    {
        $globals = OEGlobalsBag::getInstance();

        // Ensora eRx role, for the eRx entries
        if ($globals->getBoolean('erx_enable')) {
            $globals->set('newcrop_user_role', QueryUtils::fetchSingleValue(
                "SELECT `newcrop_user_role` FROM `users` WHERE `username` = ?",
                'newcrop_user_role',
                [$session->get('authUser')]
            ));
            if ($globals->get('newcrop_user_role') === 'erxadmin') {
                $globals->set('newcrop_user_role_erxadmin', 1);
            }
        }

        // whether the Track Anything form is installed
        $globals->set('track_anything_state', QueryUtils::fetchSingleValue(
            "SELECT `state` FROM `registry` WHERE `directory` = 'track_anything'",
            'state'
        ) ?? 0);

        // whether the Issues popup link is shown
        $globals->set('allow_issue_menu_link', (AclMain::aclCheckCore('encounters', 'notes', '', 'write')
            || AclMain::aclCheckCore('encounters', 'notes_a', '', 'write'))
            && AclMain::aclCheckCore('patients', 'med', '', 'write'));
    }
}
