<?php

/**
 * OemrUI class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\OeUI;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Special case where not setting up the header for a script, so using setupAssets function,
//  which does not autoload anything. The actual header is set up in another script.
Header::setupAssets();

class OemrUI
{
    private $action;
    private $action_bot_js;
    private $action_href;
    private $action_icon;
    private $action_title;
    private $action_top_js;
    private $arrAction;
    private $arrexpandIcon;
    private $arrFiles;
    private $arrOeUiSettings;
    private $arrow_direction;
    private $close;
    private $collectToken;
    private $container;
    private $contractTitle;
    private $current_state;
    private $display_help_icon;
    private $expand_icon_class;
    private $expand_title;
    private $expandable;
    private $expandable_icon;
    private $expandTitle;
    private $header_expand_js;
    private $heading;
    private $help_file;
    private $help_icon;
    private $help_modal;
    private $jquery_draggable;
    private $modal_body;
    private $print;
    private $target;
    private $web_root;

    /**
    * Create the html string that will display the formatted heading with selected icons - expandable,
    * action and help and generate the html code for the help modal and output all the jQuery needed to make it work.
    *
    * @param array $arrOeUiSettings is an associative array that contains 9 elements, string 'heading_title',
    * int|bool 'include_patient_name', int|bool 'expandable', array 'expandable_files', string 'action', string 'action_title',
    * string 'action_href', int|bool 'show_help_icon' and string 'help_file_name'.
    * The int|bool 'current_state' (expanded = 1, centered = 0) value is obtained from function collectAndOrganizeExpandSetting(array("")),
    * this function needs an indexed array as an argument (array ('expandable_files')) that contains the file name
    * of the current file as the first element, the name of any other file that needs to open in a similar state
    * needs to be included in this array,all names must be unique and have a '_xpd' suffix.
    * It will be used to generate up to 4 values - string $heading, string $expandable_icon, string $action_icon and string $help_icon
    * that will form the html string used to output the formatted heading of the page.
    * If a feature is not required set the corresponding element in the array to an empty string
    */
    public function __construct($arrOeUiSettings = array())
    {
        global $v_js_includes;

        $this->heading = (!empty($arrOeUiSettings['include_patient_name']) && !empty($arrOeUiSettings['heading_title'])) ? ($arrOeUiSettings['heading_title'] ?? '') . " - " . getPatientFullNameAsString($_SESSION['pid']) : ($arrOeUiSettings['heading_title'] ?? '');
        $this->expandable = $arrOeUiSettings['expandable'] ?? null;
        $this->arrFiles = $arrOeUiSettings['expandable_files'] ?? null;
        $this->arrAction = array(($arrOeUiSettings['action'] ?? null), ($arrOeUiSettings['action_title'] ?? null), ($arrOeUiSettings['action_href'] ?? null));
        $this->display_help_icon = $arrOeUiSettings['show_help_icon'] ?? null;
        $this->help_file = $arrOeUiSettings['help_file_name'] ?? null;
        if (!empty($arrOeUiSettings['expandable']) && !empty($arrOeUiSettings['expandable_files'])) {
            $this->current_state = collectAndOrganizeExpandSetting($arrOeUiSettings['expandable_files']);
        }
    }

    /**
    * Creates the html string that will display the formatted heading with selected icons - expandable, action and help.
    *
    * @return array containing string $heading - the formatted html string of the actual heading and string $container
    * - the value of the container class 'container' or 'container-fluid'
    *
    */
    public function pageHeading()
    {
        $heading = text($this->heading);
        if (!empty($heading)) {
            $arrexpandIcon = $this->expandIcon();// returns and array containing expandable icon string and container class string
            $action_icon = $this->actionIcon();
            $help_icon = $this->helpIcon();
            $expandable_icon = $arrexpandIcon[0];
            $heading = "<h2>$heading $expandable_icon $action_icon $help_icon</h2>";
        } else {
            $heading = "<h2>" . xlt("Please supply a heading") . " <i class='fa fa-oe-smile-o' aria-hidden='true'></i></h2>";
        }
        return $heading;
    }

    /**
    * Creates the html string that will display the formatted expandable icon - fa-expand or fa-compress.
    *
    * @param $expandable - int|bool - whether form is expandable or not and $current_state int|bool - the current state of the form
    *
    * @return array containing string $expandable_icon - the formatted html string of the expand icon and string
    * $container - the value of the container class 'container' or 'container-fluid'
    *
    */
    private function expandIcon($expandable = '', $current_state = '')
    {
        $current_state = $this->current_state;
        $expandable = $this->expandable;

        if ($current_state) {
            $container = 'container-fluid';
            $expand_title = xl('Click to Contract and set to henceforth open in Centered mode');
            $expand_icon_class = 'fa-compress oe-center';
        } else {
            $container = 'container';
            $expand_title = xl('Click to Expand and set to henceforth open in Expanded mode');
            $expand_icon_class = 'fa-expand oe-expand';
        }
        $expandable_icon = '';
        if ($expandable) {
            $expandable_icon = "<a href='#' id='exp_cont_icon' class='text-dark text-decoration-none oe-superscript-small expand_contract fa " .  attr($expand_icon_class) . "'" . " title='" . attr($expand_title) . "'
            aria-hidden='true'></a>";
        }
        return array($expandable_icon, $container);
    }

    /**
    * Will return the container class value either 'container' or 'container-fluid'
    *
    * @return string $container that will reflect the current state of the page i.e. expanded = 'container-fluid' or centered = 'container'
    */
    public function oeContainer()
    {
        $arrexpandIcon = $this->expandIcon();
        $container = $arrexpandIcon[1] ? $arrexpandIcon[1] : 'container';
        return $container;
    }

    /**
    * Creates the html string that will display the formatted action/re-direction icon - for conceal, reveal, search, reset, link and back.
    *
    * @param array $arrAction has 3 elements - string - type of action, string - optional title to be used in tooltip
    * and string - the file name or url to be redirected to, only the 3 re-directions reset, link or back need a href value
    * the 3 actions conceal, reveal, search will only use the default title strings
    *
    * @return string $action_icon that will output the action icon html string
    *
    */
    private function actionIcon($arrAction = array())
    {
        $arrAction = $this->arrAction;
        if ($arrAction) {
            $action = $arrAction[0];
            $action_title = $arrAction[1];
            $action_href = $arrAction[2];
        }
        $action_href = ($action_href) ? $action_href : "#";
        switch ($action) {
            case "reset":
                $action_title = ($action_title) ? $action_title : xl("Reset");
                $action_icon = "<a href='" . attr($action_href) . "' onclick='top.restoreSession()'><i id='advanced-action' class='fa fa-undo fa-oe-sm' title='" . attr($action_title) . "' aria-hidden='true'></i></a>";
                break;
            case "conceal":
                $action_title = xl("Click to Hide"); // default needed for jQuery to function
                $action_icon = "<i id='show_hide' class='fa fa-oe-sm fa-eye-slash' title='" . attr($action_title) . "'></i>";
                break;
            case "reveal":
                $action_title = xl("Click to Show"); // default needed for jQuery to function
                $action_icon = "<i id='show_hide' class='fa fa-oe-sm fa-eye' title='" . attr($action_title) . "'></i>";
                break;
            case "search":
                $action_title = xl("Click to show search"); // default needed for jQuery to function
                $action_icon = "<i id='show_hide' class='fa fa-search-plus fa-oe-sm' title='" . attr($action_title) . "'></i>";
                break;
            case "link":
                if (strpos($action_href, 'http') !== false) {
                    $target = '_blank';
                } else {
                    $target = '_self';
                }
                $action_title = ($action_title) ? $action_title : xl("Click to go to page");
                $action_icon = "<a href='" . attr($action_href) . "' target = '" . attr($target) . "' onclick='top.restoreSession()'><i id='advanced-action' class='fa fa-external-link-alt fa-oe-sm' title='" . attr($action_title) . "' aria-hidden='true'></i></a>";
                break;
            case "back":
                $action_title = ($action_title) ? $action_title : xl("Go Back");
                if ($_SESSION ['language_direction'] == 'ltr') {
                    $arrow_direction = 'fa-arrow-circle-left';
                } elseif ($_SESSION ['language_direction'] == 'rtl') {
                    $arrow_direction = 'fa-arrow-circle-right';
                }
                $action_icon = "<a href='" . attr($action_href) . "' onclick='top.restoreSession()'><i id='advanced-action' class='fa " . attr($arrow_direction) . " fa-oe-sm' title='" . attr($action_title) . "' aria-hidden='true'></i></a>";
                break;
            default:
                $action_icon = '';
        }
        return $action_icon;
    }

    /**
    * Creates the html string that will display the formatted help icon - fa-question-circle.
    *
    * @param int|bool  $display_help_icon
    *
    * @return string $help_icon that will output the help icon html string
    *
    */
    private function helpIcon($display_help_icon = '')
    {
        $help_icon = '';
        $display_help_icon = $this->display_help_icon;
        if ($display_help_icon) {
            if ($_SESSION ['language_direction'] == 'ltr') {
                $help_icon_title = xl("To enable help - Go to the User Name on top right > Settings > Features > Enable Help Modal");
            } elseif ($_SESSION ['language_direction'] == 'rtl') {
                $help_icon_title = xl("To enable help - Go to the User Name on top left > Settings > Features > Enable Help Modal");
            }
            if ($GLOBALS['enable_help'] == 1) {
                $help_icon = '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
            } elseif ($GLOBALS['enable_help'] == 2) {
                $help_icon = '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#DCD6D0 !Important" title="' . attr($help_icon_title) . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
            } elseif ($GLOBALS['enable_help'] == 0) {
                 $help_icon = '';
            }
        }
        return $help_icon;
    }

    /**
    * Output the help modal html along with the jQuery to make it work.
    *
    * $param string $help_file - name of the help file to be displayed, must exists in Documentation/help_files
    * will echo the entire html string of the help modal and the jQuery, needs to be used as the first line after the container div
    *
    * @return void
    *
    */
    private function helpFileModal($help_file = '')
    {
        $help_file = $this->help_file;
        $close = xla("Close");
        $print = xla("Print");
        if ($help_file) {
            $help_file = attr($help_file);
            $help_file = $GLOBALS['webroot'] . "/Documentation/help_files/$help_file";
            $modal_body = "<iframe src=\"$help_file\" id='targetiframe' class='w-100 h-100 border-0' style='overflow-x: hidden;'
                                allowtransparency='true'></iframe>";
        } else {
            $modal_body = "<h3> <i class='fa fa-exclamation-triangle  oe-text-red' aria-hidden='true'></i> " . xlt("Check if a help file exists for this page in") . " " . text("Documentation/help_files") . ".<br /><br />" . xlt("Then pass it's name as a value to the element" . " " . text("'help_file_name'") . " "  .  "in the associative array") . " " . text("\$arrOeUiSettings") . ".<br /><br />" . xlt("If the help file does not exist create one and place it in") . " " . text("Documentation/help_files") . ".<br />" . "</h3>";
        }
        $help_modal = <<<HELP
        <div class="row">
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content  oe-modal-content" style="height:700px">
                        <div class="modal-header clearfix">
                            <button type="button" class="close" data-dismiss="modal" aria-label="$close">
                            <span aria-hidden="true" class='text-black' style='font-size:1.5em;'>×</span></button>
                        </div>
                        <div class="modal-body" style="height:80%;">
                            $modal_body
                        </div>
                        <div class="modal-footer mt-0">
                           <button class="btn btn-link btn-cancel oe-pull-away" data-dismiss="modal" type="button">$close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
HELP;
        echo $help_modal . "\r\n";

        $jquery_draggable = <<<JQD
        <script>
        // Jquery draggable
            $(".modal-dialog").addClass('drag-action');
            $(".modal-content").addClass('resize-action');
            var helpTitle = $('#help-href').prop('title');
            $('#myModal').on('hidden.bs.modal', function (e) {
                $('#help-href').prop('title', '');
            });
            $('#help-href').focus( function() {
                $('#help-href').prop('title', helpTitle);
            });
        </script>
JQD;
        echo $jquery_draggable . "\r\n";
        return;
    }

    /**
    * Generates the jQuery for the form to toggle between 'expanded' and 'centered' states.
    *
    * @param array $arrFiles - that contains the files names that need to be toggled between 'expanded' and 'centered' states
    * will generate the jQuery that will be outputted on the page by function oeBelowContainerDiv()
    *
    * @return void
    *
    */
    private function headerExpandJs($arrFiles = array())
    {
        $expandTitle = xlj("Click to Contract and set to henceforth open in Centered mode");
        $contractTitle = xlj("Click to Expand and set to henceforth open in Expanded mode");
        $arrFiles = json_encode($this->arrFiles);
        $web_root = $GLOBALS['webroot'];
        $collectToken = js_escape(CsrfUtils::collectCsrfToken());
        $header_expand_js = <<<EXP
        <script>
        $(window).on('resize', function() {//hide icon on smaller devices as width is almost 100%
            var winWidth = $(this).width();
            if (winWidth <  900) {
                $("#exp_cont_icon").addClass ("hidden");
            } else {
                $("#exp_cont_icon").removeClass ("hidden");
            }
        });
        $(function () {
            $(window).trigger('resize');// to avoid repeating code triggers above on page open
        });

        $(function () {
            $('.expand_contract').click(function () {
                var elementTitle;
                var expandTitle = {$expandTitle};
                var contractTitle = {$contractTitle};
                var arrFiles = {$arrFiles};
                if ($(this).is('.oe-expand')) {
                    elementTitle = expandTitle;
                    $(this).toggleClass('fa-expand fa-compress');
                    $(this).toggleClass('oe-expand oe-center');
                    $('#container_div').toggleClass('container container-fluid');
                    if ($(arrFiles).length) {
                        $.each(arrFiles, function (index, value) {

                            $.post(
                                "{$web_root}/library/ajax/user_settings.php",
                                {
                                    target: arrFiles[index].trim(),
                                    setting: 1,
                                    csrf_token_form: {$collectToken}
                                }
                            );
                        });
                    }
                } else if ($(this).is('.oe-center')) {
                    elementTitle = contractTitle;
                    $(this).toggleClass('fa-compress fa-expand');
                    $(this).toggleClass('oe-center oe-expand');
                    $('#container_div').toggleClass('container-fluid container');
                    if ($(arrFiles).length) {
                        $.each(arrFiles, function (index, value) {
                            $.post(
                                "{$web_root}/library/ajax/user_settings.php",
                                {
                                    target: arrFiles[index].trim(),
                                    setting: 0,
                                    csrf_token_form: {$collectToken}
                                }
                            );
                        });
                    }
                }
                $(this).prop('title', elementTitle);
            });
        });
        </script>
EXP;
        echo $header_expand_js . "\r\n";
        return;
    }

    /**
    * Generates the jQuery to enable an element to toggle between hidden and revealed states.
    *
    * @param array $arrAction - first element contains the string for type of action - needed only for actions search, reveal and conceal
    * will generate the jQuery that will be outputted on the page by function oeBelowContainerDiv()
    *
    * @return void
    *
    */
    private function headerActionJs($arrAction = array())
    {
        $arrAction = $this->arrAction;
        $page = attr(str_replace(" ", "", $this->heading));

        // Build the labels for when the icon is moused-over
        $labels = "";
        if ($arrAction[0] == 'search') {
            $labels .= "var showTitle = " .  xlj('Click to show search') . "\r\n;";
            $labels .= "var hideTitle = " . xlj('Click to hide search') . "\r\n;";
        } elseif ($arrAction[0] == 'reveal' || $arrAction[0] == 'conceal') {
            $labels .= "var hideTitle = " .  xlj('Click to Hide') . "\r\n;";
            $labels .= "var showTitle = " . xlj('Click to Show') . "\r\n;";
        }

        // Build the classes for which icon to display whien hiding, showing, etc.
        $actionClasses = "";
        if ($arrAction[0] == 'search') {
            $actionClasses .= "var showActionClass = 'fa-search-plus'; \r\n";
            $actionClasses .= "var hideActionClass = 'fa-search-minus'; \r\n";
        } elseif ($arrAction[0] == 'reveal') {
            $actionClasses .= "var showActionClass = 'fa-eye'; \r\n";
            $actionClasses .= "var hideActionClass = 'fa-eye-slash'; \r\n";
        } elseif ($arrAction[0] == 'conceal') {
            $actionClasses .= "var showActionClass = 'fa-eye-slash'; \r\n";
            $actionClasses .= "var hideActionClass = 'fa-eye'; \r\n";
        }

        $action_top_js = <<<SHWTOP
        <script>
        $(function () {
            $labels
            $actionClasses
            $('#show_hide').click(function () {
                var elementTitle = '';
SHWTOP;
        echo $action_top_js . "\r\n";

                $action_bot_js = <<<SHWBOT

                $(this).toggleClass(showActionClass + ' ' + hideActionClass);

                $('.hideaway').toggle(500);
                if ($(this).is('.' + showActionClass)) {
                    elementTitle = showTitle;
                } else if ($(this).is('.' + hideActionClass)) {
                    elementTitle = hideTitle;
                }
                $(this).prop('title', elementTitle);

                // Remember our hideaway setting in local storage. If it's visible, show it on next page load
                localStorage.setItem('display#$page', elementTitle);
            }); // End of show_hide click()

            // Use localStorage to remember your last setting
            // Simulate 'click-to-show' if display is set to 'true', or null if there's no setting (default)
            // getItem() returns a string which is why we have to check for the string 'true'
            const elementTitle = localStorage.getItem('display#$page');
            let shouldDisplay = false;
            if (typeof hideTitle != 'undefined' &&
                (elementTitle == hideTitle || elementTitle == null)) {
                shouldDisplay = true
            }

            // We display if we remember we're showing it, but we don't intentionally hide it (no else here to hide)
            // Because the hideaway is probably shown by default for a reason like in the billing manager
            if (shouldDisplay) {
                $('.hideaway').show(500);
                $('#show_hide').removeClass(showActionClass);
                $('#show_hide').addClass(hideActionClass);
            }
        });
        </script>
SHWBOT;
        echo $action_bot_js . "\r\n";
        return;
    }

    /**
    * Output the help modal html with needed jQuery, jQuery to enable an element to toggle between 'hidden' and 'revealed states'
    * and/or 'expand' and 'centered' states.
    *
    * based on the values in the associative array $arrOeUiSettings the relevant code will be outputted to the page
    * for consistency always call this function just below the container div on the page
    *
    * @return void
    *
    */
    public function oeBelowContainerDiv()
    {
        $this->display_help_icon ? $this->helpFileModal() : '';
        $this->expandable ? $this->headerExpandJs() : '';
        $this->arrAction[0] ? $this->headerActionJs() : '';
        return;
    }
}
