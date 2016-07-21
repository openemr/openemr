P     H     I     N     G
=========================


Mar. 10, 2016 - Phing 2.14
--------------------------

This release contains the following new or improved functionality:

 * Phing can now emit a specific status code on exit after failing
 * Added IsPropertyTrue/IsPropertyFalse conditions
 * Added IsWritable / IsReadable selectors
 * Added GitDescribe task
 * Added CutDirs mapper
 * Line breaks in property files on Windows machines fixed
 * FileSync task now supports excluding multiple files/directories
 * Various typo and bug fixes, documentation updates

The following tickets were closed in this release:

 * [1245] ExecTask documentation has incorrect escape attribute default value
 * [1244] phpunit task -- problem when listener depends on bootstrap
 * [1242] symfonyConsoleTask does not quote path to console
 * [1241] SymfonyConsoleTask's checkreturn / propertyname are not documented
 * [1239] ResolvePath just concatenates if "dir" attribute is present
 * [1237] HttpGetTask should catch HTTP_Request2_Exception, throw BuildException
 * [1236] version-compare condition typo in documentation
 * [1235] misworded sentence in documentation
 * [1234] IsFailure condition always evaluates to TRUE
 * [1231] JsHintTask fails when filename contains double quotes
 * [1198] PropertyTask resolving UTF-8 special chars in file attribute
 * [1194] Update relax-ng schema
 * [1132] Provide SHA512 sum of all generated archives for a release
 * [1131] Verification of changelog file fails when your file is in a directory added in your classpathref
 * [1046] ReplaceTokensWithFile doesn't support begintoken/endtokens with / in them

Dec. 4, 2015 - Phing 2.13
-------------------------

This release contains the following new or improved functionality:

 * '-listener' command line argument
 * SSL connections in FtpDeploy task
 * IsFailure condition
 * Crap4J PHPUnit formatter
 * FirstMatch mapper
 * PhpArrayMapLines filter
 * NotifySend, Attrib tasks
 * Json and Xml command line loggers
 * Property parser now supports YAML files
 * PHPUnit 5.x supported
 * PHP 7 fixes
 * Updated Apigen support
 * PhpCodeSniffer task can now populate a property with used sniffs
 * PHPMD and PhpCodeSniffer task can now cache results to speed up
   subsequent runs
 * Various typo and bug fixes, documentation updates

The following tickets were closed in this release:

 * [1224] JSHint and space in the path of the workspace (Windows 7)
 * [1221] Case insensitive switch doesn't work
 * [1217] Add ability to ignore symlinks in zip task
 * [1212] Add support for formatters for PhpLoc task
 * [1187] Disable compression of phing.phar to make it work on hhvm

Aug. 24, 2015 - Phing 2.12
--------------------------

This release contains the following new or improved functionality:

 * Retry, Tempfile, Inifile tasks
 * 'keepgoing' command line mode
 * Fileset support in the Import task
 * EscapeUnicode, Concat filters
 * Profile logger
 * Composite mapper
 * Various typo and bug fixes

The following tickets were closed in this release:

 * [1208] When UntarTask fails to extract an archive it should tell why
 * [1207] PackageAsPath Task exists in 2.11, but not in documentation
 * [1206] WaitFor task has maxwaitunit attribute, not WaitUnit
 * [1205] Triple "B.37.1 Supported Nested Tags" header
 * [1204] Wrong type of record task loglevel attribute
 * [1203] Duplicated doc for Apply task, spawn attribute
 * [1199] PHPUnitReport task: package name detection no longer works
 * [1196] Target 'phing.listener.AnsiColorLogger' does not exist in this project.
 * [1193] There is no native method for manipulating .ini files.
 * [1191] phing parallel task should handle workers dying unexpectedly
 * [1190] RegexTask processes backslashes incorrectly
 * [1189] Coverage Report broken for Jenkins PHP Clover
 * [1178] Parameter getValue is null when parameter is equal to 0
 * [1148] phpdoc2 via phar

May 20, 2015 - Phing 2.11
-------------------------

This release contains the following new or improved functionality:

 * PharData and EchoProperties tasks
 * 'silent' and 'emacs' command line modes
 * Improvements to FileHash and FtpDeploy tasks
 * SuffixLines and Sort filters

The following tickets were closed in this release:

 * [1186] Implement pharLocation attribute for PHP_Depend task
 * [1185] Implement pharLocation attribute for PHPMD task
 * [1183] Fatal error in PHPMDTask
 * [1176] Showwarnings doesn't work
 * [1170] Allow more than one code standard review for PHP_CodeSniffer.
 * [1169] Allow for fuzzy parameter for phpcpdPHPCPD
 * [1162] add depth param to GitCloneTask
 * [1161] Update phpcpd & phploc tasks to work with phar versions
 * [1134] Phar version did not provide colorized output
 * [462] Incremental uploads in ftp deploy task

Feb. 19, 2015 - Phing 2.10.1
----------------------------

This release fixes the following tickets:

 * [1174] Phing can't work PHPUnit(PHAR)
 * [1173] [PHP Error] include_once(PHP/PPMD/Renderer/XMLRenderer.php): failed to open stream: No such file or directory
 * [1171] Socket condition does not work

Feb. 9, 2015 - Phing 2.10.0
--------------------------

This release contains the following new or improved functionality:

 * 'user.home' property on Windows fixed
 * Various documentation updates
 * Added support for listeners configured via phpunit.xml config
 * Basename task
 * Dirname task
 * Diagnostics task
 * FilesMatch condition
 * HasFreeSpace condition
 * PathToFileSet task
 * PhingVersion task/condition
 * PropertyRegex task
 * Recorder task
 * Socket condition
 * Xor condition

The following tickets were closed in this release:

 * [1168] PhpCodeSnifferTask incompatible with PHP_CS 2.2.0
 * [1167] include task can't really have mode
 * [1163] Phing and PHPMD via composer both
 * [1160] Documentation lists covereage-report styledir as required.
 * [1159] phpunit task ignores excludeGroups, groups attributes
 * [1152] Add socket condition
 * [1127] Removing .phar from the phar file makes it crash
 * [1120] Phing 2.8.1 does not support PDepend 2.0
 * [856]  ZPK Packaging for zend server
 * [250]  recorder task

Dec. 3, 2014 - Phing 2.9.1
--------------------------

This releases fixes a Windows regression and adds the following new functionality:

 * Http condition
 * Switch task
 * Throw task

The following tickets were closed in this release:

 * [1158] Phing fails to call itself with Exec task
 * [1157] ZIP task ignores ${phing.dir}
 * [1156] phing+windows copy file path
 * [1155] Add http condition
 * [1154] Can't read version information file
 * [1147] Resetting Phing::$msgOutputLevel

Nov. 25, 2014 - Phing 2.9.0
---------------------------

