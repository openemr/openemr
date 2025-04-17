-- Created By ViCarePlus, Visolve (vicareplus_engg@visolve.com)
---------------------------------------------------------------
---------------------------------------------------------------

-- Procedure for de-identification
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists `de_identification`;
$

$
CREATE PROCEDURE `de_identification`()
BEGIN
#Run the de-identification process. 
DECLARE unknown_table_name INT DEFAULT 0;
DECLARE unknown_col_name INT DEFAULT 0;
DECLARE unknown_prepare_stmt INT DEFAULT 0;
DECLARE table_already_exists INT DEFAULT 0;
DECLARE CONTINUE HANDLER FOR 1146 SET unknown_table_name = 1;
DECLARE CONTINUE HANDLER FOR 1054 SET unknown_col_name = 1;
DECLARE CONTINUE HANDLER FOR 1243 SET unknown_prepare_stmt = 1;
DECLARE CONTINUE HANDLER FOR 1050 SET table_already_exists = 1;

#Create the transaction_metadata_de_identification table, which contains the tables/columns to include in the report, and whether the table/column needs to be de-identified or not. 
call load_transaction_metadata_de_identification_table();

#Create an empty de_identified_data table, which will contain the complete,de-identified data once this process is finished. 
call create_de_identified_data_table();

#Filter the patients to include in the report, based on the drugs,immunizations, and diagnosis selected. 
call filter_pid();

#For each patient, and table/column name to include in the report,select the data from the appropriate tables, and insert into the de_identified_data table.  Skip any tables/columns containing identifiers (names, telephone, etc). 
call perform_de_identification();

#Handle error conditions
IF table_already_exists = 1 THEN
insert into de_identification_error_log values("de-identification",CURRENT_TIMESTAMP(), "when create table, table already exists");
update de_identification_status set status = 3;
END IF;
IF unknown_prepare_stmt = 1 THEN
insert into de_identification_error_log values("de-identification",CURRENT_TIMESTAMP(), "Unkown prepare statement");
update de_identification_status set status = 3;
END IF;
IF unknown_col_name = 1 THEN
insert into de_identification_error_log values("de-identification",CURRENT_TIMESTAMP(), "Unkown column name");
update de_identification_status set status = 3;
END IF;
IF unknown_table_name = 1 THEN
insert into de_identification_error_log values("de-identification",CURRENT_TIMESTAMP(), "Unkown table name");
update de_identification_status set status = 3;
END IF;

#If no error set status as De-identification process completed
update de_identification_status set status = 2 where status != 3;

#Drop empty columns in the final De-identified data
call drop_no_value_column();

#Drop transaction table created from De-identification process
call drop_transaction_tables();
END
$ 

-- --------------------------------------------------------

-- Procedure to create transaction tables
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists `create_transaction_tables`;
$

$
CREATE PROCEDURE `create_transaction_tables`()
BEGIN
#Create transaction tables needed for de_identification process

#transaction_metadata_de_identification : Tells which tables/columns need to be de-identified
#temp_patient_id : The list of patients to include in this report.
#temp_re_identification : Contains a re-identification code for each patient.
#temp_patient_record_id : A temporary table, contains the primary id of the record corresponding to a patient.
#param_include_tables : Contains the tables/columns to include in this report.
#param_filter_pid : Contains the drugs/immunizations/diagnosis for filtering which patients to include 

DROP TABLE IF EXISTS transaction_metadata_de_identification;
CREATE TABLE transaction_metadata_de_identification (table_name varchar(255) NOT NULL,col_name varchar(255) NOT NULL, load_to_lexical_table tinyint(1) NOT NULL,include_in_de_identification int(2) NOT NULL,include_in_re_identification tinyint(1) NOT NULL);
DROP TABLE IF EXISTS temp_patient_id_table;
create table temp_patient_id_table (pid varchar(10));
DROP TABLE IF EXISTS temp_re_identification_code_table;
create table temp_re_identification_code_table (re_identification_code varchar(50));
DROP TABLE IF EXISTS temp_patient_record_id;
create table temp_patient_record_id(number int auto_increment, id int not null, key(number));
DROP TABLE IF EXISTS param_include_tables;
create table param_include_tables(value varchar(500),include_unstructured boolean);
DROP TABLE IF EXISTS param_filter_pid;
create table param_filter_pid(begin_date date, end_date date, diagnosis_text varchar(500), drug_text varchar(500), immunization_text varchar(500));
END
$

-- --------------------------------------------------------

-- Procedure to load data to lexical look up table
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists `load_lexical_look_up_table`;
$

