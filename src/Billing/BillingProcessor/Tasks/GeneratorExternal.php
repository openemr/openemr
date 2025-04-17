<?php

/**
 * This class represents the task that will write claims to an external
 * CSV file
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\GeneratorInterface;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;

class GeneratorExternal extends AbstractGenerator implements GeneratorInterface, LoggerInterface
{
    use WritesToBillingLog;

    protected $be;

    public function setup($context = null)
    {
        global $webserver_root;
        $EXPORT_INC = "$webserver_root/custom/BillingExport.php";
        if (file_exists($EXPORT_INC)) {
            include_once($EXPORT_INC);
            $BILLING_EXPORT = true;
        }
        $this->be = new \BillingExport();
    }

    public function generate(BillingClaim $claim)
    {
        // Writes this claim row to the csv file on disk
        $this->be->addClaim($claim->getPid(), $claim->getEncounter());
        return $this->clearClaim($claim);
    }

    public function completeToFile($context = null)
    {
        // Close external billing file.
        $this->be->close();
    }
}
