<?php

// INSERT ... ON DUPLICATE KEY UPDATE: the column-list walker should
// flag the bare `rank` inside the (...) list and then exit cleanly at
// the closing `)`, leaving the ON DUPLICATE KEY UPDATE clause alone.
// Currently the walker doesn't analyse update-list assignments after
// the column list -- documented limit.
sqlStatement('INSERT INTO contact_telecom (id, contact_id, rank) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