$
CREATE PROCEDURE `load_lexical_look_up_table`()
BEGIN
#Populate lexical look up table with 18 unique identifiers specified by HIPAA as identifying data from openemr database

#The lexical_look_up_table is used to store the text of known patient identifiers, such as patient names (John Smith), telephone numbers (408-111-222), etc.  Later on, during the identification process, these text snippets will be removed from unstructured data, such as patient notes. 

DECLARE tableName VARCHAR(255) ;
DECLARE colName VARCHAR(255) ;
DECLARE done INT DEFAULT 0;
declare out_status varchar(20);
DECLARE cur1 CURSOR FOR SELECT table_name,col_name FROM metadata_de_identification where  load_to_lexical_table = 1;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
DECLARE CONTINUE HANDLER FOR 1062 SET out_status='Duplicate Entry';
  OPEN cur1;
  FETCH cur1 INTO tableName, colName;
  WHILE done = 0  do
SET @v = CONCAT("insert into lexical_look_up_table (lex_text) select ",colName," from ", tableName);
    PREPARE stmt1 FROM @v;
    EXECUTE stmt1;
FETCH cur1 INTO tableName, colName;
  end WHILE;
  CLOSE cur1;
  update lexical_look_up_table set lex_text = LOWER(lex_text);
  delete from lexical_look_up_table where char_length(lex_text) <= 1;
END 
$

-- --------------------------------------------------------

-- Procedure to load data for transaction metadata de-identification
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists `load_transaction_metadata_de_identification_table`;
$

$
CREATE PROCEDURE `load_transaction_metadata_de_identification_table`()
BEGIN

#The param_include_tables contains the tables/columns that will be used in this report.
#The metadata_de_identification table tells which tables/columns need to be de-identified.
#Populate the transaction_metadata_de_identification table with the same information as the metadata_de_identification table, except only include the tables/columns that are included in this data report. 

#Include_tables contains string of table names separated by '#', like "history_data#prescriptions#"
#Loop through each table name by getting the substring delimited by '#'. 
declare nowords int;
declare subString varchar(255);
declare include_tables varchar(500);
declare includeUnstructured int;
select value into include_tables from param_include_tables;
select include_unstructured into includeUnstructured from param_include_tables;
delete from transaction_metadata_de_identification;
#In parameter individual values are separated by '#'  
SET include_tables = LTRIM(include_tables);
SET include_tables = RTRIM(include_tables);
IF include_tables = "all" THEN
insert into transaction_metadata_de_identification (table_name,col_name,include_in_de_identification) select table_name, col_name, include_in_de_identification from metadata_de_identification where table_name = "patient_data" || table_name = "history_data" || table_name = "lists" || table_name = "immunizations" || table_name = "prescriptions" || table_name = "transactions" || table_name = "insurance_data" || table_name = "billing" || table_name = "payments";
ELSE
SET noWords=LENGTH(include_tables) - LENGTH(REPLACE(include_tables, '#', '')) + 1;
SET include_tables = CONCAT(include_tables,'#');
insert into transaction_metadata_de_identification (table_name,col_name,include_in_de_identification) select table_name, col_name, include_in_de_identification from metadata_de_identification where table_name = "patient_data";
WHILE( noWords ) do
#Obtain individual value from the parameter
SET subString = SUBSTRING_INDEX( SUBSTRING_INDEX( include_tables, '#', noWords), '#', -1 );
SET subString = LTRIM(subString);
SET subString = RTRIM(subString);
insert into transaction_metadata_de_identification (table_name,col_name,include_in_de_identification) select table_name, col_name, include_in_de_identification from metadata_de_identification where table_name = subString;
set noWords = noWords -1;
end while;
END IF;
IF includeUnstructured = 0 THEN
update transaction_metadata_de_identification set include_in_de_identification = 0 where include_in_de_identification = 4;
ELSE

#Create a lexical_look_up_table, which contains text that should be removed from unstructured text data.

call load_lexical_look_up_table();
END IF;
END
$

-- --------------------------------------------------------

-- Procedure to create de-identified data table
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists `create_de_identified_data_table`;
$

$
CREATE PROCEDURE `create_de_identified_data_table`()
BEGIN

#This creates a table (de_identified_data) containing all the patient data to be included in the report.  Each table/column that is included in this report (such as history_data/tobacco) will have a corresponding column in the de_identified_data.  
#In addition, the de_identified_data table will have columns number, sub_number which contain the primary id of the table/column row where this data was read from.

