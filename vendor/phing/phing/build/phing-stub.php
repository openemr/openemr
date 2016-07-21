#!/usr/bin/env php
<?php

try {
    Phar::mapPhar('phing.phar');
    include 'phar://phing.phar/bin/phing.php';
} catch (PharException $e) {
    echo $e->getMessage();
    echo 'Cannot initialize Phar';
    exit(1);
}

__HALT_COMPILER();
