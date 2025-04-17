<?php

  /**
   * Pre-requisites: phpunit, phpunit-selenium, selenium-standalone-server, chrome driver, php-curl extension
   *
   *
   * @Matrix Israel Ltd.
   */

require_once __DIR__ . '/../../../vendor/autoload.php';

class CheckCreateUserTest extends PHPUnit_Extensions_Selenium2TestCase
{
    const BROWSER = "chrome";
    const BROWSER_URL = "http://localhost/openemr";
    const URL = "http://localhost/openemr/interface/login/login.php?site=default";
    const VAR_AUTHUSER = "admin";
    const VAR_PASS = "pass";

    private $dbconn;

    protected function setUp()
    {
        $this->setBrowser(self::BROWSER);
        $this->setBrowserUrl(self::BROWSER_URL);
    }
    protected function tearDown()
    {
        parent::tearDown();
    }


    /**
     * Generate random names and numbers
     */
    public function generateTestData()
    {
        $name = $this->generateRandomString();
        $lname = $this->generateRandomString();
        $dob = $this->generateRandomDate();
        $randint = rand(100000, 200000);

        return array( 'name' => $name, 'lname' => $lname, 'dob' => $dob, 'randint' => $randint );
    }


   /**
    * Tests Add Patient to openEMR
    */
    public function testAddPatient()
    {
        $testset = $this->generateTestData();

        /*connect to openemr*/
        $this->url(self::URL);
        /*Move to frame Login and add login values*/
        $this->frame("Login");
        $this->byName('authUser')->value(self::VAR_AUTHUSER);
        $this->byName('clearPass')->value(self::VAR_PASS);
        $sumbmitClick = $this->byClassName("button");
        $sumbmitClick->click();

        /*Check that the login was succesfull coparing the title from the page*/
        $this->assertEquals('OpenEMR', $this->title(), "Login Failed");

        /*Move to frame left nav and click on new patient*/
        $this->frame("left_nav");
        $newPatientLink = $this->byId('new0');
        $newPatientLink->click();
        $this->frame(null);
        $this->frame("RTop");


       /*Fill the form  and submit it*/
        $this->byName('form_fname')->value($testset['name']);
        $this->byName('form_lname')->value($testset['lname']);
        $this->byName('form_DOB')->value($testset['dob']);
        $this->byName('form_ss')->value($testset['randint']);

        $this->select($this->byId('form_title'))->selectOptionByValue("Mr.");
        $this->select($this->byId('form_sex'))->selectOptionByValue("Male");
        $createLink = $this->byName('create');
        $createLink->click();

       /*Move to the popup and click on create patient*/
        $handles = $this->windowHandles();
        $this->window($handles[1]);
        $createButton = $this->byXPath("//input[@type='button']");
        $createButton->click();
        sleep(2);

       /*Accept the alert when creating a new patient*/
        $this->window($handles[0]);
        $this->acceptAlert();
        sleep(1);
        return $testset;
    }



    /**
     * Check that the new patient exist in the database
     *
     * @depends testAddPatient
     */
    public function testFindPatient($testset)
    {
        $this->database_connection();
        $sql = sprintf("SELECT * FROM patient_data where fname='%s' and lname='%s' and DOB = '%s' ", $testset['name'], $testset['lname'], $testset['dob']);
        $res = $this->dbconn->query($sql);
        $numRows = mysqli_num_rows($res);
        $this->assertEquals(1, $numRows, "Patient doesn't exists in the database");
    }

    /**
     * Generate random string used for names
     * @param int $length
     * @return string
     */
    private function generateRandomString($length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    /**
     * Generate random date in the past
     * @return bool|string
     */
    private function generateRandomDate()
    {
        $int = rand(1100000000, 1262055681);
        $string = date("Y-m-d", $int);
        return $string;
    }

    /**
     * connect to OpenEMR database
     */
    private function database_connection()
    {
        require_once(__DIR__ . "/../../sites/default/sqlconf.php");

      // Create connection
        $this->dbconn = new mysqli($sqlconf['host'], $sqlconf['login'], $sqlconf['pass'], $sqlconf['dbase']);
    }
}
