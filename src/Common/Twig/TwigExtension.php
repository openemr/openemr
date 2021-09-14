<?php

/**
 * TwigExtension class.
 *
 * OpenEMR central extension interface for twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Twig;

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
            ),

            new \Twig\TwigFunction(
                'generateFormField',
                function ($frow, $currentValue) {
                    // generate_form_field() echo's the form, here we capture the echo into
                    // the output buffer, assign it to a variable and return the variable
                    // this allows twig templates to call generate_form_field().
                    ob_start();
                    generate_form_field($frow, $currentValue);
                    $the_form = ob_get_contents();
                    ob_end_clean();
                    return $the_form;
                }
            ),

            new \Twig\TwigFunction(
                'tabRow',
                function ($formType, $result1, $result2) {
                    ob_start();
                    display_layout_tabs($formType, $result1, $result2);
                    $output = ob_get_contents();
                    ob_end_clean();
                    return $output;
                }
            ),

            new \Twig\TwigFunction(
                'tabData',
                function ($formType, $result1, $result2) {
                    ob_start();
                    display_layout_tabs_data($formType, $result1, $result2);
                    $output = ob_get_contents();
                    ob_end_clean();
                    return $output;
                }
            ),

            new \Twig\TwigFunction(
                'imageWidget',
                function ($id, $category) {
                    ob_start();
                    image_widget($id, $category);
                    $output = ob_get_contents();
                    ob_end_clean();
                    return $output;
                }
            ),
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
            ),
            new \Twig\TwigFilter(
                'money',
                function ($amount) {
                    return oeFormatMoney($amount);
                }
            ),
            new \Twig\TwigFilter(
                'shortDate',
                function ($string) {
                    return oeFormatShortDate($string);
                }
            ),
            new \Twig\TwigFilter(
                'xlDocCategory',
                function ($string) {
                    return xl_document_category($string);
                }
            )
        ];
    }
}
