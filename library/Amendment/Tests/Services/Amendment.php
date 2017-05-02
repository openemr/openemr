<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 4/28/17
 * Time: 19:41
 */

namespace OpenEMR\Amendments\Tests\Services;

$GLOBALS['OE_SITE_DIR']="/srv/openemr/sites/default";

require_once "../../../../vendor/autoload.php";
require_once("../../../sql.inc");

use PHPUnit\Framework\TestCase;
use OpenEMR\Amendment\Service\Amendment;

class AmendmentTest extends TestCase
{

    /** @var  Amendment */
    protected $amend;

    public function setUp()
    {
        parent::setUp();
        $this->amend = new Amendment();
    }

    public function testCanGetAmendment()
    {
        $id = "1";
        $result = $this->amend->get($id);

        $this->assertArrayHasKey('history', $result);
        $this->assertArrayHasKey('amendment_id', $result);

    }

    public function testEnsureGettingNonexistentAmendmentFails()
    {
        $this->amend->get("9");
        $this->expectExceptionCode('AmendmentNotFound');
    }

}
