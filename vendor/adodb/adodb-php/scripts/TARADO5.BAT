@rem REQUIRES P:\INSTALLS\CMDUTILS

echo Don't forget to strip LF's !!!!!!!!!!!
pause


set VER=518a

d:
cd \inetpub\wwwroot\php

@del /s /q zadodb\*.*
@mkdir zadodb

@REM not for release -- make sure in VSS
attrib -r adodb5\drivers\adodb-text.inc.php
del adodb5\*.bak
del adodb5\drivers\*.bak
del adodb5\hs~*.*
del adodb5\drivers\hs~*.*
del adodb5\tests\hs~*.*
del adodb5\drivers\adodb-text.inc.php
del adodb5\.#*
del adodb5\replicate\replicate-steps.php
del adodb5\replicate\test*.php
del adodb5\adodb-lite.inc.php
attrib -r adodb5\*.php
del adodb5\cute_icons_for_site\*.png

del tmp.tar
del adodb5*.tgz
del adodb5*.zip

@mkdir adodb5\docs
move /y adodb5\*.htm adodb5\docs

@rem CREATE TAR FILE
tar -f adodb%VER%.tar -c adodb5/*.* adodb5/perf/*.* adodb5/session/*.*  adodb5/pear/*.txt adodb5/pear/Auth/Container/ADOdb.php adodb5/session/old/*.* adodb5/drivers/*.* adodb5/lang/*.* adodb5/tests/*.* adodb5/cute_icons_for_site/*.* adodb5/datadict/*.* adodb5/contrib/*.* adodb5/xsl/*.* adodb5/docs/*.*

@rem CREATE ZIP FILE
cd zadodb
tar -xf ..\adodb%VER%.TAR
zip -r ..\adodb%VER%.zip adodb5
cd ..

@rem CREATE TGZ FILE, THE RENAME CHANGES UPPERCASE TO LOWERCASE
gzip -v ADODB%VER%.tar -S .tgz -9
rename ADODB%VER%.tar.TGZ adodb%VER%.tgz

