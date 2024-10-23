<?php

namespace OpenEMR\ClinicalDecisionRules\Interface\Controller;

use League\Csv\Writer;
use OpenEMR\ClinicalDecisionRules\Interface\BaseController;
use OpenEMR\ClinicalDecisionRules\Interface\Common;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ControllerLog extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    const HEADERS = ["date", "patient_pid", "user_id", "facility_id", "category", "value", "new_value"];

    public function _action_view()
    {
        if (!AclMain::aclCheckCore('patients', 'med')) {
            throw new AccessDeniedException("patients", "med", "Invalid ACL access to CDR log");
        }
        $this->viewBean->search = Common::post('search', '');

        if (!empty($this->viewBean->search) && !CsrfUtils::verifyCsrfToken(Common::post("csrf_token_form"))) {
            throw new CsrfInvalidException("Invalid CSRF token");
        } else {
            $this->viewBean->search = 1;
        }

        $form_begin_date = DateTimeToYYYYMMDDHHMMSS(Common::post('form_begin_date', ''));
        $form_end_date = DateTimeToYYYYMMDDHHMMSS(Common::post('form_end_date', ''));

        // very java-ish...
        $records = $this->getLogRecordsFromRequest($form_begin_date, $form_end_date);
        $this->viewBean->records = $records;
        $this->viewBean->_view_body_fluid = true;
        $this->viewBean->form_begin_date = $form_begin_date;
        $this->viewBean->form_end_date = $form_end_date;


        $this->set_view("view.php");
    }
    public function _action_download()
    {
        $form_begin_date = DateTimeToYYYYMMDDHHMMSS(Common::get('form_begin_date', ''));
        $form_end_date = DateTimeToYYYYMMDDHHMMSS(Common::get('form_end_date', ''));

        $records = $this->getLogRecordsFromRequest($form_begin_date, $form_end_date);
        $writer = Writer::createFromString();
        $writer->insertOne(self::HEADERS);
        foreach ($records as $record) {
            try {
                $writer->insertOne([
                    $record['date'],
                    $record['pid'],
                    $record['uid'],
                    $record['facility_id'],
                    $record['category'],
                    $record['value'],
                    $record['new_value']
                ]);
            } catch (\Exception $e) {
                // TODO: @adunsulag need to figure out error handling in addition to just logging the error
                (new SystemLogger())->errorLogCaller($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
        }
        $fileName = date(\DateTimeImmutable::ATOM) . "_log.csv";
        $response = new Response($writer->toString(), 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $fileName . '"']);
        return $response;
    }

    private function getLogRecordsFromRequest($form_begin_date, $form_end_date)
    {
        $res = listingCDRReminderLog($form_begin_date, $form_end_date);

        $records = [];
        while ($row = sqlFetchArray($res)) {
            //Create category title
            if ($row['category'] == 'clinical_reminder_widget') {
                $category_title = xl("Passive Alert");
            } elseif ($row['category'] == 'active_reminder_popup') {
                $category_title = xl("Active Alert");
            } elseif ($row['category'] == 'allergy_alert') {
                $category_title = xl("Allergy Warning");
            } else {
                $category_title = $row['category'];
            }

            //Prepare the targets
            $all_alerts = json_decode($row['value'], true);
            if (!empty($row['new_value'])) {
                $new_alerts = json_decode($row['new_value'], true);
            } else {
                $new_alerts = [];
            }
            $row['category_title'] = $category_title;
            $row['all_alerts'] = $all_alerts;
            $row['new_alerts'] = $new_alerts;
            $row['date_formatted'] = oeFormatDateTime($row['date'], "global", true);
            $row['formatted_all_alerts'] = $this->getFormattedAlerts($all_alerts, $row);
            $row['formatted_new_alerts'] = $this->getFormattedAlerts($new_alerts, $row);
            $records[] = $row;
        }
        return $records;
    }

    private function getFormattedAlerts($alerts, &$row)
    {
        $formattedAlerts = [];
        foreach ($alerts as $targetInfo => $alert) {
            if (($row['category'] == 'clinical_reminder_widget') || ($row['category'] == 'active_reminder_popup')) {
                $rule_title = getListItemTitle("clinical_rules", $alert['rule_id']);
                $catAndTarget = explode(':', $targetInfo);
                $category = $catAndTarget[0];
                $target = $catAndTarget[1];
                $formattedAlerts[] = [
                    'title' => $rule_title,
                    'rule_action_category' => generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $category),
                    'rule_action' => generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $target),
                    'due_status' => generate_display_field(array('data_type' => '1','list_id' => 'rule_reminder_due_opt'), $alert['due_status']),
                    'feedback' => $alert['feedback'] ?? null,
                    'rawAlert' => $alert,
                    'text' => null
                ];
            } else { // $row['category'] == 'allergy_alert'
                $formattedAlerts[] = [
                    'text' => $alert,
                    'feedback' => $alert['feedback'] ?? null,
                    'rawAlert' => $alert
                ];
            }
        }
        return $formattedAlerts;
    }
}
