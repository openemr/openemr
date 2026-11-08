-- Sent faxes were stored without a direction, so the column default made
-- them 'inbound'. insertSentFaxQueue() marks them TransactionType 1 in
-- details_json; correct those rows. Safe to run again.
UPDATE `oe_faxsms_queue`
   SET `direction` = 'outbound'
 WHERE `direction` = 'inbound'
   AND (CASE WHEN JSON_VALID(`details_json`) THEN JSON_VALUE(`details_json`, '$.TransactionType') END) = '1';
