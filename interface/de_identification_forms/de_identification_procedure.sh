#!/bin/bash
########### calls de-identification procedure ###########
mysql -u $2 -p$3 -h $1 -D $4 -e "call de_identification()"