DECLARE colName VARCHAR(255) ;
DECLARE newColName VARCHAR(255) ;
DECLARE tableName VARCHAR(255) ;
DECLARE done INT DEFAULT 0;
DECLARE duplicateColumn INT DEFAULT 0;
DECLARE cur1 CURSOR FOR SELECT col_name,table_name FROM transaction_metadata_de_identification where include_in_de_identification != 0;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
DECLARE CONTINUE HANDLER FOR 1060 SET duplicateColumn = 1;
drop table IF EXISTS de_identified_data;
create table de_identified_data (number INT, sub_number INT NOT NULL,re_identification_code varchar(255) NOT NULL);
OPEN cur1;
   FETCH cur1 INTO colName,tableName;
   WHILE (done = 0) do

SET @v = CONCAT("alter table de_identified_data add column `", colName, "` text not null");
    PREPARE stmt1 FROM @v;
    EXECUTE stmt1;
#add immunization name to de-identified data, if immunization data is included in report

	IF tableName = "immunizations" and colName = "immunization_id" THEN

	 alter table de_identified_data add column immunization_name text not null;

	END IF; 
#For duplicate column name append table name with the col name
IF(duplicateColumn) THEN
 SET newColName = CONCAT(tableName,":",colName);
 SET @v = CONCAT("alter table de_identified_data add column `", newColName, "` text not null");

    PREPARE stmt1 FROM @v;
    EXECUTE stmt1;
 SET duplicateColumn = 0;
 update transaction_metadata_de_identification set col_name = newColName where col_name = colName and table_name = tableName;
END IF;
FETCH cur1 INTO colName,tableName;
  end WHILE;


  CLOSE cur1;
END
$

-- --------------------------------------------------------

-- Procedure to filter pid for de-identification process
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists `filter_pid`;
$

$
CREATE PROCEDURE `filter_pid`()
BEGIN
#Retrieve a list of patient ids that satisfy the selections picked in the de-identification Input screen.
#  The table param_filter_pid contains the parameters (start/end date, diagnosis, drugs, immunizations)
#for filter out which patients to select.  Store the selected patient ids in the temp_patient_id_table 
declare startDate varchar(30);
declare endDate varchar(30);
declare diagnosis_list varchar(1000);
declare drug_list varchar(1000);
declare immunization_list varchar(1000);
declare nowords int;
declare subString varchar(255);
select begin_date into startDate from param_filter_pid;
select end_date into endDate from param_filter_pid;
select diagnosis_text into diagnosis_list from param_filter_pid;
select drug_text into drug_list from param_filter_pid;
select immunization_text into immunization_list from param_filter_pid;
drop table  IF EXISTS t1;
create table t1 (pid int);
delete from temp_patient_id_table;
insert into temp_patient_id_table (pid) select pid from patient_data;
#In parameter individual values are separated by '#'  
SET diagnosis_list = LTRIM(diagnosis_list);
SET diagnosis_list = RTRIM(diagnosis_list);
IF (diagnosis_list != "all") then
SET diagnosis_list = CONCAT(diagnosis_list,'#');
SET noWords=LENGTH(diagnosis_list) - LENGTH(REPLACE(diagnosis_list, '#', '')) + 1 ;
WHILE( noWords != 0) do
#Obtain individual value from the parameter
SET subString = SUBSTRING_INDEX( SUBSTRING_INDEX( diagnosis_list, '#', noWords), '#', -1 );
SET subString = LTRIM(subString);
SET subString = RTRIM(subString);
SET subString = SUBSTRING_INDEX(subString, '-', 1);
insert into t1 (pid) select pid from lists where diagnosis = subString and begdate >= startDate and begdate<= endDate;
set noWords = noWords -1;
end while;
ELSE
insert into t1 (pid) select pid from lists where begdate >= startDate and begdate<= endDate;
END IF;
DELETE FROM temp_patient_id_table where pid NOT IN (SELECT pid FROM t1);
DELETE FROM t1;
SET drug_list = LTRIM(drug_list);
SET drug_list = RTRIM(drug_list);
IF (drug_list != "all") then
SET drug_list = CONCAT(drug_list,'#');
SET noWords=LENGTH(drug_list) - LENGTH(REPLACE(drug_list, '#', '')) + 1;
WHILE( noWords >= 0) do
SET subString = SUBSTRING_INDEX( SUBSTRING_INDEX( drug_list, '#', noWords), '#', -1 );
SET subString = LTRIM(subString);
SET subString = RTRIM(subString);
SET subString = SUBSTRING_INDEX(subString, '-', -1);
insert into t1 (pid) select patient_id from prescriptions where drug = subString and start_date >= startDate and start_date <= endDate;
insert into t1 (pid) select pid from lists where type = "medication" and title = subString and begdate >= startDate and begdate <= endDate;
set noWords = noWords -1;
end while;
ELSE
insert into t1 (pid) select patient_id from prescriptions where start_date >= startDate and start_date <= endDate;
insert into t1 (pid) select pid from lists where type = "medication" and begdate >= startDate and begdate <= endDate;
END IF;
DELETE FROM temp_patient_id_table where pid NOT IN (SELECT pid FROM t1);
DELETE FROM t1;
SET drug_list = LTRIM(immunization_list);
SET drug_list = RTRIM(immunization_list);
IF (immunization_list != "all") then
SET immunization_list = CONCAT(immunization_list,'#');
SET noWords=LENGTH(immunization_list) - LENGTH(REPLACE(immunization_list, '#', '')) + 1;
WHILE( noWords >= 0) do
SET subString = SUBSTRING_INDEX( SUBSTRING_INDEX( immunization_list, '#', noWords), '#', -1 );
SET subString = LTRIM(subString);
SET subString = RTRIM(subString);
SET subString = SUBSTRING_INDEX(subString, '-', 1);
insert into t1 (pid) select patient_id from immunizations where immunization_id = subString and administered_date >= startDate and administered_date <= endDate;
set noWords = noWords -1;
end while;
ELSE
insert into t1 (pid) select patient_id from immunizations where administered_date >= startDate and administered_date <= endDate;
END IF;
DELETE FROM temp_patient_id_table where pid NOT IN (SELECT pid FROM t1);
DELETE FROM t1;
  
