<?php

/**
 * These are logging functions that were extracted from the original
 * billing_process.php function and placed here.
 *
 * Each Processing Task that writes to the log can 'use' the trait
 * WritesToBillingLog which helps the task implement the LoggerInterface.
 *
 * That trait will keep a reference of this object, which is passed all
 * throughout the billing process, so everything writes to the same log.
 *
 * At the end of the billing process, the BillingLogger instance is
 * returned to billing_process.php to write any log messages to the screen.
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

use OpenEMR\Common\Crypto\CryptoGen;

class BillingLogger
{
    /**
     * Contains an array of status messages that accumulate
     * through the billing process
     *
     * @var array
     */
    protected $bill_info = [];

    /**
     * Contains a string that represents the results from formatting
     * x-12 claims. This is what you see when you click the 'Logs' button
     * on the result modal.
     *
     * @var false|string
     */
    protected $hlog;

    /**
     * Callback function that is executed after the billing_process.php page
     * has rendered.
     *
     * @var callable
     */
    protected $onLogCompleteCallback;

    protected $cryptoGen;

    public function __construct()
    {
        $this->cryptoGen = new CryptoGen();

        if ($GLOBALS['billing_log_option'] == 1) {
            if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/edi/process_bills.log")) {
                $this->hlog = file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/edi/process_bills.log");
            }
            if ($this->cryptoGen->cryptCheckStandard($this->hlog)) {
                $this->hlog = $this->cryptoGen->decryptStandard($this->hlog, null, 'database');
            }
        } else { // ($GLOBALS['billing_log_option'] == 2)
            $this->hlog = '';
        }
    }

    public function setLogCompleteCallback(callable $onLogCompleteCallback)
    {
        $this->onLogCompleteCallback = $onLogCompleteCallback;
    }

    /**
     * Called when log is done writing
     *
     * @return false|mixed
     */
    public function onLogComplete()
    {
        // If the hlog isn't empty, write the log to disk
        if (!empty($this->hlog)) {
            if ($GLOBALS['drive_encryption']) {
                $this->hlog = $this->cryptoGen->encryptStandard($this->hlog, null, 'database');
            }
            file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/edi/process_bills.log", $this->hlog);
        }

        // If the generator set a callback function for when the log completes, call it here
        if (isset($this->onLogCompleteCallback)) {
            return call_user_func($this->onLogCompleteCallback);
        }

        return false;
    }

    public function printToScreen($message)
    {
        $this->bill_info[] = $message;
    }

    public function bill_info()
    {
        return $this->bill_info;
    }

    public function appendToLog($message)
    {
        // have the most recent claims on top in the log
        $this->hlog = $message . $this->hlog;
    }

    public function hlog()
    {
        return $this->hlog;
    }
}
