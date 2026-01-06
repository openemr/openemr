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
use OpenEMR\Common\Forms\Types\EncounterListOptionType;
use OpenEMR\Common\Layouts\LayoutsUtils;
use OpenEMR\Common\Utils\CacheUtils;
use OpenEMR\Core\Header;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\LogoService;
use Symfony\Component\EventDispatcher\GenericEvent;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class TwigExtension extends AbstractExtension implements GlobalsInterface
{
    protected ?OemrUI $oemrUI = null;

    protected function getOemrUiInstance($oemrSettings = []): OemrUI
    {
        if (null === $this->oemrUI) {
            $this->oemrUI = new OemrUI($oemrSettings);
        }

        return $this->oemrUI;
    }

    public function __construct(
        protected OEGlobalsBag $globals,
        protected ?Kernel $kernel = null,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'assets_dir' => $this->globals->get('assets_static_relative'),
            'srcdir' => $this->globals->get('srcdir'),
            'rootdir' => $this->globals->get('rootdir'),
            'webroot' => $this->globals->get('webroot'),
            'assetVersion' => $this->globals->get('v_js_includes'),
            'session' => $_SESSION ?? [],
        ];
    }

    public function getTests(): array
    {
        return [
            // can be used like {% if is numeric %}...{% endif %}
            new TwigTest('numeric', fn($value): bool => is_numeric($value))
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'setupHeader',
                Header::setupHeader(...)
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
                'encounterSelectList',
                function ($name, $pid, $selectedValue = '', $title = '', $opts = []) {

                    $encounterOptionType = new EncounterListOptionType($pid);
                    $frow = [
                        'field_id' => $name,
                        'title' => $title,
                        'edit_options' => '',
                        'empty_name' => $opts['empty_name'] ?? ''
                    ];
                    return $encounterOptionType->buildFormView($frow, $selectedValue);
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
                function ($eventName, $eventData = []) {
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
                CsrfUtils::collectCsrfToken(...)
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
            // I don't like how the OemrUi class is being used, it uses event listeners to control parts of the
            // UI and those events can be added again and again everytime the class is instantiated so it assumes
            // its a singleton, so we'll treat it as a singleton here, but its annoying.
            new TwigFunction('oemrUiContainerClass', function (array $oemr_settings) {
                $oemrUi = $this->getOemrUiInstance($oemr_settings);
                $heading =  $oemrUi->oeContainer();
                return $heading;
            }),
            new TwigFunction('oemrUiPageHeading', function (array $oemr_settings) {
                $oemrUi = $this->getOemrUiInstance($oemr_settings);
                $heading = $oemrUi->pageHeading();
                return $heading;
            }),
            new TwigFunction(
                'oemrUiBelowContainerDiv',
                function ($oemr_settings) {
                    $oemrUi = $this->getOemrUiInstance($oemr_settings);
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
                AclMain::aclCheckCore(...)
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
                LayoutsUtils::getListItemTitle(...)
            ),
            new TwigFunction(
                'getAssetCacheParamRaw',
                CacheUtils::getAssetCacheParamRaw(...)
            ),
            new TwigFunction(
                'uniqid',
                fn(string $prefix = "", bool $more_entropy = false): string => uniqid($prefix, $more_entropy)
            )
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('text', text(...)),
            new TwigFilter('attr', attr(...)),
            new TwigFilter('js_escape', js_escape(...)),
            new TwigFilter('attr_js', attr_js(...)),
            new TwigFilter('attr_url', attr_url(...)),
            new TwigFilter('js_url', js_url(...)),
            new TwigFilter('javascriptStringRemove', javascriptStringRemove(...)),
            new TwigFilter('xl', xl(...)),
            new TwigFilter('xlt', xlt(...)),
            new TwigFilter('xla', xla(...)),
            new TwigFilter('xlj', xlj(...)),
            new TwigFilter('money', oeFormatMoney(...)),
            new TwigFilter('shortDate', oeFormatShortDate(...)),
            new TwigFilter(
                'oeFormatDateTime',
                fn($string, $formatTime = "global", $seconds = false) => oeFormatDateTime($string, $formatTime, $seconds)
            ),
            new TwigFilter('xlLayoutLabel', xl_layout_label(...)),
            new TwigFilter('xlListLabel', xl_list_label(...)),
            new TwigFilter('xlDocCategory', xl_document_category(...)),
            new TwigFilter('xlFormTitle', xl_form_title(...)),
            // we have some weirdness if we have a date string in the format of YmdHi, it blows things up so we have
            // to pass our date filters through this dateToTime function.  Hopefully we can figure this out later.
            new TwigFilter(
                'dateToTime',
                fn($str): int|false => strtotime((string) $str)
            ),
            new TwigFilter(
                'addCacheParam',
                CacheUtils::addAssetCacheParamToPath(...)
            )
        ];
    }
}
