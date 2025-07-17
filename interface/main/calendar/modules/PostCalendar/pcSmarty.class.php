<?php

use Twig\Environment;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\PostCalendar\PostCalendarTwigExtensions;

// The PostCalendarTwigExtensions class is now autoloaded via PSR-4
// require_once __DIR__ . "/pnincludes/PostCalendarTwigExtensions.php";

/**
 *  $Id$
 *
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

require_once(__DIR__ . '/../../../../../library/smarty_legacy/smarty/Smarty_Legacy.class.php');

class pcSmarty extends Smarty_Legacy
{
    private $twigVars = [];

    /**
     * @var \Twig\Environment
     */
    public $twig;

    /**
     * @var TwigContainer
     */
    private $twigContainer;

    public function getVar($varName)
    {
        return $this->_tpl_vars[$varName] ?? null;
    }
    public function assign($tpl_var, $value = null)
    {
        parent::assign($tpl_var, $value);
        $this->_tpl_vars[$tpl_var] = $value;
    }

    public function assign_by_ref($tpl_var, &$value)
    {
        parent::assign_by_ref($tpl_var, $value);
        $this->_tpl_vars[$tpl_var] = &$value;
    }

    public function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false)
    {
        // Convert legacy template path to Twig template path
        $viewtype = '';
        $template_view = '';

        // Extract viewtype and template_view from the resource_name
        // Original pattern that works for some cases
        if (preg_match('|([^/]+)/views/([^/]+)/([^\.]+)\.html|', $resource_name, $matches)) {
            $template_name = $matches[1];
            $viewtype = $matches[2];
            $template_view = $matches[3];

            // Map to new Twig template path
            $twig_template = "calendar/default/views/$viewtype/$template_view.html.twig";

            // Check if the template exists
            if ($this->twig->getLoader()->exists($twig_template)) {
                return $this->twig->render($twig_template, $this->_tpl_vars);
            }
        }
        // Handle the case where resource_name is just a viewtype and template
        // This pattern handles paths like "day/default.html" or "month/default.html"
        elseif (preg_match('|^([^/]+)/([^\.]+)\.html$|', $resource_name, $matches)) {
            $viewtype = $matches[1];
            $template_view = $matches[2];

            // Map to new Twig template path
            $twig_template = "calendar/default/views/$viewtype/$template_view.html.twig";

            // Check if the template exists
            if ($this->twig->getLoader()->exists($twig_template)) {
                return $this->twig->render($twig_template, $this->_tpl_vars);
            }
        }
        // Handle the case for user directory templates
        elseif (preg_match('|^user/([^\.]+)\.html$|', $resource_name, $matches)) {
            $template_view = $matches[1];

            // Map to new Twig template path for user directory
            $twig_template = "calendar/default/views/user/$template_view.html.twig";

            // Check if the template exists
            if ($this->twig->getLoader()->exists($twig_template)) {
                return $this->twig->render($twig_template, $this->_tpl_vars);
            }
        }
        // Handle the case for direct Twig template names
        // This is a fallback for any direct Twig template references
        elseif (strpos($resource_name, '.html.twig') !== false) {
            $twig_template = $resource_name;

            // Check if the template exists
            if ($this->twig->getLoader()->exists($twig_template)) {
                return $this->twig->render($twig_template, $this->_tpl_vars);
            }
        }

        // Fallback to legacy Smarty if Twig template not found
        return parent::fetch($resource_name, $cache_id, $compile_id, $display);
    }

    public function display($resource_name, $cache_id = null, $compile_id = null)
    {
        echo $this->fetch($resource_name, $cache_id, $compile_id, true);
    }

    public function __construct()
    {
        $this->_tpl_vars = [];

        $this->twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
        $this->twig = $this->twigContainer->getTwig();

        $this->twig->addExtension(new PostCalendarTwigExtensions());
        // probably need to add a twig extension to handle all of the original smarty functions

        global $bgcolor1,$bgcolor2,$bgcolor3,$bgcolor4,$bgcolor5,$bgcolor6,$textcolor1,$textcolor2;

        // call constructor
        parent::__construct();

        // Always assign authUserID from session to make it available in all templates
        if (isset($_SESSION['authUserID'])) {
            $this->assign('authUserID', $_SESSION['authUserID']);
        }

        // gather module information
        $pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
        $pcDir = pnVarPrepForOS($pcModInfo['directory']);
        $pcDisplayName = $pcModInfo['displayname'];
        unset($pcModInfo);

        // setup up pcSmarty configs
        $this->compile_check    =   true;
        $this->force_compile    =   false;
        $this->debugging        =   false;
        $this->template_dir     =   "modules/$pcDir/pntemplates";
        array_push($this->plugins_dir, "modules/$pcDir/pnincludes/Smarty/plugins");
        array_push($this->plugins_dir, "modules/$pcDir/plugins");
        array_push($this->plugins_dir, "../../../../library/smarty/plugins");
        $this->compile_dir      =   $GLOBALS['OE_SITE_DIR'] . '/documents/smarty/main';
        $this->caching      =   0;
        $this->left_delimiter   =   '[-';
        $this->right_delimiter  =   '-]';

        //============================================================
        //  checks for safe mode
        //  i think it's safe to say we can do this automagically now
        //============================================================
        $safe_mode      = ini_get('safe_mode');
        $safe_mode_gid  = ini_get('safe_mode_gid');
        $open_basedir   = ini_get('open_basedir');

        $use_safe_mode = ((bool)$safe_mode || (bool)$safe_mode_gid || !empty($open_basedir));
        $this->use_sub_dirs = $use_safe_mode ? false : true;

        unset($use_safe_mode, $safe_mode, $safe_mode_gid, $open_basedir);

        $this->autoload_filters = ['output' => ['trimwhitespace']];

        $lang = 'eng';
        $func = pnVarCleanFromInput('func');
        $print = pnVarCleanFromInput('print');
        // assign theme globals
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
        $this->assign('USE_POPUPS', _SETTING_USE_POPUPS);
        $this->assign('USE_TOPICS', _SETTING_DISPLAY_TOPICS);
        $this->assign('USE_INT_DATES', _SETTING_USE_INT_DATES);
        $this->assign('OPEN_NEW_WINDOW', _SETTING_OPEN_NEW_WINDOW);
        $this->assign('EVENT_DATE_FORMAT', _SETTING_DATE_FORMAT);
        $this->assign('HIGHLIGHT_COLOR', _SETTING_DAY_HICOLOR);
        $this->assign('24HOUR_TIME', _SETTING_TIME_24HOUR);
        $this->assign_by_ref('MODULE_NAME', $pcDisplayName);
        $this->assign_by_ref('MODULE_DIR', $pcDir);

        // get some of our globals out
        $this->assign('translate_appt_categories', $GLOBALS['translate_appt_categories']);
        $this->assign('session_language_choice', $_SESSION['language_choice']);

        //=================================================================
        //  Find out what Template we're using
        //=================================================================
        $template_name = _SETTING_TEMPLATE;
        if (!isset($template_name)) {
            $template_name = 'default';
        }

        //=================================================================
        //  Find out what Template View to use
        //=================================================================
        $template_view = pnVarCleanFromInput('tplview');
        if (!isset($template_view)) {
            $template_view = 'default';
        }

        $this->config_dir = "modules/$pcDir/pntemplates/$template_name/config/";
        $this->assign_by_ref('TPL_NAME', $template_name);
        $this->assign_by_ref('TPL_VIEW', $template_view);
        $this->assign('TPL_IMAGE_PATH', $GLOBALS['rootdir'] . "/main/calendar/modules/$pcDir/pntemplates/$template_name/images");
        $this->assign('TPL_ROOTDIR', $GLOBALS['rootdir']);
        $this->assign('TPL_STYLE_PATH', "modules/$pcDir/pntemplates/$template_name/style");

        // we are storing all common PostCalendar pieces here
        $this->assign_by_ref('language_direction', $_SESSION['language_direction']);


        $this->assign('chevron_icon_left', $_SESSION['language_direction'] == 'ltr' ? 'fa-chevron-circle-left' : 'fa-chevron-circle-right');
        $this->assign('chevron_icon_right', $_SESSION['language_direction'] == 'ltr' ? 'fa-chevron-circle-right' : 'fa-chevron-circle-left');
        $this->assign('Date', postcalendar_getDate());

        $this->assign('DATE_STR_CURRENT', date('Ymd'));
    }
}
