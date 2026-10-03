<?php

declare(strict_types=1);

// Status UPDATE that also advances pc_time. Must stay silent.
sqlStatement('UPDATE openemr_postcalendar_events SET pc_apptstatus = ?, pc_time = NOW() WHERE pc_eid = ?');
