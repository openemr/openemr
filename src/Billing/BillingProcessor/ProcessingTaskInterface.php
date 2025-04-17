<?php

/**
 * Interface that all processing tasks must implement
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor;

interface ProcessingTaskInterface
{
    public function setup(array $context);

    public function execute(BillingClaim $claim);

    public function complete(array $context);
}
