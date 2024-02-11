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
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Twig;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Layouts\LayoutsUtils;
use OpenEMR\Common\Utils\CacheUtils;
use OpenEMR\Core\Header;
use OpenEMR\Core\Kernel;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\LogoService;
use Symfony\Component\EventDispatcher\GenericEvent;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class TwigExtension extends AbstractExtension implements GlobalsInterface
{
    protected $globals;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * TwigExtension constructor.
     * @param GlobalsService $globals
     * @param Kernel|null $kernel
     */
    public function __construct(GlobalsService $globals, ?Kernel $kernel)
    {
        $this->globals = $globals->getGlobalsMetadata();
        $this->kernel = $kernel;
    }

    public function getGlobals(): array
    {
        return [
            'assets_dir' => $this->globals['assets_static_relative'],
            'srcdir' => $this->globals['srcdir'],
            'rootdir' => $this->globals['rootdir'],
            'webroot' => $this->globals['webroot'],
            'assetVersion' => $this->globals['v_js_includes'],
            'session' => $_SESSION,
        ];
    }

    public function getTests(): array
    {
        return [
            // can be used like {% if is numeric %}...{% endif %}
            new TwigTest('numeric', function ($value) {
                return is_numeric($value); })
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
                'generateDisplayField',
                function ($row, $currentValue) {
                    ob_start();
                    generate_display_field($row, $currentValue);
                    return ob_get_clean();
                }
            ),

            new TwigFunction(
                'selectList',
                function ($name, $list, $value, $title, $opts = []) {
                    $empty_name = array_key_exists('empty_name', $opts) ? $opts['empty_name'] : '';
                    $class = array_key_exists('class', $opts) ? $opts['class'] : '';
                    $onchange = array_key_exists('onchange', $opts) ? $opts['onchange'] : '';
                    $tag_id = array_key_exists('tag_id', $opts) ? $opts['tag_id'] : '';
                    $custom_attributes = array_key_exists('custom_attributes', $opts) ? $opts['custom_attributes'] : '';
                    $multiple = array_key_exists('multiple', $opts) ? $opts['multiple'] : '';
                    $backup_list = array_key_exists('backup_list', $opts) ? $opts['backup_list'] : '';
                    $ignore_default = array_key_exists('ignore_default', $opts) ? $opts['ignore_default'] : '';
                    $include_inactive = array_key_exists('include_inactive', $opts) ? $opts['include_inactive'] : '';
                    $tabIndex = array_key_exists('tabIndex', $opts) ? $opts['tabIndex'] : false;
                    return generate_select_list($name, $list, $value, $title, $empty_name, $class, $onchange, $tag_id, $custom_attributes, $multiple, $backup_list, $ignore_default, $include_inactive, $tabIndex);
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
            new TwigFunction(
                'fireEvent',
                function ($eventName, $eventData = array()) {
                    if (empty($this->kernel)) {
                        return '';
                    }
                    ob_start();
                    $this->kernel->getEventDispatcher()->dispatch(new GenericEvent($eventName, $eventData), $eventName);
                    return ob_get_clean();
                }
            ),
            new TwigFunction(
                'csrfToken',
                function ($subject = 'default', $fieldName = "_token") {
                    if (empty($subject)) {
                        $subject = 'default';
                    }
                    return sprintf('<input type="hidden" name="%s" value="%s">', $fieldName, attr(CsrfUtils::collectCsrfToken($subject)));
                }
            ),
            new TwigFunction(
                'csrfTokenRaw',
                function ($subject = 'default') {
                    return CsrfUtils::collectCsrfToken($subject);
                }
            ),
            new TwigFunction(
                'jqueryDateTimePicker',
                function ($domSelector, $datetimepicker_timepicker = true, $datetimepicker_showseconds = true, $datetimepicker_formatInput = true) {
                    ob_start();
                    // In the event we need to pass the this objecto to the datetimepicker, we cannot use quotations because `this` would not be a string
                    $selector = ($domSelector == "this") ? $domSelector : "\"$domSelector\"";
                    echo "$($selector).datetimepicker({";
                    require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php');
                    echo "})";
                    return ob_get_clean();
                }
            ),
            new TwigFunction(
                'DateToYYYYMMDD_js',
                function () {
                    ob_start();
                    require $GLOBALS['srcdir'] . "/formatting_DateToYYYYMMDD_js.js.php";
                    return ob_get_clean();
                }
            ),
            new TwigFunction(
                'oemrUiBelowContainerDiv',
                function ($oemr_settings) {
                    $oemrUi = new OemrUI($oemr_settings);
                    ob_start();
                    $oemrUi->oeBelowContainerDiv();
                    return ob_get_clean();
                }
            ),
            new TwigFunction(
                'oemHelpIcon',
                function () {
                    // this setups a variable called $help_icon... strange
                    require $GLOBALS['srcdir'] . "/display_help_icon_inc.php";
                    return $help_icon ?? '';
                }
            ),
            new TwigFunction(
                'aclCore',
                function ($section, $value, $user = '', $return_value = '') {
                    return AclMain::aclCheckCore($section, $value, $user, $return_value);
                }
            ),
            new TwigFunction(
                'getLogo',
                function (string $type, string $filename = "logo.*") {
                    $ls = new LogoService();
                    return $ls->getLogo($type, $filename);
                }
            ),
            new TwigFunction(
                'getListItemTitle',
                function (string $list, $option) {
                    return LayoutsUtils::getListItemTitle($list, $option);
                }
            )
            ,new TwigFunction(
                'getAssetCacheParamRaw',
                function () {
                    return CacheUtils::getAssetCacheParamRaw();
                }
            )
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
                'javascriptStringRemove',
                function ($string) {
                    return javascriptStringRemove($string);
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
                'xlListLabel',
                function ($string) {
                    return xl_list_label($string);
                }
            ),
            new TwigFilter(
                'xlDocCategory',
                function ($string) {
                    return xl_document_category($string);
                }
            ),

            new TwigFilter(
                'xlFormTitle',
                function ($string) {
                    return xl_form_title($string);
                }
            ),
            // we have some weirdness if we have a date string in the format of YmdHi, it blows things up so we have
            // to pass our date filters through this dateToTime function.  Hopefully we can figure this out later.
            new TwigFilter(
                'dateToTime',
                function ($str) {
                    return strtotime($str);
                }
            ),
            new TwigFilter(
                'addCacheParam',
                function ($path) {
                    return CacheUtils::addAssetCacheParamToPath($path);
                }
            )
        ];
    }
}
