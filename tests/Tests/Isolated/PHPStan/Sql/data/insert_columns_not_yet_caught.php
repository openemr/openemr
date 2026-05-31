<?php

// INSERT INTO foo (col, ...) — phpmyadmin/sql-parser strips backticks from
// the column list before exposing it on IntoKeyword::$columns, so the rule
// can't tell whether columns were already backticked from the parse tree
// alone. Deferred to a future iteration; documented here so the gap is
// explicit. Must not flag (yet).
sqlStatement('INSERT INTO contact_telecom (id, contact_id, rank) VALUES (?, ?, ?)');
