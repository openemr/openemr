<?php
/**
 * phpMyAdmin configuration for OpenEMR Vietnamese Physiotherapy Development
 * Author: Dang Tran <tqvdang@msn.com>
 */

// Increase memory and time limits for large database operations
$cfg['MemoryLimit'] = '512M';
$cfg['ExecTimeLimit'] = 600;

// Vietnamese language support
$cfg['DefaultLang'] = 'vi';
$cfg['Lang'] = 'vi';

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

// Custom footer
$cfg['ServerDefault'] = 1;
?>