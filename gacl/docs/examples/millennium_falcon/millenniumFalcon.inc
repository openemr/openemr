<?php
require_once(dirname(__FILE__).'/../../../gacl.class.php');
require_once(dirname(__FILE__).'/../../../gacl_api.class.php');
require_once(dirname(__FILE__).'/../../../admin/gacl_admin.inc.php');

/*
 * Create an array containing your preferred settings, including how to connect to your database.
 */
 //'db_name' => $gacl_options['db_name'],
$gacl_options = array(
								'debug' => $gacl_options['debug'],
								'items_per_page' => 100,
								'max_select_box_items' => 100,
								'max_search_return_items' => 200,
								'db_type' => $gacl_options['db_type'],
								'db_host' => $gacl_options['db_host'],
								'db_user' => $gacl_options['db_user'],
								'db_password' => $gacl_options['db_password'],
								'db_name' => $gacl_options['db_name'],
								'db_table_prefix' => $gacl_options['db_table_prefix'],
								'caching' => FALSE,
								'force_cache_expire' => TRUE,
								'cache_dir' => '/tmp/phpgacl_cache',
								'cache_expire_time' => 600
							);

?>
