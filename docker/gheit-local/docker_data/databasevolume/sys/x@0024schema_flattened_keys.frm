TYPE=VIEW
query=select `information_schema`.`STATISTICS`.`TABLE_SCHEMA` AS `table_schema`,`information_schema`.`STATISTICS`.`TABLE_NAME` AS `table_name`,`information_schema`.`STATISTICS`.`INDEX_NAME` AS `index_name`,max(`information_schema`.`STATISTICS`.`NON_UNIQUE`) AS `non_unique`,max(if(`information_schema`.`STATISTICS`.`SUB_PART` is null,0,1)) AS `subpart_exists`,group_concat(`information_schema`.`STATISTICS`.`COLUMN_NAME` order by `information_schema`.`STATISTICS`.`SEQ_IN_INDEX` ASC separator \',\') AS `index_columns` from `INFORMATION_SCHEMA`.`STATISTICS` where `information_schema`.`STATISTICS`.`INDEX_TYPE` = \'BTREE\' and `information_schema`.`STATISTICS`.`TABLE_SCHEMA` not in (\'mysql\',\'sys\',\'INFORMATION_SCHEMA\',\'PERFORMANCE_SCHEMA\') group by `information_schema`.`STATISTICS`.`TABLE_SCHEMA`,`information_schema`.`STATISTICS`.`TABLE_NAME`,`information_schema`.`STATISTICS`.`INDEX_NAME`
md5=e009f14e47310b8c3894338840733af0
updatable=0
algorithm=2
definer_user=mariadb.sys
definer_host=localhost
suid=0
with_check_option=0
timestamp=0001747911507531692
create-version=2
source=SELECT\n    TABLE_SCHEMA,\n    TABLE_NAME,\n    INDEX_NAME,\n    MAX(NON_UNIQUE) AS non_unique,\n    MAX(IF(SUB_PART IS NULL, 0, 1)) AS subpart_exists,\n    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS index_columns\n  FROM INFORMATION_SCHEMA.STATISTICS\n  WHERE\n    INDEX_TYPE=\'BTREE\'\n    AND TABLE_SCHEMA NOT IN (\'mysql\', \'sys\', \'INFORMATION_SCHEMA\', \'PERFORMANCE_SCHEMA\')\n  GROUP BY\n    TABLE_SCHEMA, TABLE_NAME, INDEX_NAME;
client_cs_name=utf8mb3
connection_cl_name=utf8mb3_general_ci
view_body_utf8=select `information_schema`.`STATISTICS`.`TABLE_SCHEMA` AS `table_schema`,`information_schema`.`STATISTICS`.`TABLE_NAME` AS `table_name`,`information_schema`.`STATISTICS`.`INDEX_NAME` AS `index_name`,max(`information_schema`.`STATISTICS`.`NON_UNIQUE`) AS `non_unique`,max(if(`information_schema`.`STATISTICS`.`SUB_PART` is null,0,1)) AS `subpart_exists`,group_concat(`information_schema`.`STATISTICS`.`COLUMN_NAME` order by `information_schema`.`STATISTICS`.`SEQ_IN_INDEX` ASC separator \',\') AS `index_columns` from `INFORMATION_SCHEMA`.`STATISTICS` where `information_schema`.`STATISTICS`.`INDEX_TYPE` = \'BTREE\' and `information_schema`.`STATISTICS`.`TABLE_SCHEMA` not in (\'mysql\',\'sys\',\'INFORMATION_SCHEMA\',\'PERFORMANCE_SCHEMA\') group by `information_schema`.`STATISTICS`.`TABLE_SCHEMA`,`information_schema`.`STATISTICS`.`TABLE_NAME`,`information_schema`.`STATISTICS`.`INDEX_NAME`
mariadb-version=110405
