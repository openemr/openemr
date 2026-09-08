<?php

// `over_field` is not a reserved word; even though `over` IS reserved,
// the lexer tokenizes `over_field` as one identifier. The (reserved ∩
// schema-identifier) gate eliminates the false positive either way:
// `over` is reserved but not in the test schema's identifier set.
// Must not flag.
sqlStatement('SELECT over_field FROM contact_telecom');
