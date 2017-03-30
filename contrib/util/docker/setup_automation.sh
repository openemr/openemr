#!/bin/bash

# contrib/util/docker/setup_automation.sh Automated setup of openemr in docker container
#
# A script which uses curl to answer setup questions in openemr setup.
#
# Copyright (C) 2017 Jeffrey McAteer <jeffrey.p.mcateer@gmail.com>
#
# LICENSE: This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 3
# of the License, or (at your option) any later version.
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
#
# @package OpenEMR
# @author  Jeffrey McAteer <jeffrey.p.mcateer@gmail.com>
# @link    http://www.open-emr.org

if ! hash curl >/dev/null 2>&1; then
	echo '[ Error ] You need curl installed to use this script'
	exit 1
fi

# Password used for openemr accounts created during setup (sql root pass is empty)
PASS=openemr
IUFNAME=John
IUNAME=Smith

BASE_URL=$1
if [[ "$BASE_URL" == "" ]]; then
	BASE_URL=http://localhost:8000
fi
COOKIE_FILE=/tmp/curl_cookies
# Change this to /dev/stdout debug the script
CURL_REDIR=/dev/null

shopt -s expand_aliases
# Reduce duplicate clutter below
alias curl="curl -L --silent -c $COOKIE_FILE -b $COOKIE_FILE"

SETUP_URL="$BASE_URL/setup.php?site=default"

# todo maybe abstract this curl call into a function which accepts 'arg1=val1' 'arg2=val2' arguments?
curl "$SETUP_URL" -d 'state=1' -d 'site=default' \
	>$CURL_REDIR 2>&1

curl "$SETUP_URL" -d 'state=2' -d 'site=default' -d 'inst=1' \
	>$CURL_REDIR 2>&1

curl "$SETUP_URL" -d 'state=3' -d 'site=default' -d 'inst=1' \
				  -d 'server=127.0.0.1' -d 'port=3306' 			 -d 'dbname=openemr' 		  \
				  -d 'login=openemr' 	-d "pass=$PASS" 		 -d 'root=root' 			  \
				  -d 'rootpass=' 		-d 'loginhost=localhost' -d 'collate=utf8_general_ci' \
				  -d 'iuser=admin' 		-d "iuserpass=$PASS" 	 -d "iufname=$IUFNAME" 		  \
				  -d "iuname=$IUNAME"	-d 'igroup=Default' \
	>$CURL_REDIR 2>&1

curl "$SETUP_URL" -d 'state=4' -d 'site=default' -d 'iuser=admin' \
				  -d "iuserpass=$PASS" -d "iuname=$IUNAME" -d "iufname=$IUFNAME" \
				  -d 'clone_database=' \
	>$CURL_REDIR 2>&1

curl "$SETUP_URL" -d 'state=5' -d 'site=default' -d 'iuser=admin' \
				  -d "iuserpass=$PASS" \
	>$CURL_REDIR 2>&1

curl "$SETUP_URL" -d 'state=6' -d 'site=default' -d 'iuser=admin' \
				  -d "iuserpass=$PASS" \
	>$CURL_REDIR 2>&1

curl "$SETUP_URL" -d 'state=7' -d 'site=default' -d 'iuser=admin' \
				  -d "iuserpass=$PASS" \
	>$CURL_REDIR 2>&1

echo "Visit $BASE_URL/?site=default and log in with admin/$PASS"

