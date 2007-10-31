ALTER TABLE form_encounter
  ADD billing_note text NOT NULL DEFAULT '';

ALTER TABLE users
  ADD organization varchar(255) NOT NULL DEFAULT '',
  ADD valedictory  varchar(255) NOT NULL DEFAULT '';

ALTER TABLE openemr_postcalendar_events
  ADD pc_facility smallint(6) NOT NULL default '0' COMMENT 'facility id for this event';

ALTER TABLE payments
  ADD encounter bigint(20) NOT NULL DEFAULT 0,
  ADD KEY pid (pid);
