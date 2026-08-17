<?php

// MySQL accepts reserved words as identifiers after a `.` without
// quoting -- only bare unqualified references fail to parse. The rule
// must skip every qualified form, regardless of position.
sqlStatement('SELECT p.rank FROM contact_telecom p');
sqlStatement('SELECT id FROM contact_telecom p WHERE p.rank > 5');
sqlStatement('SELECT id FROM contact_telecom p ORDER BY p.rank');
sqlStatement('UPDATE contact_telecom p SET p.rank = 5 WHERE p.id = 1');
sqlStatement('SELECT a.id FROM contact_telecom a JOIN contact_telecom b ON a.rank = b.id');
