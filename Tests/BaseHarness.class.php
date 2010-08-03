<?php
/* Copyright © 2010 by Andrew Moore */
/* Licensing information appears at the end of this file. */

error_reporting(E_ALL);
require_once 'PHPUnit/Framework.php';
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../library');
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../library/classes');
require_once 'Installer.class.php';

class BaseHarness extends PHPUnit_Framework_TestCase
{
    public static function get_installer()
    {
      $fixture_cgi = array( 'login'           => 'test_login',
                            'iuser'           => 'test_iuser',
                            'iuname'          => 'test_iuname',
                            'igroup'          => 'test_igroup',
                            'pass'            => 'test_pass',
                            'server'          => 'localhost',
                            'port'            => '',
                            'root'            => 'root',
                            'rootpass'        => getenv('EMR_ROOT_DATABASE_PASSWORD'),
                            'dbname'          => 'openemr_test_suite',
                            'collate'         => 'utf8_general_ci',
                            'openemrBasePath' => '',
                            'openemrWebPath'  => '',
                            );
        return new Installer( $fixture_cgi, '' );
    }


    public static function setUpBeforeClass()
    {
        // session_start();
        $_SESSION['authUser']  = 'tester';
        $_SESSION['authGroup'] = 'testgroup';

        $GLOBALS = array( 'enable_auditlog' => '0',
                          );
        $_SERVER['REQUEST_URI'] = '';
        $_SERVER['SERVER_NAME'] = '';

        $ignoreAuth = 1;

        $installer = self::get_installer();
        if ( ! $installer->quick_install() ) {
          echo $installer->error_message;
          exit;
        }
        require_once 'translation.inc.php';
        require_once 'globals.inc.php';
        require_once 'interface/globals.php';
        require_once "$srcdir/sql.inc";
        require_once "$srcdir/options.inc.php";

        $_SESSION['authUser']  = 'tester';
        $_SESSION['authGroup'] = 'testgroup';
    }

    public static function tearDownAfterClass()
    {
        $installer = self::get_installer();
        $installer->drop_database();
    }
}

/*
This file is free software: you can redistribute it and/or modify it under the
terms of the GNU General Public License as publish by the Free Software
Foundation.

This file is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU Gneral Public License for more details.

You should have received a copy of the GNU General Public Licence along with
this file.  If not see <http://www.gnu.org/licenses/>.
*/
?>