This release contains the following new or improved functionality:

 * Phing now supports HHVM
 * Stopwatch task added
 * Unit test coverage increased
 * Source code formatted to PSR-2
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [1151] PHPMD Task does not support the format tag
 * [1149] Exclude extra files from composer package
 * [1144] Reduce PhingCall/Foreach log messages
 * [1140] DefaultLogger is not default logger
 * [1138] ParallelTask - error in subtask should fail build
 * [1135] obfuscation-key option for IoncubeEncoderTask does not work
 * [1133] copytask haltonerror = "false" function failure when source dir not exists
 * [1130] Add documentation for Manifest task
 * [1129] ManifestTask md5 hash vs FileHashTask md5 hash not the same
 * [1128] Imported target won't run until there is one with the same name in main build.xml
 * [1123] ApplyTask outputProperty doesn't append
 * [1122] Untar task does not preserve file permissions
 * [1121] Please fix the syntax error in PHP Lint
 * [1104] ArchiveComment Parameter for ZipTask
 * [1095] ReferenceExistsCondition returns true for all UnknownElements
 * [1089] phing -l is listing imported targets twice
 * [1086] Support for running on HHVM
 * [1084] pdepend task does not find dependencies when installed by composer
 * [1069] PHPUnitTask formatter does not create directory if specified "todir" does not exist
 * [1068] Phingcall and Import issues
 * [1040] Composer task has no documentation
 * [1012] SymlinkTaks overwrite fails if target doesn't exist
 * [965] includePathTask: Allow appending and replacing
 * [945] several phpunit task problems
 * [930] Attribute logoutput to property task
 * [796] Can't delete all subdirectories without directory itself
 * [441] Reformat Phing source code to PSR-2

Jul. 18, 2014 - Phing 2.8.2
---------------------------

This patch release fixes two regressions.

 * [1119] #1111 breaks PHPLint task
 * [1118] Property "X" was circularly defined.

Jul. 1, 2014 - Phing 2.8.1
--------------------------

This patch release fixes a regression preventing Phing from
being used on machines where PEAR is not installed, as well
as another (unrelated) issue.

 * [1114] PHP Fatal Error using Phing on machines without PEAR
 * [1111] setting PhpLintTask interpreter

Jun. 30, 2014 - Phing 2.8.0
---------------------------

New or improved functionality:

 * The rsync task can now handle remote connections without specifying a username
 * The rsync task now creates remote directories as needed by default
 * Support for PHP MD 2.*
 * Various tasks now support dependencies loaded through composer
 * AutoloaderTask added
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [1108] pdosqlexec doesn't throw exception for the non-first SQL instruction
 * [1106] Add .git and associated files to defaultexcludes attribute
 * [1105] PHPUnitTask: attributes 'groups' and 'excludeGroups' not documented
 * [1102] Phing is not compatible with PHPMD 2.0.0 beta
 * [1101] Add (optional) external deps to suggest section in composer.json
 * [1100] Add composer / PHAR installation instructions to README & web pages
 * [1099] Allow loading of externals through composer [meta ticket]
 * [1091] Phing is not compatible with PHPUnit 4.x
 * [1090] PearPackageFileSet copies files with baseinstalldir incorrectly
 * [1085] Conditions section (5.8) does not correctly link to mentioned tasks
 * [1084] pdepend task does not find dependencies when installed by composer
 * [980] Support for .dist files
 * [975] Included JSmin has non-free license
 * [964] includePathTask: talk about appending/prepending

Feb. 13, 2014 - Phing 2.7.0
---------------------------

New or improved functionality:

 * Support for PHP CodeSniffer 1.5, PHP Copy&Paste Detector 2.0 and PHPLOC 2.0
 * Composer support for PHPCPD and PhpDocumentor tasks
 * Fixed / improved error handling in various places
 * More unit / regression tests added
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [1083] PhpDocumentor2Task: add support for default package name
 * [1082] Tasks in root target are executed twice
 * [1081] Documentation of AvailableTask does not link to conditions page
 * [1078] IoncubeEncoderTask does not support PHP 5.4
 * [1073] Phing silently died, when cant read build.xml
 * [1070] PHPCS 1.5.0 breaks PHPCodeSniffer Task
 * [1064] Formatter 'brief' not implemented when using Unittest task
 * [1063] PHPCPD 2.0 breaks PHPCPD Task
 * [1062] AvailableTask throws exception when filepath contains duplicates
 * [1059] phing exits with return code 0 when there is unknown argument
 * [1057] pdo exception thrown from pdosqlexec not properly handled
 * [1056] filesyncTask: problem (error?) with verbose (-v) option
 * [1054] Missing or erroneous definition in phing-grammar.rng
 * [1053] Add composer support for phpdoc2 task
 * [1051] phing 2.6.1 - impossible upgrade
 * [1045] PHPLocTask broken with recent phploc updates
 * [1044] Using fileset in echo does not list subdirectories
 * [1042] Fix UnknownElement wrapping and configuring
 * [1035] phpcpd tasks does not find dependencies when installed by composer
 * [1034] Improving debuggability of errors in custom code
 * [1032] FileSync Port
 * [1030] JsMin task creates directories with 0700 permissions
 * [1028] Change visibility of FailTask variables
 * [1021] MailTask backend configuration
 * [1010] Invalid error about refid attribute when specifying multiple targets
 * [1009] certain liquibase tasks (rollback, tag and update) do not check return value
 * [994] Clarify pdoexec autocommit/transactions
 * [991] GitCommit: add fileset support
 * [984] Improve documentation about including custom tasks
 * [983] Selenium with PHPUnit: browser configurations are not processed
 * [978] svn switches: recursive
 * [976] phpunitreport: broken html for test suite names containing "/"
 * [650] Namespace support for extensions (PSR0 support)

Aug. 27, 2013 - Phing 2.6.1
---------------------------

This patch release fixes a regression when setting properties
in then/else blocks.

Note: the fix currently disables support for custom conditions,
full support will be restored in Phing 2.7.0.

 * [1041] Properties within then/else blocks are not expanded

Aug. 21, 2013 - Phing 2.6.0
---------------------------

New or improved functionality:

 * Docbook5 documentation is now the main documentation; output targets
   are 'hlhtml', 'chunkhtml', 'hlpdf', 'epub' and 'webhelp'
 * HttpRequest task supports POST request
 * PharPackage task supports PKCS#12 certificate stores
 * WikiPublish task was added
 * Smarty task is now compatible with Smarty 3
 * A new logger 'TargetLogger' was added, displaying the execution time for each target
 * Composer task and package were updated
 * More unit / regression tests added
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [1037] PropertyTask docs is wrong
 * [1036] Error in ApplyTask->executeCommand()
 * [1029] PhpDocumentor2 task broken with latest phpdoc version
 * [1027] RegexpMapper uses deprecated PREG_REPLACE_EVAL
 * [1025] PHPLocTask fails when installed via composer
 * [1023] Argument 1 passed to IniFileTokenReader::setFile() must be an instance of PhingFile
 * [1020] [PHP Error] Illegal string offset 'filename' [line 149 of /usr/share/pear/phing/tasks/ext/ExtractBaseTask.php]
 * [1015] phing does not allow phpunit to echo
 * [1011] Problem with spaces in output redirection path
 * [1004] <gitcommit .../> does not work because task definition is missing in defaults.properties + another bug
 * [1003] 2 php syntax bugs in GitCommitTask
 * [1000] Make phing.phar work out of the box
 * [999]  phing-2.5.0.phar Can't load default task list
 * [993]  passthru will redirect stderr
 * [990]  Prompting for a property value when it is not set results in a repeated input message
 * [985]  Git Commit Task missing from docs
 * [981]  FileUtil::copyFile(): $preserveLastModified causes empty symlink target file
 * [970]  FileSyncTask missing from docbook5 documentation
 * [966]  phing unit tests nice on all platforms
 * [920]  Load phpdepend dependency only when they are used
 * [906]  Move to docbook5 documentation
 * [438]  pdosqlexec: add delimiterType=none (default), clarify delimiter documentation (was: pdosqlexec triggers segmentation fault)

Feb. 16, 2013 - Phing 2.5.0
---------------------------

