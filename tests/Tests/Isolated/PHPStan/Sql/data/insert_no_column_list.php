<?php

// INSERT without an explicit column list -- nothing to walk, must not flag.
sqlStatement('INSERT INTO contact_telecom VALUES (1, 2, 3, 4, 5, 6, 7, 8)');
