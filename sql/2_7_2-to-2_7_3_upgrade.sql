ALTER TABLE lists
  ADD begdate    date          DEFAULT NULL,
  ADD enddate    date          DEFAULT NULL,
  ADD occurrence int(11)       DEFAULT 0,
  ADD referredby varchar(255)  DEFAULT NULL,
  ADD extrainfo  varchar(255)  DEFAULT NULL;
