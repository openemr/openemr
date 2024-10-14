<?php

/**
 * CcCreatePatientTest class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Patient\PatientAddTrait;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class CcCreatePatientTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;
    use PatientAddTrait;

    private $client;
    private $crawler;
}