This release addresses the following issues:

 * [979] svncommit: invalid switch ignoreexternals
 * [977] phpunit Task doesn't support @codeCoverageIgnore[...] comments
 * [972] SvnCopyTask: remove "force" from documentation
 * [971] TokenSource does not work
 * [969] PHPUnit task does not report diffs for failed assertions
 * [968] Proper handling of STDOUT and STDERR
 * [963] XSLT task fails with fatal error on PHP 5.4
 * [962] DbDeploy: infinite loop in case if directory not found
 * [961] DbDeploy: checkall output isn't informative
 * [960] Documentation of Dbdeploy task
 * [959] Bug in SvnListTask Version 2.4.14
 * [958] Property wrapped in if/then structure is not substituted by it's value
 * [954] Paths becoming part of S3 file names on Windows
 * [953] Add PHP extension check to Available Task
 * [952] Properly document how to load environment variables as properties
 * [951] S3Put throws "Source is not set" exception
 * [949] SymfonyConsoleTask improvements: checkreturn and output of command
 * [947] AvailableTask does not work on unix domain sockets
 * [946] <target hidden="true> is undocumented
 * [941] ZendGuardEncode under Windows 7
 * [937] DbDeployTask applied_by username is hardcoded and cannot be changed
 * [935] phpcodesniffertask does not work on CSS and JS files
 * [932] SshTask Methods Options
 * [921] JSL Lint Task - Halt on warning
 * [910] Add preservepermissions flag to copy task
 * [898] Add ApplyTask
 * [838] -D option doesn't work with a space after it
 * [599] Phar package does not work on Windows platforms

Nov. 29, 2012 - Phing 2.4.14
----------------------------

This release addresses the following issues:

  * [944] phing/phingdocs bad md5sum
  * [943] If task with "equals" directly in "project" tag does not work
  * [942] Typo in tasks/ext/dbdeploy/DbmsSyntaxOracle.php
  * [939] Add username/password to svn info/lastrevision/list/log task docs
  * [938] XSLT filter fails when libxslt security present in php

Starting from this version, Phing releases and release numbers will follow
the Semantic Versioning (www.semver.org) principle.

Nov. 20, 2012 - Phing 2.4.13
----------------------------

This release updates the composer package, adds a phploc task and improved
support for phpDocumentor 2 and IonCube 7, improves the unit tests,
clarifies the documentation in a number of places, and addresses
the following issues:

  * [933] PHPLoc 1.7 broken
  * [931] PHP_CodeSniffer throws errors with CodeSniffer 1.4.0
  * [929] Can not pass empty string (enclosed in double quotes) as exec task argument
  * [928] Fatal error with ZipTask when zip extension is not loaded
  * [927] PHPCPD upgrade breaks PHPCPD task
  * [926] FtpDeployTask: Missing features and patch for them (chmod and only change if different)
  * [925] Problem with spaces in error redirection path.
  * [924] Update to PEAR::VersionControl_SVN 0.5.0
  * [922] Introduce build file property that contains the build file's directory
  * [915] path with special characters does not delete
  * [909] Replace __DIR__
  * [905] Add filterchain support to the property task
  * [904] TarTask should raise error if zlib extension not installed
  * [903] Cannot redeclare class phpDocumentor\Bootstrap
  * [902] SvnBaseTask and subversion 1.7
  * [901] phpunitreport create html's classes files in wrong folder
  * [900] phpdoc2 example has error
  * [895] error in includepath when calling more than once
  * [893] Phing will run bootstrap before first task but clean up autoloader before second task
  * [892] Concatenate property lines ending with backslash
  * [891] Symfony console task: space within the arguments, not working on windows
  * [890] Allow custom child elements
  * [888] Documentation error for CvsTask setfailonerror
  * [886] Error throwing in PDOSQLExecTask breaking trycatch
  * [884] svnlist fails on empty directories
  * [882] Dbdeploy does not retrieve changelog number with oracle
  * [881] Silent fail on delete tasks
  * [880] Add phploc task
  * [867] phpcpd task should check external dep in main()
  * [866] Code coverage not showing "not executed" lines
  * [863] MoveTask ignores fileset
  * [845] GrowlNotifyTask to be notified on long-task when they are finished
  * [813] Allow custom conditions
  * [751] Allow loading of phpunit.xml in phpunit task
  * [208] ReplaceRegexp problem with newline as replace string

Apr. 6, 2012 - Phing 2.4.12
---------------------------

  * [877] Add 'level' attribute to resolvepath task
  * [876] JslLint Task is_executable() broken
  * [874] ParallelTask.php is not PHP 5.2 compatible
  * [860] SvnBaseTask: getRecursive
  * [539] Custom build log mailer
  * [406] an ability to turn phpLint verbose ON and OFF

Apr. 4, 2012 - Phing 2.4.11
---------------------------

  * [870] Can't find ParallelTask.php

Apr. 3, 2012 - Phing 2.4.10
---------------------------

  * [872] ReplaceTokens can't work with '/' char
  * [870] Can't find ParallelTask.php
  * [868] Git Clone clones into wrong directory
  * [865] static call to a non-static function PhingFile.php::getTempdir()
  * [854] PropertyTask with file. Can't use a comment delimiter in the value.
  * [853] PHP Error with HttpGetTask
  * [852] Several minor errors in documentation of core tasks
  * [851] RNG grammar hasn't been updated to current version
  * [850] Typo in documentation - required attributes for project
  * [849] Symfony 2 Console Task
  * [847] Add support for RNG grammar in task XmlLint
  * [846] RNG grammar is wrong for task 'foreach'
  * [844] symlink task - overwrite not working
  * [843] "verbose" option should print fileset/filelist filenames before execution, not afterwards
  * [840] Prevent weird bugs: raise warning when a target tag contains no ending tag
  * [835] JSL-Check faulty
  * [834] ExecTask documentation has incorrect escape attribute default value
  * [833] Exec task args with special characters cannot be escaped
  * [828] SelectorUtils::matchPath matches **/._* matches dir/file._name
  * [820] Type selector should treat symlinks to directories as such
  * [790] Make it easy to add new inherited types to phing: Use addFileset instead of createFileset
  * [772] Support for filelist in UpToDateTask
  * [671] fix CvsTask documentation
  * [587] More detailed backtrace in debug mode (patch)
  * [519] Extend mail task to include attachments
  * [419] schema file for editors and validation
  * [334] Run a task on BuildException

Dec. 29, 2011 - Phing 2.4.9
---------------------------

  * [837] PHPMDTask should check external dep in main()
  * [836] DocBlox task breaks with version 0.17.0: function getThemesPath not found
  * [831] dbdeploy undo script SQL is not formatted correctly
  * [822] rSTTask: add debug statement when creating target directory
  * [821] phingcall using a lot of memory
  * [819] Documentation for SvnUpdateTask is outdated
  * [818] [patch] Add overwrite option to Symlink task
  * [817] Adding the "trust-server-cert" option to SVN tasks
  * [816] Fix notice in SimpleTestXmlResultFormatter
  * [811] phpunitreport path fails on linux
  * [810] AvailableTask resolving symbolic links
  * [807] SVN tasks do not always show error message
  * [795] Untar : allow overwriting of newer files when extracting
  * [782] PharTask is very slow for big project
  * [776] Add waitFor task
  * [736] Incompatibility when copying from Windows to Linux on ScpTask
  * [709] talk about invalid property values
  * [697] More descriptive error messages in PharPackageTask
  * [674] Properties: global or local in tasks?
  * [653] Allow ChownTask to change only group
  * [619] verbose level in ExpandPropertiesFilter

