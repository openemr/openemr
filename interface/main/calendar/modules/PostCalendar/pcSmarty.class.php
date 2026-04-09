<?php

/**
 *  PostCalendar::PostNuke Events Calendar Module
 *  Copyright (C) 2002  The PostCalendar Team
 *  http://postcalendar.tv
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *  To read the license please read the docs/license.txt or visit
 *  http://www.gnu.org/copyleft/gpl.html
 *
 */

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PostCalendar\PostCalendarTwigExtensions;

/**
 * Twig-only template wrapper for PostCalendar.
 *
 * Provides the same public interface as the former Smarty-based class
 * (assign, assign_by_ref, getVar, fetch, display) so that callers in
 * pnuserapi.php, pnuser.php and pnadmin.php continue to work unchanged.
 */
class pcSmarty
{
    /** @var \Twig\Environment */
    public $twig;

    /** @var TwigContainer */
    private $twigContainer;

    /** @var array<string, mixed> Template variables passed to Twig on render */
    public array $_tpl_vars = [];

    // ---- Legacy properties kept for pnadmin.php compatibility ----
    public string $compile_dir = '';
    public string $template_dir = 'templates';
    public string $config_dir = '';
    /** @var int|false */
    public int|false $caching = 0;
    public string $_version = 'twig';

    public function getVar(string $varName): mixed
    {
        return $this->_tpl_vars[$varName] ?? null;
    }