END 
$

-- --------------------------------------------------------

-- Procedure to drop no value column
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists `drop_no_value_column`;
$

$
CREATE PROCEDURE `drop_no_value_column`()
begin
#In table de_identified_data, remove any empty columns (columns that contain an empty value, for every patient). 
DECLARE done INT DEFAULT 0;
DECLARE val int default 0;
declare colName VARCHAR(255) ;
DECLARE metadate_cursor CURSOR FOR SELECT col_name FROM transaction_metadata_de_identification where  include_in_de_identification != 0 ;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
OPEN metadate_cursor;
   FETCH metadate_cursor INTO colName;
   WHILE (done = 0) do
   SET @v = CONCAT("select count(`", colName ,"`) INTO @val from de_identified_data where `", colName ,"` != ' '");
PREPARE stmt1 FROM @v;
EXECUTE stmt1;   
if @val <= 1 then
SET @v = CONCAT("alter table de_identified_data drop column `", colName ,"`");
PREPARE stmt1 FROM @v;
EXECUTE stmt1;  
DELETE FROM transaction_metadata_de_identification where col_name = colName;
    
    end if;
FETCH metadate_cursor INTO colName;
   end while;
close metadate_cursor;
end 
$

-- --------------------------------------------------------

-- Procedure to check match for regular expression
-- Procedure added to accomplish HIPAA De-identification
$
drop function if exists `match_regular_expression`;
$

$
CREATE FUNCTION `match_regular_expression`(unstructuredData varchar(255)) RETURNS varchar(255)
BEGIN
#Given some unstructured data (like patient notes), replace any urls, dates, or names in the data with 'xxx'.  Then return the modified data. 
DECLARE newString varchar(255);
DECLARE subString varchar(30);
DECLARE noWords INT;
DECLARE count INT DEFAULT 1;
SET newString = " ";
SET unstructuredData = CONCAT(unstructuredData,' ');
SET noWords=LENGTH(unstructuredData) - LENGTH(REPLACE(unstructuredData, ' ', '')) ;
WHILE( noWords >= count) do
    
SET subString = SUBSTRING_INDEX( SUBSTRING_INDEX( unstructuredData, ' ', count), ' ', -1 );
#Check for url
IF ( LOCATE("www.", subString) || LOCATE(".com", subString) || LOCATE("http", subString) || LOCATE(".co", subString) || LOCATE(".in", subString) )THEN 
SET subString = "xxx";
#Check for date (yyyy/mm/dd or dd-mm-yyyy)
ELSEIF (SELECT subString REGEXP "([0-9]{4})[-|/|.|\]([0-9]{1,2})[-|/|.|\]([0-9]{1,2})")THEN  SET subString = LEFT(subString,4);
ELSEIF (SELECT subString REGEXP "([0-9]{1,2})[-|/|.|\]([0-9]{1,2})[-|/|.|\]([0-9]{4})")THEN  SET subString = RIGHT(subString,4);
ELSEIF (LOCATE("mr.", subString) || LOCATE("mrs.", subString) || LOCATE("ms.", subString)|| LOCATE("dr.", subString) )THEN
SET subString = "xxx";
END IF;
SET newString = CONCAT(newString, subString, " ");
SET count = count + 1;
end WHILE;
SET newString = LTRIM(newString);
SET newString = RTRIM(newString);
#Return updated string
RETURN newString;
END
$

