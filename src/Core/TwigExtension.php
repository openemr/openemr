<?php

/**
 * TwigExtension class.
 *
 * OpenEMR central interface for twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use OpenEMR\Core\Header;

class TwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    public function getGlobals(): array
    {
        return [
            'assets_dir' => $GLOBALS['assets_static_relative'],
            'srcdir' => $GLOBALS['srcdir'],
            'rootdir' => $GLOBALS['rootdir']
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction(
                'setupHeader',
                function ($assets = array()) {
                    return Header::setupHeader($assets);
                }
            )
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig\TwigFilter(
                'text',
                function ($string) {
                    return text($string);
                }
            ),
            new \Twig\TwigFilter(
                'attr',
                function ($string) {
                    return attr($string);
                }
            ),
            new \Twig\TwigFilter(
                'js_escape',
                function ($string) {
                    return js_escape($string);
                }
            ),
            new \Twig\TwigFilter(
                'attr_js',
                function ($string) {
                    return attr_js($string);
                }
            ),
            new \Twig\TwigFilter(
                'attr_url',
                function ($string) {
                    return attr_url($string);
                }
            ),
            new \Twig\TwigFilter(
                'js_url',
                function ($string) {
                    return js_url($string);
                }
            ),
            new \Twig\TwigFilter(
                'xl',
                function ($string) {
                    return xl($string);
                }
            ),
            new \Twig\TwigFilter(
                'xlt',
                function ($string) {
                    return xlt($string);
                }
            ),
            new \Twig\TwigFilter(
                'xla',
                function ($string) {
                    return xla($string);
                }
            ),
            new \Twig\TwigFilter(
                'xlj',
                function ($string) {
                    return xlj($string);
                }
            ),
            new \Twig\TwigFilter(
                'xls',
                function ($string) {
                    return xls($string);
                }
            )
        ];
    }
}