Nov. 2, 2011 - Phing 2.4.8
--------------------------

  * [814] Class 'PHPCPD_Log_XML' not found in /home/m/www/elvis/vendor/phpcpd/PHPCPD/Log/XML/PMD.php on line 55
  * [812] Fix PHPUnit 3.6 / PHP_CodeCoverage 1.1.0 compatibility
  * [808] Bad example for the <or> selector
  * [805] phing executable has bug in ENV/PHP_COMMAND
  * [804] PhpUnitTask overwrites autoload stack
  * [801] PhpCodeSnifferTask doesn't pass files encoding to PHP_CodeSniffer
  * [800] CoverageReportTask fails with "runtime error" on PHP 5.4.0beta1
  * [799] DbDeploy does not support pdo-dblib
  * [798] ReplaceTokensWithFile - postfix attribute ignored
  * [797] PhpLintTask performance improvement
  * [794] Fix rSTTask to avoid the need of PEAR every time
  * [793] Corrected spelling of name
  * [792] EchoTask: Fileset support
  * [789] rSTTask unittests fix
  * [788] rSTTask documentation: fix examples
  * [787] Add pearPackageFileSet type
  * [785] method execute doesn't exists in CvsTask.php
  * [784] Refactor DocBlox task to work with DocBlox 0.14+
  * [783] SvnExportTask impossible to export current version from working copy
  * [779] phplint task error summary doesn't display the errors
  * [775] ScpTask: mis-leading error message if 'host' attribute is not set
  * [772] Support for filelist in UpToDateTask
  * [770] Keep the RelaxNG grammar in sync with the code/doc
  * [707] Writing Tasks/class properties: taskname not correctly used
  * [655] PlainPHPUnitResultFormatter does not display errors if @dataProvider was used
  * [578] [PATCH] Add mapper support to ForeachTask
  * [552] 2 validargs to input task does not display defaults correctly

Aug. 19, 2011 - Phing 2.4.7.1
-----------------------------

This is a hotfix release.

  * [774] Fix PHP 5.3 dependency in CoverageReportTask
  * [773] Fix for Ticket #744 breaks PHPCodeSnifferTask's nested formatters

Aug. 18, 2011 - Phing 2.4.7
---------------------------

This release fixes and improves several tasks (particularly the DocBlox
task), adds OCI/ODBC support to the dbdeploy task and introduces
a task to render reStructuredText.

  * [771] Undefined offset: 1 [line 204 of /usr/share/php/phing/tasks/ext/JslLintTask.php]
  * [767] PharPackageTask: metadata should not be required
  * [766] The DocBlox task does not load the markdown library.
  * [765] CoverageReportTask incorrectly considers dead code to be unexecuted
  * [762] Gratuitous unit test failures on Windows
  * [760] SelectorUtils::matchPath() directory matching broken
  * [759] DocBloxTask throws an error when using DocBlox 0.12.2
  * [757] Grammar error in ChmodTask documentation
  * [755] PharPackageTask Web/Cli stub path is incorrect
  * [754] ExecTask: <arg> support
  * [753] ExecTask: Unit tests and refactoring
  * [752] Declaration of Win32FileSystem::compare()
  * [750] Enable process isolation support in the PHPUnit task
  * [747] Improve "can't load default task list" message
  * [745] MkdirTask mode param mistake
  * [744] PHP_CodeSniffer formatter doesn't work with summary
  * [742] ExecTask docs: link os.name in os attribute
  * [741] ExecTask: missing docs for "output", "error" and "level"
  * [740] PHPMDTask: "InvalidArgumentException" with no globbed files.
  * [739] Making the jsMin suffix optional
  * [737] PHPCPDTask: omitting 'outfile' attribute with 'useFIle="false"'
  * [735] CopyTask can't copy broken symlinks when included in fileset
  * [733] DeleteTask cannot delete dangling symlinks
  * [731] Implement filepath support in Available Task
  * [720] rSTTask to render reStructuredText
  * [658] Add support to Oracle (OCI) in DbDeployTask
  * [580] ODBC in DbDeployTask
  * [553] copy task bails on symbolic links (filemtime)
  * [499] PDO cannot handle PL/Perl function creation statements in PostgreSQL

Jul. 12, 2011 - Phing 2.4.6
---------------------------

This release fixes a large number of issues, improves a number of tasks
and adds several new tasks (SVN log/list, DocBlox and LoadFile). 

  * [732] execTask fails to chdir if the chdir parameter is a symlink to a dir
  * [730] phpunitreport: styledir not required
  * [729] CopyTask fails when todir="" does not exist
  * [725] Clarify documentation for using AvailableTask as a condition
  * [723] setIni() fails with memory_limit not set in Megabytes
  * [719] TouchTask: file not required?
  * [718] mkdir: are parent directories created?
  * [715] Fix for mail task documentation
  * [712] expectSpecificBuildException fails to detect wrong exception message
  * [708] typo in docs: "No you can set"
  * [706] Advanced task example missing
  * [705] Missing links in Writing Tasks: Summary
  * [704] Case problem in "Writing Tasks" with setMessage
  * [703] missing links in "Package Imports"
  * [701] Setting more then two properties in command line not possible on windows
  * [699] Add loadfile task
  * [698] Add documentation for patternset element to user guide
  * [696] CoverageReportTask doesn't recognize UTF-8 source code
  * [695] phpunit Task doesn't support @codeCoverageIgnore[...] comments
  * [692] Class 'GroupTest' not found in /usr/share/php/phing/tasks/ext/simpletest/SimpleTestTask.php on line 158
  * [691] foreach doesn't work with filelists
  * [690] Support DocBlox
  * [689] Improve documentation about selectors
  * [688] SshTask Adding (+propertysetter, +displaysetter)
  * [685] SvnLogTask and SvnListTask
  * [682] Loading custom tasks should use the autoloading mechanism
  * [681] phpunit report does not work with a single testcase
  * [680] phpunitreport: make tables sortable
  * [679] IoncubeEncoderTask improved
  * [673] new listener HtmlColorLogger
  * [672] DbDeployTask::getDeltasFilesArray has undefined variable
  * [671] fix CvsTask documentation
  * [670] DirectoryScanner: add darcs to default excludes
  * [668] Empty Default Value Behaves Like the Value is not set
  * [667] Document how symbolic links and hidden files are treated in copy task
  * [663] __toString for register slots
  * [662] Hiding the command that is executed with "ExecTask"
  * [659] optionally skip version check in codesniffer task
  * [654] fileset not selecting folders
  * [652] PDOSQLExec task doesn't close the DB connection before throw an exception or at the end of the task.
  * [642] ERROR: option "-o" not known with phpcs version 1.3.0RC2 and phing/phpcodesniffer 2.4.4
  * [639] Add verbose mode for SCPTask
  * [635] ignored autocommit="false" in PDOTask?
  * [632] CoverageThresholdTask needs exclusion option/attribute
  * [626] Coverage threshold message is too detailed...
  * [616] PhpDocumentor prematurely checks for executable
  * [613] Would be nice to have -properties=<file> CLI option
  * [611] Attribute "title" is wanted in CoverageReportTask
  * [608] Tweak test failure message from PHPUnitTask
  * [591] PhpLintTask don't log all errors for each file
  * [563] Make PatchTask silent on FreeBSD
  * [546] Support of filelist in CodeCoverageTask
  * [527] pearpkg2: unable to specify different file roles
  * [521] jslint warning logger

Mar. 3, 2011 - Phing 2.4.5
--------------------------

