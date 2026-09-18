<?php

try {
    doSomething();
} catch (Doctrine\DBAL\Exception $e) {
    handleError($e);
}

?>
