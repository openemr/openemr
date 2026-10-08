<?php

declare(strict_types=1);

// Status in SET, pc_time only in WHERE. Must flag.
sqlStatement('UPDATE openemr_postcalendar_events SET pc_apptstatus = ? WHERE pc_time = ?');
