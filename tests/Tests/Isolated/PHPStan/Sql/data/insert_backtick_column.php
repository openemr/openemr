<?php

// Backticked reserved word in INSERT column list. Must not flag.
sqlStatement('INSERT INTO contact_telecom (id, contact_id, `rank`) VALUES (?, ?, ?)');