This release fixes several issues, and reverts the changes
that introduced the ComponentHelper class.

  * [657] Wrong example of creating task in stable documentation.
  * [656] Many erratas on the "Getting Started"-page.
  * [651] Messages of ReplaceTokens should be verbose
  * [641] 2.4.4 packages contains .rej and .orig files in release tarball
  * [640] "phing -q" does not work: "Unknown argument: -q"
  * [634] php print() statement outputting to stdout
  * [624] PDOSQLExec fails with Fatal error: Class 'LogWriter' not found in [...]/PDOSQLExecFormatterElement
  * [623] 2.4.5RC1 requires PHPUnit erroneously
  * [621] PhpLintTask outputs all messages (info and errors) to same loglevel
  * [614] phpcodesniffer task changes Phing build working directory
  * [610] BUG: AdhocTaskdefTask fails when creating a task that extends from an existing task
  * [607] v 2.4.4 broke taskdef for tasks following PEAR naming standard
  * [603] Add support to PostgreSQL in DbDeployTask
  * [601] Add HTTP_Request2 to optional dependencies
  * [600] typo in ReplaceRegexpTask
  * [598] Wrong version for optional Services_Amazon_S3 dependency
  * [596] PhpDependTask no more compatible with PDepend since 0.10RC1
  * [593] Ssh/scp task: Move ssh2_connect checking from init to main
  * [564] command line "-D" switch not handled correctly under windows
  * [544] Wrong file set when exclude test/**/** is used

Dec. 2, 2010 - Phing 2.4.4
--------------------------

This release fixes several issues.

  * [595] FilterChain without ReplaceTokensWithFile creator
  * [594] Taskdef in phing 2.4.3 was broken!
  * [590] PhpLintTask don't flag files that can't be parsed as bad files
  * [589] Mail Task don't show recipients list on log
  * [588] Add (optional) dependency to VersionControl_Git and Services_Amazon_S3 packages
  * [585] Same line comments in property files are included in the property value
  * [570] XmlLintTask - check well-formedness only
  * [568] Boolean properties get incorrectly expanded
  * [544] Wrong file set when exclude test/**/** is used
  * [536] DbDeployTask: Undo script wrongly generated

Nov. 12, 2010 - Phing 2.4.3
---------------------------

This release adds tasks to interface with Git and Amazon S3, adds support for PHPUnit 3.5,
and fixes numerous issues.

  * [583] UnixFileSystem::compare() is broken
  * [582] Add haltonerror attribute to copy/move tasks
  * [581] XmlProperty creating wrong properties
  * [577] SVN commands fail on Windows XP
  * [575] xmlproperty - misplaced xml attributes
  * [574] Task "phpcodesniffer" broken, no output
  * [572] ImportTask don't skipp file if optional is set to true
  * [560] [PATCH] Compatibility with PHPUnit 3.5.
  * [559] UpToDate not override value of property when target is called by phingcall
  * [555] STRICT Declaration of UnixFileSystem::getBooleanAttributes() should be compatible with that of FileSystem::getBooleanAttributes()
  * [554] Patch to force PhpDocumentor to log using phing
  * [551] SVN Switch Task
  * [550] Ability to convert encoding of files
  * [549] ScpTask doesn't finish the transfer properly
  * [547] The new attribute version does not work
  * [543] d51PearPkg2Task: Docs link wrong
  * [542] JslLintTask: wrap conf parameter with escapeshellarg
  * [537] Install documentation incorrect/incomplete
  * [536] DbDeployTask: Undo script wrongly generated
  * [534] Task for downloading a file through HTTP
  * [531] cachefile parameter of PhpLintTask also caches erroneous files
  * [530] XmlLintTask does not stop buid process when schema validation fails
  * [529] d51pearpkg2: setOptions() call does not check return value
  * [526] pearpkg2: extdeps and replacements mappings not documented
  * [525] pearpkg2: minimal version on dependency automatically set max and recommended
  * [524] pearpkg2: maintainers mapping does not support "active" tag
  * [520] Need SvnLastChangedRevisionTask to grab the last changed revision for the current working directory
  * [518] [PHP Error] file_put_contents(): Filename cannot be empty in phpcpdesniffer task
  * [513] Version tag doesn't increment bugfix portion of the version
  * [511] Properties not being set on subsequent sets.
  * [510] to show test name when testing fails
  * [501] formatter type "clover" of task "phpunit" doesn't generate coverage according to task "coverage-setup"
  * [488] FtpDeployTask is very silent, error messages are not clear
  * [455] Should be able to ignore a task when listing them from CLI
  * [369] Add Git Support

Jul. 28, 2010 - Phing 2.4.2
---------------------------

  * [509] Phing.php setIni() does not honor -1 as unlimited
  * [506] Patch to allow -D<option> with no "=<value>"
  * [503] PHP Documentor Task not correctly documented
  * [502] Add repository url support to SvnLastRevisionTask
  * [500] static function call in PHPCPDTask
  * [498] References to Core types page are broken
  * [496] __autoload not being called
  * [492] Add executable attribute in JslLint task
  * [489] PearPackage Task fatal error trying to process Fileset options
  * [487] Allow files in subdirectories in ReplaceTokensWithFile filter
  * [486] PHP Errors in PDOSQLExecTask
  * [485] ReplaceTokensWithFile filter does not allow HTML translation to be
      switched off
  * [484] Make handling of incomplete tests when logging XML configurable
  * [483] Bug in FileUtils::copyFile() on Linux - when using FilterChains,
      doesn't preserve attributes
  * [482] Bug in ChownTask with verbose set to false
  * [480] ExportPropertiesTask does not export all the initialized properties
  * [477] HttpRequestTask should NOT validate output if regex is not provided
  * [474] Bad Comparisons in FilenameSelector (possibly others)
  * [473] CPanel can't read Phing's Zip Files
  * [472] Add a multiline option to regex replace filter
  * [471] ChownTask throws exception if group is given
  * [468] CopyTask does not accept a FileList as only source of files
  * [467] coverage of abstract class/method is always ZERO
  * [466] incomplete logging in coverage-threshold
  * [465] PatchTask should support more options
  * [463] Broken Links in coverage report
  * [461] version tag in project node

Mar. 10, 2010 - Phing 2.4.1
---------------------------

  * [460] FtpDeployTask error
  * [458] PHPCodeSniffer Task throws Exceptions
  * [456] Fileset's dir should honor expandsymboliclinks
  * [449] ZipTask creates ZIP file but doesn't set file/dir attributes
  * [448] PatchTask
  * [447] SVNCopy task is not documented
  * [446] Add documentation describing phpdocext
  * [444] PhpCodeSnifferTask fails to generate a checkstyle-like output
  * [443] HttpRequestTask is very desirable
  * [442] public key support for scp and ssh tasks
  * [436] Windows phing.bat can't handle PHP paths with spaces
  * [435] Phing download link broken in bibliography
  * [433] Error in Documentation in Book under Writing a simple Buildfile
  * [432] would be nice to create CoverateThresholdTask
  * [431] integrate Phing with PHP Mess Detector and PHP_Depend
  * [430] FtpDeployTask is extremely un-verbose...
  * [428] Ability to specify the default build listener in build file
  * [426] SvnExport task documentation does not mention "revision" property
  * [421] ExportProperties class incorrectly named
  * [420] Typo in setExcludeGroups function of PHPUnitTask
  * [418] Minor improvement for PhpLintTask

Jan. 17, 2010 - Phing 2.4.0
---------------------------

  * [414] PhpLintTask: retrieving bad files
  * [413] PDOSQLExecTask does not recognize "delimiter" command
  * [411] PhpEvalTask calculation should not always returns anything
  * [410] Allow setting alias for Phar files as well as a custom stub
  * [384] Delete directories fails on '[0]' name

