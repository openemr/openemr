<?php

// SQL string the rule can't resolve to a constant — silently skip rather
// than guess. Must not flag.

/** @var string $column */
$column = '';

sqlStatement('SELECT ' . $column . ' FROM contact_telecom WHERE contact_id = ?');
