<?php

// Column list mixing bare reserved (`rank`), bare non-reserved (`id`),
// and properly backticked (`use`) -- only `rank` should flag.
sqlStatement('INSERT INTO contact_telecom (id, contact_id, rank, `use`) VALUES (?, ?, ?, ?)');