    public function assign(string|array $tpl_var, mixed $value = null): void
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $key => $val) {
                if ($key !== '') {
                    $this->_tpl_vars[$key] = $val;
                }
            }
        } elseif ($tpl_var !== '') {
            $this->_tpl_vars[$tpl_var] = $value;
        }
    }

    public function assign_by_ref(string $tpl_var, mixed &$value): void
    {
        if ($tpl_var !== '') {
            $this->_tpl_vars[$tpl_var] = &$value;
        }
    }

    /**
     * Resolve a template path to a Twig template and render it.
     *
     * Accepts legacy Smarty-style paths (e.g. "default/views/month/default.html")
     * and maps them to the corresponding Twig template under templates/calendar/.
     */
    public function fetch(string $resource_name, mixed $cache_id = null, mixed $compile_id = null, bool $display = false): string
    {
        $twig_template = $this->resolveTemplatePath($resource_name);

        if ($twig_template !== null && $this->twig->getLoader()->exists($twig_template)) {
            return $this->twig->render($twig_template, $this->_tpl_vars);
        }

        throw new \RuntimeException(
            'PostCalendar template not found: ' . $resource_name
            . ($twig_template ? " (resolved to $twig_template)" : '')
        );
    }

    public function display(string $resource_name, mixed $cache_id = null, mixed $compile_id = null): void
    {
        echo $this->fetch($resource_name, $cache_id, $compile_id, true);
    }

    // ---- Admin cache stubs (Twig manages its own cache) ----

    public function clear_all_cache(): void
    {
        // Twig auto-manages its cache; nothing to do here
    }

    public function clear_compiled_tpl(): void
    {
        // No-op: Twig manages compiled templates automatically
    }

    // ---- Constructor ----

    public function __construct()
    {
        // Initialise Twig
        $this->twigContainer = new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel());
        $this->twig = $this->twigContainer->getTwig();
        $this->twig->addExtension(new PostCalendarTwigExtensions());

        global $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5, $bgcolor6, $textcolor1, $textcolor2;

        // Session
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $authUserID = $session->get('authUserID');
        if ($authUserID !== null) {
            $this->assign('authUserID', $authUserID);
        }

        // Module information
        $pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
        $pcDir = pnVarPrepForOS($pcModInfo['directory']);
        $pcDisplayName = $pcModInfo['displayname'];
        unset($pcModInfo);

        // Legacy directory properties (used by pnadmin.php diagnostics)
        $this->template_dir = "modules/$pcDir/pntemplates";
        $this->compile_dir  = OEGlobalsBag::getInstance()->getString('OE_SITE_DIR') . '/documents/smarty/main';

        $lang = 'eng';
        $func = pnVarCleanFromInput('func');
        $print = pnVarCleanFromInput('print');

        // Theme globals
        $this->assign_by_ref('BGCOLOR1', $bgcolor1);
        $this->assign_by_ref('BGCOLOR2', $bgcolor2);
        $this->assign_by_ref('BGCOLOR3', $bgcolor3);
        $this->assign_by_ref('BGCOLOR4', $bgcolor4);
        $this->assign_by_ref('BGCOLOR5', $bgcolor5);
        $this->assign_by_ref('BGCOLOR6', $bgcolor6);
        $this->assign_by_ref('TEXTCOLOR1', $textcolor1);
        $this->assign_by_ref('TEXTCOLOR2', $textcolor2);
        $this->assign_by_ref('USER_LANG', $lang);
        $this->assign_by_ref('FUNCTION', $func);
        $this->assign('PRINT_VIEW', $print);
        $this->assign('SCRIPT_NAME', filter_input(INPUT_SERVER, 'SCRIPT_NAME') ?? '');
        $this->assign('USE_POPUPS', _SETTING_USE_POPUPS);
        $this->assign('USE_TOPICS', _SETTING_DISPLAY_TOPICS);
        $this->assign('USE_INT_DATES', _SETTING_USE_INT_DATES);
        $this->assign('OPEN_NEW_WINDOW', _SETTING_OPEN_NEW_WINDOW);
        $this->assign('EVENT_DATE_FORMAT', _SETTING_DATE_FORMAT);
        $this->assign('HIGHLIGHT_COLOR', _SETTING_DAY_HICOLOR);
        $this->assign('24HOUR_TIME', _SETTING_TIME_24HOUR);
        $this->assign_by_ref('MODULE_NAME', $pcDisplayName);
        $this->assign_by_ref('MODULE_DIR', $pcDir);

        // Globals
        $this->assign('translate_appt_categories', OEGlobalsBag::getInstance()->getBoolean('translate_appt_categories'));
        $this->assign('session_language_choice', $session->get('language_choice'));

        // Template name / view
        $template_name = _SETTING_TEMPLATE;
        if (!isset($template_name)) {
            $template_name = 'default';
        }

        $template_view = pnVarCleanFromInput('tplview');
        if (!isset($template_view)) {
            $template_view = 'default';
        }

        $this->config_dir = "modules/$pcDir/pntemplates/$template_name/config/";
        $this->assign_by_ref('TPL_NAME', $template_name);
        $this->assign_by_ref('TPL_VIEW', $template_view);
        $rootdir = OEGlobalsBag::getInstance()->getString('rootdir');
        $this->assign('TPL_IMAGE_PATH', $rootdir . "/main/calendar/modules/$pcDir/pntemplates/$template_name/images");
        $this->assign('TPL_ROOTDIR', $rootdir);
        $this->assign('TPL_STYLE_PATH', "modules/$pcDir/pntemplates/$template_name/style");

        // Language direction & navigation chevrons
        $langDir = $session->get('language_direction') ?? 'ltr';
        $this->assign('language_direction', $langDir);
        $this->assign('chevron_icon_left', $langDir === 'ltr' ? 'fa-chevron-circle-left' : 'fa-chevron-circle-right');
        $this->assign('chevron_icon_right', $langDir === 'ltr' ? 'fa-chevron-circle-right' : 'fa-chevron-circle-left');
        $this->assign('Date', postcalendar_getDate());
        $this->assign('DATE_STR_CURRENT', date('Ymd'));
    }

    // ---- Private helpers ----

    /**
     * Map a legacy Smarty template path to its Twig equivalent.
     *
     * Supported patterns:
     *  - "default/views/month/default.html"              → calendar/default/views/month/default.html.twig
     *  - "default/admin/submit_category.html"            → calendar/default/admin/submit_category.html.twig
     *  - "month/default.html"                            → calendar/default/views/month/default.html.twig
     *  - "user/ajax_search.html"                         → calendar/default/views/user/ajax_search.html.twig
     *  - "calendar/default/views/month/default.html.twig" → passed through
     */
    private function resolveTemplatePath(string $resourceName): ?string
    {
        // Already a Twig path
        if (str_contains($resourceName, '.html.twig')) {
            return $resourceName;
        }

        // Pattern: "default/views/month/default.html" or "default/admin/submit_category.html"
        if (preg_match('|([^/]+)/(?:views/)?([^/]+)/([^\.]+)\.html|', $resourceName, $m)) {
            $section = $m[2];
            $template = $m[3];

            // Decide if it is an admin template or a views template
            if ($m[2] === 'admin' || str_contains($resourceName, '/admin/')) {
                return "calendar/default/admin/$template.html.twig";
            }

            return "calendar/default/views/$section/$template.html.twig";
        }

        // Pattern: "month/default.html", "day/ajax_template.html"
        if (preg_match('|^([^/]+)/([^\.]+)\.html$|', $resourceName, $m)) {
            return "calendar/default/views/{$m[1]}/{$m[2]}.html.twig";
        }

        // Pattern: "user/ajax_search.html"
        if (preg_match('|^user/([^\.]+)\.html$|', $resourceName, $m)) {
            return "calendar/default/views/user/{$m[1]}.html.twig";
        }

        return null;
    }
}
