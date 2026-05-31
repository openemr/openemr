<?php

// Reserved word `rank` referenced in a JOIN ON condition (table-qualified).
// Must flag — the rule strips the table alias and checks the column part.
sqlStatement('SELECT * FROM contact_telecom a JOIN contact_telecom b ON a.rank = b.id');
