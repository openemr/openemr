<?php

// String contains "ORDER BY rank" but the function is not a SQL sink.
// Must not flag.
error_log('SELECT * FROM contact_telecom ORDER BY rank ASC');
