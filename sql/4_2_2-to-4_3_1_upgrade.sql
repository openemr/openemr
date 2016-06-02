--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist,  the block will be executed

--  #IfTable
--    argument: table_name
--    behavior: if the table_name does exist, the block will be executed

--  #IfColumn
--    arguments: table_name colname
--    behavior:  if the table and column exist,  the block will be executed

--  #IfMissingColumn
--    arguments: table_name colname
--    behavior:  if the table exists but the column does not,  the block will be executed

--  #IfNotColumnType
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a column colname with a data type equal to value, then the block will be executed

--  #IfNotRow
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a row where colname = value, the block will be executed.

--  #IfNotRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfNotRow3D
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfNotRow4D
--    arguments: table_name colname value colname2 value2 colname3 value3 colname4 value4
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3 AND colname4 = value4, the block will be executed.

--  #IfNotRow2Dx2
--    desc:      This is a very specialized function to allow adding items to the list_options table to avoid both redundant option_id and title in each element.
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  The block will be executed if both statements below are true:
--               1) The table table_name does not have a row where colname = value AND colname2 = value2.
--               2) The table table_name does not have a row where colname = value AND colname3 = value3.

--  #IfRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfRow3D
--        arguments: table_name colname value colname2 value2 colname3 value3
--        behavior:  If the table table_name does have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfIndex
--    desc:      This function is most often used for dropping of indexes/keys.
--    arguments: table_name 2colname
--    behavior:  If the table and index exist the relevant statements are executed, otherwise not.

--  #IfNotIndex
--    desc:      This function will allow adding of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the index does not exist, it will be created

--  #EndIf
--    all blocks are terminated with a #EndIf statement.

--  #IfNotListReaction
--    Custom function for creating Reaction List

--  #IfNotListOccupation
--    Custom function for creating Occupation List


#IfMissingColumn layout_options validation
ALTER TABLE layout_options ADD COLUMN validation varchar(100) default NULL;
#EndIf
#IfNotRow list_options option_id LBF_Validations
INSERT INTO `list_options` ( list_id, option_id, title) VALUES ( 'lists','LBF_Validations','LBF_Validations');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`, `seq`) VALUES ('LBF_Validations','int1','Integers1-100','{\"numericality\": {\"onlyInteger\": true,\"greaterThanOrEqualTo\": 1,\"lessThanOrEqualTo\":100}}','10');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`, `seq`) VALUES ('LBF_Validations','names','Names','{"format\":{\"pattern\":\"[a-zA-z]+([ \'-\\\\s][a-zA-Z]+)*\"}}','20');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`, `seq`) VALUES ('LBF_Validations','birthdate','Birth Date','{\"date\":{\"dateOnly\":true},\"onlyPast\":{\"message\":\"must be past date\"}}','30');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`,`seq`) VALUES ('LBF_Validations','email','E-Mail','{\"email\":true}','40');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`,`seq`) VALUES ('LBF_Validations','url','URL','{\"url\":true}','50');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`,`seq`) VALUES ('LBF_Validations','postal_code','Postal Code','{\"format\":{\"pattern\":\"^(\\\\d{5}|\\\\d{7})$\"}}','60');
INSERT INTO `list_options` (`list_id`,`option_id`,`notes`,`seq`) VALUES ('LBF_Validations','il_phone','IL Phone','{\"format\":{\"pattern\":\"^((\\\\+972|972)|0)( |-)?([1-468-9]( |-)?\\\\d{7}|(5|7)[0-9]( |-)?\\\\d{7})$\"}}','70');

#EndIf

