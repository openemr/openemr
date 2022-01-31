<?php

/**
 * Sphere class.
 *  A static class that contains constants used by the other Sphere classes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PaymentProcessing\Sphere;

class Sphere
{
    // Sphere payment processing urls for clinic front (Trustee Premier for MOTO and RETAIL)
    public const CLINICFRONT_PRODUCTION_URL = 'https://tcpkb.trustcommerce.com/trustcommerce_payment/index.php';
    public const CLINICFRONT_TESTING_URL = 'https://stagetcpkb.trustcommerce.com/trustcommerce_payment/index.php';

    // Sphere payment processing urls for patient front (Trustee Premier for Ecomm)
    public const PATIENTFRONT_PRODUCTION_URL = 'https://premier.trustcommerce.com/trustcommerce_payment/index.php';
    public const PATIENTFRONT_TESTING_URL = 'https://stagepremier.trustcommerce.com/trustcommerce_payment/index.php';

    // Sphere void/credit processing url (Trustee API)
    public const TRUSTEE_API_URL = 'https://vault.trustcommerce.com/trusteeapi/';

    // Partner Registry Key for OpenEMR
    public const AGGREGATOR_ID = "21AA1ES";
}
