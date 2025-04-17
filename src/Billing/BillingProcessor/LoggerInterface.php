<?php

/**
 * Interface that allows processing tasks to write to billing log.
 *
 * The default implementation is in the trait Trait\WritesToBillingLog
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor;

interface LoggerInterface
{
    public function getLogger();

    public function setLogger(BillingLogger $logger);

    public function printToScreen($message);

    public function appendToLog($message);
}
