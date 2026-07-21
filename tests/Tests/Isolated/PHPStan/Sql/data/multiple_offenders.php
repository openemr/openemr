<?php

// `rank` appears in SELECT and ORDER BY; `groups` appears as the table
// reference. Each unique offender must be reported exactly once per call,
// not once per occurrence.
sqlStatement('SELECT rank FROM groups ORDER BY rank');
