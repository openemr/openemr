<?php

// `rank` appears in both SELECT and ORDER BY positions. v1 only inspects
// ORDER BY, so we expect a single flag for the trailing occurrence (and
// for the FROM-clause `groups` reference to remain silent).
sqlStatement('SELECT rank FROM groups ORDER BY rank');
