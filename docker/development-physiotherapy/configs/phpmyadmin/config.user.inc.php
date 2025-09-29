<?php
/**
 * phpMyAdmin configuration for OpenEMR Vietnamese Physiotherapy Development
 * Author: Dang Tran <tqvdang@msn.com>
 */

// Increase memory and time limits for large database operations
$cfg['MemoryLimit'] = '512M';
$cfg['ExecTimeLimit'] = 600;

// Enhanced Vietnamese language support
$cfg['DefaultLang'] = 'vi';
$cfg['Lang'] = 'vi';
$cfg['FilterLanguages'] = 'vi|en';
$cfg['RecodingEngine'] = 'auto';

// Character set configuration
$cfg['DefaultCharset'] = 'utf8mb4';
$cfg['DefaultCollation'] = 'utf8mb4_vietnamese_ci';

// Upload limits for large SQL files
$cfg['UploadDir'] = '/tmp/';
$cfg['SaveDir'] = '/tmp/';
$cfg['MaxSizeForUpload'] = '100M';

// Import/Export settings
$cfg['Import']['charset'] = 'utf8mb4';
$cfg['Export']['charset'] = 'utf8mb4';
$cfg['Export']['file_template_table'] = '__TABLE__';
$cfg['Export']['file_template_database'] = '__DB__';
$cfg['Export']['file_template_server'] = '__SERVER__';

// Interface customization
$cfg['ThemeDefault'] = 'pmahomme';
$cfg['ShowPhpInfo'] = true;
$cfg['ShowServerInfo'] = true;
$cfg['ShowStats'] = true;

// Grid editing
$cfg['GridEditing'] = 'click';
$cfg['SaveCellsAtOnce'] = true;

// SQL query settings
$cfg['SQLQuery']['Edit'] = true;
$cfg['SQLQuery']['Explain'] = true;
$cfg['SQLQuery']['ShowAsPHP'] = true;
$cfg['SQLQuery']['Validate'] = true;
$cfg['SQLQuery']['Refresh'] = true;

// Bookmark settings for frequently used queries
$cfg['Bookmark']['enable'] = true;

// Designer settings
$cfg['Designer']['angular_direct'] = true;
$cfg['Designer']['snap_to_grid'] = true;

// Console settings
$cfg['Console']['StartHistory'] = true;
$cfg['Console']['AlwaysExpand'] = true;
$cfg['Console']['CurrentQuery'] = true;
$cfg['Console']['EnterExecutes'] = true;
$cfg['Console']['DarkTheme'] = false;

// Navigation settings
$cfg['NavigationTree']['enable'] = true;
$cfg['NavigationTree']['ShowTables'] = true;
$cfg['NavigationTree']['ShowViews'] = true;
$cfg['NavigationTree']['ShowFunctions'] = true;
$cfg['NavigationTree']['ShowProcedures'] = true;
$cfg['NavigationTree']['ShowEvents'] = true;

// Vietnamese physiotherapy specific configurations
$cfg['FirstDayOfCalendar'] = 1; // Monday first (Vietnam standard)
$cfg['DateFormat'] = 'd/m/Y'; // Vietnamese date format

// Enhanced search and filtering for Vietnamese text
$cfg['DefaultFunctions']['CHAR'] = 'UTF8';
$cfg['DefaultFunctions']['VARCHAR'] = 'UTF8';
$cfg['DefaultFunctions']['TEXT'] = 'UTF8';

// Database structure preferences for PT data
$cfg['DefaultTabDatabase'] = 'db_structure.php';
$cfg['DefaultTabTable'] = 'tbl_structure.php';

// Enhanced export settings for Vietnamese data
$cfg['Export']['sql_max_query_size'] = 50000;
$cfg['Export']['sql_hex_for_binary'] = true;
$cfg['Export']['sql_utf8_table'] = true;

// Import settings optimized for Vietnamese data
$cfg['Import']['charset'] = 'utf8mb4';
$cfg['Import']['allow_interrupt'] = true;
$cfg['Import']['skip_queries'] = 0;

// Vietnamese PT database quick access
$cfg['Servers'][1]['bookmarkdb'] = 'openemr';
$cfg['Servers'][1]['bookmarktable'] = 'pma__bookmark';
$cfg['Servers'][1]['relation'] = 'pma__relation';
$cfg['Servers'][1]['table_info'] = 'pma__table_info';
$cfg['Servers'][1]['table_coords'] = 'pma__table_coords';
$cfg['Servers'][1]['pdf_pages'] = 'pma__pdf_pages';
$cfg['Servers'][1]['column_info'] = 'pma__column_info';
$cfg['Servers'][1]['history'] = 'pma__history';
$cfg['Servers'][1]['tracking'] = 'pma__tracking';
$cfg['Servers'][1]['userconfig'] = 'pma__userconfig';
$cfg['Servers'][1]['recent'] = 'pma__recent';
$cfg['Servers'][1]['favorite'] = 'pma__favorite';
$cfg['Servers'][1]['users'] = 'pma__users';
$cfg['Servers'][1]['usergroups'] = 'pma__usergroups';
$cfg['Servers'][1]['navigationhiding'] = 'pma__navigationhiding';
$cfg['Servers'][1]['savedsearches'] = 'pma__savedsearches';

// Custom footer with Vietnamese PT information
$cfg['ServerDefault'] = 1;
?>