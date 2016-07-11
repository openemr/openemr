# Selenium + PHPUnit Test OpenEMR

Author: Sharon Cohen, Oleg Sverdlov


This is a guide on how to install and run a simple selenium test (using phpunit) to the Open EMR system.

This is just a sample test (and simple one).

The example test includes:

* Open the browser and go to local OpenEMR installation
* Login with default admin credentials
* Go to New patient form
* Add a new patient with random names(string) and DOB
* Accept the creation of the new patient
* Check the database that this patient exist in the table patient_data
* Close the  browser

### Things to improve:

To have a complete set of test they should include:

* Login failure
* Validation test when creating a new patient
* Checking the existence/inexistence of clinical reminders according to personal configuration 

## Installation:

The test works for OpenEMR installed on local computer. You'll need Chrome browser :)
(Note that with a simple code change, there is also the option of using the Firefox browser)

### Install Composer and packages

If you don't have Composer refer to https://getcomposer.org/download/

From OpenEMR folder install phpunit and phpunit-selenium via composer

    composer require --dev phpunit/phpunit
    composer require --dev phpunit/phpunit-selenium

(depending on how you installed Composer you may need to run `php composer.phar` )

### Install Browser driver

To use Chrome as a test Browser download the driver from here: https://sites.google.com/a/chromium.org/chromedriver/downloads

Move the downloaded file to an executable directory

    sudo mv chromedriver /usr/local/bin/ 

    sudo chmod 755 /usr/local/bin/chromedriver


### Install Selenium server

Download standalone selenium server from here: http://docs.seleniumhq.org/download/

Download and install JRE. You can get JRE from java.com or from your Linux distro repositories. Check you can run it: 

    java -version


## Running tests

*never run the tests on production database!

First, run Selenium server in separate process

    java -jar selenium-server-standalone-<version>.jar

Then, run the test:

    ./vendor/bin/phpunit Tests/selenium/CheckCreateUser.php

## References:

    https://getcomposer.org/download/

    https://phpunit.de/manual/current/en/installation.html

    https://sites.google.com/a/chromium.org/chromedriver/downloads

    http://docs.seleniumhq.org/download/

    https://www.sitepoint.com/using-selenium-with-phpunit/

    https://phpunit.de/manual/3.7/en/selenium.html

    https://phpunit.de/manual/current/en/database.html


