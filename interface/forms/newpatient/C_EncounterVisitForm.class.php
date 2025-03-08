<?php

/**
 * Controller class for the encounter form (new and view) scripts.  It is responsible for rendering the encounter form.
 * I keep similar class names as to other forms.  This is to keep consistency in the codebase.  At some point
 * we may want to move all of these into the src folders but as these form folders can be moved around and are
 * supposed to be self-contained, I'm leaving this here.
 * TODO: investigate moving this into the src/ folder.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2025 Mountain Valley Health <mvhinspire@mountainvalleyhealthinc.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @license   There are segments of code in this file that have been generated via Claude.ai and are licensed as Public Domain.  They have been marked with a header and footer.
 */

namespace OpenEMR\Forms\NewPatient;

use OpenEMR\Billing\MiscBillingOptions;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Uuid\UuidRegistry;
use Symfony\Component\EventDispatcher\EventDispatcher;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\OeUI\RenderFormFieldHelper;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\UserService;
use OpenEMR\Services\ListService;
use sqlStatement;
use sqlFetchArray;
use Twig\Environment;
use Twig\TwigFunction;

class C_EncounterVisitForm
{
    private Environment $twig;
    private array $issueTypes;

    private string $rootdir;

    /**
     * @var string $pageName The name to use when firing off any events for this page
     */
    private string $pageName;

    private EventDispatcher $eventDispatcher;

    private string $mode = '';
    private bool $viewmode = false;

    /**
     * @param $templatePath
     * @param Kernel $kernel
     * @param $issueTypes
     * @param $rootdir
     * @throws \Exception
     */
    public function __construct($templatePath, Kernel $kernel, $issueTypes, $rootdir, $pageName = 'newpatient/common.php')
    {
        // Initialize Twig
        $twig = new TwigContainer($templatePath . '/templates/', $GLOBALS['kernel']);
        $this->issueTypes = $issueTypes;
        $this->twig = $twig->getTwig();
        // add a local twig function so we can make this work properly w/o too many modifications in the twig file
        $this->twig->addFunction(new TwigFunction('displayOptionClass', [$this, 'displayOption']));
        $this->eventDispatcher = $kernel->getEventDispatcher();
        $this->rootdir = $rootdir;
        $this->pageName = $pageName;
        $this->viewmode = false;
        $this->mode = 'edit';
    }

    private function setMode(?string $mode)
    {
        if (in_array($mode, ['new', 'edit', 'followup'])) {
            $this->mode = $mode;
        } else {
            throw new \InvalidArgumentException('Invalid mode provided');
        }
    }


    function displayOption($field)
    {
        $displayMode = $this->viewmode && $this->mode !== "followup" ? "edit" : "new";
        echo RenderFormFieldHelper::shouldDisplayFormField($GLOBALS[$field], $displayMode) ? '' : 'd-none';
    }

