<?php

declare(strict_types=1);

// INSERT column lists mention pc_apptstatus but are not status updates.
sqlInsert(
    'INSERT INTO openemr_postcalendar_events (pc_catid, pc_apptstatus, pc_time) VALUES (?, ?, NOW())'
);
