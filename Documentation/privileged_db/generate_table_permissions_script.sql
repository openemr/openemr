SELECT   CONCAT('GRANT ALL ON ', 'openemr.',TABLE_NAME, ' to ''openemr''@''localhost'';')
FROM     INFORMATION_SCHEMA.TABLES
WHERE    TABLE_SCHEMA = 'openemr' and NOT TABLE_NAME='users_secure';