Dec. 17, 2009 - Phing 2.4.0 RC3
-------------------------------

  * [407] some error with svn info
  * [406] an ability to turn phpLint verbose ON and OFF
  * [405] I can't get a new version of Phing through PEAR
  * [402] Add fileset/filelist support to scp tasks
  * [401] PHPUnitTask 'summary' formatter produces a long list of results
  * [400] Support for Clover coverage XML
  * [399] PhpDocumentorExternal stops in method constructArguments
  * [398] Error using ResolvePath on Windows
  * [397] DbDeployTask only looks for -- //@UNDO (requires space)
  * [396] PDOSQLExecTask requires both fileset and filelist, rather than either or
  * [395] PharPackageTask fails to compress files
  * [394] Fix differences in zip and tar tasks
  * [393] prefix parameter for tar task
  * [391] Docs: PharPackageTask 'compress' attribute wrong
  * [389] Code coverage shows incorrect results Part2
  * [388] Beautify directory names in zip archives
  * [387] IoncubeEncoderTask noshortopentags
  * [386] PhpCpd output to screen
  * [385] Directory ignored in PhpCpdTask.php
  * [382] Add prefix parameter to ZipTask
  * [381] FtpDeployTask: invalid default transfer mode
  * [380] How to use PhpDocumentorExternalTask
  * [379] PHPUnit error handler issue
  * [378] PHPUnit task bootstrap file included too late
  * [377] Code coverage shows incorrect results
  * [376] ReplaceToken boolean problems
  * [375] error in docs for echo task
  * [373] grammar errors
  * [372] Use E_DEPRECATED
  * [367] Can't build simple build.xml file
  * [361] Bug in PHPCodeSnifferTask
  * [360] &amp;&amp; transfers into & in new created task
  * [309] startdir and 'current directory' not the same when build.xml not in current directory
  * [268] Patch - xmlproperties Task
  * [204] Resolve task class names with PEAR/ZEND/etc. naming convention
  * [137] Excluded files may be included in Zip/Tar tasks

Oct. 20, 2009 - Phing 2.4.0 RC2
-------------------------------

  * [370] Fatal error: Cannot redeclare class PHPUnit_Framework_TestSuite
  * [366] Broken link in "Getting Started/More Complex Buildfile"
  * [365] Phing 2.4rc1 via pear is not usable
  * [364] 2.4.0-rc1 download links broken
  * [363] PHPUnit task fails with formatter type 'xml'
  * [359] 403 for Documentation (User Guide) Phing HEAD
  * [355] PDOSQLExecTask should accept filelist subelement
  * [352] Add API documentation

Sep. 14, 2009 - Phing 2.4.0 RC1
-------------------------------

  * [362] Can't get phpunit code coverage to export as XML
  * [361] Bug in PHPCodeSnifferTask
  * [357] SvnLastRevisionTask fails when locale != EN
  * [356] Documentation for tasks Chmod and Chown
  * [349] JslLint task fails to escape shell argument
  * [347] PHPUnit / Coverage tasks do not deal with bootstrap code
  * [344] Phing ignores public static array named $browsers in Selenium tests
  * [342] custom-made re-engine in SelectorUtils is awful slow
  * [339] PHAR signature setting
  * [336] Use intval to loop through files
  * [333] XmlLogger doesn't ensure proper ut8 encoding of log messages
  * [332] Conditions: uptodate does not work
  * [331] UpToDateTask documentation says that nested FileSet tags are allowed
  * [330] "DirectoryScanner cannot find a folder/file named ""0"" (zero)"
  * [326] Add revision to svncheckout and svnupdate
  * [325] "<filterchain id=""xxx""> and <filterchain refid=""xxx""> don't work"
  * [322] phpdoc task not parsing and including  RIC files in documentation output
  * [319] Simpletest sometimes reports an undefined variable
  * [317] PhpCodeSnifferTask lacks of haltonerror and haltonwarning attributes
  * [316] Make haltonfailure attribute for ZendCodeAnalyzerTask
  * [312] SimpleTestXMLResultFormatter
  * [311] Fileset support for the TouchTask?
  * [307] Replaceregexp filter works in Copy task but not Move task
  * [306] Command-line option to output the <target> description attribute text
  * [303] Documentation of Task Tag SimpleTest
  * [300] ExecTask should return command output as a property (different from passthru)
  * [299] PhingCall crashes if an AdhocTask is defined
  * [292] Svn copy task
  * [290] Add facility for setting resolveExternals property of DomDocument object in XML related tasks
  * [289] Undefined property in XincludeFilter class
  * [282] Import Task fix/improvement
  * [280] Add Phar support (task) to Phing
  * [279] Add documentation to PHK package task
  * [278] Add PHK package task
  * [277] PhpCodeSnifferTask has mis-named class, patch included
  * [273] PHPUnit 3.3RC1 error in phpunit task adding files to filter
  * [270] [patch] ReplaceRegExp
  * [269] Allow properties to be recursively named.
  * [263] phpunit code coverage file format change
  * [262] Archive_Zip fails to extract on Windows
  * [261] UnZip task reports success on failure on Windows
  * [259] Unneeded warning in Untar task
  * [256] Ignore dead code in code coverage
  * [254] Add extra debug resultformatter to the simpletest task
  * [252] foreach on a fileset
  * [248] Extend taskdef task to allow property file style imports
  * [247] New task: Import
  * [246] Phing test brocken but no failure entry if test case class has no test method
  * [245] TAR task
  * [243] Delete task won't delete all files
  * [240] phing test successful while phpunit test is broken
  * [233] Separate docs from phing package
  * [231] File::exists() returns false on *existing* but broken symlinks
  * [229] CopyTask shoul accept filelist subelement
  * [226] <move> task doesn't support filters
  * [222] Terminal output disappears and/or changes color
  * [221] Support for copying symlinks as is
  * [212] Make file perms configurable in copy task
  * [209] Cache the results of PHPLintTask so as to not check unmodified files
  * [187] "ExecTask attribute ""passthru"" to make use of the PHP function ""passthru"""
  * [21] svn tasks doesn't work

Dec. 8, 2008 - Phing 2.3.3
--------------------------

  * [314] <phpunit> task does not work
  * [313] Incorrect PhpDoc package of SimpleTestResultFormatter
  * [302] Incorrect error detecting in XSLT filter
  * [293] Contains condition fails on case-insensitive checks
  * [291] The release package is not the one as the version(2.3.2) suppose to be

Oct. 16, 2008 - Phing 2.3.2
---------------------------

  * [296] Problem with the Phing plugin with Hudson CI Tool
  * [288] Comment syntax for dbdeploy violates standard

