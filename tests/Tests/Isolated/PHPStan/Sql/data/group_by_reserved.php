<?php

// Reserved word `groups` in GROUP BY position. Must flag.
sqlStatement('SELECT COUNT(*) FROM contact_telecom GROUP BY groups');