    function getCareTeamFacilityForPatient($pid)
    {
        $care_team_facility = sqlQuery("SELECT `care_team_facility` FROM `patient_data` WHERE `pid` = ?", array($pid));
        // TODO: @adunsulag right now care facility is an array... the original code in common.php treats this as a single value
        // we need to look at fixing this if there is multiple facilities
        return $care_team_facility['care_team_facility'] ?? null;
    }


// Get providers list
    function getProvidersForTemplate(UserService $userService, $encounter)
    {
        $users = $userService->getActiveUsers();
        $provider_id = (int)$encounter['provider_id'];
        $providers = [];
        foreach ($users as $user) {
            $p_id = (int)$user['id'];
            $flag_it = "";
            if ($user['authorized'] != 1) {
                if ($user['id'] == $provider_id) {
                    $flag_it = " (" . xl("Non-Provider") . ")";
                } else {
                    continue;
                }
            }

            $name = $user['fname'] . ' ' . ($user['mname'] ? $user['mname'] . ' ' : '') .
                $user['lname'] . ($user['suffix'] ? ', ' . $user['suffix'] : '') .
                ($user['valedictory'] ? ', ' . $user['valedictory'] : '');
            $providers[] = [
                'id' => $user['id'],
                'name' => $name . $flag_it,
                'selected' => ($provider_id == $p_id)
            ];
        }
        return $providers;
    }
// GENERATED BY claude.ai January 30th 2025 -- HEADER
// Get facilities list
    function getFacilitiesForTemplate(FacilityService $facilityService, $default_fac_override)
    {
        $facilities = $facilityService->getAllServiceLocations();

        $facilities = array_map(function ($facility) use ($default_fac_override) {
            $item = [
                'id' => $facility['id'],
                'name' => $facility['name'],
                'pos_code' => $facility['pos_code'],
                'selected' => $default_fac_override == $facility['id']
            ];
            return $item;
        }, $facilities);
        // if there is only one facility that is the one we select by default.
        if (count($facilities) == 1) {
            $facilities[0]['selected'] = true;
        }
        return $facilities;
    }

// START AI GENERATED CODE
    function getBillingFacilityForTemplate(FacilityService $facilityService, $default_bill_fac = null)
    {
        // Determine default billing facility
        if (empty($default_bill_fac)) {
            // Use the currently logged in user's billing facility if set
            $user_facility = $facilityService->getFacilityForUser($_SESSION['authUserID']);
            if (!empty($user_facility) && $user_facility['billing_location'] == '1') {
                $default_bill_fac = $user_facility['id'];
            } else {
                // Otherwise try to find a primary business entity or billing location
                $tmp_be = $facilityService->getPrimaryBusinessEntity();
                $tmp_bl = $facilityService->getPrimaryBillingLocation();
                $default_bill_fac = !empty($tmp_be['id']) ? $tmp_be['id'] :
                    (!empty($tmp_bl['id']) ? $tmp_bl['id'] : null);
            }
        }

        // Get list of facilities for dropdown
        $fres = $facilityService->getAllBillingLocations();
        $billingFacilities = [];
        foreach ($fres as $frow) {
            $billingFacilities[] = [
                'id' => $frow['id'],
                'name' => $frow['name'],
                'selected' => ($frow['id'] == $default_bill_fac)
            ];
        }

        return $billingFacilities;
    }
// END AI GENERATED CODE


// Get visit categories
    function getVisitCategoriesForTemplate($viewmode, $encounter, $default_visit_category)
    {
        $visitSQL = "SELECT pc_catid, pc_catname, pc_cattype
                 FROM openemr_postcalendar_categories
                 WHERE pc_active = 1 AND pc_cattype IN (0,3)
                 AND pc_constant_id != 'no_show'
                 ORDER BY pc_seq";

        $categories = [];
        $result = sqlStatement($visitSQL);
        while ($row = sqlFetchArray($result)) {
            // Skip therapy group categories if not enabled
            // TODO: @adunsulag magic number 3 needs to be replaced as to wha        // TODO: t this value is...
            if ($row['pc_cattype'] == 3 && !$GLOBALS['enable_group_therapy']) {
                continue;
            }

            // Check ACL
            $postCalendarCategoryACO = AclMain::fetchPostCalendarCategoryACO($row['pc_catid']);
            if ($postCalendarCategoryACO) {
                $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
                if (!AclMain::aclCheckCore($postCalendarCategoryACO[0], $postCalendarCategoryACO[1], '', 'write')) {
                    continue;
                }
            }

            $item = [
                'id' => $row['pc_catid'],
                'name' => xl_appt_category($row['pc_catname']),
                'selected' => ($viewmode && $encounter['pc_catid'] == $row['pc_catid'])
            ];
            if (!$viewmode && $default_visit_category == $row['pc_catid']) {
                $item['selected'] = true;
            }
            $categories[] = $item;
        }

        return $categories;
    }
    function sensitivity_compare($a, $b)
    {
        return ($a[2] < $b[2]) ? -1 : 1;
    }

// Get sensitivity options
    function getSensitivitiesForTemplate($encounter)
    {
        $viewmode = $this->viewmode;

        $sensitivities = AclExtended::aclGetSensitivities();
        if (!$sensitivities || !count($sensitivities)) {
            return [];
        }

        usort($sensitivities, [$this, "sensitivity_compare"]);

        $options = [];
        foreach ($sensitivities as $value) {
            if (!AclMain::aclCheckCore('sensitivities', $value[1])) {
                continue;
            }

            $options[] = [
                'value' => $value[1],
                'display' => $value[1],
                'selected' => ($viewmode && $encounter['sensitivity'] == $value[1])
            ];
        }

        // Add "None" option
        $options[] = [
            'value' => '',
            'display' => xl('None{{Sensitivity}}'),
            'selected' => ($viewmode && !$encounter['sensitivity'])
        ];

        return $options;
    }

// Get issues for linking
    function getIssuesForTemplate($pid, $viewmode, $encounter_id, $selectedIssue = null)
    {

        $issues = [];
        $ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
            "pid = ? AND enddate IS NULL " .
            "ORDER BY type, begdate", array($pid));

