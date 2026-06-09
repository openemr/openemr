<?php

// `rank` appears only inside a single-quoted string literal value, not as
// an identifier. The SQL lexer correctly classifies it as a String token.
// Must not flag.
sqlStatement("SELECT * FROM contact_telecom WHERE value = 'rank'");
