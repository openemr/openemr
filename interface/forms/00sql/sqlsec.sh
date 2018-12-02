#!/bin/bash
### STAGING FILE FOR BINDING PARAMETERS ARRAY
# Set variables
PHP_FILE=$1
# extract all values of $_POST[...] from file into staging file
cat $PHP_FILE | grep -o '\$\_POST\[\S*\]' > stagingFile.txt
# place all instances in file with 7 words per line and add in commas
xargs -n7 <stagingFile.txt > outputFile.txt
# add in commas
sed -ie 's/\s/,\ /g' outputFile.txt
sed -ie 's/]$/],/g' outputFile.txt
# add in quotes
sed -ie 's/ST\[/ST\[\"/g' outputFile.txt
sed -ie 's/\]/\"\]/g' outputFile.txt
# clean up
rm -f stagingFile.txt
rm -f outputFile.txte

### CHANGE ORIGINAL PHP FILE
# replace most instances in query string with ?
sed -ier 's/[\=]\S*\$\_POST\[\S*\,/\=\?\,\ /g' $PHP_FILE