        while ($irow = sqlFetchArray($ires)) {
            $tcode = $irow['type'];
            if ($this->issueTypes[$tcode]) {
                $tcode = $this->issueTypes[$tcode][2];
            }

            $selected = false;
            if ($viewmode && $encounter_id) {
                $perow = sqlQuery(
                    "SELECT count(*) AS count FROM issue_encounter WHERE " .
                    "pid = ? AND encounter = ? AND list_id = ?",
                    array($pid, $encounter_id, $irow['id'])
                );
                $selected = ($perow['count'] > 0);
                // NOTE: This issue is not used anywhere in the codebase.  Appears to have been added to support squads but cannot find examples of usage in the codebase
                // TODO: consider whether this should be removed.  Last reviewed January 31st 2025
            } elseif (!empty($selectedIssue) && $selectedIssue == $irow['id']) {
                $selected = true;
            }

            $issues[] = [
                'id' => $irow['id'],
                'type' => $tcode,
                'title' => $irow['title'],
                'date' => $irow['begdate'],
                'selected' => $selected
            ];
        }

        return $issues;
    }

    function getDefaultEncounterType($viewmode, $encounter)
    {
        $encounter_type_option = $encounter['encounter_type_code'] ?? '';
        if (!empty($encounter_type_option)) {
            $listService = new ListService();
            $codes = $listService->getOptionsByListName('encounter-types', ['codes' => $encounter['encounter_type_code']]);
            if (empty($codes[0])) {
                // we may not have code types installed, in that case we will just use the option-id so we can remember the data
                $option = $listService->getListOption('encounter-types', $encounter_type_option);
                $encounter_type_option = $option['option_id'] ?? '';
            } else {
                $encounter_type_option = $codes[0]['option_id'];
            }
        }
        return $encounter_type_option;
    }

    function getInCollectionOptionsForTemplate($encounter = null)
    {
        $options = [
            ['value' => '1', 'title' => xl('Yes')],
            ['value' => '0', 'title' => xl('No')]
        ];

        // Mark selected option for existing encounters
        foreach ($options as &$option) {
            $option['selected'] = ($encounter && isset($encounter['in_collection'])
                && $encounter['in_collection'] == $option['value']);
        }

        return $options;
    }

    function getDischargeDispositionsForTemplate($encounter = null)
    {
        $dispositions = [];

        // Add blank option
        $dispositions[] = [
            'option_id' => '_blank',
            'title' => '-- ' . xl('Select One') . ' --',
            'selected' => false
        ];

        // Get list of discharge dispositions
        $dischargeService = new ListService();
        $dispositionList = $dischargeService->getOptionsByListName('discharge-disposition') ?? [];

        foreach ($dispositionList as $disposition) {
            $dispositions[] = [
                'option_id' => $disposition['option_id'],
                'title' => $disposition['title'],
                'selected' => ($encounter && isset($encounter['discharge_disposition'])
                    && $encounter['discharge_disposition'] == $disposition['option_id'])
            ];
        }

        return $dispositions;
    }

    function getTherapyGroupCategoriesForTemplate()
    {
        $categories = [];
        $visitSQL = "SELECT pc_catid, pc_catname, pc_cattype
                 FROM openemr_postcalendar_categories
                 WHERE pc_active = 1 AND pc_cattype = 3
                 AND pc_constant_id != 'no_show'
                 ORDER BY pc_seq";

        $result = sqlStatement($visitSQL);
        while ($row = sqlFetchArray($result)) {
            $categories[] = $row['pc_catid'];
        }
        return $categories;
    }

    function getGroupDataForTemplate($encounter = null)
    {
        $groupData = [
            'name' => '',
            'group_id' => '',
            'isVisible' => false
        ];

        if (!$GLOBALS['enable_group_therapy']) {
            return $groupData;
        }

        $categories = $this->getTherapyGroupCategoriesForTemplate();

        // For existing encounters, get group info if it's a therapy group encounter
        if (
            $encounter && !empty($encounter['external_id'])
            && in_array($encounter['pc_catid'], $categories)
        ) {
            $group = getGroup($encounter['external_id']);
            $groupData['name'] = $group['group_name'];
            $groupData['group_id'] = $encounter['external_id'];
            $groupData['isVisible'] = true;
        } elseif (!empty($categories)) {
            // For new encounters, just set visibility based on whether group categories exist
            $groupData['isVisible'] = true;
        }

        return $groupData;
    }

    function getPosOptionsForTemplate($facilityPosCode = null)
    {
        $pc = new \POSRef();
        $options = [];
        foreach ($pc->get_pos_ref() as $pos) {
            $options[] = [
                'code' => $pos['code'],
                'title' => $pos['title'],
                // If viewing existing record, use saved pos_code, otherwise use facility default
                'selected' => ($pos['code'] == $facilityPosCode)
            ];
        }
        return $options;
    }

    function getDuplicateEncounterRecords($viewmode, $pid)
    {
        $duplicate = ['isDuplicate' => false];
        if (!$viewmode) {
            // Search for an encounter from today
            $erow = sqlQuery("SELECT fe.encounter, fe.date " .
                "FROM form_encounter AS fe, forms AS f WHERE " .
                "fe.pid = ? " .
                " AND fe.date >= ? " .
                " AND fe.date <= ? " .
                " AND " .
                "f.formdir = 'newpatient' AND f.form_id = fe.id AND f.deleted = 0 " .
                "ORDER BY fe.encounter DESC LIMIT 1", array($pid, date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')));

            if (!empty($erow['encounter'])) {
                $duplicate = ['isDuplicate' => true, 'encounter' => $erow['encounter'], 'date' => oeFormatShortDate(substr($erow['date'], 0, 10))];
            }
        }
        return $duplicate;
    }

    /**
     * @param $pid
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render($pid)
    {


// GENERATED BY claude.ai January 30th 2025 -- FOOTER

        $mode = (!empty($_GET['mode'])) ? $_GET['mode'] : 'new';

        $viewmode = false;
        if (!empty($_GET['id'])) {
            $viewmode = true;
        }

// "followup" mode is relevant when enable follow up encounters global is enabled
// it allows the user to duplicate past encounter and connect between the two
// under this mode the facility and the visit category will be same as the origin and in readonly
        if ($mode === "followup") {
            $encounter = (!empty($_GET['enc'])) ? (int)$_GET['enc'] : null;
            if (!is_null($encounter)) {
                $viewmode = true;
                $_REQUEST['id'] = $encounter;
            }
        }
        $this->setMode($mode);
        $this->viewmode = $viewmode;


        $encounter = null;
        $encounter_followup = null;
        $encounter_followup_id = null;
        $followup_date = null;
        if ($viewmode) {
            $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
            $result = sqlQuery("SELECT * FROM form_encounter WHERE id = ?", array($id));
            $encounter = $result;
            // it won't encode in the JSON if we don't convert this.
            $encounter['uuid'] = UuidRegistry::uuidToString($result['uuid']);
            $encounter_followup_id = $encounter['parent_encounter_id'] ?? null;
            if ($encounter_followup_id) {
                $q = "SELECT fe.date as date, fe.encounter as encounter FROM form_encounter AS fe " .
                    "JOIN forms AS f ON f.form_id = fe.id AND f.encounter = fe.encounter " .
                    "WHERE fe.id = ? AND f.deleted = 0 ";
                $followup_enc = sqlQuery($q, array($encounter_followup_id));
                $followup_date = date("m/d/Y", strtotime($followup_enc['date']));
                $encounter_followup = $followup_enc['encounter'];
            }
            // @todo why is this here?
            if ($mode === "followup") {
                $followup_date = date("m/d/Y", strtotime($encounter['date']));
                $encounter_followup = $encounter['encounter'];
                $encounter['reason'] = '';
                $encounter['date'] = date('Y-m-d H:i:s');
                $parentEncounterId = $encounter['id'];
            }

            if ($encounter['sensitivity'] && !AclMain::aclCheckCore('sensitivities', $encounter['sensitivity'])) {
                $this->twig->render("newpatient/unauthorized.html.twig");
                exit();
            }
        }

        $displayMode = ($viewmode && $mode !== "followup") ? "edit" : "new";
        $posCode = '';

// Prepare data for template
        $issuesEnabled = $GLOBALS['enc_enable_issues'] !== RenderFormFieldHelper::HIDE_ALL;
        $issuesAuth = true;
        foreach ($this->issueTypes as $type => $dummy) {
            if (!AclMain::aclCheckIssue($type, '', 'write')) {
                $issuesAuth = false;
                break;
            }
        }

// Set up page display variables
        $headingTitle = $viewmode ? xl('Patient Encounter Form') : xl('New Encounter Form');

// UI settings
        $arrOeUiSettings = array(
            'heading_title' => $headingTitle,
            'include_patient_name' => true,
            'expandable' => false,
            'expandable_files' => array(),
            'action' => "",
            'action_title' => "",
            'action_href' => "",
            'show_help_icon' => true,
            'help_file_name' => "common_help.php"
        );


//Gets validation rules from Page Validation list.
//Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
        $validationConstraints = collectValidationPageRules("/interface/forms/newpatient/common.php");
        if (empty($validationConstraints)) {
            $validationConstraints = [];
        } else {
            // grab our validation constraints
            $validationConstraints = json_decode($validationConstraints["new_encounter"]["rules"], true);
            if ($validationConstraints === false) {
                $validationConstraints = [];
                (new \OpenEMR\Common\Logging\SystemLogger())->errorLogCaller("Error decoding validation constraints for encounter form");
            }
        }

        /**
         * @global $userauthorized
         * @global $pid
         */
        $provider_id = ($userauthorized ?? '') ? $_SESSION['authUserID'] : null;
        $default_fac_override = $encounter['facility_id'] ?? $this->getCareTeamFacilityForPatient($pid);
        if (!$viewmode) {
            $now = date('Y-m-d');
            $encnow = date('Y-m-d 00:00:00');
            $time = date("H:i:00");
            $q = "SELECT pc_aid, pc_facility, pc_billing_location, pc_catid, pc_startTime" .
                " FROM openemr_postcalendar_events WHERE pc_pid=? AND pc_eventDate=?" .
                " ORDER BY pc_startTime ASC";
            $q_events = sqlStatement($q, array($pid, $now));
            while ($override = sqlFetchArray($q_events)) {
                $q = "SELECT fe.encounter as encounter FROM form_encounter AS fe " .
                    "JOIN forms AS f ON f.form_id = fe.id AND f.encounter = fe.encounter " .
                    "WHERE fe.pid=? AND fe.date=? AND fe.provider_id=? AND f.deleted=0";
                $q_enc = sqlQuery($q, array($pid, $encnow, $override['pc_aid']));
                if (!empty($override) && is_array($override) && empty($q_enc['encounter'])) {
                    $provider_id = $override['pc_aid'];
                    $default_bill_fac_override = $override['pc_billing_location'];
                    $default_fac_override = $override['pc_facility'];
                    $default_catid_override = $override['pc_catid'];
                }
            }
            // set some defaults
            $encounter = [
                'provider_id' => $provider_id
                // no encounter or anything
                ,'facility_id' => $default_fac_override
                ,'billing_facility_id' => $default_bill_fac_override ?? ''
                ,'pc_catid' => $default_catid_override ?? ''
                ,'date' => date('Y-m-d H:i:00')
                ,'in_collection' => 0

            ];
            // note we don't set provider id here as an else as we want what is saved in the record.
        } else {
            $default_fac_override = $encounter['facility_id'] ?? $default_fac_override;
        }


        $MBO = new MiscBillingOptions();
        $refProviderId = QueryUtils::fetchSingleValue(
            "SELECT ref_providerID FROM patient_data WHERE pid = ?",
            'ref_ProviderID',
            [$pid]
        );
        $referringProviders = array_map(function ($provider) use ($viewmode, $encounter, $refProviderId) {
            if (!$viewmode || empty($encounter['referring_provider_id'])) {
                $encounter["referring_provider_id"] = $refProviderId ?? 0;
            }
            if ($viewmode && !empty($encounter["referring_provider_id"])) {
                $provider['selected'] = $provider['id'] == $encounter['referring_provider_id'];
            }
            return $provider;
        }, $MBO->getReferringProviders());

        $orderingProviders = array_map(function ($provider) use ($viewmode, $encounter, $pid) {
            $provider['selected'] = $provider['id'] == ($encounter['ordering_provider_id'] ?? 0);
            return $provider;
        }, $MBO->getOrderingProviders());

        $facilityService = new FacilityService();
        $facilities = $this->getFacilitiesForTemplate($facilityService, $default_fac_override);
        $posCode = '';
        foreach ($facilities as $facility) {
            if ($facility['selected']) {
                // if the $default_fac_override is not set, we want to use the default determined by the
                // getFacilitiesForTemplate function so it gets saved in the DOM.
                $default_fac_override = $facility['id'];
                $posCode = $facility['pos_code'];
                break;
            }
        }
// START AI GENERATED CODE
// If viewing an existing encounter, use its POS code instead of facility default
        if ($viewmode && !empty($encounter['pos_code'])) {
            $facilityPosCode = $encounter['pos_code'];
        } else {
            $facilityPosCode = null;
        }
        $billingFacilities = $this->getBillingFacilityForTemplate($facilityService, $encounter['billing_facility_id'] ?? null);
        $inCollectionOptions = $this->getInCollectionOptionsForTemplate($encounter);
        $dischargeDispositions = $this->getDischargeDispositionsForTemplate($viewmode ? $encounter : null);
        $groupData = $this->getGroupDataForTemplate($viewmode ? $encounter : null);
// Add therapy group categories to template data
        $therapyGroupCategories = $this->getTherapyGroupCategoriesForTemplate();
        $posOptions = $this->getPosOptionsForTemplate($facilityPosCode);
// END AI GENERATED CODE

        if (empty($encounter['onset_date']) || $encounter['onset_date'] == '0000-00-00 00:00:00') {
            $encounter['onset_date'] = null;
        }

        /**
         * @global $rootdir
         */
        $viewArgs = [
            'globals' => $GLOBALS,
            'viewmode' => $viewmode,
            'mode' => $mode,
            'saveMode' => $viewmode && $mode !== "followup" ? "update" : "new",
            'rootdir' => $rootdir ?? '',
            'encounter' => $encounter,
            'encounter_followup' => $encounter_followup,
            'followup_date' => $followup_date,
            'pageTitle' => xl('Patient Encounter'),
            'facilities' => $facilities,
            'providers' => $this->getProvidersForTemplate(new UserService(), $encounter),
            'visitCategories' => $this->getVisitCategoriesForTemplate($viewmode, $encounter, $GLOBALS['default_visit_category']),
            'sensitivities' => $this->getSensitivitiesForTemplate($encounter),
            'issuesEnabled' => $issuesEnabled,
            'issuesAuth' => $issuesAuth,
            // TODO: it doesn't seem like canAddIssues is ever going to be different than issuesAuth value... so not sure why they are separate.
            'canAddIssues' => AclMain::aclCheckCore('patients', 'med', '', 'write'),
            'issues' => $issuesEnabled && $issuesAuth ? $this->getIssuesForTemplate($pid, $viewmode, $encounter['encounter'] ?? null, $_REQUEST['issue'] ?? null) : [],
            // END AI GENERATED CODE
            'CSRF_TOKEN_FORM' => CsrfUtils::collectCsrfToken(),
            'bodyClass' => $body_javascript ?? '',
            'oemrUiSettings' => $arrOeUiSettings,
            'formAction' => '/interface/forms/newpatient/save.php',
            'language_direction' => $_SESSION['language_direction'] ?? 'ltr',
            'validationConstraints' => $validationConstraints ?? [],
            'isPosEnabled' => $GLOBALS['set_pos_code_encounter'] === "1",
            'selectedFacilityId' => $default_fac_override,
            'defaultClassCodeValue' => $viewmode ?  $encounter['class_code'] : '',
            'defaultEncounterTypeValue' => $viewmode ? $this->getDefaultEncounterType($viewmode, $encounter) : '',
            'pid' => $pid,
            'referringProviders' => $referringProviders,
            'orderingProviders' => $orderingProviders,
            'billingFacilities' => $billingFacilities,
            'defaultReferralSource' => $viewmode ? $encounter['referral_source'] : '',
            'parentEncounterId' => $parentEncounterId ?? '',
            // START AI GENERATED CODE
            'showInCollection' => ($GLOBALS['hide_billing_widget'] != 1),
            'inCollectionOptions' => $inCollectionOptions,
            'dischargeDispositions' => $dischargeDispositions,
            'groupData' => $groupData,
            'therapyGroupCategories' => $therapyGroupCategories,
            'enableGroupTherapy' => $GLOBALS['enable_group_therapy'],
            'isPosEnabled' => !empty($GLOBALS['set_pos_code_encounter']),
            'posOptions' => $posOptions,
            'textTemplatesEnabled' => $GLOBALS['text_templates_enabled'] === '1',
            'duplicate' => $this->getDuplicateEncounterRecords($viewmode, $pid),
        ];
        // END AI GENERATED CODE

        $layout = "newpatient/common.html.twig";
        $templatePageEvent = new TemplatePageEvent('newpatient/common.php', [], $layout, $viewArgs);
        $event = $this->eventDispatcher->dispatch($templatePageEvent, TemplatePageEvent::RENDER_EVENT);
        if (!$event instanceof TemplatePageEvent) {
            throw new \RuntimeException('Invalid event returned from template page event');
        }
// Render template
        echo $this->twig->render($event->getTwigTemplate(), $event->getTwigVariables());
    }
}
