<?php

/* Copyright © 2010 by Andrew Moore <amoore@cpan.org> */
/* Licensing information appears at the end of this file. */
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../../library/classes');
require_once 'PHPUnit/Framework.php';
require_once 'Installer.class.php';

class InstallerTest extends PHPUnit_Framework_TestCase
{
    protected $installer;
    protected $post_variables;

    protected function setUp()
    {
        $this->post_variables = array( 'login'           => 'boris',
                                   'iuser'           => 'initialuser',
                                   'iuname'          => 'initialusername',
                                   'igroup'          => 'initialgroup',
                                   'pass'            => 'validpassword',
                                   'server'          => 'localhost',
                                   'loginhost'       => 'localhost',
                                   'port'            => '3306',
                                   'root'            => 'root',
                                   'rootpass'        => 'notapass',
                                   'dbname'          => 'openemr_test_suite',
                                   'collate'         => '',
                   'site'            => 'default',
                                   );
        $this->installer = new Installer($this->post_variables);
    }

    public function testAttributes()
    {
        foreach ($this->post_variables as $attribute => $value) {
            $this->assertEquals($value, $this->installer->$attribute, "fetching $attribute from Installer object");
        }
    }

    public function testFilePaths()
    {
        $this->assertFileExists($this->installer->conffile);
    }

  /**
   * @dataProvider loginValidatorData
   */
    public function testLoginValidator($login, $expected_return)
    {
        $post_variables = $this->post_variables;
        $post_variables['login'] = $login;
        $installer = new Installer($post_variables);
        $this->assertEquals($expected_return, $installer->login_is_valid(), "testing login: '$login'");
    }

  /* dataProvider for testLoginValidator */
    public static function loginValidatorData()
    {
        return array( array( 'boris', true ),
                  array( '',      false )
                  );
    }

    public function testIuserValidator()
    {
        $this->assertEquals(true, $this->installer->iuser_is_valid());
    }

    public function testIuserIsInvalid()
    {
        $post_variables = $this->post_variables;
        $post_variables['iuser'] = 'initial user';
        $installer = new Installer($post_variables);

        $this->assertEquals(false, $installer->iuser_is_valid());
    }

    public function testPasswordValidator()
    {
        $this->assertEquals(true, $this->installer->password_is_valid());
    }

    public function testPasswordIsInvalid()
    {
        $post_variables = $this->post_variables;
        $post_variables['pass'] = '';
        $installer = new Installer($post_variables);

        $this->assertEquals(false, $installer->password_is_valid());
    }

    public function testRootDatabaseConnection()
    {
        $this->assertEquals(true, $this->installer->root_database_connection(), 'creating root database connection');
    }

    public function testUserDatabaseConnection()
    {
        $rootdbh = $this->installer->root_database_connection();
        $this->installer->drop_database(); // may or may not exist.
        $this->assertEquals(true, $this->installer->create_database(), 'creating user database');
        $this->assertEquals(true, $this->installer->grant_privileges(), 'granting privileges');
        $this->assertEquals(true, $this->installer->user_database_connection(), 'creating user database connection');
    }

    public function testDumpfiles()
    {
        $rootdbh = $this->installer->root_database_connection();
        $this->installer->drop_database(); // may or may not exist.
        $this->assertEquals(true, $this->installer->create_database(), 'creating user database');
        $this->assertEquals(true, $this->installer->grant_privileges(), 'granting privileges');
        $this->assertEquals(true, $this->installer->user_database_connection(), 'creating user database connection');
        $dumpresults = $this->installer->load_dumpfiles();
        $this->assertEquals(true, $dumpresults, 'installing dumpfiles');
    }

  // public function testGacl()
  // {
  //   $rootdbh = $this->installer->root_database_connection();
  //   $this->installer->drop_database(); // may or may not exist.
  //   $this->assertEquals(TRUE, $this->installer->create_database(), 'creating user database' );
  //   $this->assertEquals(TRUE, $this->installer->grant_privileges(), 'granting privileges' );
  //   $this->assertEquals(TRUE, $this->installer->user_database_connection(), 'creating user database connection' );
  //   $dumpresults = $this->installer->load_dumpfiles();
  //   $this->assertEquals(TRUE, $dumpresults, 'installing dumpfiles' );
  //   $user_results = $this->installer->add_initial_user();
  //   $this->installer->install_gacl();
  //   $this->installer->configure_gacl();
  // }

    public function testConfFile()
    {
        $this->assertEquals(true, $this->installer->write_configuration_file(), 'wrote configuration file');
    }

    public function tearDown()
    {
        $installer = $this->installer;
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
