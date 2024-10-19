<?php

namespace OpenEMR\ClinicalDecisionRules\Interface\Controller;

use OpenEMR\ClinicalDecisionRules\Interface\BaseController;
use OpenEMR\ClinicalDecisionRules\Interface\Common;
use OpenEMR\Common\Csrf\CsrfUtils;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class ControllerReview extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    function _action_view()
    {
        $ruleId = Common::get('rule_id');
        $pid = Common::get('pid');

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
        $this->viewBean->rule = $rule;
        $this->set_view("view.php");
    }

    function _action_submit_feedback()
    {

    }

}
