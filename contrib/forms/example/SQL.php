<?php

include_once("../../../interface/globals.php");
echo "Building Statement<br>";
flush();

$tablename = "example";
//our first six columns are NEEDED for operation of the suite
$create = "
	create table if not exists `$tablename` (
		`id` bigint(20) NOT NULL auto_increment,
		`date` datetime default NULL,
		`activity` tinyint(4) default '1',
		`pid` bigint(20) NOT NULL default '0',
		PRIMARY KEY  (`id`),
		KEY `id` (`id`),
		`reason` longtext,
		`cats` longtext
	) ENGINE=InnoDB
	";

echo "Connecting To SQL<br>";
flush();

echo "Creating Tables<br>";
flush();
if (!sqlStatement($create)) {
    die("failed!");
}

echo "Success!<br>";
flush();