-- --------------------------------------------------------

-- Procedure to perform de-identification
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists  `perform_de_identification`;
$

$


CREATE PROCEDURE `perform_de_identification`()

BEGIN

#When this prodecure starts:

#The temp_patient_id_table contains the list of patients to gather data for.

#The de_identified_data table contains the table/column names to gather data for

#transaction_metadata_de_identification which tells whether the table/column needs to be de-identified or not.

DECLARE lexText VARCHAR(255) ;

DECLARE unstructuredData VARCHAR(255) ;

DECLARE colName VARCHAR(255) ;

DECLARE originalColName VARCHAR(255) ;

DECLARE tableName VARCHAR(255) ;

DECLARE includeInDeIdentification INT ;

DECLARE recordNumber INT DEFAULT 0;

DECLARE patientId INT;

DECLARE charPosition INT;

DECLARE recordCount INT;

DECLARE recordId INT;

DECLARE insertFlag INT DEFAULT 0;

DECLARE columnFlag INT DEFAULT 0;

DECLARE done INT DEFAULT 0;

DECLARE unknownColumn INT DEFAULT 0;

DECLARE patient_id_cursor CURSOR FOR SELECT pid from temp_patient_id_table;

DECLARE metadate_cursor CURSOR FOR SELECT table_name,col_name,include_in_de_identification FROM transaction_metadata_de_identification where  include_in_de_identification != 0 ;

DECLARE lexical_cursor CURSOR FOR select lex_text from lexical_look_up_table;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

OPEN patient_id_cursor;

FETCH patient_id_cursor into patientId;

WHILE (done = 0) DO



#If the patient id has no re-identification code associated with it, then create a new re-identification code using UUID() and assign it to the patient id (store it in re_identification_code_data table)

IF(!( select count(*) from re_identification_code_data where pid = patientId)) THEN

insert into re_identification_code_data values (patientId, uuid());

END IF;

FETCH patient_id_cursor into patientId;

END WHILE;

close patient_id_cursor;

set done = 0;

  delete from de_identified_data;

  #first row/record of de-identified data table will be the column name (display purpose)

  insert into de_identified_data (number,sub_number,re_identification_code) values ("number","sub_number","re_identification_code");



  OPEN patient_id_cursor;



  FETCH patient_id_cursor INTO patientId;

  while (done = 0) do

    set recordNumber = recordNumber + 1;

OPEN metadate_cursor;

    FETCH metadate_cursor INTO tableName, colName, includeInDeIdentification;

    while done = 0  do



set columnFlag = 0;

#Handle case when table name is appened with the column name eg:history_data:date

set charPosition = locate(":",colName);

if charPosition && tableName = substring(colName,1,charPosition-1) then
 set @z = CONCAT("update de_identified_data set `", colName ,"` = '", colName, "' where number = 0 ");

 set originalColName = colName;

 set colName = substring(colName,charPosition+1);

 set columnFlag = 1; 
 if (tableName = 'lists' ) then
   set @z = CONCAT("update de_identified_data set `", originalColName ,"` = 'issues:", colName, "' where number = 0 ");
 end if;
else 
  if (tableName = 'lists' ) then
   set @z = CONCAT("update de_identified_data set `", colName ,"` = 'issues:", colName, "' where number = 0 ");
  else
   set @z = CONCAT("update de_identified_data set `", colName ,"` = '", tableName, ":", colName, "' where number = 0 ");
  end if;
end if;
PREPARE stmt2 FROM @z;

EXECUTE stmt2;


TRUNCATE temp_patient_record_id;

if (tableName = 'prescriptions' || tableName = 'immunizations') then

   SET @v = CONCAT("insert into temp_patient_record_id (id) select id from ", tableName," where patient_id = ", patientId);

   else SET @v = CONCAT("insert into temp_patient_record_id (id) select id from ", tableName," where pid = ", patientId);

END IF;

  PREPARE stmt1 FROM @v;

  EXECUTE stmt1;



  SELECT count(*) FROM temp_patient_record_id into recordCount;



  while recordCount != 0 do



  select count(*) from de_identified_data where number = recordNumber and sub_number = recordCount AND re_identification_code = (select re_identification_code from re_identification_code_data where pid = patientId) into insertFlag;





  if insertFlag = 0 then

  insert into de_identified_data (number,sub_number,re_identification_code) values (recordNumber,recordCount, (select re_identification_code from re_identification_code_data where pid = patientId));

  end if;





  SELECT id FROM temp_patient_record_id where number = recordCount into recordId;



