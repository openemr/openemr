<?php

declare(strict_types=1);

// Status-only UPDATE without pc_time. Must flag.
sqlStatement('UPDATE openemr_postcalendar_events SET pc_apptstatus = ? WHERE pc_eid = ?');
