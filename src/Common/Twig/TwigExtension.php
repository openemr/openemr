<?php

/**
 * TwigExtension class.
 *
 * OpenEMR central extension interface for twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Twig;

use OpenEMR\Core\Header;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        return [
            'assets_dir' => $GLOBALS['assets_static_relative'],
            'srcdir' => $GLOBALS['srcdir'],
            'rootdir' => $GLOBALS['rootdir']
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'setupHeader',
                function ($assets = array()) {
                    return Header::setupHeader($assets);
                }
            ),

            new TwigFunction(
                'generateFormField',
                function ($frow, $currentValue) {
                    // generate_form_field() echo's the form, here we capture the echo into
                    // the output buffer, assign it to a variable and return the variable
                    // this allows twig templates to call generate_form_field().
                    ob_start();
                    generate_form_field($frow, $currentValue);
                    return ob_get_clean();
                }
            ),

            new TwigFunction(
                'tabRow',
                function ($formType, $result1, $result2) {
                    ob_start();
                    display_layout_tabs($formType, $result1, $result2);
                    return ob_get_clean();
                }
            ),

            new TwigFunction(
                'tabData',
                function ($formType, $result1, $result2) {
                    ob_start();
                    display_layout_tabs_data($formType, $result1, $result2);
                    return ob_get_clean();
                }
            ),

            new TwigFunction(
                'imageWidget',
                function ($id, $category) {
                    ob_start();
                    image_widget($id, $category);
                    return ob_get_clean();
                }
            ),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'text',
                function ($string) {
                    return text($string);
                }
            ),
            new TwigFilter(
                'attr',
                function ($string) {
                    return attr($string);
                }
            ),
            new TwigFilter(
                'js_escape',
                function ($string) {
                    return js_escape($string);
                }
            ),
            new TwigFilter(
                'attr_js',
                function ($string) {
                    return attr_js($string);
                }
            ),
            new TwigFilter(
                'attr_url',
                function ($string) {
                    return attr_url($string);
                }
            ),
            new TwigFilter(
                'js_url',
                function ($string) {
                    return js_url($string);
                }
            ),
            new TwigFilter(
                'xl',
                function ($string) {
                    return xl($string);
                }
            ),
            new TwigFilter(
                'xlt',
                function ($string) {
                    return xlt($string);
                }
            ),
            new TwigFilter(
                'xla',
                function ($string) {
                    return xla($string);
                }
            ),
            new TwigFilter(
                'xlj',
                function ($string) {
                    return xlj($string);
                }
            ),
            new TwigFilter(
                'xls',
                function ($string) {
                    return xls($string);
                }
            ),
            new TwigFilter(
                'money',
                function ($amount) {
                    return oeFormatMoney($amount);
                }
            ),
            new TwigFilter(
                'shortDate',
                function ($string) {
                    return oeFormatShortDate($string);
                }
            ),
            new TwigFilter(
                'xlDocCategory',
                function ($string) {
                    return xl_document_category($string);
                }
            )
        ];
    }
}
