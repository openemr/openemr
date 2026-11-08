<?php

use Doctrine\DBAL\Exception;

try {
    doSomething();
} catch (Exception $e) {
    handleError($e);
}

?>