#Case 4 :unstructured data(eg:patient notes) perform lexical analysis - replace any identifying text (name, telephone, etc) with xxx

IF includeInDeIdentification = 4 then

SET @v = CONCAT("select ", colName, " into @unstructuredData from ", tableName, "  where id = ",recordId);

PREPARE stmt1 FROM @v;

    EXECUTE stmt1;

SET @unstructuredData = LOWER(@unstructuredData);

OPEN lexical_cursor;



  FETCH lexical_cursor INTO lexText;

  while (done = 0) do



SET @unstructuredData = REPLACE (@unstructuredData, lexText, "xxx");

FETCH lexical_cursor INTO lexText;

  end while;

  CLOSE lexical_cursor;

  set done = 0 ;

set @unstructuredData = match_regular_expression(@unstructuredData);

IF columnFlag = 0 THEN

SET @v = CONCAT("update de_identified_data set `", colName, "` = '", @unstructuredData,"' where sub_number = ",recordCount, " and number = ", recordNumber );

ELSE

SET @v = CONCAT("update de_identified_data set `", originalColName, "` = '", @unstructuredData,"' where sub_number = ",recordCount, " and number = ", recordNumber );

END IF;

#Case 2:date feild , provide only year part

ELSEIF includeInDeIdentification = 2 then

IF columnFlag = 0 THEN

SET @v = CONCAT("update de_identified_data set `", colName, "` = ( select LEFT ( (select ",colName," from ", tableName, "  where id = ",recordId," ), 4)) where sub_number = ",recordCount,  " and number = ", recordNumber );

ELSE

SET @v = CONCAT("update de_identified_data set `", originalColName, "` = ( select LEFT ( (select ",colName," from ", tableName, "  where id = ",recordId," ), 4)) where sub_number = ",recordCount,  " and number = ", recordNumber );

END IF;

#Case 3:zip code, provide only first 3 digits

ELSEIF includeInDeIdentification = 3 then

IF columnFlag = 0 THEN

SET @v = CONCAT("update de_identified_data set `", colName, "` = ( select LEFT ( (select ",colName," from ", tableName, "  where id = ",recordId," ), 3)) where sub_number = ",recordCount, " and number = ", recordNumber  );

ELSE

SET @v = CONCAT("update de_identified_data set `", originalColName, "` = ( select LEFT ( (select ",colName," from ", tableName, "  where id = ",recordId," ), 3)) where sub_number = ",recordCount, " and number = ", recordNumber  );

END IF;

ELSE

IF columnFlag = 0 THEN

SET @v = CONCAT("update de_identified_data set `", colName, "` = ( select ",colName," from ", tableName, "  where id = ",recordId," ) where sub_number = ",recordCount,  " and number = ", recordNumber  );

ELSE

SET @v = CONCAT("update de_identified_data set `", originalColName, "` = ( select ",colName," from ", tableName, "  where id = ",recordId," ) where sub_number = ",recordCount,  " and number = ", recordNumber  );

END IF;

END IF;

    PREPARE stmt1 FROM @v;

    EXECUTE stmt1;

#add immunization name to de-identified data, if immunization data is included in report

	IF tableName = "immunizations" and colName = "immunization_id" THEN
 update de_identified_data set immunization_name = "immunization:immunization_name" where number = 0;

	 SET @v = CONCAT("select immunization_id into @immunizationId from immunizations where id = ", recordId  );

    PREPARE stmt1 FROM @v;

    EXECUTE stmt1;
 

	

	 SET @z = CONCAT("update de_identified_data set immunization_name = ( select title from list_options where list_id = 'immunizations' and option_id = ",@immunizationId," ) where sub_number = ",recordCount,  " and number = ", recordNumber  );

    PREPARE stmt2 FROM @z;

    EXECUTE stmt2;
  

	END IF;
set recordCount = recordCount - 1;

end while;

FETCH metadate_cursor INTO tableName, colName, includeInDeIdentification;

end while;

  CLOSE metadate_cursor;

  set done = 0;

  FETCH patient_id_cursor INTO patientId;

  end while;

  CLOSE patient_id_cursor;

# Note that a single patient can have multiple row entries in the de_identified_data.

# That is because a single patient can have multiple entries for prescriptions, immunizations, etc.



END 
$
-- --------------------------------------------------------
-- Procedure to drop transaction tables
-- --------------------------------------------------------
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists  `drop_transaction_tables`;
$

