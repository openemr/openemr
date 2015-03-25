#!/bin/bash
# HOWTO
# 1 	Run ./create_migration.sh (this script.) The result will be a new file with your current
#		timestamp in the sql/migrations directory.
# 2		Add your add/remove database modifications to the sql/migrations/migration_*.sql file just
#		as you would with an upgrade or patch.
# 3		Run [web root]/migrate.php to run all mirations. 
#
# NOTES:
# * Migrations are run in order by file name. Do not change the file name.
# * Migrations current migrations are logged in sql/migrations.log

mkdir -p sql/migrations
TEMPLATE="sql/migrations/migration_$(date +%Y_%m_%d_%H_%M_%S).sql"
cp sql/patch.sql $TEMPLATE
echo "Migration template $TEMPLATE created successfully."
