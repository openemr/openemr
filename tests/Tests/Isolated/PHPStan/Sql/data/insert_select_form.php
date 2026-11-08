<?php

// INSERT INTO ... SELECT (no explicit column list after the table).
// The column-list walker should bail at depth 0 when it sees the
// SELECT keyword instead of `(`, and not yield anything. The inner
// SELECT body might still contain bare reserved-word column refs;
// those are handled by the SelectStatement walker if the parser
// surfaces them, not by the INSERT walker.
sqlStatement('INSERT INTO contact_telecom SELECT 1, 2, 3, 4, 5, 6, 7, 8');
