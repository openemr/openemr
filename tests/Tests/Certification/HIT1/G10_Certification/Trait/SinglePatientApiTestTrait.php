<?php

/*
 * SinglePatientApiTestTrait.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Certification\HIT1\G10_Certification\Trait;

use OpenEMR\Tests\Api\ApiTestClient;

trait SinglePatientApiTestTrait
{
    // Alice Jones (96506861-511f-4f6d-bc97-b65a78cf1995),
    // Jeremy Bates (96891ab2-01ad-49f9-9958-cdad71bd33c1),
    // Happy Child(968944d0-180a-48ac-8049-636ae8ac6127),
    // and John Appleseed (969f72c3-0256-488e-b25b-8fff3af18b1b)
    // are the patients used in the Inferno Single Patient API tests.
    const PATIENT_IDS = '96506861-511f-4f6d-bc97-b65a78cf1995,96891ab2-01ad-49f9-9958-cdad71bd33c1,968944d0-180a-48ac-8049-636ae8ac6127,969f72c3-0256-488e-b25b-8fff3af18b1b';
    const PATIENT_ID_PRIMARY = '96506861-511f-4f6d-bc97-b65a78cf1995';

    const DEFAULT_OPENEMR_BASE_URL_API = 'http://openemr';

    private static ApiTestClient $testClient;


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: self::DEFAULT_OPENEMR_BASE_URL_API;
        self::$testClient = new ApiTestClient($baseUrl, false);
        self::$testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }
}
