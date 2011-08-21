--
--  Comment Meta Language for sql upgrades:
--
--  Each section within an upgrade sql file is enveloped with an #If*/#EndIf block.  At first glance, these appear to be standard mysql
--  comments meant to be cryptic hints to -other developers about the sql goodness contained therein.  However, were you to rely on such basic premises,
--  you would find yourself grossly decieved.  Indeed, without the knowledge that these comments are, in fact a sneakily embedded meta langauge derived
--  for a purpose none-other than to aid in the protection of the database during upgrades,  you would no doubt be subject to much ridicule and public
--  beratement at the hands of the very developers who envisioned such a crafty use of comments. -jwallace
--
--  While these lines are as enigmatic as they are functional, there is a method to the madness.  Let's take a moment to briefly go over proper comment meta language use.
--
--  The #If* sections have the behavior of functions and come complete with arguments supplied command-line style
--
--  Your Comment meta language lines cannot contain any other comment styles such as the nefarious double dashes "--" lest your lines be skipped and
--  the blocks automatcially executed with out regard to the existing database state.
--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist,  the block will be executed

--  #IfTable
--    argument: table_name
--    behavior: if the table_name does exist, the block will be executed

--  #IfMissingColumn
--    arguments: table_name colname
--    behavior:  if the colname in the table_name table does not exist,  the block will be executed

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

--  #EndIf
--    all blocks are terminated with and #EndIf statement.