$
CREATE PROCEDURE `drop_transaction_tables`()
BEGIN
#After de-identification process is completed drop transaction tables
DROP TABLE IF EXISTS transaction_metadata_de_identification;
DROP TABLE IF EXISTS temp_patient_id_table;
DROP TABLE IF EXISTS temp_re_identification_code_table;
DROP TABLE IF EXISTS temp_patient_record_id;
DROP TABLE IF EXISTS param_filter_pid;
   
DROP TABLE IF EXISTS param_filter_pid;
END
$
-- --------------------------------------------------------

-- Procedure for re-identification
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists  `re_identification`;
$

$
CREATE PROCEDURE `re_identification`()
BEGIN
DECLARE unknown_table_name INT DEFAULT 0;
DECLARE unknown_col_name INT DEFAULT 0;
DECLARE unknown_prepare_stmt INT DEFAULT 0;
DECLARE table_already_exists INT DEFAULT 0;
DECLARE CONTINUE HANDLER FOR 1146 SET unknown_table_name = 1;
DECLARE CONTINUE HANDLER FOR 1054 SET unknown_col_name = 1;
DECLARE CONTINUE HANDLER FOR 1243 SET unknown_prepare_stmt = 1;
DECLARE CONTINUE HANDLER FOR 1050 SET table_already_exists = 1;
call create_re_identified_data_table();
call perform_re_identification();
#Set re-identification status as completed
update re_identification_status set status = 2;
#Handle error conditions
IF table_already_exists = 1 THEN
insert into de_identification_error_log values("re-identification",CURRENT_TIMESTAMP(), "when create table, table already exists");
END IF;
IF unknown_prepare_stmt = 1 THEN
insert into de_identification_error_log values("re-identification",CURRENT_TIMESTAMP(), "Unkown prepare statement");
END IF;  
IF unknown_col_name = 1 THEN
insert into de_identification_error_log values("re-identification",CURRENT_TIMESTAMP(), "Unkown column name");
END IF;
IF unknown_table_name = 1 THEN
insert into de_identification_error_log values("re-identification",CURRENT_TIMESTAMP(), "Unkown table name");
END IF;
update re_identification_status set status = 2;
END
$


-- --------------------------------------------------------

-- Procedure to create re-identified data table
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists  `create_re_identified_data_table`;
$

$
CREATE PROCEDURE `create_re_identified_data_table`()
BEGIN
#Create re-identified data table for the particular iteration of the re-identification process
DECLARE colName VARCHAR(255) ;
DECLARE done INT DEFAULT 0;
DECLARE metadata_cursor CURSOR FOR SELECT col_name FROM metadata_de_identification where include_in_re_identification = 1;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
drop table IF EXISTS re_identified_data;
create table re_identified_data (number varchar(255), pid varchar(255), re_identification_code varchar(255) NOT NULL);
OPEN metadata_cursor;
   FETCH metadata_cursor INTO colName;
   WHILE (done = 0) do

SET @v = CONCAT("alter table re_identified_data add column ", colName, " varchar(255) not null");
    PREPARE stmt1 FROM @v;
    EXECUTE stmt1;
FETCH metadata_cursor INTO colName;
  end WHILE;

  CLOSE metadata_cursor;
END 
$

-- --------------------------------------------------------

-- Procedure to perform re-identification
-- Procedure added to accomplish HIPAA De-identification
$
drop procedure if exists `perform_re_identification`;
$

$
CREATE PROCEDURE `perform_re_identification`()
BEGIN
#When this prodecure starts:
   #The temp_re_identification_code_table contains the list of re-identification codes to gather data for.
   #The re_identified_data table contains the table/column names to gather data for
   #metadata_de_identification which tells whether the table/column needs to be de-identified or not. 
DECLARE colName VARCHAR(255) ;
DECLARE tableName VARCHAR(255) ;
DECLARE patientId INT;
DECLARE recordNumber INT DEFAULT 0;
DECLARE reIdentificationCode varchar(50);
DECLARE done INT DEFAULT 0;
DECLARE unknownColumn INT DEFAULT 0;
DECLARE found_re_id_code INT DEFAULT 0;
DECLARE re_identification_code_cursor CURSOR FOR select re_identification_code from temp_re_identification_code_table;
DECLARE metadata_cursor CURSOR FOR SELECT col_name,table_name FROM metadata_de_identification where  include_in_re_identification = 1 ;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
delete from re_identified_data;

insert into re_identified_data (number) values ("record number");
update re_identified_data set pid = "patient id" where number = 0;
update re_identified_data set re_identification_code = "re_identification_code" where number = 0;
OPEN re_identification_code_cursor;
  FETCH re_identification_code_cursor INTO reIdentificationCode;
  WHILE (done = 0) DO
   select count(*) from re_identification_code_data where re_identification_code = reIdentificationCode INTO found_re_id_code;

   if (found_re_id_code) then
