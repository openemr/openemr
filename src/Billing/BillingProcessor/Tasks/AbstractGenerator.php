<?php

/**
 * This class represents the abstract implementation of GeneratorInterface
 *
 * The class implements the execute() method of the ProcessingTaskInterface
 * and further breaks down the task depending on the action that is being
 * run by the user.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingProcessor;
use OpenEMR\Billing\BillingProcessor\GeneratorCanValidateInterface;
use OpenEMR\Common\Csrf\CsrfUtils;

abstract class AbstractGenerator extends AbstractProcessingTask
{
    protected $action = null;

    public function __construct($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action): void
    {
        $this->action = $action;
    }

    /**
     * This abstract class for generators implements the execute method
     * so we can further hone exactly which operation we want to run.
     *
     * This helps reduce conditional statements in the generator classes
     * by checking the action here and calling the appropriate method.
     *
     * If needed the individual generator can override this method and
     * take control of the entire execute() process.
     *
     * If the generator doesn't implement validation, and there's
     * no action specified, just run generate()
     *
     * @param BillingClaim $claim
     */
    public function execute(BillingClaim $claim)
    {
        if ($this instanceof GeneratorCanValidateInterface) {
            if ($this->getAction() === BillingProcessor::VALIDATE_ONLY) {
                $this->validateOnly($claim);
            } elseif ($this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
                $this->validateAndClear($claim);
            }
        }

        if (
            $this->getAction() === BillingProcessor::NORMAL ||
            $this->getAction() === null
        ) {
            $this->generate($claim);
        }
    }

    /**
     * This AbstractGenerator captures the complete method so we
     * can filter by action and call the appropriate method
     * on the child generator.
     *
     * If we are validating, just print to screen for the user,
     * but if we are running 'normal' action, we'll complete to
     * file and write our batch file to EDI directory(ies).
     *
     * If the generator doesn't implement validation, and there's
     * no action specified, just run completeToFile()
     *
     * @param array $context
     */
    public function complete(array $context)
    {
        if ($this instanceof GeneratorCanValidateInterface) {
            if (
                $this->getAction() === BillingProcessor::VALIDATE_ONLY ||
                $this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR
            ) {
                    $this->completeToScreen($context);
            }
        }

        if (
            $this->getAction() === BillingProcessor::NORMAL ||
            $this->getAction() === null
        ) {
            $this->completeToFile($context);
        }
    }

    /**
     * This is a helper function for generators that produce a file
     * as output, and need to initiate a file download for the
     * user. This prints javascript that will call the get_claim_file.php
     * endpoint and initiate the download.
     *
     * @param $filename
     * @param $location
     * @param false $delete
     */
    public function printDownloadClaimFileJS($filename, $location = '', $delete = false)
    {
        $url = $GLOBALS['webroot'] . '/interface/billing/get_claim_file.php?' .
            'key=' . urlencode($filename) .
            '&location=' . urlencode($location) .
            '&delete=' . urlencode($delete) .
            '&csrf_token_form=' . urlencode(CsrfUtils::collectCsrfToken());
        echo "<script type='text/JavaScript'>window.location = " . js_escape($url) . "</script>";
    }
}
