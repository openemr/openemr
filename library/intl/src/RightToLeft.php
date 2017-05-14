<?php

namespace OpenEMR\Intl;

/**
 * Class RightToLeft.
 *
 * @package OpenEMR
 * @subpackage Intl
 * @author Robert Down <robertdown@live.com
 * @copyright Copyright (c) 2017 Robert Down
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class RightToLeft
{

    /**
     * Handle languages that flow right to left.
     *
     * This is severely under-powered. Lots of logic exists in `interface/globals.php`
     * that does other RTL logic. For now this abstracts the logic away from view
     * pages and allows the call of `RightToLeft::handle()` to ensure we include
     * the stylesheet. However, we really need to move all the logic into this
     * class as well as add a twig template that does the display instead of
     * echo'ing the link from here.
     *
     * @TODO Migrate RTL to this class from globals.php RD 2017-05-14
     */
    static public function handle()
    {
        if (array_key_exists('language_direction', $GLOBALS) && $GLOBALS['language_direction'] == 'rtl') {
            echo "<link href=\"{$GLOBALS['assets_static_relative']}/bootstrap-rtl-3-3-4/dist/css/bootstrap-rtl.css\" type=\"text/css\" rel=\"stylesheet\">";
        }
    }
}