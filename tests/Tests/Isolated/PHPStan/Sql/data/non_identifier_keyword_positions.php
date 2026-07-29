<?php

// Reserved words like INTERVAL and TABLE appear here as SQL keywords in
// positions where the grammar treats them as keywords, not identifiers.
// v1 doesn't analyze these positions, so no false positives fire.
sqlStatement('SELECT DATE_ADD(NOW(), INTERVAL 7 DAY) FROM contact_telecom');
sqlStatement('CREATE TABLE foo (id INT)');
