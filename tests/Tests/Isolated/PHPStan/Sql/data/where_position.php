<?php

// Reserved word `rank` referenced in a WHERE condition. Must flag.
sqlStatement('SELECT * FROM contact_telecom WHERE rank > 5');
