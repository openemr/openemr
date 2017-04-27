<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 4/23/17
 * Time: 18:03
 */

namespace OpenEMR\Encounter\Tests;

require_once '../../../vendor/autoload.php';
//require_once '../../../../globals.php';

use OpenEMR\Encounter\Service\ViewHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class ViewHelperTest
 * @package OpenEMR\Encounter\Tests
 * @covers ViewHelper;
 */
final class ViewHelperTest extends TestCase
{

    public function testCanGetListOfRegisteredMenuItems()
    {
        $this->assertInstanceOf(
            ViewHelper::class,
            ViewHelper::myGetRegistered()
        );
    }
}
