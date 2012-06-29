# the input file. normally, you will call make with 'make INFILE=my_xml_file_here.xml', to override this.
INFILE=gyn_ros.xml

# The xslt processor. We can use xalan or xsltproc standalone. to use libxslt in php5, see xmlformthrough.

# Uncomment this block to use xsltproc
XSLTPROC=/usr/bin/xsltproc
INOPT=
XSLOPT=

## Uncomment this block to use xalan
#XSLTPROC=/usr/bin/xalan
#INOPT=-in
#XSLOPT=-xsl

# detect the name of the directory to create for storing the files generated from the input xml document.
DIRNAME=$(shell ${XSLTPROC} ${XSLOPT} xslt/safename.xslt ${INOPT} ${INFILE})

# building rules

all: ${DIRNAME} ${DIRNAME}/info.txt ${DIRNAME}/table.sql ${DIRNAME}/style.css ${DIRNAME}/new.php ${DIRNAME}/print.php ${DIRNAME}/report.php ${DIRNAME}/view.php ${DIRNAME}/save.php ${DIRNAME}/show.php

${DIRNAME}:
	mkdir ${DIRNAME}

${DIRNAME}/%.txt: xslt/%.txt.xslt ${INFILE}
	${XSLTPROC} ${XSLOPT} xslt/info.txt.xslt ${INOPT} ${INFILE} > $@

${DIRNAME}/%.sql: xslt/%.sql.xslt ${INFILE}
	${XSLTPROC} ${XSLOPT} xslt/$*.sql.xslt ${INOPT} ${INFILE} > $@

${DIRNAME}/%.css: xslt/%.css.xslt ${INFILE}
	${XSLTPROC} ${XSLOPT} xslt/$*.css.xslt ${INOPT} ${INFILE} > $@

${DIRNAME}/%.php: xslt/%.php.xslt ${INFILE}
	${XSLTPROC} ${XSLOPT} xslt/$*.php.xslt ${INOPT} ${INFILE} > $@

clean:
	rm -rf ${DIRNAME}

formscript.pl: formscript.pl.orig
	cat formscript.pl.orig | tr -d '\r' > formscript.pl
	chmod 755 formscript.pl

#additional dependancies, to regenerate output when the developer is editing code.

${DIRNAME}/new.php: xslt/field_objects.xslt xslt/common_objects.xslt

${DIRNAME}/print.php: xslt/field_objects.xslt xslt/common_objects.xslt

${DIRNAME}/show.php: xslt/field_objects.xslt xslt/common_objects.xslt xslt/report_objects.xslt

${DIRNAME}/view.php:  xslt/field_objects.xslt xslt/common_objects.xslt

${DIRNAME}/save.php: xslt/common_objects.xslt

${DIRNAME}/report.php: xslt/common_objects.xslt