#If input code matchs with re-identification code in database obtain re-identifiying data for the input code
set recordNumber = recordNumber + 1;
insert into re_identified_data (number) values (recordNumber);
select pid from re_identification_code_data where re_identification_code = reIdentificationCode INTO patientId;
update re_identified_data set pid = patientId where number = recordNumber;
update re_identified_data set re_identification_code = reIdentificationCode where number = recordNumber;
OPEN metadata_cursor;
FETCH metadata_cursor INTO colName, tableName;
       WHILE (done = 0) do

   SET @v = CONCAT("update re_identified_data set ", colName, " = ( select ",colName," from ", tableName, "  where pid = ",patientId," ) where number = ",recordNumber );
       PREPARE stmt1 FROM @v;
       EXECUTE stmt1;


   set @z = CONCAT("update re_identified_data set `", colName ,"` = '", tableName, ":", colName, "' where number = 0 "); 
   PREPARE stmt2 FROM @z;
       EXECUTE stmt2;

   FETCH metadata_cursor INTO colName, tableName;
end WHILE;
CLOSE metadata_cursor;
  set done = 0;
  end if;
  FETCH re_identification_code_cursor INTO reIdentificationCode;
  end while;
CLOSE re_identification_code_cursor;

END
$

-- --------------------------------------------------------

-- Table structure for table `metadata for de-identification`
-- Table added to accomplish HIPAA De-identification

#IfNotTable metadata_de_identification
CREATE TABLE `metadata_de_identification` (
  `table_name` varchar(255) NOT NULL,
  `col_name` varchar(255) NOT NULL,
  `load_to_lexical_table` tinyint(1) NOT NULL,
  -- load_to_lexical_table can be
  --  0 do not include in lexical look up table
  --  1 include in lexical look up table
  `include_in_de_identification` int(2) NOT NULL,
  -- include_in_de_identification can be
  --  0 do not include in de-identification
  --  1 include in de-identification
  --  2 date feild - include only year part
  --  3 zip code - include only first 3 digits
  --  4 unstructured data - perform lexical analysis
  `include_in_re_identification` tinyint(1) NOT NULL
  -- include_in_re_identification can be
  --  0 do not include in re-identification
  --  1 include in re-identification
) ENGINE=MyISAM;
#EndIf


-- --------------------------------------------------------

-- Table structure for table `lexical look up table`
-- Table added to accomplish HIPAA De-identification

#IfNotTable lexical_look_up_table
CREATE TABLE `lexical_look_up_table` (
  `id` int(11) NOT NULL auto_increment,
  `lex_text` varchar(255) NOT NULL,
   KEY `id` (`id`)
) ENGINE=MyISAM;
#EndIf

-- --------------------------------------------------------

-- Table structure for table `re_identification_code_data`
-- Table added to accomplish HIPAA De-identification

#IfNotTable re_identification_code_data
CREATE TABLE `re_identification_code_data` (
  `pid` bigint(20) NOT NULL,
  `re_identification_code` varchar(50) NOT NULL
) ENGINE=MyISAM;
#EndIf

-- --------------------------------------------------------

-- Table structure for table `de_identification_status`insert into re_identification_code_data values (patientId, uuid());
-- Table added to accomplish HIPAA De-identification

#IfNotTable de_identification_status
CREATE TABLE `de_identification_status` (
 -- status can be
  --  2 re-identification process completed, file ready to download
  --  1 de-identification process running
  --  0 de-identification process not running
  --  3 error status
  `status` int(11) default NULL,
  `last_available_de_identified_data_file` varchar(100) default NULL
) ENGINE=MyISAM;
--
-- Dumping data for table `de_identification_status`
--
insert into de_identification_status values (0," ");
#EndIf


-- --------------------------------------------------------

-- Table structure for table `de_identification_error_log`
-- Table added to accomplish HIPAA De-identification

#IfNotTable de_identification_error_log
CREATE TABLE `de_identification_error_log` (
  `activity` varchar(100),
  `date_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `error_msg` text
) ENGINE=MyISAM;
#EndIf


-- --------------------------------------------------------

-- Table structure for table `re_identification_status`
-- Table added to accomplish HIPAA De-identification


#IfNotTable re_identification_status
CREATE TABLE `re_identification_status` (
  -- status can be
  --  2 re-identification process completed, file ready to download
  --  1 re-identification process running
  --  0 re-identification process not running
   `status` int(11) default NULL
) ENGINE=MyISAM;
--
-- Dumping data for table `re_identification_status`
--
insert into re_identification_status values (0);
#EndIf


------------------------------------------------------------


