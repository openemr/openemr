<?php

// Already backticked — must not flag.
sqlStatement('SELECT * FROM contact_telecom ORDER BY `rank` ASC');
