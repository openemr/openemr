<?php

/**
 * This class is the billing processor.
 *
 * The billing processor takes the input from the UI's Billing Manager
 * screen, and converts the user input into an executable task object. That happens
 * in the buildProcessingTaskFromPost() method. The object that is returned is
 * an instance of ProcessingTaskInterface which is responsible for processing
 * all of the claims in the group of claims sent from the UI.
 *
 * There are many classes that implement this interface for different types of
 * processing and formatting of the submitted claims. For example TaskReopen simply
 * re-opens all of the claims, where GeneratorX12 creates multiple claim batch files.
 *
 * Each ProcessingTaskInterface implementation has three methods.
 *  - setup($context) called before claim loop to do any setup of the processing task
 *  - execute(BatchClaim $claim) called on each claim, where the task can add to a batch file, or whatever
 *  - complete($context) called after the claim loop to generate any output, or clean up
 *
 * This file is a refactoring of the original billing_process.php file which was
 * becoming increasingly cluttered and ridden with logic errors because of the complexity
 * of the looping and branching. This pattern allows each processing task to own it's
 * class and not be mixed with others.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Daniel Pflieger <daniel@growlingflea.com>
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Daniel Pflieger <daniel@growlingflea.com>
 * @copyright Copyright (c) 2014-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor;

use OpenEMR\Billing\BillingProcessor\Tasks;
use OpenEMR\Common\Session\SessionUtil;

class BillingProcessor
{
    /**
     * Post from the billing manager form
     * @var
     */
    protected $post;

    /**
     * Our logger instance that we use and also pass down
     * to the processing tasks
     * @var
     */
    protected $logger;

    /**
     * The following constants are the options for processing tasks, which are the actions
     * applied to the checked claims on the billing manager screen
     */
    const VALIDATE_ONLY = 'validate-only';
    const VALIDATE_AND_CLEAR = 'validate-and-clear';
    const NORMAL = 'normal';

    public function __construct($post)
    {
        $this->post = $post;
        $this->logger = new BillingLogger();
    }

    /**
     * This is the entry-point of claim-processing called in billing_process.php
     */
    public function execute()
    {
        // Use the user's input parameters to build the appropriate processing task
        $processing_task = $this->buildProcessingTaskFromPost($this->post);

        // Based on UI form input, get the claims we actually need to bill
        $claims = $this->prepareClaims();

        // What task are we running, as directed by the user. Process the claims using
        // each Processing Task's execute method
        if (!empty($claims)) {
            $this->processClaims($processing_task, $claims);
        }

        // Return our logger instance so any non-claim-specific data
        // can be written to the screen like notification, alerts, status, etc.
        return $processing_task->getLogger();
    }

    protected function prepareClaims()
    {
        $claims = [];
        // Build the claims we actually want to process from the post
        // The form posts all claims whether they were selected or not, and we
        // just want the claims that were selected by the user, which have 'bill'
        // index set on their array
        foreach ($this->post['claims'] as $claimId => $partner_and_payor) {
            if (isset($partner_and_payor['bill'])) {
                // The format coming in from POST is like this:
                // [ encounter-pid => [ 'partner' => partnerId, 'payor' => 'p'.payorId ], ... ]
                // Since the format is cryptic, we use the BillingClaim constructor to parse that into meaningful
                // attributes
                $billingClaim = new BillingClaim($claimId, $partner_and_payor);
                $bn_x12 = $_SESSION['bn_x12'] ?? '';
                if (($billingClaim->getPartner() == -1) && $bn_x12) {
                    // If the x-12 partner is unassigned, don't process it.
                    $this->logger->printToScreen(xl("No X-12 partner assigned for claim " . $billingClaim->getId()));
                    continue;
                }

                $claims[] = $billingClaim;
            }
        }

        return $claims;
    }

    protected function processClaims(ProcessingTaskInterface $processingTask, array $claims)
    {
        // Call setup on our processing task. If the task is a file-generator,
        // this calls setup on the generator (to set up batch file, etc)
        $processingTask->setup([
            'claims' => $claims,
            'post' => $this->post
        ]);

        // Go through each claim and process it while organizing them into batches
        foreach ($claims as $claim) {
            // Call the execute method on the task we created below based on user input
            // If the task is generating a file, one of the Generator* file's execute methods is called
            $processingTask->execute($claim);
        }

        // Call the task's complete method so it can produce it's output
        // and do any clean-up
        $processingTask->complete([
            'claims' => $claims,
            'post' => $this->post
        ]);
    }

    protected function buildProcessingTaskFromPost($post)
    {
        // Depending on which type of process we are running, create the appropriate
        // processing task object to process the claims and produce output (if any).
        // Determine which processing task the user wants us to run based on the input
        // on the billing manager form. In the case of the Generator tasks that create
        // an output file, if the user selects validate only, we don't do
        // any writing or create a batch to send, we just perform validation
        // Normal operation will submit generate the files and submit
        $processing_task = null;
        SessionUtil::unsetSession(['bn_x12']);
        if (isset($post['bn_reopen'])) {
            $processing_task = new Tasks\TaskReopen();
        } elseif (isset($post['bn_mark'])) {
            $processing_task = new Tasks\TaskMarkAsClear();
        } elseif ($GLOBALS['gen_x12_based_on_ins_co'] && isset($post['bn_x12'])) {
            SessionUtil::setSession('bn_x12', true);
            $processing_task = new Tasks\GeneratorX12Direct($this->extractAction());
        } elseif ($GLOBALS['gen_x12_based_on_ins_co'] && isset($post['bn_x12_encounter'])) {
            SessionUtil::setSession('bn_x12', true);
            $processing_task = new Tasks\GeneratorX12Direct($this->extractAction(), true);
        } elseif (isset($post['bn_x12'])) {
            SessionUtil::setSession('bn_x12', true);
            $processing_task = new Tasks\GeneratorX12($this->extractAction());
        } elseif (isset($post['bn_x12_encounter'])) {
            SessionUtil::setSession('bn_x12', true);
            $processing_task = new Tasks\GeneratorX12($this->extractAction(), true);
        } elseif (isset($post['bn_hcfa_txt_file'])) {
            $processing_task = new Tasks\GeneratorHCFA($this->extractAction());
        } elseif (isset($post['bn_process_hcfa'])) {
            $processing_task = new Tasks\GeneratorHCFA_PDF($this->extractAction());
        } elseif (isset($post['bn_process_hcfa_form'])) {
            $processing_task = new Tasks\GeneratorHCFA_PDF_IMG($this->extractAction());
        } elseif (isset($post['bn_ub04_x12'])) {
            SessionUtil::setSession('bn_x12', true);
            $processing_task = new Tasks\GeneratorUB04X12($this->extractAction());
        } elseif (isset($post['bn_process_ub04_form'])) {
            $processing_task = new Tasks\GeneratorUB04Form_PDF($this->extractAction());
        } elseif (isset($post['bn_external'])) {
            $processing_task = new Tasks\GeneratorExternal($this->extractAction());
        }

        // If the processing task can write to the billing log, let's set it's log
        // instance. The default implementation of the LoggerInterface and the way
        // this is usually implemented on tasks is the trait Traits\WritesToBillingLog
        if ($processing_task instanceof LoggerInterface) {
            $processing_task->setLogger($this->logger);
        }

        return $processing_task;
    }

    /**
     * Get the 'action' the user wants us to run based on UI input passed
     * to us in the POST array
     *
     * @return string|null
     */
    protected function extractAction()
    {
        $action = null;
        if (isset($this->post['btn-clear'])) {
            $action = self::VALIDATE_AND_CLEAR;
        } elseif (isset($this->post['btn-validate'])) {
            $action = self::VALIDATE_ONLY;
        } elseif (isset($this->post['btn-continue'])) {
            $action = self::NORMAL;
        }

        return $action;
    }
}
