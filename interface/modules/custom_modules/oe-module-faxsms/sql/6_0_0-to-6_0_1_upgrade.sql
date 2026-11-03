-- The fax list (EtherFaxActions::fetchFaxQueue) filters on receive_date alone.
-- Without this index it scans every row, reading each stored fax image
-- (details_json) on the way; with a large queue that takes tens of seconds.
#IfNotIndex oe_faxsms_queue receive_date
ALTER TABLE `oe_faxsms_queue` ADD INDEX `receive_date` (`receive_date`);
#EndIf
