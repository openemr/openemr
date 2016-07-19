#!/usr/bin/env bash
rm -f *.pdf

cd ../examples
for PHP_SCRIPT in $(ls ./*.php);
do
    PDF_FILE=`echo "$PHP_SCRIPT" | sed -r 's/\.php/\.pdf/g' | sed -r 's/\.\//\.\.\/test\//g'`
    echo "Example $PHP_SCRIPT => $PDF_FILE"
    php $PHP_SCRIPT > $PDF_FILE
done

cd ../test
ls -l
