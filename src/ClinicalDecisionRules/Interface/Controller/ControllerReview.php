<?php

namespace OpenEMR\ClinicalDecisionRules\Interface\Controller;

use OpenEMR\ClinicalDecisionRules\Interface\BaseController;
use OpenEMR\ClinicalDecisionRules\Interface\Common;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\Rule;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ControllerReview extends BaseController
{
    const ERROR_MESSAGE_INVALID = 'feedback_invalid';
    const ERROR_MESSAGE_SUCCESS = 'feedback_success';
    const ERROR_MESSAGE_FAILED = 'feedback_failed';
    public function __construct()
    {
        parent::__construct();
    }

    public function _action_view()
    {
        $ruleId = Common::get('rule_id');
        $pid = $_SESSION['pid']; // don't trust the pid in the URL

        if (!AclMain::aclCheckCore('patients', 'med')) {
            throw new AccessDeniedException("patients", "med", "Invalid ACL access to CDR review");
        }

        if ($pid == null) {
            throw new NotFoundHttpException("Patient ID not found");
        }

        if (!CsrfUtils::verifyCsrfToken(Common::get("csrf_token_form"))) {
            throw new InvalidCsrfTokenException("Invalid CSRF token");
        }
        // first try to grab the more specific rule in case this is a custom rule, then grab the generic one
        $rule = $this->getRuleManager()->getRule($ruleId, $pid);
        if (is_null($rule)) {
            // check for empty baseline rule
            $rule = $this->getRuleManager()->getRule($ruleId);
            if (is_null($rule)) {
                throw new NotFoundHttpException("Rule not found");
            }
        }
        $default_message = xl("Unknown");
        $rule->updateEmptySourceAttributesWithDefaultMessage($default_message);

        $this->viewBean->rule = $rule;
        $this->viewBean->canEdit = AclMain::aclCheckCore("admin", "super");
        $this->viewBean->message = Common::get('message');
        $this->set_view("view.php");
    }

    public function _action_submit_feedback()
    {
        $ruleId = Common::post('rule_id');
        $pid = $_SESSION['pid']; // don't trust the pid in the URL

        if ($pid == null) {
            throw new NotFoundHttpException("Patient ID not found");
        }

        if (!CsrfUtils::verifyCsrfToken(Common::post("csrf_token"))) {
            throw new CsrfInvalidException("Invalid CSRF token");
        }
        // first try to grab the more specific rule in case this is a custom rule, then grab the generic one
        $rule = $this->getRuleManager()->getRule($ruleId, $pid);
        if (is_null($rule)) {
            // check for empty baseline rule
            $rule = $this->getRuleManager()->getRule($ruleId);
            if (is_null($rule)) {
                throw new NotFoundHttpException("Rule not found");
            }
        }
        $rule->setFeedback(Common::post('feedback'));
        // note some browser implementations appear to screw up on html maxlength attribute due to line breaks and other wierd characters
        // so we need to check the length here, but note that the client side may see a different length in certain edge cases.
        if (mb_strlen($rule->getFeedback()) > 2048) {
            (new SystemLogger())->errorLogCaller("Rule length exceeded, client side should have caught this", ['ruleId' => $ruleId]);
            return $this->redirect("index.php?action=review!view&rule_id=" . urlencode($ruleId) . '&pid=' . urlencode($pid) . '&csrf_token_form=' . urlencode(CsrfUtils::collectCsrfToken())
                . '&message=' . self::ERROR_MESSAGE_INVALID);
        }
        $this->viewBean->rule = $rule;

        $sqlCategory = "SELECT `value`,`new_value`, `category` FROM `clinical_rules_log` " .
            "WHERE `category` = ? AND `pid` = ? AND `uid` = ? " .
            "ORDER BY `id` DESC LIMIT 1";

        $combinedSql = "(" . $sqlCategory . ") UNION (" . $sqlCategory . ") ORDER BY `category`";
        $data = QueryUtils::fetchRecords($combinedSql, ['clinical_reminder_widget', $pid, $_SESSION['authUserID']
        , 'active_reminder_popup', $pid, $_SESSION['authUserID']]);
        $deserializeData = [];
        foreach ($data as $record) {
            $record['valueArray'] = json_decode($record['value'], true);
            $deserializeData[] = $record;
        }
        $clinicalRuleLog = $this->findClinicalRuleLog($deserializeData, $rule);
        if (!empty($clinicalRuleLog)) {
            $this->insertFeedbackForClinicalRuleLog($clinicalRuleLog, $rule, $pid, $_SESSION['authUserID']);
            return $this->redirect("index.php?action=review!view&rule_id=" . urlencode($ruleId) . '&pid=' . urlencode($pid) . '&csrf_token_form=' . urlencode(CsrfUtils::collectCsrfToken())
                . '&message=' . self::ERROR_MESSAGE_SUCCESS);
        } else {
            // TODO: if there is no feedback... we never should have gotten here... log an error and throw an exception
            (new SystemLogger())->errorLogCaller("No rule found in clinical rule log. This should never have been reached", ['ruleId' => $ruleId]);
            return $this->redirect("index.php?action=review!view&rule_id=" . urlencode($ruleId) . '&pid=' . urlencode($pid) . '&csrf_token_form=' . urlencode(CsrfUtils::collectCsrfToken())
                . '&message=' . self::ERROR_MESSAGE_FAILED);
        }
    }

    private function findClinicalRuleLog(array $data, Rule $rule)
    {
        // note this assumes the rule_id is NEVER a substring of the JSON structure of value itself other than the actual rule id
        $otherMatchingRow = null;
        foreach ($data as $row) {
            foreach ($row['valueArray'] as $key => $ruleItem) {
                if ($ruleItem['rule_id'] === $rule->id) {
                    if ($row['category'] == 'clinical_reminder_widget') {
                        return $row;
                    } else {
                        $otherMatchingRow = $row;
                    }
                }
            }
        }
        // if we have a matching row, but it's not the clinical_reminder_widget row, return that one
        return $otherMatchingRow;
    }

    private function insertFeedbackForClinicalRuleLog(array $clinicalRuleLog, Rule $rule, $patientId, $userId)
    {
        $updatedValueArray = [];
        foreach ($clinicalRuleLog['valueArray'] as $key => $ruleItem) {
            if ($ruleItem['rule_id'] === $rule->id) {
                $ruleItem['feedback'] = !empty($rule->getFeedback()) ? $rule->getFeedback() : null;
            }
            $updatedValueArray[$key] = $ruleItem;
        }

        // this seems counter-intuitive but we are adding feedback on the EXISTING rules, not adding any new rules
        // the system behavior will add new alerts/rules if the 'new_value' has any rules in the JSON, so we don't need to do that here
        $updatedValue = json_encode($updatedValueArray);
        $newValue = '';
        sqlStatement("INSERT INTO `clinical_rules_log` " .
            "(`date`,`pid`,`uid`,`category`,`value`,`new_value`) " .
            "VALUES (NOW(),?,?,?,?,?)", array($patientId,$userId,$clinicalRuleLog['category'], $updatedValue,$newValue));
    }
}
