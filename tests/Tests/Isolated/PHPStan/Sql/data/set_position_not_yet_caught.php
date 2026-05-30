<?php

// UPDATE ... SET <col> = ... is a valid identifier position, but v1 of the
// rule only inspects ORDER BY / GROUP BY / PARTITION BY positions to stay
// false-positive-free without a real SQL parser. This fixture documents
// the current limit — must not flag (yet).
sqlStatement('UPDATE contact_telecom SET rank = 5 WHERE id = ?');
