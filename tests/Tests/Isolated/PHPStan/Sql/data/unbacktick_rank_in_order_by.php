<?php

// Reserved word `rank` used unbackticked as a column. Must flag.
sqlStatement('SELECT * FROM contact_telecom WHERE contact_id = ? ORDER BY rank ASC');
