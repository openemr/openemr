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
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Events\UserInterface\BaseActionButtonHelper;
use OpenEMR\Events\UserInterface\PageHeadingRenderEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
    private $ed;
    private $twig;
    private $page_id;

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

        $this->page_id = $arrOeUiSettings['page_id'] ?? 'unknown';
        $this->heading = (!empty($arrOeUiSettings['include_patient_name']) && !empty($arrOeUiSettings['heading_title'])) ? ($arrOeUiSettings['heading_title'] ?? '') . " - " . getPatientFullNameAsString($_SESSION['pid']) : ($arrOeUiSettings['heading_title'] ?? '');
        $this->expandable = $arrOeUiSettings['expandable'] ?? null;
        $this->arrFiles = $arrOeUiSettings['expandable_files'] ?? null;
        $this->arrAction = array(($arrOeUiSettings['action'] ?? null), ($arrOeUiSettings['action_title'] ?? null), ($arrOeUiSettings['action_href'] ?? null));
        $this->display_help_icon = $arrOeUiSettings['show_help_icon'] ?? null;
        $this->help_file = $arrOeUiSettings['help_file_name'] ?? null;
        if (!empty($arrOeUiSettings['expandable']) && !empty($arrOeUiSettings['expandable_files'])) {
            $this->current_state = collectAndOrganizeExpandSetting($arrOeUiSettings['expandable_files']);
        }

        $act = $this->arrAction;
        $this->action = false;
        // This is a holdover, the original code does not have a boolean for action, just a bunch of options, easier to keep for now
        if ($act[0] != "") {
            $this->action = true;
        }

        /**
         * @var EventDispatcher
         */
        $this->ed = $GLOBALS['kernel']->getEventDispatcher();

        /**
         * @var TwigEnvironment
         */
        $twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
        $this->twig = $twigContainer->getTwig();

        if ($this->expandable) {
            $this->ed->addListener(PageHeadingRenderEvent::EVENT_PAGE_HEADING_RENDER, [$this, 'expandIconListener']);
        }

        if ($this->action) {
            $this->ed->addListener(PageHeadingRenderEvent::EVENT_PAGE_HEADING_RENDER, [$this, 'actionIconListener']);
        }

        if ($GLOBALS['enable_help'] !== 0 && $this->display_help_icon) {
            $this->ed->addListener(PageHeadingRenderEvent::EVENT_PAGE_HEADING_RENDER, [$this, 'helpIconListener']);
        }
    }

    /**
    * Returns the page heading based on the options passed into the constructor.
    *
    * @var bool $render If true, will echo the results from within this function
    * @return string The Twig-rendered html content, suitable for simply echo'ing
    *
    */
    public function pageHeading(bool $render = false): null|string
    {
        /**
         * @var PageHeadingRenderEvent
         */
        $pageHeadingEvent = $this->ed->dispatch(new PageHeadingRenderEvent($this->page_id), PageHeadingRenderEvent::EVENT_PAGE_HEADING_RENDER);
        $vars = [
            "primaryMenu" => $pageHeadingEvent->getPrimaryMenu(),
            "buttonList" => $pageHeadingEvent->getActions(),
            "heading" => $this->heading,
        ];

        $html = $this->twig->render("oemr_ui/page_heading/partials/page_heading.html.twig", $vars);

        if ($render) {
            echo $html;
        } else {
            return $html;
        }
    }

    /**
    * Creates the html string that will display the formatted expandable icon - fa-expand or fa-compress.
    */
    public function expandIconListener(PageHeadingRenderEvent $e): PageHeadingRenderEvent
    {
        $current_state = $this->current_state;

        $text = ($current_state) ? xl('Click to contract page to center view') : xl('Click to expand page to full width');
        $title = $text;
        $anchor_class = ($current_state) ? 'oe-center' : 'oe-expand';
        $icon = ($current_state) ? 'fa-compress' : 'fa-expand';

        $opts = [
            'id' => 'exp_cont_icon',
            'title' => $title,
            'href' => "#",
            'iconClass' => "fa fa-fw fa-lg $icon",
            'attributes' => [
                'aria-hidden' => 'true',
            ],
            'anchorClasses' => [
                $anchor_class,
                'expand_contract',
            ],
        ];
        $expandClass = new BaseActionButtonHelper($opts);
        $actions = $e->getActions();
        $actions[] = $expandClass;
        $e->setActions($actions);
        return $e;
    }

    /**
    * Will return the container class value either 'container' or 'container-fluid'
    *
    * @return string $container that will reflect the current state of the page i.e. expanded = 'container-fluid' or centered = 'container'
    */
    public function oeContainer()
    {
        $container = ($this->current_state) ? 'container-fluid' : 'container';
        return $container;
    }

    /**
    * Creates the html string that will display the formatted action/re-direction icon - for conceal, reveal, search, reset, link and back.
    *
    * Considers the following
    * * $action (reset/conceal/reveal/search/link/back), what kind of action is being taken
    * * $action_title, the Tooltip title, optional
    * * $action_href, the HREF (used only for reset, link, and back)
    *
    * @TBD this was narrowly scoped when introduced. These actions should probably be there own separate buttons with custom listeners
    *
    */
    public function actionIconListener(PageHeadingRenderEvent $e)
    {
        $arrAction = $this->arrAction;
        if ($arrAction) {
            $action = $arrAction[0];
            $action_title = $arrAction[1];
            $action_href = $arrAction[2];
        }

        if (!$action) {
            return;
        }

        $action_href = ($action_href) ? $action_href : "#";
        $id = "advanced-action";
        switch ($action) {
            case "reset":
                $action_title = ($action_title) ? $action_title : xl("Reset");
                $icon = "fa-undo";
                break;
            case "conceal":
                $action_title = xl("Click to Hide"); // default needed for jQuery to function
                $icon = "fa-eye-slash";
                $id = "show_hide";
                $action_href = "#";
                break;
            case "reveal":
                $action_title = xl("Click to Show"); // default needed for jQuery to function
                $icon = "fa-eye";
                $id = "show_hide";
                $action_href = "#";
                break;
            case "search":
                $action_title = xl("Click to show search"); // default needed for jQuery to function
                $icon = "fa-search";
                $id = "show_hide";
                $action_href = "#";
                break;
            case "link":
                $target = (strpos($action_href, 'http') !== false) ? "_blank" : "_self";
                $action_title = ($action_title) ? $action_title : xl("Click to go to page");
                $icon = "fa-external-link-alt";
                break;
            case "back":
                $action_title = ($action_title) ? $action_title : xl("Go Back");
                $arrow_direction = ($_SESSION['language_direction'] == 'rtl') ? "fa-arrow-circle-right" : "fa-arrow-circle-left";
                $icon = $arrow_direction;
                break;
            default:
                $icon = '';
        }

        // @TBD Handle the HREF and onclick
        $opts = [
            'id' => $id,
            'title' => $action_title,
            'displayText' => '',
            'href' => $action_href,
            'iconClass' => "fa fa-fw fa-lg {$icon}",
            'dataAttributes' => [
                'aria-hidden' => 'true',
            ],
        ];

        $actionClass = new BaseActionButtonHelper($opts);
        $actions = $e->getActions();
        $actions[] = $actionClass;
        $e->setActions($actions);

        return $e;
    }

    /**
    * Creates the html string that will display the formatted help icon - fa-question-circle.
    *
    * @param int|bool  $display_help_icon
    *
    * @return string $help_icon that will output the help icon html string
    *
    */
    public function helpIconListener(PageHeadingRenderEvent $e)
    {
        $title = "";
        if ($GLOBALS['enable_help'] == "1") {
            $title = xl("Click to view Help");
        } elseif ($GLOBALS['enable_help'] == "2") {
            $title = xl("Enable help under your User Menu > Settings > Featurees > Enable Help Modal");
        }

        $id = 'help-href';
        $opts = [
            'id' => $id,
            'title' => $title,
            'iconClass' => "fa fa-fw fa-lg fa-question-circle",
            'href' => "#",
            'anchorClasses' => [
                'oe-help-redirect',
            ],
            'attributes' => [
                'data-target' => "#myModal",
                'data-toggle' => 'modal',
                'name' => $id,
            ]
        ];

        $a = $e->getActions();
        $a[] = new BaseActionButtonHelper($opts);
        $e->setActions($a);
        return $e;
    }

    /**
    * Output the help modal html along with the jQuery to make it work.
    *
    * $param string $help_file - name of the help file to be displayed, must exists in Documentation/help_files
    * will echo the entire html string of the help modal and the jQuery, needs to be used as the first line after the container div
    *
    * @todo Move this to a twig file, RD 1/12/2023
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
    * @todo Move this to a twig file, RD 1/12/2023
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
                    $("a.oe-expand i").toggleClass('fa-expand fa-compress');
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
                    $("a.oe-center i").toggleClass('fa-compress fa-expand');
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
    * @todo Move this to a twig file, RD 1/12/2023
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
            let element = document.querySelector('a#show_hide i');
            let elementIcon = document.querySelector('a#show_hide i');
            $labels
            $actionClasses
            $('#show_hide').click(function () {
                var elementTitle = '';
                let element = document.querySelector('a#show_hide i');
                let elementIcon = document.querySelector('a#show_hide i');
        SHWTOP;
            echo $action_top_js . "\r\n";

                $action_bot_js = <<<SHWBOT

                elementIcon.classList.toggle(showActionClass);
                elementIcon.classList.toggle(hideActionClass);
                document.querySelector('.hideaway').classList.toggle('d-none');

                if (element.classList.contains(showActionClass)) {
                    elementTitle = showTitle;
                }

                if (element.classList.contains(hideActionClass)) {
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
            if (typeof hideTitle != 'undefined' && (elementTitle == hideTitle || elementTitle == null)) {
                shouldDisplay = true
            }

            // We display if we remember we're showing it, but we don't intentionally hide it (no else here to hide)
            // Because the hideaway is probably shown by default for a reason like in the billing manager
            if (shouldDisplay) {
                if (document.querySelector('.hideaway')) {
                    document.querySelector('.hideaway').classList.remove('d-none');
                }    
                if (document.getElementById(showActionClass) && document.getElementById(hideActionClass)) {
                    elementIcon.classList.remove(showActionClass);
                    elementIcon.classList.add(hideActionClass);
                }
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
