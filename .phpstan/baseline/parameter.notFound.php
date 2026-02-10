<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$search_string$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_PatientFinder.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$filter$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$form_id$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$visit_date$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$create$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$Date$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$from$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Events/TelehealthNotificationSendEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$validationErrors$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Exception/TelehealthValidationException.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$seg$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$patientPids$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$actionName$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$controllerName$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$params$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$adapter$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$id$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$slug$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$removeBr$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$dir$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$mod$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$encounter_date$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$seg$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$encounter_date$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$encounter_date$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$encounter_date$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$day$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/calendar.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$source_site_id$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$and$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/NumberToText.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$capitalize$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/NumberToText.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$currency$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/NumberToText.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$number$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/NumberToText.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$exclude_filter$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$pass_filter$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$pass_target$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$results_comp$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$clm01$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$out_str$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$html_str$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$istrim$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$end$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$itemized_details$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$start$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$section_name$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Config_File_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$output$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$tag_attrs$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$_smarty_include_tpl_file$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$_smarty_include_vars$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$block_functs$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$get_source$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$map_array$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$modifier_name$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$quiet$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$resource_base_path$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$resource_name$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$resource_timestamp$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$resource_type$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$source_content$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$type$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$name$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.assemble_plugin_filepath.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$type$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.assemble_plugin_filepath.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$dir$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.create_dir_structure.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$file_path$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_include_path.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$new_file_path$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_include_path.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$resource$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_php_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$resource_type$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_php_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$resource_name$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.is_secure.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$resource_type$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.is_secure.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$resource_name$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.is_trusted.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$resource_type$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.is_trusted.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$plugins$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_plugins.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$type$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_resource_plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$results$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_cached_inserts.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$cached_source$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_compiled_include.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$compiled_tpl$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_compiled_include.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$cache_id$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$compile_id$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$results$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$tpl_file$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$auto_base$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rm_auto.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$auto_id$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rm_auto.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$auto_source$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rm_auto.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$exp_time$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rm_auto.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$dirname$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rmdir.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$exp_time$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rmdir.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$level$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rmdir.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$args$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$smarty_assign$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.smarty_include_php.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$smarty_file$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.smarty_include_php.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$smarty_include_vars$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.smarty_include_php.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$smarty_once$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.smarty_include_php.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$cache_id$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$compile_id$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$results$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$tpl_file$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$compile_path$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_compiled_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$compiled_content$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_compiled_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$contents$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$create_dirs$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$filename$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_file.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$date_format$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$log$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$params$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$sql$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$border$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3/resources/Savant3_Plugin_image.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$fieldname$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/FileUpload.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$b64encode$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$observer$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$mapping$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$fm$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/QueryBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$direction$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$pid$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$claims$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaimBatch.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$hcfa_curr_col$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$hcfa_curr_line$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$hcfa_data$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$hcfa_proc_index$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$value$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/ReminderIntervalRange.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$value$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/ReminderIntervalType.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$criteria$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$criteriaType$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaListsBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$value$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaType.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$guid$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$value$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleType.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$value$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/TimeUnit.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$acl_return_value$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$object_section_name$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$object_section_title$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$context$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CreateClientCredentialsAssertionSymfonyCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$context$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CreateReleaseChangelogCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$interpretation_code$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormVitalDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$reason_status_code$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormVitalDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$client$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpClient.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$prefix$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/ModulesClassLoader.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$scripts$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Core/StyleFilterEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$id$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/Export/ExportJob.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$display_help_icon$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$scope$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/AuthorizationListener.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$alias$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$contactId$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$targetId2$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$targetTable$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$targetTable2$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$conditionCategory$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$conditionCategory$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$conditionCategory$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$dataRecord$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$meta$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$dataRecord$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$meta$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$openEMRSearchParameters$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$search$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ListService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$mainObservationData$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$subObservationsData$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$isSmsEnabled$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientPortalService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$foreignId$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$foreignTable$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$request$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/CqmCalculator.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$record$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$record$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$andCondition$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param references unknown parameter\\: \\$isAnd$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/BasicSearchField.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
