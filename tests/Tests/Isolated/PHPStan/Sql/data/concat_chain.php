<?php

// PHPStan resolves literal-string concatenation chains to a constant string.
// The rule should see the assembled SQL and flag the reserved word.
$sql = 'SELECT * FROM contact_telecom';
$sql .= ' WHERE contact_id = ?';
$sql .= ' ORDER BY rank ASC';
sqlStatement($sql);
