TYPE=VIEW
query=select `t`.`THREAD_ID` AS `thread_id`,if(`t`.`NAME` = \'thread/sql/one_connection\',concat(`t`.`PROCESSLIST_USER`,\'@\',`t`.`PROCESSLIST_HOST`),replace(`t`.`NAME`,\'thread/\',\'\')) AS `user`,sum(`mt`.`CURRENT_COUNT_USED`) AS `current_count_used`,sum(`mt`.`CURRENT_NUMBER_OF_BYTES_USED`) AS `current_allocated`,ifnull(sum(`mt`.`CURRENT_NUMBER_OF_BYTES_USED`) / nullif(sum(`mt`.`CURRENT_COUNT_USED`),0),0) AS `current_avg_alloc`,max(`mt`.`CURRENT_NUMBER_OF_BYTES_USED`) AS `current_max_alloc`,sum(`mt`.`SUM_NUMBER_OF_BYTES_ALLOC`) AS `total_allocated` from (`performance_schema`.`memory_summary_by_thread_by_event_name` `mt` join `performance_schema`.`threads` `t` on(`mt`.`THREAD_ID` = `t`.`THREAD_ID`)) group by `t`.`THREAD_ID`,if(`t`.`NAME` = \'thread/sql/one_connection\',concat(`t`.`PROCESSLIST_USER`,\'@\',`t`.`PROCESSLIST_HOST`),replace(`t`.`NAME`,\'thread/\',\'\')) order by sum(`mt`.`CURRENT_NUMBER_OF_BYTES_USED`) desc
md5=090446b6a45d058a679ed900e7b94967
updatable=0
algorithm=2
definer_user=mariadb.sys
definer_host=localhost
suid=0
with_check_option=0
timestamp=0001747911507641076
create-version=2
source=SELECT t.thread_id,\n       IF(t.name = \'thread/sql/one_connection\',\n          CONCAT(t.processlist_user, \'@\', t.processlist_host),\n          REPLACE(t.name, \'thread/\', \'\')) user,\n       SUM(mt.current_count_used) AS current_count_used,\n       SUM(mt.current_number_of_bytes_used) AS current_allocated,\n       IFNULL(SUM(mt.current_number_of_bytes_used) / NULLIF(SUM(current_count_used), 0), 0) AS current_avg_alloc,\n       MAX(mt.current_number_of_bytes_used) AS current_max_alloc,\n       SUM(mt.sum_number_of_bytes_alloc) AS total_allocated\n  FROM performance_schema.memory_summary_by_thread_by_event_name AS mt\n  JOIN performance_schema.threads AS t USING (thread_id)\n GROUP BY thread_id, IF(t.name = \'thread/sql/one_connection\',\n          CONCAT(t.processlist_user, \'@\', t.processlist_host),\n          REPLACE(t.name, \'thread/\', \'\'))\n ORDER BY SUM(mt.current_number_of_bytes_used) DESC;
client_cs_name=utf8mb3
connection_cl_name=utf8mb3_general_ci
view_body_utf8=select `t`.`THREAD_ID` AS `thread_id`,if(`t`.`NAME` = \'thread/sql/one_connection\',concat(`t`.`PROCESSLIST_USER`,\'@\',`t`.`PROCESSLIST_HOST`),replace(`t`.`NAME`,\'thread/\',\'\')) AS `user`,sum(`mt`.`CURRENT_COUNT_USED`) AS `current_count_used`,sum(`mt`.`CURRENT_NUMBER_OF_BYTES_USED`) AS `current_allocated`,ifnull(sum(`mt`.`CURRENT_NUMBER_OF_BYTES_USED`) / nullif(sum(`mt`.`CURRENT_COUNT_USED`),0),0) AS `current_avg_alloc`,max(`mt`.`CURRENT_NUMBER_OF_BYTES_USED`) AS `current_max_alloc`,sum(`mt`.`SUM_NUMBER_OF_BYTES_ALLOC`) AS `total_allocated` from (`performance_schema`.`memory_summary_by_thread_by_event_name` `mt` join `performance_schema`.`threads` `t` on(`mt`.`THREAD_ID` = `t`.`THREAD_ID`)) group by `t`.`THREAD_ID`,if(`t`.`NAME` = \'thread/sql/one_connection\',concat(`t`.`PROCESSLIST_USER`,\'@\',`t`.`PROCESSLIST_HOST`),replace(`t`.`NAME`,\'thread/\',\'\')) order by sum(`mt`.`CURRENT_NUMBER_OF_BYTES_USED`) desc
mariadb-version=110405
