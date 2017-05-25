<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 5/25/17
 * Time: 02:02
 */



use OpenEMR\Core\Header;

class HeaderTest extends PHPUnit_Framework_TestCase
{

    public function testCanIncludeAsset()
    {
        $result = Header::includeAsset('test');
        var_dump($result);
    }

}
