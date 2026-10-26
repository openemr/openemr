<?php

declare(strict_types=1);

// pc_apptstatus only in WHERE. Must stay silent.
sqlStatement('UPDATE openemr_postcalendar_events SET pc_room = ? WHERE pc_apptstatus = ?');
