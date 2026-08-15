<?php

namespace OpenEMR\Tests\Rector\Fixture;

use Doctrine\DBAL\Exception\DriverException as Exception;

try {
    doSomething();
} catch (Exception $e) {
    handleError($e);
}

?>
