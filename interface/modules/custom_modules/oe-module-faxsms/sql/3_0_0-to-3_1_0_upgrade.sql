
#IfMissingColumn module_faxsms_credentials setup_persist
ALTER TABLE `module_faxsms_credentials` ADD `setup_persist` tinytext;
#Endif