Oct. 16, 2008 - Phing 2.3.1
---------------------------

  * [287] DateSelector.php bug
  * [286] dbdeploy failes with MySQL strict mode
  * [285] Syntax error in dbdeploy task
  * [284] XSL Errors in coverage-report task
  * [275] AnsiColorLogger should not be final
  * [274] PHPUnit 3.3RC1 incompatibility with code coverage
  * [272] Using CDATA with ReplaceTokens values
  * [271] Warning on iterating over empty keys
  * [264] Illeal use of max() with empty array
  * [260] Error processing reults: SQLSTATE [HY000]: General error: 2053 when executing inserts or create statements.
  * [258] getPhingVersion + printVersion should be public static
  * [255] Timestamp in Phing Properties for Echo etc
  * [253] CCS nav bug on PHING.info site
  * [251] debug statement in Path datatype for DirSet
  * [249] See failed tests in console
  * [244] Phing pear install nor working
  * [242] Log incomplete and skipped tests for phpunit3
  * [241] FtpDeployTask reports FTP port as FTP server on error
  * [239] ExecTask shows no output from running command
  * [238] Bug in SummaryPHPUnit3ResultFormatter
  * [237] Several PHP errors in XSLTProcessor
  * [236] Do not show passwords for svn in log
  * [234] typo in foreach task documentation
  * [230] Fatal error: Call to undefined method PHPUnit2_Framework_TestResult::skippedCount() in /usr/local/lib/php/phing/tasks/ext/phpunit/PHPUnitTestRunner.php on line 120
  * [227] simpletestformaterelement bad require
  * [225] Missing Software Dependence in documentation
  * [224] Path class duplicates absolute path on subsequent path includes
  * [220] AnsiColorLogger colors cannot be changed by build.properties
  * [219] Add new chown task
  * [218] Clear support of PHPUnit versions
  * [217] Memory limit in phpdoc
  * [216] output messages about errors and warnings in JslLint task
  * [215] boolean attributes of task PhpCodeSniffer are wrong
  * [214] PhpCodeSnifferTask should be able to output file
  * [213] Error in documentation task related to copy task
  * [211] XSLT does not handle multiple testcase nodes for the same test method
  * [210] Reworked PhpDocumentorExternalTask
  * [208] ReplaceRegexp problem with newline as replace string
  * [207] PhpLintTask: optional use a different PHP interpreter
  * [206] Installation guide out of date (phing fails to run)
  * [205] AvailableTask::_checkResource ends up with an exception if resource isn't found.
  * [203] ExecTask returnProperty
  * [202] Add PHP_CodeSniffer task
  * [201] "Improve Phing's ability to work as an ""embedded"" process"
  * [200] Additional attribute for SvnUpdateTask
  * [199] Invalid error message in delete task when deleting directory fails.
  * [198] PDO SQL exec task unable to handle multi-line statements
  * [197] phing delete task sometimes fails to delete file that could be deleted
  * [195] SvnLastRevisionTask fails if Subversion is localized (Spanish)
  * [194] haltonincomplete attribute for phpunit task
  * [193] Manifest Task
  * [192] Error when skip test
  * [191] Akismet says content is spam
  * [190] Add test name in printsummary in PHPUnit task
  * [185] PHPUnit_MAIN_METHOD defined more than once
  * [184] PlainPHPUnit3ResultFormatter filteres test in stack trace
  * [183] Undefined variable in PhingTask.php
  * [182] Undefined variable in  SummaryPHPUnit3ResultFormatter
  * [181] PhingCallTask should call setHaltOnFailure
  * [179] Add documentation for TidyFilter
  * [178] printsummary doens work in PHP Unit task
  * [177] Only write ConfigurationExceptions to stdout
  * [176] Cleanup installation documentation.
  * [175] passing aarguments to phing
  * [169] Spurious PHP Error from XSLT Filter
  * [150] unable to include phpdocumentor.ini in PHPDoc-Task
  * [15] FTP upload task

Nov. 3, 2007 - Phing 2.3.0
--------------------------

  * [174] Add differentiation for build loggers that require explicit streams to be set
  * [173] Add 'value' alias to XSLTParam type.
  * [172] broken phpunit2-frames.xsl
  * [171] Allow results from selector to be loosely type matched to true/false
  * [170] SvnLastRevisionTask cannot get SVN revision number on single file
  * [168] XincludeFilter PHP Error
  * [167] Add new formatter support for PDOSQLExecTask
  * [166] Change CreoleTask to use <creole> tagname instead of <sql>
  * [165] Add support for PHPUnit_Framework_TestSuite subclasses in fileset of test classes
  * [164] Failed build results in empty log.xml
  * [163] Add stripwhitespace filter
  * [162] Add @pattern alias for @name in <fileset>
  * [161] phing/etc directory missing (breaking PHPUnit)
  * [157] Fatal error in PDOSQLExecTask when using filesets
  * [155] <delete> fails when it encounters symlink pointing to non-writable file
  * [154] Suggestion to add attribute to PDOSQLExecTask for fetch_style
  * [153] sqlite select failure
  * [152] result of PHP-Unit seems to be incorrect
  * [151] add group-option to PHPUnit-Task
  * [149] using TestSuites in fileset of PHPUnit-Task
  * [148] remove dependency to PEAR in PHPUnit-Task
  * [146] Illegal offset type PHP notice in CopyTask
  * [143] Example for PhpDocumentor task has typographical errors and a wrong attribute.
  * [142] SvnCheckout task only makes non-recursive checkouts.
  * [141] Add 'recursive' attribute to svncheckout task.
  * [136] Attribute os of ExecTask is not working
  * [135] add source file attribute for code coverage xml report
  * [133] Error in documenation: AppendTask
  * [129] Typo in documentation
  * [128] <pearpkg2> is missing in the doc completely
  * [127] Error in documentation
  * [126] Typo in documentation
  * [122] PearPackage2Task Replacements don't seem to work
  * [121] BUILD FAILED use JsLintTask
  * [119] PhpDocumentorTask fails when trying to use parsePrivate attribute.
  * [118] custom tasks have this->project == null
  * [117] CoverageSetupTask and autoloaders
  * [116] Test unit don't report notice or strict warnings
  * [110] "Add ""errorproperty"" attribute to PhpLintTask"
  * [107] SvnLastRevisionTask doesn't work with repositoryUrl
  * [106] "document ""haltonfailure"" attribute for phplint task"
  * [105] FileSystemUnix::normalize method: Improve handling
  * [97] delete dir and mkdir are incompatible
  * [92] Inconsistent newlines in PHP files
  * [91] Improve detection for PHPUnit3
  * [83] "XmlLogger improperly handling ""non-traditional"" buildfile execution paths"
  * [82] Error when use markTestIncomplete in test
  * [79] Allow escaped dots in classpaths
  * [78] (SVN doc) ${phing.version} and ${php.version} are different!
  * [77] taskdef doesn't support fileset
  * [76] Overhaul PhpDocumentor task
  * [75] files excluded by fileset end up in .tgz but not .zip
  * [74] Phing commandline args don't support quoting / spaces
  * [73] Semantical error in PhingFile::getParent()
  * [72] "Remove use of getProperty(""line.separator"") in favor of PHP_EOL"
  * [71] "Add ""-p"" alias for project help"
  * [70] Create Project class constants for log levels (replacing PROJECT_MSG_*)
  * [69] mkdir and delete tasks don't work properly together
  * [68] Xinclude filter
  * [67] Add PDO SQL execution task
  * [66] Incorrectly set PHP_CLASSPATH in phing.bat
  * [65] Convert all loggers/listeners to use streams
  * [64] Build listeners currently not working
  * [63] Configured -logger can get overridden
  * [62] phing.buildfile.dirname built-in property
  * [58] Path::listPaths() broken for DirSet objects.
  * [57] FileList.getListFile method references undefined variable
  * [56] TaskHandler passing incorrect param to ProjectConfigurator->configureId()
  * [53] _makeCircularException seems to have an infinite loop
  * [52] \<match>-syntax does not work correctly with preg_*()
  * [51] Cannot get phing to work with PHPUnit 3
  * [48] Supported PHPUnit2_Framework_TestSuite and PHPUnit2_Extensions_TestSetup sub-classes for the PHPUnit2Task and CoverageReportTask tasks
  * [33] Implement changes to use PHPUnit2 3.0 code coverage information
  * [22] Description about integrating into CruiseControl

