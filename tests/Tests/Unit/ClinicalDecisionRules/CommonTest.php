<?php

namespace OpenEMR\Tests\Unit\ClinicalDecisionRules;

use OpenEMR\ClinicalDecisionRules\Interface\Common;
use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
{
    /**
     * Test for implode_funcs method
     */
    public function testImplodeFuncs()
    {
        $pieces = ['apple', 'banana', 'cherry'];
        $funcs = [
            function ($value) {
                return strtoupper($value);
            },
            function ($value) {
                return substr($value, 0, 3);
            }
        ];
        $result = Common::implode_funcs('!', $pieces, $funcs);
        $this->assertEquals('APP!BAN!CHE', $result);
    }

    /**
     * Test for get method
     */
    public function testGet()
    {
        $_GET['testVar'] = 'testValue';
        $result = Common::get('testVar', 'defaultValue');
        $this->assertEquals('testValue', $result);

        $result = Common::get('nonExistentVar', 'defaultValue');
        $this->assertEquals('defaultValue', $result);
    }

    /**
     * Test for post method
     */
    public function testPost()
    {
        $_POST['testVar'] = 'testValue';
        $result = Common::post('testVar', 'defaultValue');
        $this->assertEquals('testValue', $result);

        $result = Common::post('nonExistentVar', 'defaultValue');
        $this->assertEquals('defaultValue', $result);
    }

    /**
     * Test for base_url method
     */
    public function testBaseUrl()
    {
        $webroot = $GLOBALS['webroot'];
        $result = Common::base_url();
        $this->assertEquals($webroot . '/interface/super/rules', $result);
    }

    /**
     * Test for src_dir method
     */
    public function testSrcDir()
    {
        $srcdir = $GLOBALS['srcdir'];
        $result = Common::src_dir();
        $this->assertEquals($srcdir, $result);
    }

    /**
     * Test for base_dir method
     */
    public function testBaseDir()
    {
        $rootdir = $GLOBALS['incdir'];
        $result = Common::base_dir();
        $this->assertEquals($rootdir . '/super/rules/', $result);
    }

    /**
     * Test for library_dir method
     */
    public function testLibraryDir()
    {
        $rootdir = $GLOBALS['incdir'];
        $result = Common::library_dir();
        $this->assertEquals($rootdir . '/super/rules/library', $result);
    }

    /**
     * Test for library_src method
     */
    public function testLibrarySrc()
    {
        $rootdir = $GLOBALS['incdir'];
        $result = Common::library_src('somefile.php');
        $this->assertEquals($rootdir . '/super/rules/library/somefile.php', $result);
    }
}