Aug. 21, 2006 - Phing 2.2.0
---------------------------

  * Refactored parser to support many tags as children of base <project> tag (HL)
  * Added new IfTask (HL)
  * Added "spawn" attribute to ExecTask (only applies to *nix)
  * Several bugfixes & behavior imporvements to ExecTask (HL, MR, Ben Gollmer)
  * Bugfixes & refactoring for SVNLastRevisionTask (MR, Knut Urdalen)
  * Fixed reference copy bug (HL, Matthias Pigulla)
  * Added SvnExportTask (MR)
  * Added support for FileList in DeleteTask. (HL)
  * Added support for using setting Properties using CDATA value of <property> tag. (HL)
  * Added ReferenceExistsCondition (Matthias Pigulla)
  * Added Phing::log() static method & integrated PHP error handling with Phing logging (HL)
  * Added new task to run the ionCube Encoder (MR)
  * Added new HTML Tidy filter (HL)
  * Added PhpLintTask (Knut Urdalen)
  * Added XmlLintTask (Knut Urdalen)
  * Added ZendCodeAnalyzerTask (Knut Urdalen)
  * Removed CoverageFormatter class (MR)
    NOTE: This changes the usage of the collection of PHPUnit2 code coverage reports, see the
    updated documentation for the CoverageSetupTask
  * Added Unzip and Untar tasks contributed by Joakim Bodin
  * [8], [49] Fixed bugs in TarTask related to including empty directories (HL)
  * [44] Fixed bug related to copying empty dirs. (HL)
  * [32] Fixed PHPUnit2 tasks to work with PHPUnit2-3.0.0 (MR)
  * [31] Fixed bug with using PHPDocumentor 1.3.0RC6 (MR)
  * [43] Fixed top-level (no target) IfTask behavior (Matthias Pigulla)
  * [41] Removed some lingering E_STRICT errors, bugs with 5.1.x and PHP >= 5.0.5 (HL)
  * [25] Fixed 'phing' script to also run on non-bash unix /bin/sh 
  * Numerous documentation improvements by many members of the community (Thanks!)
  
Sept. 18, 2005 - Phing 2.1.1
----------------------------

  * Added support for specifying 4-char mask (e.g. 1777) to ChmodTask. (Hans Lellelid)
  * Added .svn files to default excludes in DirectoryScanner.
  * Updated PHPUnit2 BatchTest to use class detection and non-dot-path loader. (Michiel Rook)
  * Added support for importing non dot-path files (Michiel Rook)
  * Add better error message when build fails with exception (Hans Lellelid)
  * Fixed runtime error when errors were encountered in AppendTask (Hans Lellelid)

June 17, 2005 - Phing 2.1.0
---------------------------

  * Renamed File -> PhingFile to avoid namespace collisions (Michiel Rook)
  * Add ZipTask to create .zip files (Michiel Rook)
  * Removed redudant logging of build errors in Phing::start() (Michiel Rook)
  * Added tasks to execute PHPUnit2 testsuites and generate coverage and
    test reports. (Michiel Rook, Sebastian Bergmann)
  * Added SvnLastRevisionTask that stores the number of the last revision
    of a workingcopy in a property. (Michiel Rook)
  * Added MailTask that sends a message by mail() (Michiel Rook, contributed by Francois Harvey)
  * New IncludePathTask (<includepath/>) for adding values to PHP's include_path. (Hans Lellelid)
  * Fix to Phing::import() to *not* attempt to invoke __autoload() in class_exists() check. (Hans Lellelid)
  * Fixed AppendTask to allow use of only <fileset> as source. (Hans Lellelid)
  * Removed dependency on posix, by changing posix_uname to php_uname if needed. (Christian Stocker)
  * Fixed issues: (Michiel Rook)
    11  ExtendedFileStream does not work on Windows
    12  CoverageFormatter problem on Windows
    13  DOMElement warnings in PHPUnit2 tasks
    14  RuntimeException conflicts with SPL class
    15  It is not possible to execute it with PHP5.1
    16  Add Passthru option to ExecTask
    17  Blank list on foreach task will loop once
    19  Problem with <formatter outfile="...">
    20  Phpunit2report missing XSL stylesheets
    21  Warnings when output dir does not exist in PHPUnit2Report

Oct 16, 2004 - Phing 2.0.0
--------------------------

  * Minor fixes to make Phing run under E_STRICT/PHP5.
  * Fix to global/system properties not being set in project. (Matt Zandstra)
  * Fixes to deprecated return by reference issues w/ PHP5.0.0

June 8, 2004 - Phing 2.0.0b3
----------------------------

  * Brought up-to-date w/ PHP5.0.0RC3
  * Fixed several bugs in ForeachTask
  * Fixed runtime errors and incomplete inheriting of properties in PhingTask
  * Added <fileset> support to AppendTask

March 19, 2004 - Phing 2.0.0b2
------------------------------

  * Brought up-to-date w/ PHP5.0.0RC1 (Hans)
  * Fixed bug in seting XSLT params using XSLTask (Hans, Jeff Moss)
  * Fixed PHPUnit test framework for PHPUnit-2.0.0alpha3
  * Added "Adhoc" tasks, which allow for defining PHP task or type classes within the
  buildfile. (Hans)
  * Added PhpEvalTask which allows property values to be set to simple PHP evaluations or
  the results of function/method calls. (Hans)
  * Added new phing.listener.PearLogger listener (logger).  Also, the -logfile arg is now
  supported. (Hans)
  * Fixed broken ForeachTask task.  (Manuel)

Dec 24, 2003 - Phing 2.0.0b1
----------------------------

  * Added PEAR installation framework & ability to build Phing into PEAR package.
  * Added TarTask using PEAR Archive_Tar
  * Added PearPackageTask which creates a PEAR package.xml (using PEAR_PackageFileManager).
  * Added ResolvePathTask which converts relative paths into absolute paths.
  * Removed System class, due to namespace collision w/ PEAR.
  * Basic "working?" tests performed with all selectors.
  * Added selectors:  TypeSelector, ContainsRegexpSelector
  * CreoleSQLExec task is now operational.
  * Corrected non-fatal bugs in: DeleteTask, ReflexiveTask
  * All core Phing classes now in PHP5 syntax (no "var" used, etc.)
  * CopyTask will not stop build execution if a file cannot be copied (will log and
  continue to next file).
  * New abstract MatchingTask task makes it easier to create your own tasks that use
  selectors.
  * Removed redundant calls in DirectoryScanner (<fileset> scanning now much faster).
  * Fixed fatal errors in File::equals()

Nov 24, 2003 - Phing 2.0.0a2
----------------------------

  * Fixed ReplaceTokens filter to correctly replace matched tokens
  * Changed "project.basedir" property to be absolute path of basedir
  * Made IntrospectionHelper more tollerant of add*() and addConfigured*() signatures
  * New CvsTask and CvsPassTask for working with CVS repositories
  * New TranslateGettext filter substitutes _("hello!") with "hola!" / "bonjour!" / etc.
  * More consistent use of classhints to enable auto-casting by IntrospectionHelper
  * Fixed infinite loop bug in FileUtils::normalize() for paths containing "/./"
  * Fixed bug in CopyFile/fileset that caused termination of copy operation on encounter
  of unreadable file

Nov 6, 20003 - Phing 2.0.0a1
----------------------------

  * First release of Phing 2, an extensive rewrite and upgrade.
  * Refactored much of codebase, using new PHP5 features (e.g. Interfaces, Exceptions!)
  * Many, many, many bugfixes to existing functionality
  * Restructuring for more intuitive directory layout, change the parser class names.
  * Introduction of new tasks: AppendTask, ReflexiveTask, ExitTask, Input, PropertyPrompt
  * Introduction of new types: Path, FileList, DirSet, selectors, conditions
  * Introduction of new filters: ReplaceRegexp
  * Introduction of new logger: AnsiColorLogger
  * Many features from ANT 1.5 added to existing Tasks/Types
  * New "Register Slot" functionality allows for tracking "inner" dynamic variables.
