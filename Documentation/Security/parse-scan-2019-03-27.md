-#Security Scan - March 27th, 2019

Scan was executed with the following command:
```
psecio-parse scan --no-ansi --blacklist-rules=GlobalsUse,RequestUse --ignore-paths=vendor,public/assets,library/html2pdf/vendor,node_modules /projects/emr/open-emr/src/openemr/ > Documentation/Security/parse-scan-2019-03-27.md
```

Note the ignore-paths uses the realpath() in order to generate the exclude directory.  You will need to run this command within the root of the OpenEMR project

```
Parse: A PHP Security Scanner

I......I....................................................
................................II..I...I..............I..I.
.......I......II.........I...I.....................I.II.....
..I....I...IIII.I.IIII.IIIIIIIII..I..III.III..III..I...I....
............................................................
............................................................
............................................................
............................................................
............................................................
............................................................
............................................................
............................................................
............................................................
............................................................
............................................................
..............................................II...I........
.I...........I......I..II.I...........I.........II.I..I.....
...........I..................I.I.................II.......I
..I............II.......I..I.................I......I.......
.......II....I...I...I..I.......I.....................I.....
.................................I.......I.I.II.I.....I.II..
I...............I.I....I....I..I...I......I.II....II...II..I
II..I...I....I..I..................II....I..I.....I.......I.
......I.II...II......I...I..I......I.I.........II.I......I..
..........I.............I....I............................II
II.I.II..I....................I...I..I....................I.
..............I...I...............................I.I......I
II......I.....I...I......I..I...I......I...I...I.I...I.I...I
.I.III.II...II..I.............II.IIIIII..II..I..IIIIIII.IIII
..I........II..I....I.I..II...I.I.II..II................I.I.
...I.II...II......II.I....I.I...........I..I....I.I.........
.I.I....I.I....I...............I...................I..I..I..
....I............II.II.IIIIII..II.III.I.....I..IE.....I.....
............................................................
..............I........I..................................I.
..................................................I...IIII.I
.......I..I......I.................I.....II...........I..II.
.II....I.II..I.................III.I..........III.II..I.....
.II.I....II......I.....I................I.....III..II......I
.I................II...I.....I..............I...............
............................................................
............................................................
............................................................
...........................................II...I..........I
I.I...........................................I...II.II.I...
..........I................

There was 1 error

1) /projects/emr/open-emr/src/openemr/common/logging/EventAuditLogger.php
Syntax error, unexpected T_CONST, expecting T_FUNCTION on line 27

There were 1792 issues

1) /projects/emr/open-emr/src/openemr/modules/sms_email_reminder/sms_clickatell.php on line 175
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($this->unicode == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

2) /projects/emr/open-emr/src/openemr/modules/sms_email_reminder/sms_clickatell.php on line 178
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die("Your unicode message is too long! (Current lenght=".mb_strlen($text).")");
For more information execute 'psecio-parse rules ExitOrDie'

3) /projects/emr/open-emr/src/openemr/modules/sms_email_reminder/sms_clickatell.php on line 189
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die("Your message is too long! (Current lenght=".strlen($text).")");
For more information execute 'psecio-parse rules ExitOrDie'

4) /projects/emr/open-emr/src/openemr/portal/find_appt_popup_user.php on line 44
'header()' calls should not use concatenation directly
>     header('Location: '.$landingpage.'&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

5) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQLi.php on line 259
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($ommitEmptyTables == false || $rs ['Data_free'] > 0) {
For more information execute 'psecio-parse rules BooleanIdentity'

6) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL_PDO.php on line 222
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($ommitEmptyTables == false || $rs ['Data_free'] > 0) {
For more information execute 'psecio-parse rules BooleanIdentity'

7) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php on line 194
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($ommitEmptyTables == false || $rs ['Data_free'] > 0) {
For more information execute 'psecio-parse rules BooleanIdentity'

8) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/String/NameValue.php on line 31
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         $this->Value = $nameonly == false && isset($keyval [1]) ? $keyval [1] : $keyval [0];
For more information execute 'psecio-parse rules BooleanIdentity'

9) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php on line 124
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (! in_array($propname, self::$NoCacheProperties)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

10) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php on line 124
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (! in_array($propname, self::$NoCacheProperties)) {
For more information execute 'psecio-parse rules InArrayStrict'

11) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php on line 149
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (! in_array($propname, self::$NoCacheProperties)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

12) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php on line 149
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (! in_array($propname, self::$NoCacheProperties)) {
For more information execute 'psecio-parse rules InArrayStrict'

13) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php on line 202
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (! in_array($prop, $omit)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

14) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php on line 202
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (! in_array($prop, $omit)) {
For more information execute 'psecio-parse rules InArrayStrict'

15) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php on line 438
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                             if (! in_array($this->$prop, $fm->GetEnumValues())) {
For more information execute 'psecio-parse rules TypeSafeInArray'

16) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php on line 438
Evaluation using in_array should enforce type checking (third parameter should be true)
>                             if (! in_array($this->$prop, $fm->GetEnumValues())) {
For more information execute 'psecio-parse rules InArrayStrict'

17) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php on line 480
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (in_array("Phreezable", class_parents($child))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

18) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php on line 480
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (in_array("Phreezable", class_parents($child))) {
For more information execute 'psecio-parse rules InArrayStrict'

19) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/AuthAccount.php on line 183
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>         $this->_original_password = ""; // force Save to crypt the password
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

20) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/SavantRenderEngine.php on line 64
'header()' calls should not use concatenation directly
>             header("Location: " . $this->savant->url);
For more information execute 'psecio-parse rules SetHeaderWithInput'

21) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Controller.php on line 374
'header()' calls should not use concatenation directly
>                 header("Content-type: " . $contentType);
For more information execute 'psecio-parse rules SetHeaderWithInput'

22) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Controller.php on line 490
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (! in_array($var, $supressProps)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

23) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Controller.php on line 490
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (! in_array($var, $supressProps)) {
For more information execute 'psecio-parse rules InArrayStrict'

24) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Controller.php on line 794
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($on_fail_action && $this->IsApiRequest() == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

25) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Controller.php on line 984
'header()' calls should not use concatenation directly
>                 header('Location: ' . $url);
For more information execute 'psecio-parse rules SetHeaderWithInput'

26) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php on line 106
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 if ($fieldmap_exists == false || $is_numeric [$column] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

27) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php on line 106
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 if ($fieldmap_exists == false || $is_numeric [$column] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

28) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php on line 141
'header()' calls should not use concatenation directly
>         header('Content-Disposition: attachment; filename="' . $fileName . '"');
For more information execute 'psecio-parse rules SetHeaderWithInput'

29) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/PHPRenderEngine.php on line 63
'header()' calls should not use concatenation directly
>             header("Location: " . $this->model ['url']);
For more information execute 'psecio-parse rules SetHeaderWithInput'

30) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/PHPRenderEngine.php on line 66
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("<h4>" . $this->model ['message'] . "</h4>" . $this->model ['stacktrace']);
For more information execute 'psecio-parse rules ExitOrDie'

31) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php on line 131
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (! in_array($propname, self::$NoCacheProperties)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

32) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php on line 131
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (! in_array($propname, self::$NoCacheProperties)) {
For more information execute 'psecio-parse rules InArrayStrict'

33) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php on line 190
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (! in_array($propname, self::$NoCacheProperties)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

34) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php on line 190
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (! in_array($propname, self::$NoCacheProperties)) {
For more information execute 'psecio-parse rules InArrayStrict'

35) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php on line 230
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (! in_array($prop, $omit)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

36) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php on line 230
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (! in_array($prop, $omit)) {
For more information execute 'psecio-parse rules InArrayStrict'

37) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/HTTP/FormValidator.php on line 32
Remove any use of ereg functions, deprecated as of PHP 5.3.0. Use preg_
>         return (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email));
For more information execute 'psecio-parse rules EregFunctions'

38) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/HTTP/BrowserDevice.php on line 103
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($this->IsMobile == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

39) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php on line 390
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if (self::$VALIDATE_FILE_UPLOAD && is_uploaded_file($upload ['tmp_name']) == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

40) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php on line 405
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if ($ok_types && ! in_array($fupload->Extension, $ok_types)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

41) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php on line 405
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if ($ok_types && ! in_array($fupload->Extension, $ok_types)) {
For more information execute 'psecio-parse rules InArrayStrict'

42) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Authentication/Auth401.php on line 27
'header()' calls should not use concatenation directly
>         header("WWW-Authenticate: Basic realm=\"" . $realm . "\"");
For more information execute 'psecio-parse rules SetHeaderWithInput'

43) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/Authentication/PassPhrase.php on line 96
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>         $password = "";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

44) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/XML/VerySimpleXmlUtil.php on line 252
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             $name = strlen($root) > 0 && is_numeric($root) == false ? $root : get_class($var);
For more information execute 'psecio-parse rules BooleanIdentity'

45) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/verysimple/XML/VerySimpleXmlUtil.php on line 292
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($xml->isEmptyElement == false && $xml->read() && $xml->nodeType == XMLReader::TEXT) {
For more information execute 'psecio-parse rules BooleanIdentity'

46) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/thumbnail.php on line 69
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (! in_array($size [2], $this->allowableTypes)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

47) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/thumbnail.php on line 69
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (! in_array($size [2], $this->allowableTypes)) {
For more information execute 'psecio-parse rules InArrayStrict'

48) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/thumbnail.php on line 111
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($useExactSize == false && $size [0] <= $maxWidth && $size [1] <= $maxHeight) {
For more information execute 'psecio-parse rules BooleanIdentity'

49) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/Mime_Types.php on line 205
Use of system functions, especially with user input, is not recommended
>             $result = @exec($cmd);
For more information execute 'psecio-parse rules SystemFunctions'

50) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/Mime_Types.php on line 210
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (in_array($match [2], array (
>                             'application',
>                             'audio',
>                             'image',
>                             'message',
>                             'multipart',
>                             'text',
>                             'video',
>                             'chemical',
>                             'model'
>                     )) || (substr($match [2], 0, 2) == 'x-')) {
For more information execute 'psecio-parse rules TypeSafeInArray'

51) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/Mime_Types.php on line 210
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (in_array($match [2], array (
>                             'application',
>                             'audio',
>                             'image',
>                             'message',
>                             'multipart',
>                             'text',
>                             'video',
>                             'chemical',
>                             'model'
>                     )) || (substr($match [2], 0, 2) == 'x-')) {
For more information execute 'psecio-parse rules InArrayStrict'

52) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/Mime_Types.php on line 356
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         return (in_array(strtolower($type), $this->mime_types));
For more information execute 'psecio-parse rules TypeSafeInArray'

53) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/Mime_Types.php on line 356
Evaluation using in_array should enforce type checking (third parameter should be true)
>         return (in_array(strtolower($type), $this->mime_types));
For more information execute 'psecio-parse rules InArrayStrict'

54) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/password.php on line 7
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> defined('PASSWORD_BCRYPT') or define('PASSWORD_BCRYPT', 1);
For more information execute 'psecio-parse rules LogicalOperators'

55) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/password.php on line 9
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> defined('PASSWORD_DEFAULT') or define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
For more information execute 'psecio-parse rules LogicalOperators'

56) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/parsecsv.lib.php on line 290
'header()' calls should not use concatenation directly
>             header('Content-Disposition: attachment; filename="' . $filename . '"');
For more information execute 'psecio-parse rules SetHeaderWithInput'

57) /projects/emr/open-emr/src/openemr/portal/patient/fwk/libs/util/zip.lib.php on line 102
Don't use eval. Ever.
>         eval('$hexdtime = "' . $hexdtime . '";');
For more information execute 'psecio-parse rules EvalFunction'

58) /projects/emr/open-emr/src/openemr/portal/patient/_machine_config.php on line 32
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage);
For more information execute 'psecio-parse rules SetHeaderWithInput'

59) /projects/emr/open-emr/src/openemr/portal/patient/_app_config.php on line 33
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die('<h3>Server Configuration Problem: asp_tags is enabled, but is not compatible with Savant.</h3>' . '<p>You can disable asp_tags in .htaccess, php.ini or generate your app with another template engine such as Smarty.</p>');
For more information execute 'psecio-parse rules ExitOrDie'

60) /projects/emr/open-emr/src/openemr/portal/import_template.php on line 88
'header()' calls should not use concatenation directly
>     header("location: " . $_SERVER['HTTP_REFERER']);
For more information execute 'psecio-parse rules SetHeaderWithInput'

61) /projects/emr/open-emr/src/openemr/portal/logout.php on line 37
'header()' calls should not use concatenation directly
> header('Location: '.$landingpage.'&logout');
For more information execute 'psecio-parse rules SetHeaderWithInput'

62) /projects/emr/open-emr/src/openemr/portal/home.php on line 20
'header()' calls should not use concatenation directly
>     header('Location: '.$landingpage.'&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

63) /projects/emr/open-emr/src/openemr/portal/report/portal_custom_report.php on line 37
'header()' calls should not use concatenation directly
>     header('Location: '.$landingpage.'&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

64) /projects/emr/open-emr/src/openemr/portal/report/portal_custom_report.php on line 756
Avoid the use of an output method (echo, print, etc) directly with a variable
>                 echo $row['administered_date'] . " - " . $vaccine_display;
For more information execute 'psecio-parse rules OutputWithVariable'

65) /projects/emr/open-emr/src/openemr/portal/report/portal_patient_report.php on line 24
'header()' calls should not use concatenation directly
>     header('Location: '.$landingpage.'&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

66) /projects/emr/open-emr/src/openemr/portal/report/portal_patient_report.php on line 251
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

67) /projects/emr/open-emr/src/openemr/portal/report/portal_patient_report.php on line 251
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

68) /projects/emr/open-emr/src/openemr/portal/report/portal_patient_report.php on line 279
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

69) /projects/emr/open-emr/src/openemr/portal/report/portal_patient_report.php on line 279
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

70) /projects/emr/open-emr/src/openemr/portal/report/portal_patient_report.php on line 761
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

71) /projects/emr/open-emr/src/openemr/portal/report/portal_patient_report.php on line 761
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

72) /projects/emr/open-emr/src/openemr/portal/report/portal_patient_report.php on line 800
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

73) /projects/emr/open-emr/src/openemr/portal/report/portal_patient_report.php on line 800
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

74) /projects/emr/open-emr/src/openemr/portal/report/pat_ledger.php on line 358
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=svc_financial_report_".attr($form_from_date)."--".attr($form_to_date).".csv");
For more information execute 'psecio-parse rules SetHeaderWithInput'

75) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 36
'header()' calls should not use concatenation directly
>     header('Location: ' . $landingpage . '&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

76) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 43
'header()' calls should not use concatenation directly
>     header('Location: ' . $landingpage . '&w&c');
For more information execute 'psecio-parse rules SetHeaderWithInput'

77) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 49
'header()' calls should not use concatenation directly
>     header('Location: ' . $landingpage . '&w&c');
For more information execute 'psecio-parse rules SetHeaderWithInput'

78) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 80
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
> DEFINE("COL_POR_PWD", "portal_pwd");
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

79) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 81
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
> DEFINE("COL_POR_USER", "portal_username");
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

80) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 97
'header()' calls should not use concatenation directly
>     header('Location: ' . $landingpage . '&w&u');
For more information execute 'psecio-parse rules SetHeaderWithInput'

81) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 105
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage . '&w&p');
For more information execute 'psecio-parse rules SetHeaderWithInput'

82) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 122
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage . '&w&p');
For more information execute 'psecio-parse rules SetHeaderWithInput'

83) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 137
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage . '&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

84) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 144
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage . '&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

85) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 152
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage . '&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

86) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 159
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage . '&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

87) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 192
'header()' calls should not use concatenation directly
>             header('Location: ' . $landingpage);
For more information execute 'psecio-parse rules SetHeaderWithInput'

88) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 220
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage . '&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

89) /projects/emr/open-emr/src/openemr/portal/get_patient_info.php on line 225
'header()' calls should not use concatenation directly
>     header('Location: ' . $landingpage . '&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

90) /projects/emr/open-emr/src/openemr/portal/get_patient_documents.php on line 102
The readfile/readlink/readgzfile functions output content directly (possible injection)
>     readfile($tmp."/".$pid.'.zip');
For more information execute 'psecio-parse rules Readfile'

91) /projects/emr/open-emr/src/openemr/portal/get_patient_documents.php on line 130
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($empty == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

92) /projects/emr/open-emr/src/openemr/portal/lib/doc_lib.php on line 23
'header()' calls should not use concatenation directly
>         header('Location: '.$landingpage);
For more information execute 'psecio-parse rules SetHeaderWithInput'

93) /projects/emr/open-emr/src/openemr/portal/lib/doc_lib.php on line 101
The readfile/readlink/readgzfile functions output content directly (possible injection)
>     readfile($fname);
For more information execute 'psecio-parse rules Readfile'

94) /projects/emr/open-emr/src/openemr/portal/lib/appsql.class.php on line 266
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo 'ERROR : ' . $logMsg;
For more information execute 'psecio-parse rules OutputWithVariable'

95) /projects/emr/open-emr/src/openemr/portal/lib/paylib.php on line 23
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage);
For more information execute 'psecio-parse rules SetHeaderWithInput'

96) /projects/emr/open-emr/src/openemr/portal/account/account.lib.php on line 107
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     $password = '';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

97) /projects/emr/open-emr/src/openemr/portal/account/account.php on line 33
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo xlt("This account already exists.") . "\r\n\r\n" . xlt("If you are having troubles logging into your account.") . "\r\n" . xlt("Please contact your provider.") . "\r\n" . xlt("Reference this Account Id: ") . $rtn;
For more information execute 'psecio-parse rules OutputWithVariable'

98) /projects/emr/open-emr/src/openemr/portal/account/register.php on line 413
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                     if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

99) /projects/emr/open-emr/src/openemr/portal/account/register.php on line 413
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                     if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
For more information execute 'psecio-parse rules InArrayStrict'

100) /projects/emr/open-emr/src/openemr/portal/messaging/messages.php on line 25
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage);
For more information execute 'psecio-parse rules SetHeaderWithInput'

101) /projects/emr/open-emr/src/openemr/portal/messaging/secure_chat.php on line 27
'header()' calls should not use concatenation directly
>         header('Location: '.$landingpage);
For more information execute 'psecio-parse rules SetHeaderWithInput'

102) /projects/emr/open-emr/src/openemr/portal/messaging/secure_chat.php on line 304
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if ((in_array(C_USER, $u)) || $row['sender_id'] == C_USER) {
For more information execute 'psecio-parse rules TypeSafeInArray'

103) /projects/emr/open-emr/src/openemr/portal/messaging/secure_chat.php on line 304
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if ((in_array(C_USER, $u)) || $row['sender_id'] == C_USER) {
For more information execute 'psecio-parse rules InArrayStrict'

104) /projects/emr/open-emr/src/openemr/portal/messaging/secure_chat.php on line 351
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         return sqlStatementNoLog("REPLACE INTO onsite_online
>             VALUES ( ?, ?, NOW(), ?, ? )", array($hash, $ip, $username, $userid)) or die(mysql_error());
For more information execute 'psecio-parse rules LogicalOperators'

105) /projects/emr/open-emr/src/openemr/portal/messaging/handle_note.php on line 21
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage);
For more information execute 'psecio-parse rules SetHeaderWithInput'

106) /projects/emr/open-emr/src/openemr/portal/portal_payment.php on line 39
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage);
For more information execute 'psecio-parse rules SetHeaderWithInput'

107) /projects/emr/open-emr/src/openemr/portal/portal_payment.php on line 60
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
> $adminUser = '';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

108) /projects/emr/open-emr/src/openemr/portal/import_template_ui.php on line 42
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     $d = @dir($dir) or die("File List: Failed opening directory " . text($dir) . " for reading");
For more information execute 'psecio-parse rules LogicalOperators'

109) /projects/emr/open-emr/src/openemr/portal/import_template_ui.php on line 42
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     $d = @dir($dir) or die("File List: Failed opening directory " . text($dir) . " for reading");
For more information execute 'psecio-parse rules ExitOrDie'

110) /projects/emr/open-emr/src/openemr/portal/index.php on line 305
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                     if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

111) /projects/emr/open-emr/src/openemr/portal/index.php on line 305
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                     if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
For more information execute 'psecio-parse rules InArrayStrict'

112) /projects/emr/open-emr/src/openemr/portal/add_edit_event_user.php on line 32
'header()' calls should not use concatenation directly
>     header('Location: '.$landingpage.'&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

113) /projects/emr/open-emr/src/openemr/portal/verify_session.php on line 46
'header()' calls should not use concatenation directly
>     header('Location: '.$landingpage.'&w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

114) /projects/emr/open-emr/src/openemr/myportal/soap_service/server_side.php on line 1101
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if (($credentials[1]==$GLOBALS['portal_offsite_username'] && $ok==1 && $GLOBALS['portal_offsite_enable']==1)||$GLOBALS['validated_offsite_portal']==true) {
For more information execute 'psecio-parse rules BooleanIdentity'

115) /projects/emr/open-emr/src/openemr/myportal/soap_service/server_side.php on line 1106
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 } elseif (UserService::validcredential($credentials) == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

116) /projects/emr/open-emr/src/openemr/myportal/soap_service/server_audit.php on line 39
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>         $password = "";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

117) /projects/emr/open-emr/src/openemr/myportal/soap_service/server_med_rec.php on line 435
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     $result = curl_exec($ch) or die(curl_error($ch));
For more information execute 'psecio-parse rules LogicalOperators'

118) /projects/emr/open-emr/src/openemr/myportal/soap_service/server_med_rec.php on line 596
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 if ($GLOBALS['phimail_enable']==false) {
For more information execute 'psecio-parse rules BooleanIdentity'

119) /projects/emr/open-emr/src/openemr/myportal/soap_service/server_med_rec.php on line 641
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($xml_type, array('CCR', 'CCDA', 'CDA'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

120) /projects/emr/open-emr/src/openemr/myportal/soap_service/server_med_rec.php on line 641
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($xml_type, array('CCR', 'CCDA', 'CDA'))) {
For more information execute 'psecio-parse rules InArrayStrict'

121) /projects/emr/open-emr/src/openemr/sites/default/sqlconf.php on line 12
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
> $pass   = 'openemr';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

122) /projects/emr/open-emr/src/openemr/sites/default/documents/smarty/main/%%1E/1EA/1EA5C8E9%%ajax_template.html.php on line 627
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                     if (($found == true) && ($outevent['catid'] == 3)) {
For more information execute 'psecio-parse rules BooleanIdentity'

123) /projects/emr/open-emr/src/openemr/sites/default/documents/smarty/main/%%1E/1EA/1EA5C8E9%%ajax_template.html.php on line 936
Avoid the use of an output method (echo, print, etc) directly with a variable
>     var tsHeight='<?php  echo $timeslotHeightVal.$timeslotHeightUnit;  ?>';
For more information execute 'psecio-parse rules OutputWithVariable'

124) /projects/emr/open-emr/src/openemr/sites/default/documents/smarty/main/%%C9/C93/C9312278%%ajax_template.html.php on line 640
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                     if (($found == true) && ($outevent['catid'] == 3)) {
For more information execute 'psecio-parse rules BooleanIdentity'

125) /projects/emr/open-emr/src/openemr/sites/default/documents/smarty/main/%%C9/C93/C9312278%%ajax_template.html.php on line 897
Avoid the use of an output method (echo, print, etc) directly with a variable
>     var tsHeight='<?php  echo $timeslotHeightVal.$timeslotHeightUnit;  ?>';
For more information execute 'psecio-parse rules OutputWithVariable'

126) /projects/emr/open-emr/src/openemr/setup.php on line 51
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (!in_array($dirName, $dirNames)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

127) /projects/emr/open-emr/src/openemr/setup.php on line 51
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (!in_array($dirName, $dirNames)) {
For more information execute 'psecio-parse rules InArrayStrict'

128) /projects/emr/open-emr/src/openemr/setup.php on line 177
Avoid the use of an output method (echo, print, etc) directly with a variable
>     echo $site_id . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

129) /projects/emr/open-emr/src/openemr/setup.php on line 193
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die("Site ID '".htmlspecialchars($site_id, ENT_NOQUOTES)."' contains invalid characters.");
For more information execute 'psecio-parse rules ExitOrDie'

130) /projects/emr/open-emr/src/openemr/setup.php on line 421
Avoid the use of an output method (echo, print, etc) directly with a variable
>             echo $end_div . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

131) /projects/emr/open-emr/src/openemr/setup.php on line 460
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $step1 ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

132) /projects/emr/open-emr/src/openemr/setup.php on line 474
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $step2top ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

133) /projects/emr/open-emr/src/openemr/setup.php on line 566
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $step2tabletop1 ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

134) /projects/emr/open-emr/src/openemr/setup.php on line 707
Avoid the use of an output method (echo, print, etc) directly with a variable
>                             echo $step2tabletop2 ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

135) /projects/emr/open-emr/src/openemr/setup.php on line 758
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                                 echo $source_site_top . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

136) /projects/emr/open-emr/src/openemr/setup.php on line 794
Avoid the use of an output method (echo, print, etc) directly with a variable
>                             echo $source_site_bot ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

137) /projects/emr/open-emr/src/openemr/setup.php on line 811
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>                             $randomsecret = "";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

138) /projects/emr/open-emr/src/openemr/setup.php on line 947
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $step2tablebot ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

139) /projects/emr/open-emr/src/openemr/setup.php on line 1232
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                     echo $form_top . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

140) /projects/emr/open-emr/src/openemr/setup.php on line 1243
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                     echo $form_bottom . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

141) /projects/emr/open-emr/src/openemr/setup.php on line 1251
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $step4_top . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

142) /projects/emr/open-emr/src/openemr/setup.php on line 1282
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $step4_bottom . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

143) /projects/emr/open-emr/src/openemr/setup.php on line 1293
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $step5_top . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

144) /projects/emr/open-emr/src/openemr/setup.php on line 1364
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $step5_table . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

145) /projects/emr/open-emr/src/openemr/setup.php on line 1393
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $step5_bottom . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

146) /projects/emr/open-emr/src/openemr/setup.php on line 1434
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $step6_bottom . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

147) /projects/emr/open-emr/src/openemr/setup.php on line 1475
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $theme_form ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

148) /projects/emr/open-emr/src/openemr/setup.php on line 1522
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                 echo $check_file . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

149) /projects/emr/open-emr/src/openemr/setup.php on line 1543
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                 echo $check_directory . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

150) /projects/emr/open-emr/src/openemr/setup.php on line 1560
Avoid the use of an output method (echo, print, etc) directly with a variable
>                             echo $form ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

151) /projects/emr/open-emr/src/openemr/setup.php on line 1570
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $bot ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

152) /projects/emr/open-emr/src/openemr/public/themes/themeBuilder.php on line 41
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array($key, $variables)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

153) /projects/emr/open-emr/src/openemr/public/themes/themeBuilder.php on line 41
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array($key, $variables)) {
For more information execute 'psecio-parse rules InArrayStrict'

154) /projects/emr/open-emr/src/openemr/tests/e2e/CheckCreateUser.php on line 15
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     const VAR_AUTHUSER = "admin";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

155) /projects/emr/open-emr/src/openemr/tests/e2e/CheckCreateUser.php on line 16
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     const VAR_PASS = "pass";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

156) /projects/emr/open-emr/src/openemr/interface/cmsportal/portal.inc.php on line 60
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt('There is no patient with portal login') . " '$wp_login'");
For more information execute 'psecio-parse rules ExitOrDie'

157) /projects/emr/open-emr/src/openemr/interface/cmsportal/portal.inc.php on line 64
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt('There are multiple patients with portal login') . " '$wp_login'");
For more information execute 'psecio-parse rules ExitOrDie'

158) /projects/emr/open-emr/src/openemr/interface/eRx_xml.php on line 957
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     $result=curl_exec($ch)  or die(curl_error($ch)) ;
For more information execute 'psecio-parse rules LogicalOperators'

159) /projects/emr/open-emr/src/openemr/interface/themes/themeBuilder.php on line 41
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array($key, $variables)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

160) /projects/emr/open-emr/src/openemr/interface/themes/themeBuilder.php on line 41
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array($key, $variables)) {
For more information execute 'psecio-parse rules InArrayStrict'

161) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php on line 702
'header()' calls should not use concatenation directly
>                 header('Content-Disposition: attachment; filename=' . $filename);
For more information execute 'psecio-parse rules SetHeaderWithInput'

162) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php on line 156
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($from_date!=0 and $to_date!=0) {
For more information execute 'psecio-parse rules LogicalOperators'

163) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php on line 165
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($from_date!=0 or $to_date!=0) {
For more information execute 'psecio-parse rules LogicalOperators'

164) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Acl/view/acl/acl/acl.phtml on line 52
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 							if(is_array($ACL_DATA[$MODULE_DATA['module_name']['id']]) && in_array($KEY,$ACL_DATA[$MODULE_DATA['module_name']['id']])) $selected = "checked";
For more information execute 'psecio-parse rules TypeSafeInArray'

165) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Acl/view/acl/acl/acl.phtml on line 52
Evaluation using in_array should enforce type checking (third parameter should be true)
> 							if(is_array($ACL_DATA[$MODULE_DATA['module_name']['id']]) && in_array($KEY,$ACL_DATA[$MODULE_DATA['module_name']['id']])) $selected = "checked";
For more information execute 'psecio-parse rules InArrayStrict'

166) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Acl/view/acl/acl/acl.phtml on line 69
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 								if(in_array($KEY_GROUP,$ACL_DATA[$KEY])) $selected = "checked";
For more information execute 'psecio-parse rules TypeSafeInArray'

167) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Acl/view/acl/acl/acl.phtml on line 69
Evaluation using in_array should enforce type checking (third parameter should be true)
> 								if(in_array($KEY_GROUP,$ACL_DATA[$KEY])) $selected = "checked";
For more information execute 'psecio-parse rules InArrayStrict'

168) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Ccr/view/ccr/ccr/revandapprove.phtml on line 161
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                         if(in_array($this->problems['diagnosis'], $this->problems_audit['lists1'][$k])){
For more information execute 'psecio-parse rules TypeSafeInArray'

169) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Ccr/view/ccr/ccr/revandapprove.phtml on line 161
Evaluation using in_array should enforce type checking (third parameter should be true)
>                         if(in_array($this->problems['diagnosis'], $this->problems_audit['lists1'][$k])){
For more information execute 'psecio-parse rules InArrayStrict'

170) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php on line 144
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($result['category_id'], $categoryIds) && $contentType == 'text/xml'  && !$doEncryption) {
For more information execute 'psecio-parse rules TypeSafeInArray'

171) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php on line 144
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($result['category_id'], $categoryIds) && $contentType == 'text/xml'  && !$doEncryption) {
For more information execute 'psecio-parse rules InArrayStrict'

172) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php on line 167
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (in_array($result['mimetype'], $previewAvailableFiles)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

173) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php on line 167
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (in_array($result['mimetype'], $previewAvailableFiles)) {
For more information execute 'psecio-parse rules InArrayStrict'

174) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php on line 168
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($result['category_id'], $categoryIds) && $contentType == 'text/xml') {
For more information execute 'psecio-parse rules TypeSafeInArray'

175) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php on line 168
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($result['category_id'], $categoryIds) && $contentType == 'text/xml') {
For more information execute 'psecio-parse rules InArrayStrict'

176) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php on line 111
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                         die($this->listenerObject->z_xlt("Unable to modify application config Please give write permission to")." $fileName");
For more information execute 'psecio-parse rules ExitOrDie'

177) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php on line 879
Don't use eval. Ever.
>                 eval($phpObjCode);
For more information execute 'psecio-parse rules EvalFunction'

178) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php on line 892
Don't use eval. Ever.
>             eval($phpObjCode);
For more information execute 'psecio-parse rules EvalFunction'

179) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/MultipledbController.php on line 168
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($mode == 'view' or $mode == 'write') {
For more information execute 'psecio-parse rules LogicalOperators'

180) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/MultipledbTable.php on line 66
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($count and $_SESSION['multiple_edit_id'] == 0) {
For more information execute 'psecio-parse rules LogicalOperators'

181) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 205
'header()' calls should not use concatenation directly
>                 header("Content-Disposition: attachment; filename=".$practice_filename);
For more information execute 'psecio-parse rules SetHeaderWithInput'

182) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 398
'header()' calls should not use concatenation directly
>             header("Content-Disposition: attachment; filename=".$practice_filename);
For more information execute 'psecio-parse rules SetHeaderWithInput'

183) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 428
Use of system functions, especially with user input, is not recommended
>                     exec($cmd . " > /dev/null &");
For more information execute 'psecio-parse rules SystemFunctions'

184) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 509
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('encounters', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

185) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 509
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('encounters', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

186) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 513
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('continuity_care_document', $sections_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

187) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 513
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('continuity_care_document', $sections_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

188) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 517
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('progress_note', $sections_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

189) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 517
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('progress_note', $sections_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

190) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 521
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('discharge_summary', $sections_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

191) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 521
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('discharge_summary', $sections_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

192) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 525
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('procedure_note', $sections_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

193) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 525
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('procedure_note', $sections_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

194) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 529
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('operative_note', $sections_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

195) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 529
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('operative_note', $sections_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

196) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 533
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('consultation_note', $sections_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

197) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 533
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('consultation_note', $sections_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

198) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 537
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('history_physical_note', $sections_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

199) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 537
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('history_physical_note', $sections_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

200) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 541
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('unstructured_document', $sections_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

201) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 541
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('unstructured_document', $sections_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

202) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 574
'header()' calls should not use concatenation directly
>         header("Content-Disposition: attachment; filename=".$practice_filename);
For more information execute 'psecio-parse rules SetHeaderWithInput'

203) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 577
The readfile/readlink/readgzfile functions output content directly (possible injection)
>         readfile($tmpfile);
For more information execute 'psecio-parse rules Readfile'

204) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 583
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('allergies', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

205) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 583
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('allergies', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

206) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 587
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('medications', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

207) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 587
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('medications', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

208) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 591
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('problems', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

209) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 591
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('problems', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

210) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 595
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('procedures', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

211) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 595
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('procedures', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

212) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 599
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('results', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

213) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 599
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('results', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

214) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 603
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('immunizations', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

215) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 603
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('immunizations', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

216) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 607
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('plan_of_care', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

217) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 607
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('plan_of_care', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

218) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 611
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('functional_status', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

219) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 611
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('functional_status', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

220) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 615
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('instructions', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

221) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 615
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('instructions', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

222) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 744
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('vitals', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

223) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 744
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('vitals', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

224) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 748
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array('social_history', $components_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

225) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php on line 748
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array('social_history', $components_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

226) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncountermanagerController.php on line 175
The readfile/readlink/readgzfile functions output content directly (possible injection)
>         readfile($zip_dir.$zip_name);
For more information execute 'psecio-parse rules Readfile'

227) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncountermanagerController.php on line 222
The readfile/readlink/readgzfile functions output content directly (possible injection)
>             readfile($zip_dir.$zip_name);
For more information execute 'psecio-parse rules Readfile'

228) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php on line 199
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($GLOBALS['phimail_enable']==false) {
For more information execute 'psecio-parse rules BooleanIdentity'

229) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php on line 135
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if (($credentials[1]==$GLOBALS['portal_offsite_username'] && $ok==1 && $GLOBALS['portal_offsite_enable']==1)||$GLOBALS['validated_offsite_portal']==true) {
For more information execute 'psecio-parse rules BooleanIdentity'

230) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php on line 141
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 } elseif ($this->validcredential($credentials) == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

231) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 624
The third parameter should be set (and be true) on in_array to avoid type switching issues
>           if(in_array(substr($res_existing_prob['diagnosis'], 10), $this->problems_audit['lists1'][$k])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

232) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 624
Evaluation using in_array should enforce type checking (third parameter should be true)
>           if(in_array(substr($res_existing_prob['diagnosis'], 10), $this->problems_audit['lists1'][$k])) {
For more information execute 'psecio-parse rules InArrayStrict'

233) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 707
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array(substr($res_existing_prob['diagnosis'], 10), $imported_conflicted_problems_Arr[$k])) { ?>
For more information execute 'psecio-parse rules TypeSafeInArray'

234) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 707
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array(substr($res_existing_prob['diagnosis'], 10), $imported_conflicted_problems_Arr[$k])) { ?>
For more information execute 'psecio-parse rules InArrayStrict'

235) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 814
The third parameter should be set (and be true) on in_array to avoid type switching issues
>           if(in_array(substr($res_existing_allergies['diagnosis'], 7), $this->allergies_audit['lists2'][$key])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

236) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 814
Evaluation using in_array should enforce type checking (third parameter should be true)
>           if(in_array(substr($res_existing_allergies['diagnosis'], 7), $this->allergies_audit['lists2'][$key])) {
For more information execute 'psecio-parse rules InArrayStrict'

237) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 910
The third parameter should be set (and be true) on in_array to avoid type switching issues
>              if (in_array(substr($res_existing_allergies['diagnosis'], 7), $imported_conflicted_allergies_Arr[$key])) { ?>
For more information execute 'psecio-parse rules TypeSafeInArray'

238) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 910
Evaluation using in_array should enforce type checking (third parameter should be true)
>              if (in_array(substr($res_existing_allergies['diagnosis'], 7), $imported_conflicted_allergies_Arr[$key])) { ?>
For more information execute 'psecio-parse rules InArrayStrict'

239) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 1030
The third parameter should be set (and be true) on in_array to avoid type switching issues
>           if(in_array($res_existing_medications['rxnorm_drugcode'], $this->medications_audit['lists3'][$key])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

240) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 1030
Evaluation using in_array should enforce type checking (third parameter should be true)
>           if(in_array($res_existing_medications['rxnorm_drugcode'], $this->medications_audit['lists3'][$key])) {
For more information execute 'psecio-parse rules InArrayStrict'

241) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 1109
The third parameter should be set (and be true) on in_array to avoid type switching issues
>              if (in_array($res_existing_medications['rxnorm_drugcode'], $imported_conflicted_medications_Arr[$key])) { ?>
For more information execute 'psecio-parse rules TypeSafeInArray'

242) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml on line 1109
Evaluation using in_array should enforce type checking (third parameter should be true)
>              if (in_array($res_existing_medications['rxnorm_drugcode'], $imported_conflicted_medications_Arr[$key])) { ?>
For more information execute 'psecio-parse rules InArrayStrict'

243) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php on line 137
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo 'ERROR : ' . $logMsg;
For more information execute 'psecio-parse rules OutputWithVariable'

244) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/index/auto-suggest.phtml on line 147
Avoid the use of an output method (echo, print, etc) directly with a variable
>             id="<?php echo 'list_' . $this->searchEleNo . '_' . $lineNo; ?>" 
For more information execute 'psecio-parse rules OutputWithVariable'

245) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/index/auto-suggest.phtml on line 154
Avoid the use of an output method (echo, print, etc) directly with a variable
>               id="<?php echo $fill . '_' . $this->searchEleNo . '_' . $lineNo; ?>" 
For more information execute 'psecio-parse rules OutputWithVariable'

246) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/index/auto-suggest.phtml on line 170
Avoid the use of an output method (echo, print, etc) directly with a variable
>               id="<?php echo $fill . '_' . $this->searchEleNo . '_' . $lineNo; ?>" 
For more information execute 'psecio-parse rules OutputWithVariable'

247) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 34
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 		<div class="ap-st-st-1" style="<?php if(!in_array('hie',$required_buttons)) echo "display:none;"?>"><input class="ap-st-st-3" type="radio" name="send_to" id="send_to_hie" value="hie" <?php if($this->send_via == "hie") echo "checked"; ?>>&nbsp;<span><?php echo $this->listenerObject->z_xlt('HIE')?></span></div>		
For more information execute 'psecio-parse rules TypeSafeInArray'

248) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 34
Evaluation using in_array should enforce type checking (third parameter should be true)
> 		<div class="ap-st-st-1" style="<?php if(!in_array('hie',$required_buttons)) echo "display:none;"?>"><input class="ap-st-st-3" type="radio" name="send_to" id="send_to_hie" value="hie" <?php if($this->send_via == "hie") echo "checked"; ?>>&nbsp;<span><?php echo $this->listenerObject->z_xlt('HIE')?></span></div>		
For more information execute 'psecio-parse rules InArrayStrict'

249) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 35
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 		<div class="ap-st-st-1" style="<?php if(!in_array('emr_direct',$required_buttons)) echo "display:none;"?>"><input class="ap-st-st-3" type="radio" name="send_to" id="send_to_emrdirect" value="emr_direct" <?php if($this->send_via == "emr_direct") echo "checked"; ?>>&nbsp;<span><?php echo $this->listenerObject->z_xlt('EMR Direct')?></span></div>
For more information execute 'psecio-parse rules TypeSafeInArray'

250) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 35
Evaluation using in_array should enforce type checking (third parameter should be true)
> 		<div class="ap-st-st-1" style="<?php if(!in_array('emr_direct',$required_buttons)) echo "display:none;"?>"><input class="ap-st-st-3" type="radio" name="send_to" id="send_to_emrdirect" value="emr_direct" <?php if($this->send_via == "emr_direct") echo "checked"; ?>>&nbsp;<span><?php echo $this->listenerObject->z_xlt('EMR Direct')?></span></div>
For more information execute 'psecio-parse rules InArrayStrict'

251) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 36
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 		<div class="ap-st-st-1" style="<?php if(!in_array('download',$required_buttons)) echo "display:none;"?>"><input class="ap-st-st-3" type="radio" name="send_to" id="download" value="download" <?php if($this->send_via == "download") echo "checked"; ?>>&nbsp;<span><?php echo $this->listenerObject->z_xlt('Print To')?></span></div>
For more information execute 'psecio-parse rules TypeSafeInArray'

252) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 36
Evaluation using in_array should enforce type checking (third parameter should be true)
> 		<div class="ap-st-st-1" style="<?php if(!in_array('download',$required_buttons)) echo "display:none;"?>"><input class="ap-st-st-3" type="radio" name="send_to" id="download" value="download" <?php if($this->send_via == "download") echo "checked"; ?>>&nbsp;<span><?php echo $this->listenerObject->z_xlt('Print To')?></span></div>
For more information execute 'psecio-parse rules InArrayStrict'

253) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 37
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     <div class="ap-st-st-1" style="<?php if(!in_array('download_all',$required_buttons)) echo "display:none;"?>"><input class="ap-st-st-3" type="radio" name="send_to" id="download_all" value="download_all" <?php if($this->send_via == "download_all") echo "checked"; ?>>&nbsp;<span><?php echo $this->listenerObject->z_xlt('Download')?></span></div>
For more information execute 'psecio-parse rules TypeSafeInArray'

254) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 37
Evaluation using in_array should enforce type checking (third parameter should be true)
>     <div class="ap-st-st-1" style="<?php if(!in_array('download_all',$required_buttons)) echo "display:none;"?>"><input class="ap-st-st-3" type="radio" name="send_to" id="download_all" value="download_all" <?php if($this->send_via == "download_all") echo "checked"; ?>>&nbsp;<span><?php echo $this->listenerObject->z_xlt('Download')?></span></div>
For more information execute 'psecio-parse rules InArrayStrict'

255) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 132
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 						<input <?php if(!in_array('pdf_format', $download_format)) echo "disabled"?> type="radio" name="download_format" id="pdf_format" style="margin: 0; clear: right; float: left;">
For more information execute 'psecio-parse rules TypeSafeInArray'

256) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 132
Evaluation using in_array should enforce type checking (third parameter should be true)
> 						<input <?php if(!in_array('pdf_format', $download_format)) echo "disabled"?> type="radio" name="download_format" id="pdf_format" style="margin: 0; clear: right; float: left;">
For more information execute 'psecio-parse rules InArrayStrict'

257) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 136
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 						<input <?php if(!in_array('doc_format', $download_format)) echo "disabled"?> type="radio" name="download_format" id="doc_format" style="margin: 0; clear: right; float: left;">
For more information execute 'psecio-parse rules TypeSafeInArray'

258) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 136
Evaluation using in_array should enforce type checking (third parameter should be true)
> 						<input <?php if(!in_array('doc_format', $download_format)) echo "disabled"?> type="radio" name="download_format" id="doc_format" style="margin: 0; clear: right; float: left;">
For more information execute 'psecio-parse rules InArrayStrict'

259) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 140
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 						<input <?php if(!in_array('hl7_format', $download_format)) echo "disabled"?> type="radio" name="download_format" id="hl7_format" style="margin: 0; clear: right; float: left;" checked="true">
For more information execute 'psecio-parse rules TypeSafeInArray'

260) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Application/view/application/sendto/send.phtml on line 140
Evaluation using in_array should enforce type checking (third parameter should be true)
> 						<input <?php if(!in_array('hl7_format', $download_format)) echo "disabled"?> type="radio" name="download_format" id="hl7_format" style="margin: 0; clear: right; float: left;" checked="true">
For more information execute 'psecio-parse rules InArrayStrict'

261) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/SyndromicsurveillanceTable.php on line 517
'header()' calls should not use concatenation directly
>         header('Content-Disposition: attachment; filename=' . $filename);
For more information execute 'psecio-parse rules SetHeaderWithInput'

262) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Syndromicsurveillance/view/syndromicsurveillance/syndromicsurveillance/index.phtml on line 80
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                     <option <?php if(in_array($row_code['id'], $this->form_data['form_icd_codes'])) echo "selected";?> value="<?php echo $this->escapeHtml($row_code['id']);?>"><?php echo $this->escapeHtml($row_code['name']);?></option>
For more information execute 'psecio-parse rules TypeSafeInArray'

263) /projects/emr/open-emr/src/openemr/interface/modules/zend_modules/module/Syndromicsurveillance/view/syndromicsurveillance/syndromicsurveillance/index.phtml on line 80
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                     <option <?php if(in_array($row_code['id'], $this->form_data['form_icd_codes'])) echo "selected";?> value="<?php echo $this->escapeHtml($row_code['id']);?>"><?php echo $this->escapeHtml($row_code['name']);?></option>
For more information execute 'psecio-parse rules InArrayStrict'

264) /projects/emr/open-emr/src/openemr/interface/eRxXMLBuilder.php on line 111
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $result = curl_exec($curlHandler) or die(curl_error($curlHandler));
For more information execute 'psecio-parse rules LogicalOperators'

265) /projects/emr/open-emr/src/openemr/interface/eRxXMLBuilder.php on line 296
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>                 $newCropUser = 'Staff';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

266) /projects/emr/open-emr/src/openemr/interface/eRxXMLBuilder.php on line 299
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>                 $newCropUser = 'LicensedPrescriber';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

267) /projects/emr/open-emr/src/openemr/interface/eRxXMLBuilder.php on line 302
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>                 $newCropUser = 'SupervisingDoctor';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

268) /projects/emr/open-emr/src/openemr/interface/eRxXMLBuilder.php on line 305
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>                 $newCropUser = 'MidlevelPrescriber';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

269) /projects/emr/open-emr/src/openemr/interface/eRxXMLBuilder.php on line 308
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>                 $newCropUser = '';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

270) /projects/emr/open-emr/src/openemr/interface/super/load_codes.php on line 52
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt('Code type not yet defined') . ": '" . text($code_type) . "'");
For more information execute 'psecio-parse rules ExitOrDie'

271) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 231
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($addGroup == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

272) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 501
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                         in_array(RuleType::ActiveAlert, $types) ? 1 : 0,
For more information execute 'psecio-parse rules TypeSafeInArray'

273) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 501
Evaluation using in_array should enforce type checking (third parameter should be true)
>                         in_array(RuleType::ActiveAlert, $types) ? 1 : 0,
For more information execute 'psecio-parse rules InArrayStrict'

274) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 502
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                         in_array(RuleType::PassiveAlert, $types) ? 1 : 0,
For more information execute 'psecio-parse rules TypeSafeInArray'

275) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 502
Evaluation using in_array should enforce type checking (third parameter should be true)
>                         in_array(RuleType::PassiveAlert, $types) ? 1 : 0,
For more information execute 'psecio-parse rules InArrayStrict'

276) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 503
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                         in_array(RuleType::CQM, $types) ? 1 : 0,
For more information execute 'psecio-parse rules TypeSafeInArray'

277) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 503
Evaluation using in_array should enforce type checking (third parameter should be true)
>                         in_array(RuleType::CQM, $types) ? 1 : 0,
For more information execute 'psecio-parse rules InArrayStrict'

278) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 504
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                         in_array(RuleType::AMC, $types) ? 1 : 0,
For more information execute 'psecio-parse rules TypeSafeInArray'

279) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 504
Evaluation using in_array should enforce type checking (third parameter should be true)
>                         in_array(RuleType::AMC, $types) ? 1 : 0,
For more information execute 'psecio-parse rules InArrayStrict'

280) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 505
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                         in_array(RuleType::PatientReminder, $types) ? 1 : 0,
For more information execute 'psecio-parse rules TypeSafeInArray'

281) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 505
Evaluation using in_array should enforce type checking (third parameter should be true)
>                         in_array(RuleType::PatientReminder, $types) ? 1 : 0,
For more information execute 'psecio-parse rules InArrayStrict'

282) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 520
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 in_array(RuleType::ActiveAlert, $types) ? 1 : 0,
For more information execute 'psecio-parse rules TypeSafeInArray'

283) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 520
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 in_array(RuleType::ActiveAlert, $types) ? 1 : 0,
For more information execute 'psecio-parse rules InArrayStrict'

284) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 521
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 in_array(RuleType::PassiveAlert, $types) ? 1 : 0,
For more information execute 'psecio-parse rules TypeSafeInArray'

285) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 521
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 in_array(RuleType::PassiveAlert, $types) ? 1 : 0,
For more information execute 'psecio-parse rules InArrayStrict'

286) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 522
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 in_array(RuleType::CQM, $types) ? 1 : 0,
For more information execute 'psecio-parse rules TypeSafeInArray'

287) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 522
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 in_array(RuleType::CQM, $types) ? 1 : 0,
For more information execute 'psecio-parse rules InArrayStrict'

288) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 523
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 in_array(RuleType::AMC, $types) ? 1 : 0,
For more information execute 'psecio-parse rules TypeSafeInArray'

289) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 523
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 in_array(RuleType::AMC, $types) ? 1 : 0,
For more information execute 'psecio-parse rules InArrayStrict'

290) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 524
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 in_array(RuleType::PatientReminder, $types) ? 1 : 0,
For more information execute 'psecio-parse rules TypeSafeInArray'

291) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleManager.php on line 524
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 in_array(RuleType::PatientReminder, $types) ? 1 : 0,
For more information execute 'psecio-parse rules InArrayStrict'

292) /projects/emr/open-emr/src/openemr/interface/super/rules/library/RuleType.php on line 21
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     const PassiveAlert = "passivealert";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

293) /projects/emr/open-emr/src/openemr/interface/super/rules/include/common.php on line 83
Avoid the use of an output method (echo, print, etc) directly with a variable
>     echo _base_url() . '/www/js/' . $file;
For more information execute 'psecio-parse rules OutputWithVariable'

294) /projects/emr/open-emr/src/openemr/interface/super/rules/include/common.php on line 88
Avoid the use of an output method (echo, print, etc) directly with a variable
>     echo _base_url() . '/www/css/' . $file;
For more information execute 'psecio-parse rules OutputWithVariable'

295) /projects/emr/open-emr/src/openemr/interface/super/manage_document_templates.php on line 54
'header()' calls should not use concatenation directly
>     header("Content-Length: " . strlen($fileData));
For more information execute 'psecio-parse rules SetHeaderWithInput'

296) /projects/emr/open-emr/src/openemr/interface/super/manage_document_templates.php on line 91
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array(strtolower($path_parts['extension']), array('odt', 'txt', 'docx', 'zip'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

297) /projects/emr/open-emr/src/openemr/interface/super/manage_document_templates.php on line 91
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array(strtolower($path_parts['extension']), array('odt', 'txt', 'docx', 'zip'))) {
For more information execute 'psecio-parse rules InArrayStrict'

298) /projects/emr/open-emr/src/openemr/interface/super/manage_document_templates.php on line 92
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die(text(strtolower($path_parts['extension'])) . ' ' . xlt('filetype is not accepted'));
For more information execute 'psecio-parse rules ExitOrDie'

299) /projects/emr/open-emr/src/openemr/interface/super/manage_document_templates.php on line 118
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die(xlt('Unable to create') . " '" . text($templatepath) . "'");
For more information execute 'psecio-parse rules ExitOrDie'

300) /projects/emr/open-emr/src/openemr/interface/super/layout_service_codes.php on line 65
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die(xlt('Cannot open') . text(" '$tmp_name'"));
For more information execute 'psecio-parse rules ExitOrDie'

301) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 125
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($grpname, $USER_SPECIFIC_TABS)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

302) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 125
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($grpname, $USER_SPECIFIC_TABS)) {
For more information execute 'psecio-parse rules InArrayStrict'

303) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 127
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

304) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 127
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
For more information execute 'psecio-parse rules InArrayStrict'

305) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 191
'header()' calls should not use concatenation directly
>         header("Content-Disposition: attachment; filename=".$practice_filename);
For more information execute 'psecio-parse rules SetHeaderWithInput'

306) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 195
The readfile/readlink/readgzfile functions output content directly (possible injection)
>         readfile($tmpfilename);
For more information execute 'psecio-parse rules Readfile'

307) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 404
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!$userMode || in_array($grpname, $USER_SPECIFIC_TABS)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

308) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 404
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!$userMode || in_array($grpname, $USER_SPECIFIC_TABS)) {
For more information execute 'psecio-parse rules InArrayStrict'

309) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 418
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!$userMode || in_array($grpname, $USER_SPECIFIC_TABS)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

310) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 418
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!$userMode || in_array($grpname, $USER_SPECIFIC_TABS)) {
For more information execute 'psecio-parse rules InArrayStrict'

311) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 434
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (!$userMode || in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

312) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 434
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (!$userMode || in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
For more information execute 'psecio-parse rules InArrayStrict'

313) /projects/emr/open-emr/src/openemr/interface/super/edit_globals.php on line 455
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>                 $userSetting = "";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

314) /projects/emr/open-emr/src/openemr/interface/super/manage_site_files.php on line 84
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array(strtolower($path_parts['extension']), array('gif','jpg','jpe','jpeg','png','svg'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

315) /projects/emr/open-emr/src/openemr/interface/super/manage_site_files.php on line 84
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array(strtolower($path_parts['extension']), array('gif','jpg','jpe','jpeg','png','svg'))) {
For more information execute 'psecio-parse rules InArrayStrict'

316) /projects/emr/open-emr/src/openemr/interface/super/manage_site_files.php on line 100
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die(xlt('Unable to create') . " '" . text($imagepath) . "'");
For more information execute 'psecio-parse rules ExitOrDie'

317) /projects/emr/open-emr/src/openemr/interface/super/manage_site_files.php on line 398
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (!in_array($type, $white_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

318) /projects/emr/open-emr/src/openemr/interface/super/manage_site_files.php on line 398
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (!in_array($type, $white_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

319) /projects/emr/open-emr/src/openemr/interface/super/edit_layout.php on line 229
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt('Internal error in tableNameFromLayout') . '(' . text($layout_id) . ')');
For more information execute 'psecio-parse rules ExitOrDie'

320) /projects/emr/open-emr/src/openemr/interface/super/edit_layout.php on line 1376
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($firstgroup == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

321) /projects/emr/open-emr/src/openemr/interface/therapy_groups/therapy_groups_views/groupDetailsGeneralData.php on line 152
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                                         <option value="<?php echo attr($user['id']);?>" <?php echo !is_null($groupData['counselors']) && in_array($user['id'], $groupData['counselors']) ? 'selected' : '';?>><?php echo text($user['fname'] . ' ' . $user['lname']);?></option>
For more information execute 'psecio-parse rules TypeSafeInArray'

322) /projects/emr/open-emr/src/openemr/interface/therapy_groups/therapy_groups_views/groupDetailsGeneralData.php on line 152
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                                         <option value="<?php echo attr($user['id']);?>" <?php echo !is_null($groupData['counselors']) && in_array($user['id'], $groupData['counselors']) ? 'selected' : '';?>><?php echo text($user['fname'] . ' ' . $user['lname']);?></option>
For more information execute 'psecio-parse rules InArrayStrict'

323) /projects/emr/open-emr/src/openemr/interface/therapy_groups/therapy_groups_views/listGroups.php on line 144
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     <td><?php echo ($group['group_end_date'] == '0000-00-00' or $group['group_end_date'] == '00-00-0000' or empty($group['group_end_date'])) ? '' : text(oeFormatShortDate($group['group_end_date'])); ?></td>
For more information execute 'psecio-parse rules LogicalOperators'

324) /projects/emr/open-emr/src/openemr/interface/therapy_groups/therapy_groups_views/listGroups.php on line 144
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     <td><?php echo ($group['group_end_date'] == '0000-00-00' or $group['group_end_date'] == '00-00-0000' or empty($group['group_end_date'])) ? '' : text(oeFormatShortDate($group['group_end_date'])); ?></td>
For more information execute 'psecio-parse rules LogicalOperators'

325) /projects/emr/open-emr/src/openemr/interface/therapy_groups/therapy_groups_views/addGroup.php on line 112
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                     <option value="<?php echo attr($user['id']);?>" <?php echo !is_null($groupData['counselors']) && in_array($user['id'], $groupData['counselors']) ? 'selected' : '';?>><?php echo text($user['fname'] . ' ' . $user['lname']);?></option>
For more information execute 'psecio-parse rules TypeSafeInArray'

326) /projects/emr/open-emr/src/openemr/interface/therapy_groups/therapy_groups_views/addGroup.php on line 112
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                     <option value="<?php echo attr($user['id']);?>" <?php echo !is_null($groupData['counselors']) && in_array($user['id'], $groupData['counselors']) ? 'selected' : '';?>><?php echo text($user['fname'] . ' ' . $user['lname']);?></option>
For more information execute 'psecio-parse rules InArrayStrict'

327) /projects/emr/open-emr/src/openemr/interface/therapy_groups/therapy_groups_controllers/base_controller.php on line 43
By default 'extract' overwrites variables in the local scope
>         extract($data);
For more information execute 'psecio-parse rules Extract'

328) /projects/emr/open-emr/src/openemr/interface/patient_file/history/encounters.php on line 292
Avoid the use of an output method (echo, print, etc) directly with a variable
> <a href='encounters.php?billing=0&issue=<?php echo $issue.$getStringForPage; ?>' onclick='top.restoreSession()' style='font-size:8pt'>(<?php echo xlt('To Clinical View'); ?>)</a>
For more information execute 'psecio-parse rules OutputWithVariable'

329) /projects/emr/open-emr/src/openemr/interface/patient_file/history/encounters.php on line 294
Avoid the use of an output method (echo, print, etc) directly with a variable
> <a href='encounters.php?billing=1&issue=<?php echo $issue.$getStringForPage; ?>' onclick='top.restoreSession()' style='font-size:8pt'>(<?php echo xlt('To Billing View'); ?>)</a>
For more information execute 'psecio-parse rules OutputWithVariable'

330) /projects/emr/open-emr/src/openemr/interface/patient_file/history/encounters.php on line 427
Avoid the use of an output method (echo, print, etc) directly with a variable
> echo ($pagestart + 1)."-".$upper." " . htmlspecialchars(xl('of'), ENT_NOQUOTES) . " " .$numRes;
For more information execute 'psecio-parse rules OutputWithVariable'

331) /projects/emr/open-emr/src/openemr/interface/patient_file/history/encounters.php on line 536
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo "<td>".$reason_string;
For more information execute 'psecio-parse rules OutputWithVariable'

332) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/labdata.php on line 145
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (in_array($myrow['value_code'], $value_select)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

333) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/labdata.php on line 145
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (in_array($myrow['value_code'], $value_select)) {
For more information execute 'psecio-parse rules InArrayStrict'

334) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/labdata.php on line 390
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         while ($a==true) {
For more information execute 'psecio-parse rules BooleanIdentity'

335) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/create_portallogin.php on line 36
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     $password = '';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

336) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/add_edit_issue.php on line 156
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (in_array($codeTyX, $allowCodes2)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

337) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/add_edit_issue.php on line 156
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (in_array($codeTyX, $allowCodes2)) {
For more information execute 'psecio-parse rules InArrayStrict'

338) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/disclosure_full.php on line 25
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> if (isset($_POST["mode"]) and  $_POST["mode"] == "disclosure") {
For more information execute 'psecio-parse rules LogicalOperators'

339) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/disclosure_full.php on line 35
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     if (isset($_POST["updatemode"]) and $_POST["updatemode"] == "disclosure_update") {
For more information execute 'psecio-parse rules LogicalOperators'

340) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/stats.php on line 121
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $unit . " " . text($row_currentMed['dosage']) . " " . $rin . " " . $rroute . " " . $rint;
For more information execute 'psecio-parse rules OutputWithVariable'

341) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/stats.php on line 388
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $unit . " " . text($row_currentMed['dosage']) . " " . $rin . " " . $rroute . " " . $rint; ?></td>
For more information execute 'psecio-parse rules OutputWithVariable'

342) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/demographics.php on line 105
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     if ($pid and $doc_catg) {
For more information execute 'psecio-parse rules LogicalOperators'

343) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/demographics.php on line 127
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array($extension, $viewable_types)) { // extension matches list
For more information execute 'psecio-parse rules TypeSafeInArray'

344) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/demographics.php on line 127
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array($extension, $viewable_types)) { // extension matches list
For more information execute 'psecio-parse rules InArrayStrict'

345) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/demographics.php on line 1428
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if ($photos or $idcard_doc_id) {
For more information execute 'psecio-parse rules LogicalOperators'

346) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/demographics.php on line 1698
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                             if (!in_array($row['pc_catid'], $therapyGroupCategories)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

347) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/demographics.php on line 1698
Evaluation using in_array should enforce type checking (third parameter should be true)
>                             if (!in_array($row['pc_catid'], $therapyGroupCategories)) {
For more information execute 'psecio-parse rules InArrayStrict'

348) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/demographics.php on line 1713
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                             if (in_array($row['pc_catid'], $therapyGroupCategories)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

349) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/demographics.php on line 1713
Evaluation using in_array should enforce type checking (third parameter should be true)
>                             if (in_array($row['pc_catid'], $therapyGroupCategories)) {
For more information execute 'psecio-parse rules InArrayStrict'

350) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/demographics.php on line 1722
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                             echo !in_array($row['pc_catid'], $therapyGroupCategories) ? '</a>' : '<span>';
For more information execute 'psecio-parse rules TypeSafeInArray'

351) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/demographics.php on line 1722
Evaluation using in_array should enforce type checking (third parameter should be true)
>                             echo !in_array($row['pc_catid'], $therapyGroupCategories) ? '</a>' : '<span>';
For more information execute 'psecio-parse rules InArrayStrict'

352) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/pnotes.php on line 103
Avoid the use of an output method (echo, print, etc) directly with a variable
>     $formatted = sprintf((xl('$').'%01.2f'), $balance);
For more information execute 'psecio-parse rules OutputWithVariable'

353) /projects/emr/open-emr/src/openemr/interface/patient_file/summary/add_edit_amendments.php on line 84
'header()' calls should not use concatenation directly
>     header("Location:add_edit_amendments.php?id=" . urlencode($amendment_id));
For more information execute 'psecio-parse rules SetHeaderWithInput'

354) /projects/emr/open-emr/src/openemr/interface/patient_file/report/custom_report.php on line 134
Avoid the use of an output method (echo, print, etc) directly with a variable
> <link rel="stylesheet" href="<?php echo  $web_root . '/interface/themes/style_pdf.css' ?>" type="text/css">
For more information execute 'psecio-parse rules OutputWithVariable'

355) /projects/emr/open-emr/src/openemr/interface/patient_file/report/custom_report.php on line 504
Avoid the use of an output method (echo, print, etc) directly with a variable
>                     echo text($row['administered_date']) . " - " . $vaccine_display;
For more information execute 'psecio-parse rules OutputWithVariable'

356) /projects/emr/open-emr/src/openemr/interface/patient_file/report/patient_report.php on line 139
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                             if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

357) /projects/emr/open-emr/src/openemr/interface/patient_file/report/patient_report.php on line 139
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                             if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

358) /projects/emr/open-emr/src/openemr/interface/patient_file/report/patient_report.php on line 174
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                         if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

359) /projects/emr/open-emr/src/openemr/interface/patient_file/report/patient_report.php on line 174
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                         if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

360) /projects/emr/open-emr/src/openemr/interface/patient_file/report/patient_report.php on line 651
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

361) /projects/emr/open-emr/src/openemr/interface/patient_file/report/patient_report.php on line 651
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

362) /projects/emr/open-emr/src/openemr/interface/patient_file/report/patient_report.php on line 695
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

363) /projects/emr/open-emr/src/openemr/interface/patient_file/report/patient_report.php on line 695
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

364) /projects/emr/open-emr/src/openemr/interface/patient_file/reminder/patient_reminders.php on line 161
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($begin == "" or $begin == 0) {
For more information execute 'psecio-parse rules LogicalOperators'

365) /projects/emr/open-emr/src/openemr/interface/patient_file/reminder/patient_reminders.php on line 236
Avoid the use of an output method (echo, print, etc) directly with a variable
>                             <td align=right class='text'><?php echo $prevlink . " " . text($end) . " of " . text($total) . " " . $nextlink; ?></td>
For more information execute 'psecio-parse rules OutputWithVariable'

366) /projects/emr/open-emr/src/openemr/interface/patient_file/deleter.php on line 291
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("There is no form with id '" . text($formid) . "'");
For more information execute 'psecio-parse rules ExitOrDie'

367) /projects/emr/open-emr/src/openemr/interface/patient_file/deleter.php on line 346
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                           die(xlt('Unable to match this payment in ar_activity') . ": " . text($tpmt));
For more information execute 'psecio-parse rules ExitOrDie'

368) /projects/emr/open-emr/src/openemr/interface/patient_file/printed_fee_sheet.php on line 102
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> if (!empty($_SESSION['pidList']) and $form_fill == 2) {
For more information execute 'psecio-parse rules LogicalOperators'

369) /projects/emr/open-emr/src/openemr/interface/patient_file/download_template.php on line 414
'header()' calls should not use concatenation directly
> header("Content-Length: " . filesize($fname));
For more information execute 'psecio-parse rules SetHeaderWithInput'

370) /projects/emr/open-emr/src/openemr/interface/patient_file/download_template.php on line 417
The readfile/readlink/readgzfile functions output content directly (possible injection)
> readfile($fname);
For more information execute 'psecio-parse rules Readfile'

371) /projects/emr/open-emr/src/openemr/interface/patient_file/letter.php on line 540
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die(xlt('Cannot read') . ' ' . text($tpldir));
For more information execute 'psecio-parse rules ExitOrDie'

372) /projects/emr/open-emr/src/openemr/interface/patient_file/merge_patients.php on line 147
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die("<br />" . xlt('Change failed! CouchDB connect error?'));
For more information execute 'psecio-parse rules ExitOrDie'

373) /projects/emr/open-emr/src/openemr/interface/patient_file/merge_patients.php on line 164
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die(xlt('Cannot read directory') . " '" . text($sencdir) . "'");
For more information execute 'psecio-parse rules ExitOrDie'

374) /projects/emr/open-emr/src/openemr/interface/patient_file/merge_patients.php on line 176
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                         die("<br />" . xlt('Delete failed!'));
For more information execute 'psecio-parse rules ExitOrDie'

375) /projects/emr/open-emr/src/openemr/interface/patient_file/merge_patients.php on line 186
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                     die("<br />" . xlt('Move failed!'));
For more information execute 'psecio-parse rules ExitOrDie'

376) /projects/emr/open-emr/src/openemr/interface/patient_file/transaction/print_referral.php on line 59
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die(text($template_file). " does not exist!");
For more information execute 'psecio-parse rules ExitOrDie'

377) /projects/emr/open-emr/src/openemr/interface/patient_file/ccr_review_approve.php on line 212
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                 if (in_array($res_existing_prob['diagnosis'], $aud_res['lists1'][$k])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

378) /projects/emr/open-emr/src/openemr/interface/patient_file/ccr_review_approve.php on line 212
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                 if (in_array($res_existing_prob['diagnosis'], $aud_res['lists1'][$k])) {
For more information execute 'psecio-parse rules InArrayStrict'

379) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/superbill_custom_full.php on line 680
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                             if (isset($filter) && in_array($value['id'], $filter)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

380) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/superbill_custom_full.php on line 680
Evaluation using in_array should enforce type checking (third parameter should be true)
>                             if (isset($filter) && in_array($value['id'], $filter)) {
For more information execute 'psecio-parse rules InArrayStrict'

381) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1016
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if ((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write') and $is_group == 0 and $authPostCalendarCategoryWrite)
>             or (((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write')) and $is_group and acl_check("groups", "glog", false, 'write')) and $authPostCalendarCategoryWrite)) {
For more information execute 'psecio-parse rules LogicalOperators'

382) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1016
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if ((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write') and $is_group == 0 and $authPostCalendarCategoryWrite)
For more information execute 'psecio-parse rules LogicalOperators'

383) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1016
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if ((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write') and $is_group == 0 and $authPostCalendarCategoryWrite)
For more information execute 'psecio-parse rules LogicalOperators'

384) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1017
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             or (((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write')) and $is_group and acl_check("groups", "glog", false, 'write')) and $authPostCalendarCategoryWrite)) {
For more information execute 'psecio-parse rules LogicalOperators'

385) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1017
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             or (((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write')) and $is_group and acl_check("groups", "glog", false, 'write')) and $authPostCalendarCategoryWrite)) {
For more information execute 'psecio-parse rules LogicalOperators'

386) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1017
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             or (((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write')) and $is_group and acl_check("groups", "glog", false, 'write')) and $authPostCalendarCategoryWrite)) {
For more information execute 'psecio-parse rules LogicalOperators'

387) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1028
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (($esign->isButtonViewable() and $is_group == 0 and $authPostCalendarCategoryWrite) or ($esign->isButtonViewable() and $is_group and acl_check("groups", "glog", false, 'write') and $authPostCalendarCategoryWrite)) {
For more information execute 'psecio-parse rules LogicalOperators'

388) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1028
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (($esign->isButtonViewable() and $is_group == 0 and $authPostCalendarCategoryWrite) or ($esign->isButtonViewable() and $is_group and acl_check("groups", "glog", false, 'write') and $authPostCalendarCategoryWrite)) {
For more information execute 'psecio-parse rules LogicalOperators'

389) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1028
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (($esign->isButtonViewable() and $is_group == 0 and $authPostCalendarCategoryWrite) or ($esign->isButtonViewable() and $is_group and acl_check("groups", "glog", false, 'write') and $authPostCalendarCategoryWrite)) {
For more information execute 'psecio-parse rules LogicalOperators'

390) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1028
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (($esign->isButtonViewable() and $is_group == 0 and $authPostCalendarCategoryWrite) or ($esign->isButtonViewable() and $is_group and acl_check("groups", "glog", false, 'write') and $authPostCalendarCategoryWrite)) {
For more information execute 'psecio-parse rules LogicalOperators'

391) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1028
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (($esign->isButtonViewable() and $is_group == 0 and $authPostCalendarCategoryWrite) or ($esign->isButtonViewable() and $is_group and acl_check("groups", "glog", false, 'write') and $authPostCalendarCategoryWrite)) {
For more information execute 'psecio-parse rules LogicalOperators'

392) /projects/emr/open-emr/src/openemr/interface/patient_file/encounter/forms.php on line 1028
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (($esign->isButtonViewable() and $is_group == 0 and $authPostCalendarCategoryWrite) or ($esign->isButtonViewable() and $is_group and acl_check("groups", "glog", false, 'write') and $authPostCalendarCategoryWrite)) {
For more information execute 'psecio-parse rules LogicalOperators'

393) /projects/emr/open-emr/src/openemr/interface/patient_file/education.php on line 53
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die(xlt('Code type not recognized') . ': ' . text($codetype));
For more information execute 'psecio-parse rules ExitOrDie'

394) /projects/emr/open-emr/src/openemr/interface/patient_file/education.php on line 94
'header()' calls should not use concatenation directly
>             header("Content-Length: " . filesize($filepath));
For more information execute 'psecio-parse rules SetHeaderWithInput'

395) /projects/emr/open-emr/src/openemr/interface/patient_file/education.php on line 97
The readfile/readlink/readgzfile functions output content directly (possible injection)
>             readfile($filepath);
For more information execute 'psecio-parse rules Readfile'

396) /projects/emr/open-emr/src/openemr/interface/orders/procedure_stats.php on line 417
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (is_array($form_show) && in_array($key, $form_show)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

397) /projects/emr/open-emr/src/openemr/interface/orders/procedure_stats.php on line 417
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (is_array($form_show) && in_array($key, $form_show)) {
For more information execute 'psecio-parse rules InArrayStrict'

398) /projects/emr/open-emr/src/openemr/interface/orders/qoe.inc.php on line 187
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($code, $answers)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

399) /projects/emr/open-emr/src/openemr/interface/orders/qoe.inc.php on line 187
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($code, $answers)) {
For more information execute 'psecio-parse rules InArrayStrict'

400) /projects/emr/open-emr/src/openemr/interface/orders/qoe.inc.php on line 207
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (in_array($code, $answers)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

401) /projects/emr/open-emr/src/openemr/interface/orders/qoe.inc.php on line 207
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (in_array($code, $answers)) {
For more information execute 'psecio-parse rules InArrayStrict'

402) /projects/emr/open-emr/src/openemr/interface/orders/qoe.inc.php on line 231
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (in_array($code, $answers)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

403) /projects/emr/open-emr/src/openemr/interface/orders/qoe.inc.php on line 231
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (in_array($code, $answers)) {
For more information execute 'psecio-parse rules InArrayStrict'

404) /projects/emr/open-emr/src/openemr/interface/orders/orders_results.php on line 42
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> if ($form_review and !$reviewauth and !$thisauth) {
For more information execute 'psecio-parse rules LogicalOperators'

405) /projects/emr/open-emr/src/openemr/interface/orders/orders_results.php on line 42
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> if ($form_review and !$reviewauth and !$thisauth) {
For more information execute 'psecio-parse rules LogicalOperators'

406) /projects/emr/open-emr/src/openemr/interface/orders/orders_results.php on line 553
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if ($result_facility <> "" && !in_array($result_facility, $facilities)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

407) /projects/emr/open-emr/src/openemr/interface/orders/orders_results.php on line 553
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if ($result_facility <> "" && !in_array($result_facility, $facilities)) {
For more information execute 'psecio-parse rules InArrayStrict'

408) /projects/emr/open-emr/src/openemr/interface/patient_tracker/patient_tracker.php on line 835
Avoid the use of an output method (echo, print, etc) directly with a variable
>                             echo "<span style='font-size:0.7em;' onclick='return calendarpopup(" . attr_js($appt_eid) . "," . attr_js($date_squash) . ")'>" . implode($icon_here) . $icon2_here . "</span> " . $icon_CALL;
For more information execute 'psecio-parse rules OutputWithVariable'

409) /projects/emr/open-emr/src/openemr/interface/logview/erx_logview.php on line 60
'header()' calls should not use concatenation directly
>         header('Content-Disposition: attachment; filename='.$filename);
For more information execute 'psecio-parse rules SetHeaderWithInput'

410) /projects/emr/open-emr/src/openemr/interface/logview/erx_logview.php on line 62
'header()' calls should not use concatenation directly
>         header('Content-Length: '.strlen($bat_content));
For more information execute 'psecio-parse rules SetHeaderWithInput'

411) /projects/emr/open-emr/src/openemr/interface/usergroup/ssl_certificates_admin.php on line 159
'header()' calls should not use concatenation directly
>     header("Content-Type: application/" . $filetype);
For more information execute 'psecio-parse rules SetHeaderWithInput'

412) /projects/emr/open-emr/src/openemr/interface/usergroup/ssl_certificates_admin.php on line 160
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=" . basename($filename) . ";");
For more information execute 'psecio-parse rules SetHeaderWithInput'

413) /projects/emr/open-emr/src/openemr/interface/usergroup/ssl_certificates_admin.php on line 162
'header()' calls should not use concatenation directly
>     header("Content-Length: " . filesize($filename));
For more information execute 'psecio-parse rules SetHeaderWithInput'

414) /projects/emr/open-emr/src/openemr/interface/usergroup/ssl_certificates_admin.php on line 163
The readfile/readlink/readgzfile functions output content directly (possible injection)
>     readfile($filename);
For more information execute 'psecio-parse rules Readfile'

415) /projects/emr/open-emr/src/openemr/interface/usergroup/usergroup_admin.php on line 172
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>               $password_err_msg="";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

416) /projects/emr/open-emr/src/openemr/interface/usergroup/usergroup_admin.php on line 252
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($doit == true && $row['username'] == trim($_POST['rumple'])) {
For more information execute 'psecio-parse rules BooleanIdentity'

417) /projects/emr/open-emr/src/openemr/interface/usergroup/usergroup_admin.php on line 257
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($doit == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

418) /projects/emr/open-emr/src/openemr/interface/usergroup/usergroup_admin.php on line 297
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>             $password_err_msg="";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

419) /projects/emr/open-emr/src/openemr/interface/usergroup/addrbook_list.php on line 168
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>         $username = '--';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

420) /projects/emr/open-emr/src/openemr/interface/usergroup/mfa_u2f.php on line 125
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt('Registration error') . ': ' . text($e->getMessage()));
For more information execute 'psecio-parse rules ExitOrDie'

421) /projects/emr/open-emr/src/openemr/interface/usergroup/user_admin.php on line 367
The third parameter should be set (and be true) on in_array to avoid type switching issues
>    <option <?php echo in_array($frow['id'], $ufid) || $frow['id'] == $iter['facility_id'] ? "selected" : null ?>
For more information execute 'psecio-parse rules TypeSafeInArray'

422) /projects/emr/open-emr/src/openemr/interface/usergroup/user_admin.php on line 367
Evaluation using in_array should enforce type checking (third parameter should be true)
>    <option <?php echo in_array($frow['id'], $ufid) || $frow['id'] == $iter['facility_id'] ? "selected" : null ?>
For more information execute 'psecio-parse rules InArrayStrict'

423) /projects/emr/open-emr/src/openemr/interface/usergroup/user_admin.php on line 483
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (($username_acl_groups) && in_array($value, $username_acl_groups)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

424) /projects/emr/open-emr/src/openemr/interface/usergroup/user_admin.php on line 483
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (($username_acl_groups) && in_array($value, $username_acl_groups)) {
For more information execute 'psecio-parse rules InArrayStrict'

425) /projects/emr/open-emr/src/openemr/interface/eRxSOAP.php on line 316
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             $return = in_array($currentStatus, $status);
For more information execute 'psecio-parse rules TypeSafeInArray'

426) /projects/emr/open-emr/src/openemr/interface/eRxSOAP.php on line 316
Evaluation using in_array should enforce type checking (third parameter should be true)
>             $return = in_array($currentStatus, $status);
For more information execute 'psecio-parse rules InArrayStrict'

427) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.php on line 98
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($_POST['app_s']!=0 and $_POST['app_s']!='') {
For more information execute 'psecio-parse rules LogicalOperators'

428) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.php on line 103
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($_POST['app_e']!=0 and $_POST['app_e']!='') {
For more information execute 'psecio-parse rules LogicalOperators'

429) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.php on line 109
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($_POST['seen_since']!=0 and $_POST['seen_since']!='') {
For more information execute 'psecio-parse rules LogicalOperators'

430) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.php on line 114
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($_POST['seen_before']!=0 and $_POST['seen_before']!='') {
For more information execute 'psecio-parse rules LogicalOperators'

431) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.php on line 120
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($_POST['age_from']!=0 and $_POST['age_from']!='') {
For more information execute 'psecio-parse rules LogicalOperators'

432) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.php on line 125
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($_POST['age_upto']!=0 and $_POST['age_upto']!='') {
For more information execute 'psecio-parse rules LogicalOperators'

433) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.inc.php on line 18
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     return preg_match($pat, $date) or $date=='' or $date=='0000-00-00';
For more information execute 'psecio-parse rules LogicalOperators'

434) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.inc.php on line 18
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     return preg_match($pat, $date) or $date=='' or $date=='0000-00-00';
For more information execute 'psecio-parse rules LogicalOperators'

435) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.inc.php on line 25
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     return preg_match($pat, $age) or $age=='';
For more information execute 'psecio-parse rules LogicalOperators'

436) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.inc.php on line 30
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     return array_search($select, $array) or 0===array_search($select, $array);
For more information execute 'psecio-parse rules LogicalOperators'

437) /projects/emr/open-emr/src/openemr/interface/batchcom/batchcom.inc.php on line 92
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=" . $filename);
For more information execute 'psecio-parse rules SetHeaderWithInput'

438) /projects/emr/open-emr/src/openemr/interface/language/lang_definition.php on line 252
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if ($row['definition']=='' or $row['definition']=='NULL') {
For more information execute 'psecio-parse rules LogicalOperators'

439) /projects/emr/open-emr/src/openemr/interface/language/lang_definition.php on line 260
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if ($row['def_id']=='' or $row['def_id']=='NULL') {
For more information execute 'psecio-parse rules LogicalOperators'

440) /projects/emr/open-emr/src/openemr/interface/de_identification_forms/re_identification_op_single_patient.php on line 81
Use of system functions, especially with user input, is not recommended
> system($sh_cmd);
For more information execute 'psecio-parse rules SystemFunctions'

441) /projects/emr/open-emr/src/openemr/interface/de_identification_forms/re_identification_op_single_patient.php on line 190
'header()' calls should not use concatenation directly
>             header('Content-Disposition: attachment; filename='.basename($filename));
For more information execute 'psecio-parse rules SetHeaderWithInput'

442) /projects/emr/open-emr/src/openemr/interface/de_identification_forms/re_identification_op_single_patient.php on line 197
'header()' calls should not use concatenation directly
>             header('Content-Length: ' . filesize($filename));
For more information execute 'psecio-parse rules SetHeaderWithInput'

443) /projects/emr/open-emr/src/openemr/interface/de_identification_forms/re_identification_op_single_patient.php on line 200
The readfile/readlink/readgzfile functions output content directly (possible injection)
>             readfile($filename);
For more information execute 'psecio-parse rules Readfile'

444) /projects/emr/open-emr/src/openemr/interface/de_identification_forms/de_identification_screen2.php on line 143
Use of system functions, especially with user input, is not recommended
>         system($sh_cmd);
For more information execute 'psecio-parse rules SystemFunctions'

445) /projects/emr/open-emr/src/openemr/interface/de_identification_forms/de_identification_screen2.php on line 259
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> } else if ($deIdentificationStatus == 2 or $deIdentificationStatus == 3) {
For more information execute 'psecio-parse rules LogicalOperators'

446) /projects/emr/open-emr/src/openemr/interface/de_identification_forms/de_identification_screen2.php on line 272
'header()' calls should not use concatenation directly
>         header('Content-Disposition: attachment; filename='.basename($filename));
For more information execute 'psecio-parse rules SetHeaderWithInput'

447) /projects/emr/open-emr/src/openemr/interface/de_identification_forms/de_identification_screen2.php on line 279
'header()' calls should not use concatenation directly
>         header('Content-Length: ' . filesize($filename));
For more information execute 'psecio-parse rules SetHeaderWithInput'

448) /projects/emr/open-emr/src/openemr/interface/de_identification_forms/de_identification_screen2.php on line 282
The readfile/readlink/readgzfile functions output content directly (possible injection)
>         readfile($filename);
For more information execute 'psecio-parse rules Readfile'

449) /projects/emr/open-emr/src/openemr/interface/globals.php on line 28
The third parameter should be set (and be true) on in_array to avoid type switching issues
> if (!(in_array('aes-256-cbc', openssl_get_cipher_methods()))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

450) /projects/emr/open-emr/src/openemr/interface/globals.php on line 28
Evaluation using in_array should enforce type checking (third parameter should be true)
> if (!(in_array('aes-256-cbc', openssl_get_cipher_methods()))) {
For more information execute 'psecio-parse rules InArrayStrict'

451) /projects/emr/open-emr/src/openemr/interface/globals.php on line 147
'header()' calls should not use concatenation directly
>             header('Location: index.php?site=' . urlencode($tmp));
For more information execute 'psecio-parse rules SetHeaderWithInput'

452) /projects/emr/open-emr/src/openemr/interface/globals.php on line 150
'header()' calls should not use concatenation directly
>             header('Location: ../login/login.php?site=' . urlencode($tmp)); // Assuming in the interface/main directory
For more information execute 'psecio-parse rules SetHeaderWithInput'

453) /projects/emr/open-emr/src/openemr/interface/globals.php on line 274
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if (array_key_exists('debug', $twigOptions) && $twigOptions['debug'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

454) /projects/emr/open-emr/src/openemr/interface/globals.php on line 636
The "display_errors" setting should not be enabled manually
>     ini_set('display_errors', 1);
For more information execute 'psecio-parse rules DisplayErrors'

455) /projects/emr/open-emr/src/openemr/interface/forms/LBF/printable.php on line 485
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($data_type, array(21,27,40))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

456) /projects/emr/open-emr/src/openemr/interface/forms/LBF/printable.php on line 485
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($data_type, array(21,27,40))) {
For more information execute 'psecio-parse rules InArrayStrict'

457) /projects/emr/open-emr/src/openemr/interface/forms/CAMOS/save.php on line 26
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (substr($key, 0, 3) == 'ch_' and $val='on') {
For more information execute 'psecio-parse rules LogicalOperators'

458) /projects/emr/open-emr/src/openemr/interface/forms/vitals/growthchart/chart.php on line 434
By default 'extract' overwrites variables in the local scope
>         extract(getPatientAgeYMD($dob, $date));
For more information execute 'psecio-parse rules Extract'

459) /projects/emr/open-emr/src/openemr/interface/forms/vitals/growthchart/chart.php on line 588
By default 'extract' overwrites variables in the local scope
>     extract(getPatientAgeYMD($dob, $date));
For more information execute 'psecio-parse rules Extract'

460) /projects/emr/open-emr/src/openemr/interface/forms/requisition/barcode.php on line 20
The third parameter should be set (and be true) on in_array to avoid type switching issues
> if (in_array(strtolower($code_type), array("code128", "code128b"))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

461) /projects/emr/open-emr/src/openemr/interface/forms/requisition/barcode.php on line 20
Evaluation using in_array should enforce type checking (third parameter should be true)
> if (in_array(strtolower($code_type), array("code128", "code128b"))) {
For more information execute 'psecio-parse rules InArrayStrict'

462) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 77
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> if ($encounter == "" && !$id && !$AJAX_PREFS && (($_REQUEST['mode'] != "retrieve") or ($_REQUEST['mode'] == "show_PDF"))) {
For more information execute 'psecio-parse rules LogicalOperators'

463) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($row['Field'] == 'id' or
>                     $row['Field'] == 'date' or
>                     $row['Field'] == 'pid' or
>                     $row['Field'] == 'user' or
>                     $row['Field'] == 'groupname' or
>                     $row['Field'] == 'authorized' or
>                     $row['Field'] == 'LOCKED' or
>                     $row['Field'] == 'LOCKEDBY' or
>                     $row['Field'] == 'activity' or
>                     $row['Field'] == 'PLAN' or
>                     $row['Field'] == 'Resource') {
For more information execute 'psecio-parse rules LogicalOperators'

464) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($row['Field'] == 'id' or
>                     $row['Field'] == 'date' or
>                     $row['Field'] == 'pid' or
>                     $row['Field'] == 'user' or
>                     $row['Field'] == 'groupname' or
>                     $row['Field'] == 'authorized' or
>                     $row['Field'] == 'LOCKED' or
>                     $row['Field'] == 'LOCKEDBY' or
>                     $row['Field'] == 'activity' or
>                     $row['Field'] == 'PLAN' or
For more information execute 'psecio-parse rules LogicalOperators'

465) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($row['Field'] == 'id' or
>                     $row['Field'] == 'date' or
>                     $row['Field'] == 'pid' or
>                     $row['Field'] == 'user' or
>                     $row['Field'] == 'groupname' or
>                     $row['Field'] == 'authorized' or
>                     $row['Field'] == 'LOCKED' or
>                     $row['Field'] == 'LOCKEDBY' or
>                     $row['Field'] == 'activity' or
For more information execute 'psecio-parse rules LogicalOperators'

466) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($row['Field'] == 'id' or
>                     $row['Field'] == 'date' or
>                     $row['Field'] == 'pid' or
>                     $row['Field'] == 'user' or
>                     $row['Field'] == 'groupname' or
>                     $row['Field'] == 'authorized' or
>                     $row['Field'] == 'LOCKED' or
>                     $row['Field'] == 'LOCKEDBY' or
For more information execute 'psecio-parse rules LogicalOperators'

467) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($row['Field'] == 'id' or
>                     $row['Field'] == 'date' or
>                     $row['Field'] == 'pid' or
>                     $row['Field'] == 'user' or
>                     $row['Field'] == 'groupname' or
>                     $row['Field'] == 'authorized' or
>                     $row['Field'] == 'LOCKED' or
For more information execute 'psecio-parse rules LogicalOperators'

468) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($row['Field'] == 'id' or
>                     $row['Field'] == 'date' or
>                     $row['Field'] == 'pid' or
>                     $row['Field'] == 'user' or
>                     $row['Field'] == 'groupname' or
>                     $row['Field'] == 'authorized' or
For more information execute 'psecio-parse rules LogicalOperators'

469) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($row['Field'] == 'id' or
>                     $row['Field'] == 'date' or
>                     $row['Field'] == 'pid' or
>                     $row['Field'] == 'user' or
>                     $row['Field'] == 'groupname' or
For more information execute 'psecio-parse rules LogicalOperators'

470) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($row['Field'] == 'id' or
>                     $row['Field'] == 'date' or
>                     $row['Field'] == 'pid' or
>                     $row['Field'] == 'user' or
For more information execute 'psecio-parse rules LogicalOperators'

471) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($row['Field'] == 'id' or
>                     $row['Field'] == 'date' or
>                     $row['Field'] == 'pid' or
For more information execute 'psecio-parse rules LogicalOperators'

472) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/save.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($row['Field'] == 'id' or
>                     $row['Field'] == 'date' or
For more information execute 'psecio-parse rules LogicalOperators'

473) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/Anything_simple.php on line 540
Avoid the use of an output method (echo, print, etc) directly with a variable
>                             echo ' <a '.$disabled.' title="'.$count.' '.xla('Document').$s.'" class="" >
> 								<span class="borderShadow '.$class.'">'.text($zone['name']).'</span></a>
> 							'.$append;
For more information execute 'psecio-parse rules OutputWithVariable'

474) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/Anything_simple.php on line 544
Avoid the use of an output method (echo, print, etc) directly with a variable
>                             echo ' <a '.$disabled.' title="'.$count.' '.xla('Document').$s.'" class="'.$class.'"
> 								href="Anything_simple.php?display=i&category_id='.$zone['id'].'&encounter='.$encounter.'&category_name='.$category_name.'">
> 								<span  class="borderShadow">'.text($zone['name']).'</span></a>
> 								'.$append;
For more information execute 'psecio-parse rules OutputWithVariable'

475) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/taskman_functions.php on line 292
By default 'extract' overwrites variables in the local scope
>     @extract($encounter_data);
For more information execute 'psecio-parse rules Extract'

476) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 257
By default 'extract' overwrites variables in the local scope
>     @extract($result);
For more information execute 'psecio-parse rules Extract'

477) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 2158
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $div.$header1;
For more information execute 'psecio-parse rules OutputWithVariable'

478) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 2164
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $div.$header1;
For more information execute 'psecio-parse rules OutputWithVariable'

479) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 2217
Avoid the use of an output method (echo, print, etc) directly with a variable
>                     echo $close_table.$div.$open_table;
For more information execute 'psecio-parse rules OutputWithVariable'

480) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 2260
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $close_table.$div.$open_table;
For more information execute 'psecio-parse rules OutputWithVariable'

481) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 2304
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $close_table.$div.$open_table;
For more information execute 'psecio-parse rules OutputWithVariable'

482) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 3699
Avoid the use of an output method (echo, print, etc) directly with a variable
>                             <li id="menu_PRINT_narrative" name="menu_PRINT_report"><a id="BUTTON_PRINT_report" target="_new" href="<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/report/custom_report.php?printable=1&pdf=0&<?php echo $form_folder."_".$form_id."=".$encounter; ?>"><?php echo xlt("Print Report"); ?></a></li>
For more information execute 'psecio-parse rules OutputWithVariable'

483) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 3750
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                     <a onclick="openNewForm('<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/encounter/load_form.php?formname=fee_sheet');top.restoreSession();dopopup('<?php echo $_SERVER['REQUEST_URI']. '&display=fullscreen&encounter='.$encounter; ?>');" href="JavaScript:void(0);" class=""><?php echo xlt('Fullscreen'); ?></a>
For more information execute 'psecio-parse rules OutputWithVariable'

484) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 4958
Avoid the use of an output method (echo, print, etc) directly with a variable
>                 <?php echo $current_VF.$old_VFs;
For more information execute 'psecio-parse rules OutputWithVariable'

485) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 4994
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo $current_OCT.$old_OCTs;
For more information execute 'psecio-parse rules OutputWithVariable'

486) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 5062
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         echo "<td class='GFS_title center'>".xlt('OD{{right eye}}')."</td><td class='GFS_title center'>".xlt('OS{{left eye}}')."</td>".$plus;
For more information execute 'psecio-parse rules OutputWithVariable'

487) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 5550
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($ID, $TXs_arr)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

488) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 5550
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($ID, $TXs_arr)) {
For more information execute 'psecio-parse rules InArrayStrict'

489) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 5557
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $label."</label><br />";
For more information execute 'psecio-parse rules OutputWithVariable'

490) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 5579
By default 'extract' overwrites variables in the local scope
>         @extract($wear);
For more information execute 'psecio-parse rules Extract'

491) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/php/eye_mag_functions.php on line 5746
By default 'extract' overwrites variables in the local scope
>     @extract($encounter_data);
For more information execute 'psecio-parse rules Extract'

492) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/view.php on line 97
By default 'extract' overwrites variables in the local scope
> @extract($encounter_data);
For more information execute 'psecio-parse rules Extract'

493) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/view.php on line 160
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> if ($refresh and $refresh != 'fullscreen') {
For more information execute 'psecio-parse rules LogicalOperators'

494) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/view.php on line 2498
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                                     <input type="checkbox" name="ACT" id="ACT" <?php if ($ACT =='on' or $ACT=='1') {
For more information execute 'psecio-parse rules LogicalOperators'

495) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/view.php on line 3228
Avoid the use of an output method (echo, print, etc) directly with a variable
>                       <a class="closeButton_4" title="<?php echo xla('Once completed, view and store this encounter as a PDF file'); ?>" target="_report" href="<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/report/custom_report.php?printable=1&pdf=1&<?php echo $form_folder."_".$form_id."=".$encounter; ?>&"><i class="fa fa-file-pdf-o"></i></a>
For more information execute 'psecio-parse rules OutputWithVariable'

496) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/view.php on line 3524
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                                                 if (in_array($row['codes'], $arrTESTS)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

497) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/view.php on line 3524
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                                                 if (in_array($row['codes'], $arrTESTS)) {
For more information execute 'psecio-parse rules InArrayStrict'

498) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/view.php on line 3548
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                                                 echo $label."</label>";
For more information execute 'psecio-parse rules OutputWithVariable'

499) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/view.php on line 3680
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                                     echo $label."</label><br />";
For more information execute 'psecio-parse rules OutputWithVariable'

500) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 136
By default 'extract' overwrites variables in the local scope
>     @extract($objQuery);
For more information execute 'psecio-parse rules Extract'

501) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 264
By default 'extract' overwrites variables in the local scope
>     @extract($encounter_data);
For more information execute 'psecio-parse rules Extract'

502) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 986
Avoid the use of an output method (echo, print, etc) directly with a variable
>                       <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('Type').$count_rx; ?></td>
For more information execute 'psecio-parse rules OutputWithVariable'

503) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1453
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             <?php if ($ACT =='on' and $MOTILITYNORMAL == 'on') { ?>
For more information execute 'psecio-parse rules LogicalOperators'

504) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1561
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($ODCOLOR or $OSCOLOR) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

505) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1570
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($ODREDDESAT or $OSREDDESAT) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

506) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1579
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($ODCOINS or $OSCOINS) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

507) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1588
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($ODNPA or $OSNPA) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

508) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1597
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($ODNPC or $OSNPC) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

509) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1605
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($DACCDIST or $DACCNEAR or $CACCDIST or $CACCNEAR or $VERTFUSAMPS) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

510) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1605
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($DACCDIST or $DACCNEAR or $CACCDIST or $CACCNEAR or $VERTFUSAMPS) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

511) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1605
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($DACCDIST or $DACCNEAR or $CACCDIST or $CACCNEAR or $VERTFUSAMPS) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

512) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1605
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($DACCDIST or $DACCNEAR or $CACCDIST or $CACCNEAR or $VERTFUSAMPS) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

513) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1614
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($DACCDIST or $DACCNEAR) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

514) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/report.php on line 1623
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($CACCDIST or $CACCNEAR) { ?>
For more information execute 'psecio-parse rules LogicalOperators'

515) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/js/eye_base.php on line 3258
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                                 echo "//**************PROVIDER NAME+".$providerNAME;
For more information execute 'psecio-parse rules OutputWithVariable'

516) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/SpectacleRx.php on line 85
By default 'extract' overwrites variables in the local scope
> @extract($data);
For more information execute 'psecio-parse rules Extract'

517) /projects/emr/open-emr/src/openemr/interface/forms/eye_mag/SpectacleRx.php on line 183
By default 'extract' overwrites variables in the local scope
>         @extract($wearing);
For more information execute 'psecio-parse rules Extract'

518) /projects/emr/open-emr/src/openemr/interface/forms/newGroupEncounter/save.php on line 141
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die("Unknown mode '" . text($mode) . "'");
For more information execute 'psecio-parse rules ExitOrDie'

519) /projects/emr/open-emr/src/openemr/interface/forms/newGroupEncounter/common.php on line 304
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                     <input type='text'name='form_group' class='form-control col-sm-12' id="form_group"  placeholder='<?php echo xla('Click to select');?>' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr(getGroup($result['external_id'])['group_name']) : ''; ?>' onclick='sel_group()' title='<?php echo xla('Click to select group'); ?>' readonly />
For more information execute 'psecio-parse rules TypeSafeInArray'

520) /projects/emr/open-emr/src/openemr/interface/forms/newGroupEncounter/common.php on line 304
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                     <input type='text'name='form_group' class='form-control col-sm-12' id="form_group"  placeholder='<?php echo xla('Click to select');?>' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr(getGroup($result['external_id'])['group_name']) : ''; ?>' onclick='sel_group()' title='<?php echo xla('Click to select group'); ?>' readonly />
For more information execute 'psecio-parse rules InArrayStrict'

521) /projects/emr/open-emr/src/openemr/interface/forms/newGroupEncounter/common.php on line 305
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                     <input type='hidden' name='form_gid' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr($result['external_id']) : '' ?>' />
For more information execute 'psecio-parse rules TypeSafeInArray'

522) /projects/emr/open-emr/src/openemr/interface/forms/newGroupEncounter/common.php on line 305
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                     <input type='hidden' name='form_gid' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr($result['external_id']) : '' ?>' />
For more information execute 'psecio-parse rules InArrayStrict'

523) /projects/emr/open-emr/src/openemr/interface/forms/fee_sheet/new.php on line 58
Avoid the use of an output method (echo, print, etc) directly with a variable
>     return sprintf('%01.' . ($GLOBALS['currency_decimals'] + $extradecimals) . 'f', $value);
For more information execute 'psecio-parse rules OutputWithVariable'

524) /projects/emr/open-emr/src/openemr/interface/forms/fee_sheet/new.php on line 110
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo "  <td class='billcell'>$strike1" .
>         ($codetype == 'COPAY' ? xlt($codetype) : text($codetype)) . $strike2;
For more information execute 'psecio-parse rules OutputWithVariable'

525) /projects/emr/open-emr/src/openemr/interface/forms/track_anything/create.php on line 351
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> } elseif (!$encounter and $pid) {
For more information execute 'psecio-parse rules LogicalOperators'

526) /projects/emr/open-emr/src/openemr/interface/forms/track_anything/create.php on line 353
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> } elseif (!$encounter and !$pid) {
For more information execute 'psecio-parse rules LogicalOperators'

527) /projects/emr/open-emr/src/openemr/interface/forms/newpatient/save.php on line 135
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die("Unknown mode '" . text($mode) . "'");
For more information execute 'psecio-parse rules ExitOrDie'

528) /projects/emr/open-emr/src/openemr/interface/forms/newpatient/common.php on line 372
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                         <input type='text'name='form_group' class='form-control col-sm-12' id="form_group"  placeholder='<?php echo xla('Click to select');?>' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr(getGroup($result['external_id'])['group_name']) : ''; ?>' onclick='sel_group()' title='<?php echo xla('Click to select group'); ?>' readonly />
For more information execute 'psecio-parse rules TypeSafeInArray'

529) /projects/emr/open-emr/src/openemr/interface/forms/newpatient/common.php on line 372
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                         <input type='text'name='form_group' class='form-control col-sm-12' id="form_group"  placeholder='<?php echo xla('Click to select');?>' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr(getGroup($result['external_id'])['group_name']) : ''; ?>' onclick='sel_group()' title='<?php echo xla('Click to select group'); ?>' readonly />
For more information execute 'psecio-parse rules InArrayStrict'

530) /projects/emr/open-emr/src/openemr/interface/forms/newpatient/common.php on line 373
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                         <input type='hidden' name='form_gid' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr($result['external_id']) : '' ?>' />
For more information execute 'psecio-parse rules TypeSafeInArray'

531) /projects/emr/open-emr/src/openemr/interface/forms/newpatient/common.php on line 373
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                         <input type='hidden' name='form_gid' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr($result['external_id']) : '' ?>' />
For more information execute 'psecio-parse rules InArrayStrict'

532) /projects/emr/open-emr/src/openemr/interface/forms/newpatient/common.php on line 609
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if ($viewmode && in_array($result['pc_catid'], $therapyGroupCategories)) {?>
For more information execute 'psecio-parse rules TypeSafeInArray'

533) /projects/emr/open-emr/src/openemr/interface/forms/newpatient/common.php on line 609
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if ($viewmode && in_array($result['pc_catid'], $therapyGroupCategories)) {?>
For more information execute 'psecio-parse rules InArrayStrict'

534) /projects/emr/open-emr/src/openemr/interface/weno/drugPaidInsert.php on line 39
'header()' calls should not use concatenation directly
> header('Location: ' . $_SERVER['HTTP_REFERER']);
For more information execute 'psecio-parse rules SetHeaderWithInput'

535) /projects/emr/open-emr/src/openemr/interface/weno/import_pharmacies.php on line 98
'header()' calls should not use concatenation directly
> header("Location: ". $ref."?status=finished");
For more information execute 'psecio-parse rules SetHeaderWithInput'

536) /projects/emr/open-emr/src/openemr/interface/fax/fax_view.php on line 30
Use of system functions, especially with user input, is not recommended
>         passthru("groups");
For more information execute 'psecio-parse rules SystemFunctions'

537) /projects/emr/open-emr/src/openemr/interface/fax/fax_view.php on line 32
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt("Cannot open ") . text($jfname));
For more information execute 'psecio-parse rules ExitOrDie'

538) /projects/emr/open-emr/src/openemr/interface/fax/fax_view.php on line 46
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt("Cannot find postscript document reference in ") . text($jfname));
For more information execute 'psecio-parse rules ExitOrDie'

539) /projects/emr/open-emr/src/openemr/interface/fax/fax_view.php on line 55
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die(xlt("Cannot find ") . text($ffname));
For more information execute 'psecio-parse rules ExitOrDie'

540) /projects/emr/open-emr/src/openemr/interface/fax/fax_view.php on line 59
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die(xlt("I do not have permission to read ") . text($ffname));
For more information execute 'psecio-parse rules ExitOrDie'

541) /projects/emr/open-emr/src/openemr/interface/fax/fax_view.php on line 66
Use of system functions, especially with user input, is not recommended
>     passthru("TMPDIR=/tmp ps2pdf '" . escapeshellarg($ffname) . "' -");
For more information execute 'psecio-parse rules SystemFunctions'

542) /projects/emr/open-emr/src/openemr/interface/fax/fax_view.php on line 68
The readfile/readlink/readgzfile functions output content directly (possible injection)
>     readfile($ffname);
For more information execute 'psecio-parse rules Readfile'

543) /projects/emr/open-emr/src/openemr/interface/fax/fax_view.php on line 70
Use of system functions, especially with user input, is not recommended
>     passthru("tiff2pdf '" . escapeshellarg($ffname) . "'");
For more information execute 'psecio-parse rules SystemFunctions'

544) /projects/emr/open-emr/src/openemr/interface/fax/fax_view.php on line 77
'header()' calls should not use concatenation directly
> header("Content-Length: " . ob_get_length());
For more information execute 'psecio-parse rules SetHeaderWithInput'

545) /projects/emr/open-emr/src/openemr/interface/fax/fax_view.php on line 78
'header()' calls should not use concatenation directly
> header("Content-Disposition: inline; filename=" . basename($ffname, $ext) . '.pdf');
For more information execute 'psecio-parse rules SetHeaderWithInput'

546) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 97
Use of system functions, especially with user input, is not recommended
>     $tmp0 = exec("cd " . escapeshellarg($faxcache) . "; tiffcp $inames temp.tif", $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

547) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 124
Use of system functions, especially with user input, is not recommended
>         exec("mkdir -p " . escapeshellarg($docdir));
For more information execute 'psecio-parse rules SystemFunctions'

548) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 158
Use of system functions, especially with user input, is not recommended
>             $tmp0 = exec("tiff2pdf -j -p letter -o " . escapeshellarg($target) . " " . escapeshellarg($faxcache.'/temp.tif'), $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

549) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 246
Use of system functions, especially with user input, is not recommended
>                         $tmp0 = exec('mkdir -p ' . escapeshellarg($imagedir), $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

550) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 248
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                         die("mkdir returned " . text($tmp2) . ": " . text($tmp0));
For more information execute 'psecio-parse rules ExitOrDie'

551) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 251
Use of system functions, especially with user input, is not recommended
>                         exec("touch " . escapeshellarg($imagedir."/index.html"));
For more information execute 'psecio-parse rules SystemFunctions'

552) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 261
Use of system functions, especially with user input, is not recommended
>                 $tmp0 = exec($cmd, $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

553) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 263
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                     die("\"" . text($cmd) . "\" returned " . text($tmp2) . ": " . text($tmp0));
For more information execute 'psecio-parse rules ExitOrDie'

554) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 318
Use of system functions, especially with user input, is not recommended
>         $tmp0 = exec("cd " . escapeshellarg($webserver_root.'/custom') . "; " . escapeshellcmd($GLOBALS['hylafax_enscript']) .
>         " -o " . escapeshellarg($tmpfn2) . " " . escapeshellarg($tmpfn1), $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

555) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 328
Use of system functions, especially with user input, is not recommended
>         $tmp0 = exec(
>             "sendfax -A -n " . escapeshellarg($form_finemode) . " -d " .
>             escapeshellarg($form_fax) . " " . escapeshellarg($tmpfn2) . " " . escapeshellarg($faxcache.'/temp.tif'),
>             $tmp1,
>             $tmp2
>         );
For more information execute 'psecio-parse rules SystemFunctions'

556) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 357
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die("Cannot read " . text($faxcache));
For more information execute 'psecio-parse rules ExitOrDie'

557) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 425
Use of system functions, especially with user input, is not recommended
>     $tmp0 = exec('mkdir -p ' . escapeshellarg($faxcache), $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

558) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 427
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die("mkdir returned " . text($tmp2) . ": " . text($tmp0));
For more information execute 'psecio-parse rules ExitOrDie'

559) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 434
Use of system functions, especially with user input, is not recommended
>         $tmp0 = exec("convert -density 203x196 " . escapeshellarg($filepath) . " " . escapeshellarg($faxcache.'/deleteme.tif'), $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

560) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 436
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("convert returned " . text($tmp2) . ": " . text($tmp0));
For more information execute 'psecio-parse rules ExitOrDie'

561) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 439
Use of system functions, especially with user input, is not recommended
>         $tmp0 = exec("cd " . escapeshellarg($faxcache) . "; tiffsplit 'deleteme.tif'; rm -f 'deleteme.tif'", $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

562) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 441
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("tiffsplit/rm returned " . text($tmp2) . ": " . text($tmp0));
For more information execute 'psecio-parse rules ExitOrDie'

563) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 444
Use of system functions, especially with user input, is not recommended
>         $tmp0 = exec("cd " . escapeshellarg($faxcache) . "; tiffsplit " . escapeshellarg($filepath), $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

564) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 446
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("tiffsplit returned " . text($tmp2) . ": " . text($tmp0));
For more information execute 'psecio-parse rules ExitOrDie'

565) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 450
Use of system functions, especially with user input, is not recommended
>     $tmp0 = exec("cd " . escapeshellarg($faxcache) . "; mogrify -resize 750x970 -format jpg *.tif", $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

566) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 452
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die("mogrify returned " . text($tmp2) . ": " . text($tmp0) . "; ext is '" . text($ext) . "'; filepath is '" . text($filepath) . "'");
For more information execute 'psecio-parse rules ExitOrDie'

567) /projects/emr/open-emr/src/openemr/interface/fax/fax_dispatch.php on line 821
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die("Cannot read " . text($faxcache));
For more information execute 'psecio-parse rules ExitOrDie'

568) /projects/emr/open-emr/src/openemr/interface/fax/faxq.php on line 32
Use of system functions, especially with user input, is not recommended
>     exec("faxstat -r -l -h " . escapeshellarg($GLOBALS['hylafax_server']), $statlines);
For more information execute 'psecio-parse rules SystemFunctions'

569) /projects/emr/open-emr/src/openemr/interface/fax/faxq.php on line 52
Use of system functions, especially with user input, is not recommended
>     exec("faxstat -s -d -l -h " . escapeshellarg($GLOBALS['hylafax_server']), $donelines);
For more information execute 'psecio-parse rules SystemFunctions'

570) /projects/emr/open-emr/src/openemr/interface/fax/faxq.php on line 68
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die("Cannot read " . text($scandir));
For more information execute 'psecio-parse rules ExitOrDie'

571) /projects/emr/open-emr/src/openemr/interface/reports/immunization_report.php on line 258
'header()' calls should not use concatenation directly
>     header('Content-Disposition: attachment; filename=' . $filename);
For more information execute 'psecio-parse rules SetHeaderWithInput'

572) /projects/emr/open-emr/src/openemr/interface/reports/immunization_report.php on line 352
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array($codeid, $form_code)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

573) /projects/emr/open-emr/src/openemr/interface/reports/immunization_report.php on line 352
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array($codeid, $form_code)) {
For more information execute 'psecio-parse rules InArrayStrict'

574) /projects/emr/open-emr/src/openemr/interface/reports/edi_270.php on line 157
'header()' calls should not use concatenation directly
>             header("Content-Length: " . strlen($log));
For more information execute 'psecio-parse rules SetHeaderWithInput'

575) /projects/emr/open-emr/src/openemr/interface/reports/edi_270.php on line 158
'header()' calls should not use concatenation directly
>             header('Content-Disposition: attachment; filename="' . $fn . '"');
For more information execute 'psecio-parse rules SetHeaderWithInput'

576) /projects/emr/open-emr/src/openemr/interface/reports/non_reported.php on line 273
'header()' calls should not use concatenation directly
>     header('Content-Disposition: attachment; filename=' . $filename);
For more information execute 'psecio-parse rules SetHeaderWithInput'

577) /projects/emr/open-emr/src/openemr/interface/reports/non_reported.php on line 373
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($codeid, $form_code)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

578) /projects/emr/open-emr/src/openemr/interface/reports/non_reported.php on line 373
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($codeid, $form_code)) {
For more information execute 'psecio-parse rules InArrayStrict'

579) /projects/emr/open-emr/src/openemr/interface/reports/edi_271.php on line 62
'header()' calls should not use concatenation directly
>     header("Content-Length: " . strlen($batch_log));
For more information execute 'psecio-parse rules SetHeaderWithInput'

580) /projects/emr/open-emr/src/openemr/interface/reports/edi_271.php on line 63
'header()' calls should not use concatenation directly
>     header('Content-Disposition: attachment; filename="' . $fn . '"');
For more information execute 'psecio-parse rules SetHeaderWithInput'

581) /projects/emr/open-emr/src/openemr/interface/reports/ippf_statistics.php on line 1194
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (is_array($form_show) && in_array($key, $form_show)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

582) /projects/emr/open-emr/src/openemr/interface/reports/ippf_statistics.php on line 1194
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (is_array($form_show) && in_array($key, $form_show)) {
For more information execute 'psecio-parse rules InArrayStrict'

583) /projects/emr/open-emr/src/openemr/interface/reports/pat_ledger.php on line 366
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=svc_financial_report_".attr($form_from_date)."--".attr($form_to_date).".csv");
For more information execute 'psecio-parse rules SetHeaderWithInput'

584) /projects/emr/open-emr/src/openemr/interface/reports/svc_code_financial_report.php on line 57
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=svc_financial_report_".attr($form_from_date)."--".attr($form_to_date).".csv");
For more information execute 'psecio-parse rules SetHeaderWithInput'

585) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 327
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                                                     if ($_POST['form_pt_name'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

586) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 336
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                                                     if ($_POST['form_pt_age'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

587) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 345
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                                                     if ($_POST['form_diagnosis_allergy'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

588) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 354
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                                                     if ($_POST['form_diagnosis_medprb'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

589) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 363
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                                                     if ($_POST['form_drug'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

590) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 372
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                                                     if ($_POST['ndc_no'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

591) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 381
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                                                     if ($_POST['lab_results'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

592) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 390
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                                                     if ($_POST['communication_check'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

593) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 442
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_diagnosis) > 0 || $_POST['form_diagnosis_allergy'] == true || $_POST['form_diagnosis_medprb'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

594) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 442
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_diagnosis) > 0 || $_POST['form_diagnosis_allergy'] == true || $_POST['form_diagnosis_medprb'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

595) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 448
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_drug_name) > 0 || $_POST['form_drug'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

596) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 453
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_lab_results) > 0 || $_POST['lab_results'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

597) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 499
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_diagnosis) > 0 || ($_POST['form_diagnosis_allergy'] == true && $_POST['form_diagnosis_medprb'] == true)) {
For more information execute 'psecio-parse rules BooleanIdentity'

598) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 499
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_diagnosis) > 0 || ($_POST['form_diagnosis_allergy'] == true && $_POST['form_diagnosis_medprb'] == true)) {
For more information execute 'psecio-parse rules BooleanIdentity'

599) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 501
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     } elseif ($_POST['form_diagnosis_allergy'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

600) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 503
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     } elseif ($_POST['form_diagnosis_medprb'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

601) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 507
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($type == 'Procedure' ||( strlen($form_lab_results)!=0) || $_POST['lab_results'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

602) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 514
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_lab_results)!=0 || $_POST['lab_results'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

603) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 524
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_drug_name)!=0 || $_POST['form_drug'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

604) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 544
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_diagnosis) > 0 || $_POST['form_diagnosis_allergy'] == true || $_POST['form_diagnosis_medprb'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

605) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 544
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_diagnosis) > 0 || $_POST['form_diagnosis_allergy'] == true || $_POST['form_diagnosis_medprb'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

606) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 549
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_lab_results)!=0 || $_POST['lab_results'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

607) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 554
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_drug_name)!=0 || $_POST['form_drug'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

608) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 574
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_lab_results) != 0 || $_POST['lab_results'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

609) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 583
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($form_drug_name) > 0 || $_POST['form_drug'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

610) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 644
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($communication) > 0 || $_POST['communication_check'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

611) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 653
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         } else if ($communication == "" && $_POST['communication_check'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

612) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 668
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($_POST['form_pt_name'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

613) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 672
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($_POST['form_pt_age'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

614) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 678
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     } elseif (($_POST['form_diagnosis_allergy'] == true) || ($_POST['form_diagnosis_medprb'] == true)) {
For more information execute 'psecio-parse rules BooleanIdentity'

615) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 678
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     } elseif (($_POST['form_diagnosis_allergy'] == true) || ($_POST['form_diagnosis_medprb'] == true)) {
For more information execute 'psecio-parse rules BooleanIdentity'

616) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 682
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (($_POST['form_drug'] == true) || (strlen($form_drug_name) > 0)) {
For more information execute 'psecio-parse rules BooleanIdentity'

617) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 686
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (($_POST['ndc_no'] == true) && (strlen($form_drug_name) > 0)) {
For more information execute 'psecio-parse rules BooleanIdentity'

618) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 690
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (($_POST['lab_results'] == true) || (strlen($form_lab_results) > 0)) {
For more information execute 'psecio-parse rules BooleanIdentity'

619) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 694
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (strlen($communication) > 0 || $_POST['communication_check'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

620) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 746
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 if (strlen($communication) == 0 || $_POST['communication_check'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

621) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 749
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 <?php if (strlen($communication) > 0 || ($_POST['communication_check'] == true)) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

622) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 761
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                                 if (strlen($communication) == 0 || ($_POST['communication_check'] == true)) {
For more information execute 'psecio-parse rules BooleanIdentity'

623) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 765
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                                 <?php if (strlen($communication) > 0 || $_POST['communication_check'] == true) { ?>
For more information execute 'psecio-parse rules BooleanIdentity'

624) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 771
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 if (strlen($form_diagnosis) > 0 || $_POST['form_diagnosis_allergy'] == true || $_POST['form_diagnosis_medprb'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

625) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 771
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 if (strlen($form_diagnosis) > 0 || $_POST['form_diagnosis_allergy'] == true || $_POST['form_diagnosis_medprb'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

626) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 793
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if (strlen($form_drug_name) > 0 || $_POST['form_drug'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

627) /projects/emr/open-emr/src/openemr/interface/reports/clinical_reports.php on line 836
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 if (strlen($form_lab_results) > 0 || $_POST['lab_results'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

628) /projects/emr/open-emr/src/openemr/interface/reports/cdr_log.php on line 213
Avoid the use of an output method (echo, print, etc) directly with a variable
>                  echo $alert . "<br>";
For more information execute 'psecio-parse rules OutputWithVariable'

629) /projects/emr/open-emr/src/openemr/interface/reports/cdr_log.php on line 234
Avoid the use of an output method (echo, print, etc) directly with a variable
>                     echo $alert . "<br>";
For more information execute 'psecio-parse rules OutputWithVariable'

630) /projects/emr/open-emr/src/openemr/interface/forms_admin/forms_admin.php on line 40
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     registerForm($_GET['name']) or $err=xl('error while registering form!');
For more information execute 'psecio-parse rules LogicalOperators'

631) /projects/emr/open-emr/src/openemr/interface/forms_admin/forms_admin.php on line 43
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> $bigdata = getRegistered("%") or $bigdata = false;
For more information execute 'psecio-parse rules LogicalOperators'

632) /projects/emr/open-emr/src/openemr/interface/new/new_comprehensive_save.php on line 53
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die("Internal error: setpid(" .text($newpid) . ") failed!");
For more information execute 'psecio-parse rules ExitOrDie'

633) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 199
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

634) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 205
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

635) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 209
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

636) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 213
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

637) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 213
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

638) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 220
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

639) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 226
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

640) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 230
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

641) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 234
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

642) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 234
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

643) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 241
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

644) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 247
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

645) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 251
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

646) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 255
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

647) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 255
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

648) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 262
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

649) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 268
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

650) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 272
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

651) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 276
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

652) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 276
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

653) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 283
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

654) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 289
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

655) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 293
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

656) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 297
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

657) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 297
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

658) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 304
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

659) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 310
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

660) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 314
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

661) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 318
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

662) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 318
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

663) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 325
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

664) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 331
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

665) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 335
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

666) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 339
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

667) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 339
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

668) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 346
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

669) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 352
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

670) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 356
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

671) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 360
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

672) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 360
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

673) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 367
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

674) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 373
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

675) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 377
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

676) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 381
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

677) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 381
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

678) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 388
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

679) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 394
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

680) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 398
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

681) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 402
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

682) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 402
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

683) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 409
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

684) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 415
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

685) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 419
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

686) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 423
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

687) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 423
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

688) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 430
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

689) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 436
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

690) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 440
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

691) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 444
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

692) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 444
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

693) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 451
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

694) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 457
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

695) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 461
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

696) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 465
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

697) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 465
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

698) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 472
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

699) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 478
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

700) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 482
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

701) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 486
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

702) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 486
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

703) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 493
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

704) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 499
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

705) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 503
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

706) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 507
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

707) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 507
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

708) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 514
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

709) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 520
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

710) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 524
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

711) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 528
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

712) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 528
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

713) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 535
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

714) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 541
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

715) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 545
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

716) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 549
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

717) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 549
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

718) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 556
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

719) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 562
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

720) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 566
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

721) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 570
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

722) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 570
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

723) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 577
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

724) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 583
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

725) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 587
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

726) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 591
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

727) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 591
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

728) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 598
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

729) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 604
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

730) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 608
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

731) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 612
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

732) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 612
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

733) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 623
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

734) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 629
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

735) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 633
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

736) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 637
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

737) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 637
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

738) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 644
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

739) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 650
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

740) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 654
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

741) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 658
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

742) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 658
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

743) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 665
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

744) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 671
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

745) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 675
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

746) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 679
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

747) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 679
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

748) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 686
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

749) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 692
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

750) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 696
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

751) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 700
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

752) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 700
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

753) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 707
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

754) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 713
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

755) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 717
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

756) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 721
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

757) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 721
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

758) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 728
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

759) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 734
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

760) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 738
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

761) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 742
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

762) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 742
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

763) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 749
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

764) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 755
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

765) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 759
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

766) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 763
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

767) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 763
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

768) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 770
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

769) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 776
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

770) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 780
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

771) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 784
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

772) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 784
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

773) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 791
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

774) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 797
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

775) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 801
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

776) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 805
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

777) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 805
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

778) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 812
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

779) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 818
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

780) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 822
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

781) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 826
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

782) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 826
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

783) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 833
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

784) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 839
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

785) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 843
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

786) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 847
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

787) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 847
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

788) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 854
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

789) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 860
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

790) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 864
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

791) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 868
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

792) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 868
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

793) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 875
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

794) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 881
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

795) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 885
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

796) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 889
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

797) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 889
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

798) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 896
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

799) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 902
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

800) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 906
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

801) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 910
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

802) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 910
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

803) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 917
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

804) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 923
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

805) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 927
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

806) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 931
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

807) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 931
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

808) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 938
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

809) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 944
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

810) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 948
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

811) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 952
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

812) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 952
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

813) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 959
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

814) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 965
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

815) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 969
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

816) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 973
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

817) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 973
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

818) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 980
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

819) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 986
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

820) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 990
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

821) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 994
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

822) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 994
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

823) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1001
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

824) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1007
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

825) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1011
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

826) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1015
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

827) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1015
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

828) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1022
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

829) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1028
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

830) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1032
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

831) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1036
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

832) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1036
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

833) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1049
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if ($old_pid != $iter{'pid'} and ($iter{'code_type'} != 'payment_info')) {
For more information execute 'psecio-parse rules LogicalOperators'

834) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1080
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

835) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1084
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

836) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1088
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

837) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1092
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

838) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1092
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

839) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1096
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

840) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1100
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

841) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1100
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

842) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1111
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

843) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1116
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_code'}) != 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

844) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1121
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'code_type'}) != 'Patient Payment' and ($iter{'code_type'}) != 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

845) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1126
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

846) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1131
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) != 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

847) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1136
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'code_type'}) != 'Insurance Payment' and ($iter{'code_type'}) != 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

848) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1136
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'code_type'}) != 'Insurance Payment' and ($iter{'code_type'}) != 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

849) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1172
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($old_pid != $new_old_pid and ($iter{'code_type'} != 'payment_info')) {
For more information execute 'psecio-parse rules LogicalOperators'

850) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1696
Avoid the use of an output method (echo, print, etc) directly with a variable
>         Printf("</span></td><td width=250><span class=text><center>".text($user_info[user][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

851) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1698
Avoid the use of an output method (echo, print, etc) directly with a variable
>         printf("</span></td><td width=250><span class=text><b>" . xlt("Total Charges") .': '." %1\$.2f ", text($user_info[fee][$i])). "</b>";
For more information execute 'psecio-parse rules OutputWithVariable'

852) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1700
Avoid the use of an output method (echo, print, etc) directly with a variable
>         printf("</span></td><td width=250><span class=text><b>"  . xlt("Total Payments").': '. "(%1\$.2f)", text($user_info[inspay][$i] + $user_info[patpay][$i])) . "</b>";
For more information execute 'psecio-parse rules OutputWithVariable'

853) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1707
Avoid the use of an output method (echo, print, etc) directly with a variable
>         printf("</span></td><td width=250><span class=text><b>" . xlt("Total Adj").'.: '."(%1\$.2f)", text($user_info[patadj][$i] + $user_info[insadj][$i])). "</b>";
For more information execute 'psecio-parse rules OutputWithVariable'

854) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1709
Avoid the use of an output method (echo, print, etc) directly with a variable
>         printf("</span></td><td width=250><span class=text><b>" . xlt("Refund").': '."(%1\$.2f)", text($user_info[patref][$i] + $user_info[insref][$i]))."</b>";
For more information execute 'psecio-parse rules OutputWithVariable'

855) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1727
Avoid the use of an output method (echo, print, etc) directly with a variable
>         printf("</span></td><td width=250><span class=text><b>" . xlt("Actual Receipts").': '."(%1\$.2f)", text($user_info[patref][$i] + $user_info[insref][$i] + $user_info[inspay][$i] + $user_info[patpay][$i])). "</b>";
For more information execute 'psecio-parse rules OutputWithVariable'

856) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1767
Avoid the use of an output method (echo, print, etc) directly with a variable
>         Printf("</span></td><td width=250><span class=text><center>".text($provider_info[user][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

857) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1769
Avoid the use of an output method (echo, print, etc) directly with a variable
>         printf("</span></td><td width=250><span class=text><b>" . xlt("Total Charges").': '." %1\$.2f ", text($provider_info[fee][$i])). "</b>";
For more information execute 'psecio-parse rules OutputWithVariable'

858) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1771
Avoid the use of an output method (echo, print, etc) directly with a variable
>         printf("</span></td><td width=250><span class=text><b>"  . xlt("Total Payments").': '. "(%1\$.2f)", text($provider_info[inspay][$i] + $provider_info[patpay][$i])) . "</b>";
For more information execute 'psecio-parse rules OutputWithVariable'

859) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1778
Avoid the use of an output method (echo, print, etc) directly with a variable
>         printf("</span></td><td width=250><span class=text><b>" . xlt("Total Adj").'.: '."(%1\$.2f)", text($provider_info[patadj][$i] + $provider_info[insadj][$i])). "</b>";
For more information execute 'psecio-parse rules OutputWithVariable'

860) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1780
Avoid the use of an output method (echo, print, etc) directly with a variable
>         printf("</span></td><td width=250><span class=text><b>" . xlt("Refund").': '."(%1\$.2f)", text($provider_info[patref][$i] + $provider_info[insref][$i]))."</b>";
For more information execute 'psecio-parse rules OutputWithVariable'

861) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num1.php on line 1798
Avoid the use of an output method (echo, print, etc) directly with a variable
>         printf("</span></td><td width=250><span class=text><b>" . xlt("Actual Receipts").': '."(%1\$.2f)", text($provider_info[patref][$i] + $provider_info[insref][$i] + $provider_info[inspay][$i] + $provider_info[patpay][$i])). "</b>";
For more information execute 'psecio-parse rules OutputWithVariable'

862) /projects/emr/open-emr/src/openemr/interface/billing/get_claim_file.php on line 43
'header()' calls should not use concatenation directly
>     header("Content-Length: " . filesize($fname));
For more information execute 'psecio-parse rules SetHeaderWithInput'

863) /projects/emr/open-emr/src/openemr/interface/billing/get_claim_file.php on line 44
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=" . basename($fname));
For more information execute 'psecio-parse rules SetHeaderWithInput'

864) /projects/emr/open-emr/src/openemr/interface/billing/era_payments.php on line 67
Use of system functions, especially with user input, is not recommended
>         exec("unzip -p " . escapeshellarg($tmp_name.".zip") . " > " . escapeshellarg($tmp_name));
For more information execute 'psecio-parse rules SystemFunctions'

865) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 227
'header()' calls should not use concatenation directly
>     header("Content-Length: " . filesize($file_to_send));
For more information execute 'psecio-parse rules SetHeaderWithInput'

866) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 228
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=" . basename($file_to_send));
For more information execute 'psecio-parse rules SetHeaderWithInput'

867) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 230
The readfile/readlink/readgzfile functions output content directly (possible injection)
>     readfile($file_to_send);
For more information execute 'psecio-parse rules Readfile'

868) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 281
The readfile/readlink/readgzfile functions output content directly (possible injection)
>         readfile($file_to_send, "r");//this file contains the HTML to be converted to pdf.
For more information execute 'psecio-parse rules Readfile'

869) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 314
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if (stristr($OneLine, "\014") == true && !feof($file)) {//form feed means we should start a new page.
For more information execute 'psecio-parse rules BooleanIdentity'

870) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 320
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if (stristr($OneLine, 'REMIT TO') == true || stristr($OneLine, 'Visit Date') == true || stristr($OneLine, 'Future Appointments') == true || stristr($OneLine, 'Current') == true) { //lines are made bold when 'REMIT TO' or 'Visit Date' is there.
For more information execute 'psecio-parse rules BooleanIdentity'

871) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 320
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if (stristr($OneLine, 'REMIT TO') == true || stristr($OneLine, 'Visit Date') == true || stristr($OneLine, 'Future Appointments') == true || stristr($OneLine, 'Current') == true) { //lines are made bold when 'REMIT TO' or 'Visit Date' is there.
For more information execute 'psecio-parse rules BooleanIdentity'

872) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 320
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if (stristr($OneLine, 'REMIT TO') == true || stristr($OneLine, 'Visit Date') == true || stristr($OneLine, 'Future Appointments') == true || stristr($OneLine, 'Current') == true) { //lines are made bold when 'REMIT TO' or 'Visit Date' is there.
For more information execute 'psecio-parse rules BooleanIdentity'

873) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 320
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if (stristr($OneLine, 'REMIT TO') == true || stristr($OneLine, 'Visit Date') == true || stristr($OneLine, 'Future Appointments') == true || stristr($OneLine, 'Current') == true) { //lines are made bold when 'REMIT TO' or 'Visit Date' is there.
For more information execute 'psecio-parse rules BooleanIdentity'

874) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 340
'header()' calls should not use concatenation directly
>     header("Content-Length: " . filesize($STMT_TEMP_FILE_PDF));
For more information execute 'psecio-parse rules SetHeaderWithInput'

875) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 341
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=" . basename($STMT_TEMP_FILE_PDF));
For more information execute 'psecio-parse rules SetHeaderWithInput'

876) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 343
The readfile/readlink/readgzfile functions output content directly (possible injection)
>     readfile($STMT_TEMP_FILE_PDF);
For more information execute 'psecio-parse rules Readfile'

877) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 577
Use of system functions, especially with user input, is not recommended
>             exec(escapeshellcmd($STMT_PRINT_CMD) . " " . escapeshellarg($STMT_TEMP_FILE));
For more information execute 'psecio-parse rules SystemFunctions'

878) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_search.php on line 914
Use of system functions, especially with user input, is not recommended
>                                     exec("unzip -p " . escapeshellarg($tmp_name . ".zip") . " > " . escapeshellarg($tmp_name));
For more information execute 'psecio-parse rules SystemFunctions'

879) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_process.php on line 180
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($WarningFlag==true) {
For more information execute 'psecio-parse rules BooleanIdentity'

880) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_process.php on line 656
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt("Cannot create") . " '" . text($fnreport) . "'");
For more information execute 'psecio-parse rules ExitOrDie'

881) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 358
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if ($old_pid != $iter{'pid'} and ($iter{'code_type'} != 'payment_info')) {
For more information execute 'psecio-parse rules LogicalOperators'

882) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 409
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

883) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 413
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_code'}) != 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

884) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 417
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'code_type'}) != "Patient Payment" and ($iter{'code_type'}) != 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

885) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 421
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

886) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 425
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) != 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

887) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 431
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

888) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 435
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

889) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 439
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

890) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 443
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

891) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 443
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

892) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 447
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

893) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 451
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

894) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 451
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

895) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 459
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'code_type'}) != 'Insurance Payment' and ($iter{'code_type'}) != 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

896) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 459
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'code_type'}) != 'Insurance Payment' and ($iter{'code_type'}) != 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

897) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 739
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=70><span class=text><b>". xlt("User "). "</center></b><center>".text($user_info[user][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

898) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 740
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=140><span class=text><b><center>". xlt("Charges") . ' ' ."</center></b><center>"." %1\$.2f", text($user_info[fee][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

899) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 741
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=140><span class=text><b><center>". xlt("Insurance Adj").'. '."</center></b><center>"."%1\$.2f", text($user_info[insadj][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

900) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 742
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=140><span class=text><b><center>". xlt("Insurance Payments") . ' ' . "</center></b><center>"."%1\$.2f", text($user_info[inspay][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

901) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 743
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=140><span class=text><b><center>". xlt("Patient Adj").'. '."</center></b><center>"."%1\$.2f", text($user_info[patadj][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

902) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 744
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=140><span class=text><b><center>". xlt("Patient Payments"). ' ' ."</center></b><center>"."%1\$.2f", text($user_info[patpay][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

903) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 760
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=70><span class=text><b><center>". xlt("Grand Totals").' ');
For more information execute 'psecio-parse rules OutputWithVariable'

904) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 761
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=140><span class=text><b><center>". xlt("Total Charges").' '."</center></b><center>"." %1\$.2f", text($gtotal_fee)). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

905) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 762
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=140><span class=text><b><center>". xlt("Insurance Adj").'. '."</center></b><center>"."%1\$.2f", text($gtotal_insadj)). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

906) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 763
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=140><span class=text><b><center>". xlt("Insurance Payments") . ' ' ."</center></b><center>"."%1\$.2f", text($gtotal_inspay)). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

907) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 764
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=140><span class=text><b><center>". xlt("Patient Adj").'. '."</center></b><center>"."%1\$.2f", text($gtotal_patadj)). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

908) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num2.php on line 765
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=140><span class=text><b><center>". xlt("Patient Payments"). ' ' . "</center></b><center>"."%1\$.2f", text($gtotal_patpay)). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

909) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 360
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if ($old_pid != $iter{'pid'} and ($iter{'code_type'} != 'payment_info')) {
For more information execute 'psecio-parse rules LogicalOperators'

910) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 370
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         Printf("<br></span></td><td width=100><span class=text><center>"." %1\$.2f", text($line_total)). "</center></td>";
For more information execute 'psecio-parse rules OutputWithVariable'

911) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 373
Avoid the use of an output method (echo, print, etc) directly with a variable
>                         Printf("<br></span></td><td width=100><span class=text><center>"." %1\$.2f", text($line_total_pay)). "</center></td>";
For more information execute 'psecio-parse rules OutputWithVariable'

912) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 426
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

913) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 431
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_code'}) != 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

914) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 436
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'code_type'}) != 'Patient Payment' and ($iter{'code_type'}) != 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

915) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 441
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

916) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 446
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) != 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

917) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 453
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

918) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 457
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_adjust_dollar'}) != 0 and ($iter{'code_type'}) === 'Patient Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

919) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 461
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_code'}) > 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

920) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 465
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

921) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 465
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) > 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

922) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 469
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'ins_code'}) < 0 and ($iter{'code_type'}) === 'Insurance Payment') {
For more information execute 'psecio-parse rules LogicalOperators'

923) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 473
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

924) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 473
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'pat_code'}) < 0 and ($iter{'code_type'}) === 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

925) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 481
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'code_type'}) != 'Insurance Payment' and ($iter{'code_type'}) != 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

926) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 481
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if (($iter{'code_type'}) != 'Insurance Payment' and ($iter{'code_type'}) != 'Patient Payment' and $iter{'paytype'} != 'PCP') {
For more information execute 'psecio-parse rules LogicalOperators'

927) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 534
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($old_pid != $new_old_pid and ($iter{'code_type'} != 'payment_info')) {
For more information execute 'psecio-parse rules LogicalOperators'

928) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 756
Avoid the use of an output method (echo, print, etc) directly with a variable
>         Printf("<br></span></td><td width=100><span class=text><center>"." %1\$.2f", text($line_total)). "</center></span></td>\n<br>";
For more information execute 'psecio-parse rules OutputWithVariable'

929) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 760
Avoid the use of an output method (echo, print, etc) directly with a variable
>         Printf("<br></span></td><td width=100><span class=text><center>"." %1\$.2f", text($line_total_pay)). "</center></td>\n<br>";
For more information execute 'psecio-parse rules OutputWithVariable'

930) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 777
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=70><span class=text><b><center>". xlt("User") . ' ' . "</center></b><center>".text($user_info[user][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

931) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 778
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=140><span class=text><b><center>". xlt("Charges") . ' ' . "</center></b><center>"." %1\$.2f", text($user_info[fee][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

932) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 779
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=140><span class=text><b><center>". xlt("Insurance Adj").'. '."</center></b><center>"."%1\$.2f", text($user_info[insadj][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

933) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 780
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=140><span class=text><b><center>". xlt("Insurance Payments") . ' ' . "</center></b><center>"."%1\$.2f", text($user_info[inspay][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

934) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 781
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=140><span class=text><b><center>". xlt("Patient Adj").'. '."</center></b><center>"."%1\$.2f", text($user_info[patadj][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

935) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 782
Avoid the use of an output method (echo, print, etc) directly with a variable
>     Printf("<td width=140><span class=text><b><center>". xlt("Patient Payments") . ' ' . "</center></b><center>"."%1\$.2f", text($user_info[patpay][$i])). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

936) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 798
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=70><span class=text><b><center>". xlt("Grand Totals") . ' ');
For more information execute 'psecio-parse rules OutputWithVariable'

937) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 799
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=140><span class=text><b><center>". xlt("Total Charges") . ' ' . "</center></b><center>"." %1\$.2f", text($gtotal_fee)). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

938) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 800
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=140><span class=text><b><center>". xlt("Insurance Adj").'. '."</center></b><center>"."%1\$.2f", text($gtotal_insadj)). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

939) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 801
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=140><span class=text><b><center>". xlt("Insurance Payments") . ' ' . "</center></b><center>"."%1\$.2f", text($gtotal_inspay)). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

940) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 802
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=140><span class=text><b><center>". xlt("Patient Adj").'.'."</center></b><center>"."%1\$.2f", text($gtotal_patadj)). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

941) /projects/emr/open-emr/src/openemr/interface/billing/print_daysheet_report_num3.php on line 803
Avoid the use of an output method (echo, print, etc) directly with a variable
> Printf("<td width=140><span class=text><b><center>". xlt("Patient Payments") . ' ' . "</center></b><center>"."%1\$.2f", text($gtotal_patpay)). "</center>";
For more information execute 'psecio-parse rules OutputWithVariable'

942) /projects/emr/open-emr/src/openemr/interface/billing/billing_process.php on line 85
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("Error:<br>\nInput must begin with 'ISA'; " . "found '" . text($elems[0]) . "' instead");
For more information execute 'psecio-parse rules ExitOrDie'

943) /projects/emr/open-emr/src/openemr/interface/billing/billing_process.php on line 152
'header()' calls should not use concatenation directly
>     header("Content-Length: " . strlen($bat_content));
For more information execute 'psecio-parse rules SetHeaderWithInput'

944) /projects/emr/open-emr/src/openemr/interface/billing/billing_process.php on line 232
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die(xlt("Claim ") . text($claimid) . xlt(" update failed, not in database?"));
For more information execute 'psecio-parse rules ExitOrDie'

945) /projects/emr/open-emr/src/openemr/interface/billing/billing_process.php on line 385
'header()' calls should not use concatenation directly
>         header("Content-Length: " . strlen($bat_content));
For more information execute 'psecio-parse rules SetHeaderWithInput'

946) /projects/emr/open-emr/src/openemr/interface/billing/sl_eob_invoice.php on line 286
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die("There is no encounter with form_encounter.id = '" . text($trans_id) . "'.");
For more information execute 'psecio-parse rules ExitOrDie'

947) /projects/emr/open-emr/src/openemr/interface/billing/edih_main.php on line 89
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (csv_setup() == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

948) /projects/emr/open-emr/src/openemr/interface/billing/edih_main.php on line 334
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die("No content in response <br />" . PHP_EOL);
For more information execute 'psecio-parse rules ExitOrDie'

949) /projects/emr/open-emr/src/openemr/interface/billing/new_payment.php on line 100
'header()' calls should not use concatenation directly
>         header("Location: edit_payment.php?payment_id=" . urlencode($payment_id) . "&ParentPage=new_payment");
For more information execute 'psecio-parse rules SetHeaderWithInput'

950) /projects/emr/open-emr/src/openemr/interface/main/backuplog.php on line 33
Use of system functions, especially with user input, is not recommended
> system($cmd);
For more information execute 'psecio-parse rules SystemFunctions'

951) /projects/emr/open-emr/src/openemr/interface/main/ippf_export.php on line 662
'header()' calls should not use concatenation directly
>     header("Content-Length: " . strlen($out));
For more information execute 'psecio-parse rules SetHeaderWithInput'

952) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 55
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> if (isset($_GET['mID']) and is_numeric($_GET['mID'])) {
For more information execute 'psecio-parse rules LogicalOperators'

953) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 89
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>     isset($_POST['dueDate']) and preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $_POST['dueDate']) and
> // ------- check priority, only allow 1-3
>     isset($_POST['priority']) and intval($_POST['priority']) <= 3 and
> // ------- check message, only up to 160 characters limited by Db
>     isset($_POST['message']) and mb_strlen($_POST['message']) <= $max_reminder_words and mb_strlen($_POST['message']) > 0 and
> // ------- check if PatientID is set and in numeric
>     isset($_POST['PatientID']) and is_numeric($_POST['PatientID'])) {
For more information execute 'psecio-parse rules LogicalOperators'

954) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 89
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>     isset($_POST['dueDate']) and preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $_POST['dueDate']) and
> // ------- check priority, only allow 1-3
>     isset($_POST['priority']) and intval($_POST['priority']) <= 3 and
> // ------- check message, only up to 160 characters limited by Db
>     isset($_POST['message']) and mb_strlen($_POST['message']) <= $max_reminder_words and mb_strlen($_POST['message']) > 0 and
> // ------- check if PatientID is set and in numeric
>     isset($_POST['PatientID']) and is_numeric($_POST['PatientID'])) {
For more information execute 'psecio-parse rules LogicalOperators'

955) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 89
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>     isset($_POST['dueDate']) and preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $_POST['dueDate']) and
> // ------- check priority, only allow 1-3
>     isset($_POST['priority']) and intval($_POST['priority']) <= 3 and
> // ------- check message, only up to 160 characters limited by Db
>     isset($_POST['message']) and mb_strlen($_POST['message']) <= $max_reminder_words and mb_strlen($_POST['message']) > 0 and
For more information execute 'psecio-parse rules LogicalOperators'

956) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 89
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>     isset($_POST['dueDate']) and preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $_POST['dueDate']) and
> // ------- check priority, only allow 1-3
>     isset($_POST['priority']) and intval($_POST['priority']) <= 3 and
> // ------- check message, only up to 160 characters limited by Db
>     isset($_POST['message']) and mb_strlen($_POST['message']) <= $max_reminder_words and mb_strlen($_POST['message']) > 0 and
For more information execute 'psecio-parse rules LogicalOperators'

957) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 89
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>     isset($_POST['dueDate']) and preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $_POST['dueDate']) and
> // ------- check priority, only allow 1-3
>     isset($_POST['priority']) and intval($_POST['priority']) <= 3 and
> // ------- check message, only up to 160 characters limited by Db
>     isset($_POST['message']) and mb_strlen($_POST['message']) <= $max_reminder_words and mb_strlen($_POST['message']) > 0 and
For more information execute 'psecio-parse rules LogicalOperators'

958) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 89
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>     isset($_POST['dueDate']) and preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $_POST['dueDate']) and
> // ------- check priority, only allow 1-3
>     isset($_POST['priority']) and intval($_POST['priority']) <= 3 and
For more information execute 'psecio-parse rules LogicalOperators'

959) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 89
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>     isset($_POST['dueDate']) and preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $_POST['dueDate']) and
> // ------- check priority, only allow 1-3
>     isset($_POST['priority']) and intval($_POST['priority']) <= 3 and
For more information execute 'psecio-parse rules LogicalOperators'

960) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 89
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>     isset($_POST['dueDate']) and preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $_POST['dueDate']) and
For more information execute 'psecio-parse rules LogicalOperators'

961) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 89
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>     isset($_POST['dueDate']) and preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $_POST['dueDate']) and
For more information execute 'psecio-parse rules LogicalOperators'

962) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_add.php on line 103
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (isset($_POST['sendSeperately']) and $_POST['sendSeperately']) {
For more information execute 'psecio-parse rules LogicalOperators'

963) /projects/emr/open-emr/src/openemr/interface/main/dated_reminders/dated_reminders_log.php on line 32
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (empty($_GET['sentBy']) and empty($_GET['sentTo'])) {
For more information execute 'psecio-parse rules LogicalOperators'

964) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 116
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($_GET['group'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

965) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 266
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array(($day+1), explode(',', $recurrence))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

966) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 266
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array(($day+1), explode(',', $recurrence))) {
For more information execute 'psecio-parse rules InArrayStrict'

967) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1051
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($_GET['prov']==true) {
For more information execute 'psecio-parse rules BooleanIdentity'

968) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1055
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($_GET['group'] == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

969) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1410
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($_GET['prov']==true) {
For more information execute 'psecio-parse rules BooleanIdentity'

970) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1412
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> } elseif ($_GET['group']==true) {
For more information execute 'psecio-parse rules BooleanIdentity'

971) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1534
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if ($_SESSION['authorizedUser'] || in_array($facrow, $facils)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

972) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1534
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if ($_SESSION['authorizedUser'] || in_array($facrow, $facils)) {
For more information execute 'psecio-parse rules InArrayStrict'

973) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1594
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($_GET['group']==true &&  $have_group_global_enabled) {
For more information execute 'psecio-parse rules BooleanIdentity'

974) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1626
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($_GET['group']==true) {
For more information execute 'psecio-parse rules BooleanIdentity'

975) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1663
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (in_array($urow['id'], $providers_array) || ($urow['id'] == $userid)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

976) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1663
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (in_array($urow['id'], $providers_array) || ($urow['id'] == $userid)) {
For more information execute 'psecio-parse rules InArrayStrict'

977) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1845
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (in_array($key, explode(',', $repeatfreq)) && isDaysEveryWeek($repeats)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

978) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 1845
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (in_array($key, explode(',', $repeatfreq)) && isDaysEveryWeek($repeats)) {
For more information execute 'psecio-parse rules InArrayStrict'

979) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 2182
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array($sdate, $holidays)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

980) /projects/emr/open-emr/src/openemr/interface/main/calendar/add_edit_event.php on line 2182
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array($sdate, $holidays)) {
For more information execute 'psecio-parse rules InArrayStrict'

981) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuser.php on line 131
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

982) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuser.php on line 302
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

983) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuser.php on line 385
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

984) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuser.php on line 1334
By default 'extract' overwrites variables in the local scope
>     extract($eventdata);
For more information execute 'psecio-parse rules Extract'

985) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pninit.php on line 217
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die('event table alter error : '.$dbconn->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

986) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pninit.php on line 231
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die('cat table create error : '.$dbconn->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

987) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pninit.php on line 253
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                     die('cat table insert error : '.$dbconn->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

988) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pninit.php on line 270
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                     die('event table update error : '.$dbconn->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

989) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pninit.php on line 279
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die('cat table alter error : '.$dbconn->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

990) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 428
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

991) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 450
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

992) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 487
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

993) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 525
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

994) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 563
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

995) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 710
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

996) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 994
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     if ($intime == false or $outtime == false) {
For more information execute 'psecio-parse rules LogicalOperators'

997) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 994
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($intime == false or $outtime == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

998) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 994
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($intime == false or $outtime == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

999) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1015
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 if ($eend < $intime_sec or $estart > $outtime_sec) {
For more information execute 'psecio-parse rules LogicalOperators'

1000) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1021
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                 } elseif ($estart < $i and $eend > $i) {
For more information execute 'psecio-parse rules LogicalOperators'

1001) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1107
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1002) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1771
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1003) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1901
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>         $user_edit_url = $user_delete_url = '';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1004) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1962
By default 'extract' overwrites variables in the local scope
>     extract($params);
For more information execute 'psecio-parse rules Extract'

1005) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1969
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('value', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1006) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1969
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('value', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1007) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1974
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('order', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1008) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1974
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('order', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1009) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1978
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('inc', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1010) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1978
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('inc', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1011) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1982
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('start', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1012) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1982
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('start', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1013) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1989
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('end', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1014) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/common.api.php on line 1989
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('end', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1015) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadmin.php on line 327
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1016) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadmin.php on line 1507
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1017) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadmin.php on line 2071
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1018) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadmin.php on line 2221
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1019) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php on line 848
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         } elseif ($month == 4 or $month == 6 or $month == 9 or $month == 11) {
For more information execute 'psecio-parse rules LogicalOperators'

1020) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php on line 848
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         } elseif ($month == 4 or $month == 6 or $month == 9 or $month == 11) {
For more information execute 'psecio-parse rules LogicalOperators'

1021) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php on line 848
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         } elseif ($month == 4 or $month == 6 or $month == 9 or $month == 11) {
For more information execute 'psecio-parse rules LogicalOperators'

1022) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 39
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1023) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 79
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1024) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 108
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1025) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 235
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1026) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 257
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1027) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 287
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1028) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 304
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1029) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 319
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1030) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 368
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1031) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 386
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1032) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnadminapi.php on line 401
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1033) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_view_select.php on line 62
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($f, $hide_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1034) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_view_select.php on line 62
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($f, $hide_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

1035) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 29
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1036) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 74
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array('user', $types)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1037) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 74
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array('user', $types)) {
For more information execute 'psecio-parse rules InArrayStrict'

1038) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 99
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array('category', $types)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1039) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 99
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array('category', $types)) {
For more information execute 'psecio-parse rules InArrayStrict'

1040) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 116
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array('topic', $types) && _SETTING_DISPLAY_TOPICS) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1041) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 116
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array('topic', $types) && _SETTING_DISPLAY_TOPICS) {
For more information execute 'psecio-parse rules InArrayStrict'

1042) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 150
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (!in_array($key, $newOrder)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1043) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 150
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (!in_array($key, $newOrder)) {
For more information execute 'psecio-parse rules InArrayStrict'

1044) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 164
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('user', $types)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1045) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php on line 164
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('user', $types)) {
For more information execute 'psecio-parse rules InArrayStrict'

1046) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_url.php on line 33
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1047) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_form_nav_open.php on line 29
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1048) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_date_select.php on line 134
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (!in_array($key, $newOrder)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1049) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_date_select.php on line 134
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (!in_array($key, $newOrder)) {
For more information execute 'psecio-parse rules InArrayStrict'

1050) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_date_format.php on line 30
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1051) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_week_range.php on line 86
Avoid the use of an output method (echo, print, etc) directly with a variable
>     echo $firstDay.$args['sep'].$lastDay;
For more information execute 'psecio-parse rules OutputWithVariable'

1052) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_form_nav_close.php on line 29
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1053) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 29
By default 'extract' overwrites variables in the local scope
>     extract($params);
For more information execute 'psecio-parse rules Extract'

1054) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 36
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('value', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1055) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 36
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('value', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1056) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 41
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('order', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1057) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 41
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('order', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1058) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 45
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('inc', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1059) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 45
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('inc', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1060) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 49
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('start', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1061) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 49
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('start', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1062) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 56
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('end', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1063) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_day.php on line 56
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('end', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1064) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php on line 34
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1065) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_events.php on line 29
By default 'extract' overwrites variables in the local scope
>     extract($params);
For more information execute 'psecio-parse rules Extract'

1066) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_events.php on line 36
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('value', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1067) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_events.php on line 36
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('value', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1068) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_events.php on line 41
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('sort', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1069) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_events.php on line 41
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('sort', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1070) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_events.php on line 46
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array('order', array_keys($params))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1071) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_events.php on line 46
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array('order', array_keys($params))) {
For more information execute 'psecio-parse rules InArrayStrict'

1072) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuserapi.php on line 42
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1073) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuserapi.php on line 70
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1074) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuserapi.php on line 607
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1075) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuserapi.php on line 741
By default 'extract' overwrites variables in the local scope
>     extract($edata);
For more information execute 'psecio-parse rules Extract'

1076) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuserapi.php on line 800
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1077) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuserapi.php on line 1044
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1078) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuserapi.php on line 1051
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>         $pc_username = "__PC_ALL__";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1079) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuserapi.php on line 1393
By default 'extract' overwrites variables in the local scope
>     extract($args);
For more information execute 'psecio-parse rules Extract'

1080) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuserapi.php on line 1581
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                         if ($excluded == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1081) /projects/emr/open-emr/src/openemr/interface/main/calendar/modules/PostCalendar/pnuserapi.php on line 1669
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                         if ($excluded == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1082) /projects/emr/open-emr/src/openemr/interface/main/calendar/config.php on line 92
By default 'extract' overwrites variables in the local scope
> extract($pnconfig, EXTR_OVERWRITE);
For more information execute 'psecio-parse rules Extract'

1083) /projects/emr/open-emr/src/openemr/interface/main/calendar/find_appt_popup.php on line 240
The third parameter should be set (and be true) on in_array to avoid type switching issues
> if (in_array($sdate, $holidays)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1084) /projects/emr/open-emr/src/openemr/interface/main/calendar/find_appt_popup.php on line 240
Evaluation using in_array should enforce type checking (third parameter should be true)
> if (in_array($sdate, $holidays)) {
For more information execute 'psecio-parse rules InArrayStrict'

1085) /projects/emr/open-emr/src/openemr/interface/main/calendar/index.php on line 182
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ((empty($return)) || ($return == false)) {
For more information execute 'psecio-parse rules BooleanIdentity'

1086) /projects/emr/open-emr/src/openemr/interface/main/calendar/includes/pnAPI.php on line 49
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ((isset($$__s) == true) && (is_array($$__s) == true)) {
For more information execute 'psecio-parse rules BooleanIdentity'

1087) /projects/emr/open-emr/src/openemr/interface/main/calendar/includes/pnAPI.php on line 49
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ((isset($$__s) == true) && (is_array($$__s) == true)) {
For more information execute 'psecio-parse rules BooleanIdentity'

1088) /projects/emr/open-emr/src/openemr/interface/main/calendar/includes/pnAPI.php on line 50
By default 'extract' overwrites variables in the local scope
>                 extract($$__s, EXTR_OVERWRITE);
For more information execute 'psecio-parse rules Extract'

1089) /projects/emr/open-emr/src/openemr/interface/main/calendar/includes/pnAPI.php on line 68
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ((isset($$__s) == true) && (is_array($$__s) == true)) {
For more information execute 'psecio-parse rules BooleanIdentity'

1090) /projects/emr/open-emr/src/openemr/interface/main/calendar/includes/pnAPI.php on line 68
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ((isset($$__s) == true) && (is_array($$__s) == true)) {
For more information execute 'psecio-parse rules BooleanIdentity'

1091) /projects/emr/open-emr/src/openemr/interface/main/calendar/includes/pnAPI.php on line 69
By default 'extract' overwrites variables in the local scope
>                 extract($$__s, EXTR_OVERWRITE);
For more information execute 'psecio-parse rules Extract'

1092) /projects/emr/open-emr/src/openemr/interface/main/calendar/includes/pnSession.php on line 91
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (pnConfigGetVar('intranet') == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1093) /projects/emr/open-emr/src/openemr/interface/main/daemon_frame.php on line 31
Use of system functions, especially with user input, is not recommended
>     exec("faxstat -r -l -h " . escapeshellarg($GLOBALS['hylafax_server']), $statlines);
For more information execute 'psecio-parse rules SystemFunctions'

1094) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 48
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>       die('Abort '.basename(__FILE__).' : Missing zlib extensions');
For more information execute 'psecio-parse rules ExitOrDie'

1095) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 116
'header()' calls should not use concatenation directly
>     header("Content-Length: " . filesize($TAR_FILE_PATH));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1096) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 117
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=" . basename($TAR_FILE_PATH));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1097) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 145
'header()' calls should not use concatenation directly
>     header("Content-Length: " . filesize($EXPORT_FILE));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1098) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 146
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=" . basename($EXPORT_FILE));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1099) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 148
The readfile/readlink/readgzfile functions output content directly (possible injection)
>     readfile($EXPORT_FILE);
For more information execute 'psecio-parse rules Readfile'

1100) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 223
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die(xlt("Couldn't remove old backup file:") . " " . text($TAR_FILE_PATH));
For more information execute 'psecio-parse rules ExitOrDie'

1101) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 228
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt("Couldn't remove dir:"). " " . text($TMP_BASE));
For more information execute 'psecio-parse rules ExitOrDie'

1102) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 232
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt("Couldn't create backup dir:") . " " . text($BACKUP_DIR));
For more information execute 'psecio-parse rules ExitOrDie'

1103) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 284
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die("Cannot read directory '" . text($webserver_root) . "'.");
For more information execute 'psecio-parse rules ExitOrDie'

1104) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 430
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die(xlt("Couldn't remove old export file: ") . text($EXPORT_FILE));
For more information execute 'psecio-parse rules ExitOrDie'

1105) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 634
Use of system functions, especially with user input, is not recommended
>     $tmp0 = exec($cmd, $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

1106) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 647
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die("\"" . text($cmd) . "\" returned " . text($tmp2) . ": " . text($tmp0));
For more information execute 'psecio-parse rules ExitOrDie'

1107) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 667
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt("Error in gzip compression of file: ") . text($file_to_compress));
For more information execute 'psecio-parse rules ExitOrDie'

1108) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 730
Use of system functions, especially with user input, is not recommended
>         $temp0 = exec($command, $temp1, $temp2);
For more information execute 'psecio-parse rules SystemFunctions'

1109) /projects/emr/open-emr/src/openemr/interface/main/backup.php on line 732
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("\"" . text($command) . "\" returned " . text($temp2) . ": " . text($temp0));
For more information execute 'psecio-parse rules ExitOrDie'

1110) /projects/emr/open-emr/src/openemr/interface/main/holidays/Holidays_Controller.php on line 126
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($date, $holidays)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1111) /projects/emr/open-emr/src/openemr/interface/main/holidays/Holidays_Controller.php on line 126
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($date, $holidays)) {
For more information execute 'psecio-parse rules InArrayStrict'

1112) /projects/emr/open-emr/src/openemr/interface/main/holidays/import_holidays.php on line 43
The readfile/readlink/readgzfile functions output content directly (possible injection)
>         readfile($target_file);
For more information execute 'psecio-parse rules Readfile'

1113) /projects/emr/open-emr/src/openemr/interface/main/messages/save.php on line 56
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($result['output'] == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1114) /projects/emr/open-emr/src/openemr/interface/main/messages/messages.php on line 318
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                                             die("getPnoteById() did not find id '" . text($noteid) . "'");
For more information execute 'psecio-parse rules ExitOrDie'

1115) /projects/emr/open-emr/src/openemr/interface/main/messages/messages.php on line 377
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                     if ($task == "addnew" or $task == "edit") {
For more information execute 'psecio-parse rules LogicalOperators'

1116) /projects/emr/open-emr/src/openemr/interface/main/messages/messages.php on line 447
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                                 <input type='text'  id='form_patient' name='form_patient' class='form-control <?php echo $cursor . " " .$background;?>' onclick="multi_sel_patient()" placeholder='<?php echo xla("Click to add patient"); ?>' value='<?php echo attr($patientname); ?>' readonly/>
For more information execute 'psecio-parse rules OutputWithVariable'

1117) /projects/emr/open-emr/src/openemr/interface/main/messages/messages.php on line 622
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                         if ($begin == "" or $begin == 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1118) /projects/emr/open-emr/src/openemr/interface/main/main_screen.php on line 317
If 'session_regenerate_id' is used, must use second paramater and set to true
>     session_regenerate_id(false);
For more information execute 'psecio-parse rules SessionRegenerateId'

1119) /projects/emr/open-emr/src/openemr/interface/main/main_screen.php on line 356
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>       (!isset($_SESSION['expiration_msg'])
>       or $_SESSION['expiration_msg'] == 0)) {
For more information execute 'psecio-parse rules LogicalOperators'

1120) /projects/emr/open-emr/src/openemr/interface/main/main_screen.php on line 436
'header()' calls should not use concatenation directly
>     header('Location: ' . $web_root . "/interface/main/tabs/main.php?token_main=" . urlencode($_SESSION['token_main_php']));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1121) /projects/emr/open-emr/src/openemr/interface/main/pwd_expires_alert.php on line 21
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
> $pwd_expires = "";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1122) /projects/emr/open-emr/src/openemr/interface/main/pwd_expires_alert.php on line 35
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> if (($pwd_expires == "0000-00-00") or ($pwd_expires == "")) {
For more information execute 'psecio-parse rules LogicalOperators'

1123) /projects/emr/open-emr/src/openemr/interface/login/login.php on line 66
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (!in_array($std_app, $emr_app)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1124) /projects/emr/open-emr/src/openemr/interface/login/login.php on line 66
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (!in_array($std_app, $emr_app)) {
For more information execute 'psecio-parse rules InArrayStrict'

1125) /projects/emr/open-emr/src/openemr/interface/login/login.php on line 349
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                                             if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1126) /projects/emr/open-emr/src/openemr/interface/login/login.php on line 349
Evaluation using in_array should enforce type checking (third parameter should be true)
>                                             if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
For more information execute 'psecio-parse rules InArrayStrict'

1127) /projects/emr/open-emr/src/openemr/custom/ajax_download.php on line 74
'header()' calls should not use concatenation directly
>     header("Content-Length: " . filesize($finalZip));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1128) /projects/emr/open-emr/src/openemr/custom/ajax_download.php on line 75
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=" . basename($finalZip) . ";");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1129) /projects/emr/open-emr/src/openemr/custom/ajax_download.php on line 77
The readfile/readlink/readgzfile functions output content directly (possible injection)
>     readfile($finalZip);
For more information execute 'psecio-parse rules Readfile'

1130) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 689
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if ((in_array($row['cqm_nqf_code'], $denExcepNotNeedRules) ) && ($cqmKey == "exception_patients")) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1131) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 689
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if ((in_array($row['cqm_nqf_code'], $denExcepNotNeedRules) ) && ($cqmKey == "exception_patients")) {
For more information execute 'psecio-parse rules InArrayStrict'

1132) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 785
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($row['cqm_nqf_code'], $multNumNQFArr)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1133) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 785
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($row['cqm_nqf_code'], $multNumNQFArr)) {
For more information execute 'psecio-parse rules InArrayStrict'

1134) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 808
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if (( !isset($skipMultNumArr[$row['cqm_nqf_code']]) ) || ($skipMultNumArr[$row['cqm_nqf_code']] == false)) {
For more information execute 'psecio-parse rules BooleanIdentity'

1135) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 923
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if ((in_array($row['cqm_nqf_code'], $denExcepNotNeedRules) ) && ($cqmKey == "exception_patients")) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1136) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 923
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if ((in_array($row['cqm_nqf_code'], $denExcepNotNeedRules) ) && ($cqmKey == "exception_patients")) {
For more information execute 'psecio-parse rules InArrayStrict'

1137) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 1441
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($row['cqm_nqf_code'], $multNumNQFArr)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1138) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 1441
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($row['cqm_nqf_code'], $multNumNQFArr)) {
For more information execute 'psecio-parse rules InArrayStrict'

1139) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 1443
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (in_array($row['cqm_nqf_code'], $multNumNQFArr)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1140) /projects/emr/open-emr/src/openemr/custom/export_qrda_xml.php on line 1443
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (in_array($row['cqm_nqf_code'], $multNumNQFArr)) {
For more information execute 'psecio-parse rules InArrayStrict'

1141) /projects/emr/open-emr/src/openemr/custom/download_qrda.php on line 173
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (count($cqmCodes) && in_array($row['cqm_nqf_code'], $cqmCodes)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1142) /projects/emr/open-emr/src/openemr/custom/download_qrda.php on line 173
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (count($cqmCodes) && in_array($row['cqm_nqf_code'], $cqmCodes)) {
For more information execute 'psecio-parse rules InArrayStrict'

1143) /projects/emr/open-emr/src/openemr/custom/qrda_download.php on line 44
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=\"".basename($xmlurl)."\";");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1144) /projects/emr/open-emr/src/openemr/custom/qrda_download.php on line 46
'header()' calls should not use concatenation directly
>     header("Content-Length: ". filesize($xmlurl));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1145) /projects/emr/open-emr/src/openemr/custom/qrda_download.php on line 49
The readfile/readlink/readgzfile functions output content directly (possible injection)
>     readfile($xmlurl);
For more information execute 'psecio-parse rules Readfile'

1146) /projects/emr/open-emr/src/openemr/custom/export_labworks.php on line 22
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>  $FTP_USER   = "openemr";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1147) /projects/emr/open-emr/src/openemr/custom/export_labworks.php on line 23
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>  $FTP_PASS   = "secret";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1148) /projects/emr/open-emr/src/openemr/custom/export_labworks.php on line 286
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     $ftpconn = ftp_connect($FTP_SERVER) or die("FTP connection failed");
For more information execute 'psecio-parse rules LogicalOperators'

1149) /projects/emr/open-emr/src/openemr/custom/export_labworks.php on line 287
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     ftp_login($ftpconn, $FTP_USER, $FTP_PASS) or die("FTP login failed");
For more information execute 'psecio-parse rules LogicalOperators'

1150) /projects/emr/open-emr/src/openemr/custom/export_labworks.php on line 289
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         ftp_chdir($ftpconn, $FTP_DIR) or die("FTP chdir failed");
For more information execute 'psecio-parse rules LogicalOperators'

1151) /projects/emr/open-emr/src/openemr/custom/export_labworks.php on line 292
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     ftp_put($ftpconn, $initialname, $finalpath, FTP_BINARY) or die("FTP put failed");
For more information execute 'psecio-parse rules LogicalOperators'

1152) /projects/emr/open-emr/src/openemr/custom/export_labworks.php on line 293
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     ftp_rename($ftpconn, $initialname, $finalname) or die("FTP rename failed");
For more information execute 'psecio-parse rules LogicalOperators'

1153) /projects/emr/open-emr/src/openemr/custom/export_registry_xml.php on line 133
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($firstProviderFlag == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1154) /projects/emr/open-emr/src/openemr/custom/export_registry_xml.php on line 157
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($firstPlanFlag == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1155) /projects/emr/open-emr/src/openemr/custom/export_registry_xml.php on line 158
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($firstProviderFlag == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1156) /projects/emr/open-emr/src/openemr/custom/export_registry_xml.php on line 176
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($existProvider == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1157) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-9-1/test/xhtml.php on line 4
The readfile/readlink/readgzfile functions output content directly (possible injection)
> 	readfile("index.html");
For more information execute 'psecio-parse rules Readfile'

1158) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-9-1/test/polluted.php on line 46
Avoid the use of an output method (echo, print, etc) directly with a variable
> 				echo "unsupported library ". $name;
For more information execute 'psecio-parse rules OutputWithVariable'

1159) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-9-1/test/polluted.php on line 56
Avoid the use of an output method (echo, print, etc) directly with a variable
> 				echo "library ". $name ." not supported in version ". $ver;
For more information execute 'psecio-parse rules OutputWithVariable'

1160) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-9-1/test/data/jsonp.php on line 10
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo $callback . '([ {"name": "John", "age": 21}, {"name": "Peter", "age": 25 } ])';
For more information execute 'psecio-parse rules OutputWithVariable'

1161) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-9-1/test/data/jsonp.php on line 12
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo $callback . '({ "data": {"lang": "en", "length": 25} })';
For more information execute 'psecio-parse rules OutputWithVariable'

1162) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-9-1/test/data/etag.php on line 13
'header()' calls should not use concatenation directly
> header("Etag: " . $etag);
For more information execute 'psecio-parse rules SetHeaderWithInput'

1163) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-9-1/test/data/etag.php on line 16
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo "OK: " . $etag;
For more information execute 'psecio-parse rules OutputWithVariable'

1164) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-9-1/test/data/if_modified_since.php on line 12
'header()' calls should not use concatenation directly
> header("Last-Modified: " . $ts);
For more information execute 'psecio-parse rules SetHeaderWithInput'

1165) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-9-1/test/data/if_modified_since.php on line 15
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo "OK: " . $ts;
For more information execute 'psecio-parse rules OutputWithVariable'

1166) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-7-2/test/xhtml.php on line 4
The readfile/readlink/readgzfile functions output content directly (possible injection)
> 	readfile("index.html");
For more information execute 'psecio-parse rules Readfile'

1167) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-7-2/test/data/jsonp.php on line 10
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo $callback . '([ {"name": "John", "age": 21}, {"name": "Peter", "age": 25 } ])';
For more information execute 'psecio-parse rules OutputWithVariable'

1168) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-7-2/test/data/jsonp.php on line 12
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo $callback . '({ "data": {"lang": "en", "length": 25} })';
For more information execute 'psecio-parse rules OutputWithVariable'

1169) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-7-2/test/data/etag.php on line 13
'header()' calls should not use concatenation directly
> header("Etag: " . $etag);
For more information execute 'psecio-parse rules SetHeaderWithInput'

1170) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-7-2/test/data/etag.php on line 16
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo "OK: " . $etag;
For more information execute 'psecio-parse rules OutputWithVariable'

1171) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-7-2/test/data/if_modified_since.php on line 12
'header()' calls should not use concatenation directly
> header("Last-Modified: " . $ts);
For more information execute 'psecio-parse rules SetHeaderWithInput'

1172) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-7-2/test/data/if_modified_since.php on line 15
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo "OK: " . $ts;
For more information execute 'psecio-parse rules OutputWithVariable'

1173) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-10-2/test/xhtml.php on line 4
The readfile/readlink/readgzfile functions output content directly (possible injection)
> 	readfile("index.html");
For more information execute 'psecio-parse rules Readfile'

1174) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-10-2/test/data/jsonp.php on line 10
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo $callback . '([ {"name": "John", "age": 21}, {"name": "Peter", "age": 25 } ])';
For more information execute 'psecio-parse rules OutputWithVariable'

1175) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-10-2/test/data/jsonp.php on line 12
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo $callback . '({ "data": {"lang": "en", "length": 25} })';
For more information execute 'psecio-parse rules OutputWithVariable'

1176) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-10-2/test/data/etag.php on line 13
'header()' calls should not use concatenation directly
> header("Etag: " . $etag);
For more information execute 'psecio-parse rules SetHeaderWithInput'

1177) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-10-2/test/data/etag.php on line 16
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo "OK: " . $etag;
For more information execute 'psecio-parse rules OutputWithVariable'

1178) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-10-2/test/data/if_modified_since.php on line 12
'header()' calls should not use concatenation directly
> header("Last-Modified: " . $ts);
For more information execute 'psecio-parse rules SetHeaderWithInput'

1179) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-10-2/test/data/if_modified_since.php on line 15
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo "OK: " . $ts;
For more information execute 'psecio-parse rules OutputWithVariable'

1180) /projects/emr/open-emr/src/openemr/.docs/.out/public/assets/jquery-1-10-2/test/data/support/csp.php on line 7
'header()' calls should not use concatenation directly
> 	header("X-WebKit-CSP: script-src " . $_SERVER["HTTP_HOST"] . " 'self'");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1181) /projects/emr/open-emr/src/openemr/Documentation/privileged_db/secure_sqlconf.php on line 13
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
> $secure_pass    = 'securepassword';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1182) /projects/emr/open-emr/src/openemr/ccr/createCCR.php on line 31
'header()' calls should not use concatenation directly
>         header('Location: '.$landingpage.'?w');
For more information execute 'psecio-parse rules SetHeaderWithInput'

1183) /projects/emr/open-emr/src/openemr/ccr/createCCR.php on line 177
'header()' calls should not use concatenation directly
>             header("Content-Length: " . filesize($zipName));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1184) /projects/emr/open-emr/src/openemr/ccr/createCCR.php on line 178
'header()' calls should not use concatenation directly
>             header("Content-Disposition: attachment; filename=" . basename($zipName) . ";");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1185) /projects/emr/open-emr/src/openemr/ccr/createCCR.php on line 180
The readfile/readlink/readgzfile functions output content directly (possible injection)
>             readfile($zipName);
For more information execute 'psecio-parse rules Readfile'

1186) /projects/emr/open-emr/src/openemr/ccr/createCCR.php on line 275
'header()' calls should not use concatenation directly
>             header("Content-Length: " . filesize($zipName));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1187) /projects/emr/open-emr/src/openemr/ccr/createCCR.php on line 276
'header()' calls should not use concatenation directly
>             header("Content-Disposition: attachment; filename=" . basename($zipName) . ";");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1188) /projects/emr/open-emr/src/openemr/ccr/createCCR.php on line 278
The readfile/readlink/readgzfile functions output content directly (possible injection)
>             readfile($zipName);
For more information execute 'psecio-parse rules Readfile'

1189) /projects/emr/open-emr/src/openemr/ccr/createCCR.php on line 365
'header()' calls should not use concatenation directly
>     header("Content-Disposition: attachment; filename=" . $main_filename . "");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1190) /projects/emr/open-emr/src/openemr/ccr/transmitCCD.php on line 60
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($GLOBALS['phimail_enable']==false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1191) /projects/emr/open-emr/src/openemr/ccr/display.php on line 41
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo xl('The requested document is not present at the expected location on the filesystem or there are not sufficient permissions to access it.', '', '', ' ') . $temp_url;
For more information execute 'psecio-parse rules OutputWithVariable'

1192) /projects/emr/open-emr/src/openemr/rest_controllers/DocumentRestController.php on line 47
'header()' calls should not use concatenation directly
>             header('Content-Disposition: attachment; filename=' . urlencode(basename($file)));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1193) /projects/emr/open-emr/src/openemr/rest_controllers/DocumentRestController.php on line 51
'header()' calls should not use concatenation directly
>             header('Content-Length: ' . filesize($file));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1194) /projects/emr/open-emr/src/openemr/rest_controllers/DocumentRestController.php on line 54
The readfile/readlink/readgzfile functions output content directly (possible injection)
>             readfile($file);
For more information execute 'psecio-parse rules Readfile'

1195) /projects/emr/open-emr/src/openemr/gacl/Cache_Lite/Hashed_Cache_Lite.php on line 86
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 		if (in_array(substr($dir,-1),array(DIR_SEP,'/','\\'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1196) /projects/emr/open-emr/src/openemr/gacl/Cache_Lite/Hashed_Cache_Lite.php on line 86
Evaluation using in_array should enforce type checking (third parameter should be true)
> 		if (in_array(substr($dir,-1),array(DIR_SEP,'/','\\'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1197) /projects/emr/open-emr/src/openemr/gacl/Cache_Lite/Lite.php on line 229
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if(in_array($key, $availableOptions)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1198) /projects/emr/open-emr/src/openemr/gacl/Cache_Lite/Lite.php on line 229
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if(in_array($key, $availableOptions)) {
For more information execute 'psecio-parse rules InArrayStrict'

1199) /projects/emr/open-emr/src/openemr/gacl/Cache_Lite/Lite.php on line 275
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if (($data) and ($this->_memoryCaching)) {
For more information execute 'psecio-parse rules LogicalOperators'

1200) /projects/emr/open-emr/src/openemr/gacl/Cache_Lite/Lite.php on line 278
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if (($this->_automaticSerialization) and (is_string($data))) {
For more information execute 'psecio-parse rules LogicalOperators'

1201) /projects/emr/open-emr/src/openemr/gacl/admin/edit_objects.php on line 78
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if (!empty($value) AND !empty($name)) {
For more information execute 'psecio-parse rules LogicalOperators'

1202) /projects/emr/open-emr/src/openemr/gacl/admin/about.php on line 29
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 	if($gacl_api->_caching == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1203) /projects/emr/open-emr/src/openemr/gacl/admin/about.php on line 36
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 	if($gacl_api->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1204) /projects/emr/open-emr/src/openemr/gacl/admin/edit_object_sections.php on line 19
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> if ( isset($_GET['object_type']) AND $_GET['object_type'] != '' ) {
For more information execute 'psecio-parse rules LogicalOperators'

1205) /projects/emr/open-emr/src/openemr/gacl/admin/edit_object_sections.php on line 78
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if (!empty($value) AND !empty($order) AND !empty($name)) {
For more information execute 'psecio-parse rules LogicalOperators'

1206) /projects/emr/open-emr/src/openemr/gacl/admin/edit_object_sections.php on line 78
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if (!empty($value) AND !empty($order) AND !empty($name)) {
For more information execute 'psecio-parse rules LogicalOperators'

1207) /projects/emr/open-emr/src/openemr/gacl/admin/edit_group.php on line 53
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 				if ($result == FALSE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1208) /projects/emr/open-emr/src/openemr/gacl/admin/edit_group.php on line 79
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (!empty($_POST['group_id']) AND $parent_id == $_POST['group_id']) {
For more information execute 'psecio-parse rules LogicalOperators'

1209) /projects/emr/open-emr/src/openemr/gacl/admin/gacl_admin_api.class.php on line 78
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($url) AND !empty($_SERVER[HTTP_REFERER])) {
For more information execute 'psecio-parse rules LogicalOperators'

1210) /projects/emr/open-emr/src/openemr/gacl/admin/gacl_admin_api.class.php on line 83
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (!$debug OR $debug==0) {
For more information execute 'psecio-parse rules LogicalOperators'

1211) /projects/emr/open-emr/src/openemr/gacl/admin/acl_admin.php on line 54
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($selected_aro_array) AND empty($_POST['aro_groups'])) {
For more information execute 'psecio-parse rules LogicalOperators'

1212) /projects/emr/open-emr/src/openemr/gacl/admin/acl_admin.php on line 68
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 			if ($gacl_api->edit_acl($acl_id, $selected_aco_array, $selected_aro_array, $_POST['aro_groups'], $selected_axo_array, $_POST['axo_groups'], $_POST['allow'], $enabled, $_POST['return_value'], $_POST['note'], $_POST['acl_section']) == FALSE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1213) /projects/emr/open-emr/src/openemr/gacl/admin/acl_admin.php on line 74
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 			if ($gacl_api->add_acl($selected_aco_array, $selected_aro_array, $_POST['aro_groups'], $selected_axo_array, $_POST['axo_groups'], $_POST['allow'], $enabled, $_POST['return_value'], $_POST['note'], $_POST['acl_section']) == FALSE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1214) /projects/emr/open-emr/src/openemr/gacl/admin/acl_admin.php on line 84
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($_GET['action'] == 'edit' AND !empty($_GET['acl_id'])) {
For more information execute 'psecio-parse rules LogicalOperators'

1215) /projects/emr/open-emr/src/openemr/gacl/admin/acl_admin.php on line 130
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			$show_axo = (!empty($selected_axo_groups) OR !empty($options_selected_axo));
For more information execute 'psecio-parse rules LogicalOperators'

1216) /projects/emr/open-emr/src/openemr/gacl/admin/acl_admin.php on line 186
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 					if (!isset($tmp_section_value) OR $section_value != $tmp_section_value) {
For more information execute 'psecio-parse rules LogicalOperators'

1217) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 22
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array ($_GET['delete_acl']) AND !empty($_GET['delete_acl'])) {
For more information execute 'psecio-parse rules LogicalOperators'

1218) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 43
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (isset($_GET['action']) AND $_GET['action'] == 'Filter') {
For more information execute 'psecio-parse rules LogicalOperators'

1219) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 53
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_aco_section']) AND $_GET['filter_aco_section'] != '-1') {
For more information execute 'psecio-parse rules LogicalOperators'

1220) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 56
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_aco']) AND $_GET['filter_aco'] != '') {
For more information execute 'psecio-parse rules LogicalOperators'

1221) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 64
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_aro_section']) AND $_GET['filter_aro_section'] != '-1') {
For more information execute 'psecio-parse rules LogicalOperators'

1222) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 67
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_aro']) AND $_GET['filter_aro'] != '') {
For more information execute 'psecio-parse rules LogicalOperators'

1223) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 74
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_aro_group']) AND $_GET['filter_aro_group'] != '') {
For more information execute 'psecio-parse rules LogicalOperators'

1224) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 82
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_axo_section']) AND $_GET['filter_axo_section'] != '-1') {
For more information execute 'psecio-parse rules LogicalOperators'

1225) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 85
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_axo']) AND $_GET['filter_axo'] != '') {
For more information execute 'psecio-parse rules LogicalOperators'

1226) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 92
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_axo_group']) AND $_GET['filter_axo_group'] != '') {
For more information execute 'psecio-parse rules LogicalOperators'

1227) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 100
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_acl_section']) AND $_GET['filter_acl_section'] != '-1') {
For more information execute 'psecio-parse rules LogicalOperators'

1228) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 103
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_return_value']) AND $_GET['filter_return_value'] != '') {
For more information execute 'psecio-parse rules LogicalOperators'

1229) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 106
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_allow']) AND $_GET['filter_allow'] != '-1') {
For more information execute 'psecio-parse rules LogicalOperators'

1230) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 109
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( isset($_GET['filter_enabled']) AND $_GET['filter_enabled'] != '-1') {
For more information execute 'psecio-parse rules LogicalOperators'

1231) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 113
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if (isset($filter_query) AND is_array($filter_query)) {
For more information execute 'psecio-parse rules LogicalOperators'

1232) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 148
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ( !($_GET['action'] == 'Filter' AND $acl_ids_sql == -1) ) {
For more information execute 'psecio-parse rules LogicalOperators'

1233) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 265
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if (!isset($_GET['filter_' . $type . '_section']) OR $_GET['filter_' . $type . '_section'] == '') {
For more information execute 'psecio-parse rules LogicalOperators'

1234) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 276
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (!isset($_GET['filter_allow']) OR $_GET['filter_allow'] == '') {
For more information execute 'psecio-parse rules LogicalOperators'

1235) /projects/emr/open-emr/src/openemr/gacl/admin/acl_list.php on line 279
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (!isset($_GET['filter_enabled']) OR $_GET['filter_enabled'] == '') {
For more information execute 'psecio-parse rules LogicalOperators'

1236) /projects/emr/open-emr/src/openemr/gacl/admin/acl_test2.php on line 73
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 	if ($aco_section_name != $tmp_aco_section_name OR $aco_name != $tmp_aco_name) {
For more information execute 'psecio-parse rules LogicalOperators'

1237) /projects/emr/open-emr/src/openemr/gacl/admin/assign_group.php on line 133
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if (!isset($tmp_section_value) OR $section_value != $tmp_section_value) {
For more information execute 'psecio-parse rules LogicalOperators'

1238) /projects/emr/open-emr/src/openemr/gacl/admin/object_search.php on line 41
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (count($exploded_value_search_str) > 1 OR count($exploded_name_search_str) > 1) {
For more information execute 'psecio-parse rules LogicalOperators'

1239) /projects/emr/open-emr/src/openemr/gacl/admin/acl_test3.php on line 98
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 	if ($aco_section_name != $tmp_aco_section_name OR $aco_name != $tmp_aco_name) {
For more information execute 'psecio-parse rules LogicalOperators'

1240) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 35
Avoid the use of an output method (echo, print, etc) directly with a variable
> 	echo $text."<br/>\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1241) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 58
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 	case ($db_type == "mysql" OR $db_type == "mysqlt" OR $db_type == "maxsql" OR $db_type == "mysqli" OR $db_type == "mysqli_mod" ):
For more information execute 'psecio-parse rules LogicalOperators'

1242) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 58
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 	case ($db_type == "mysql" OR $db_type == "mysqlt" OR $db_type == "maxsql" OR $db_type == "mysqli" OR $db_type == "mysqli_mod" ):
For more information execute 'psecio-parse rules LogicalOperators'

1243) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 58
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 	case ($db_type == "mysql" OR $db_type == "mysqlt" OR $db_type == "maxsql" OR $db_type == "mysqli" OR $db_type == "mysqli_mod" ):
For more information execute 'psecio-parse rules LogicalOperators'

1244) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 58
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 	case ($db_type == "mysql" OR $db_type == "mysqlt" OR $db_type == "maxsql" OR $db_type == "mysqli" OR $db_type == "mysqli_mod" ):
For more information execute 'psecio-parse rules LogicalOperators'

1245) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 64
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 		if (in_array($db_name, $databases) ) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1246) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 64
Evaluation using in_array should enforce type checking (third parameter should be true)
> 		if (in_array($db_name, $databases) ) {
For more information execute 'psecio-parse rules InArrayStrict'

1247) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 81
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 	case ( $db_type == "postgres8" OR $db_type == "postgres7" ):
For more information execute 'psecio-parse rules LogicalOperators'

1248) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 88
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 		if (in_array($db_name, $databases) ) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1249) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 88
Evaluation using in_array should enforce type checking (third parameter should be true)
> 		if (in_array($db_name, $databases) ) {
For more information execute 'psecio-parse rules InArrayStrict'

1250) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 113
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 		if (in_array($db_name, $databases) ) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1251) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 113
Evaluation using in_array should enforce type checking (third parameter should be true)
> 		if (in_array($db_name, $databases) ) {
For more information execute 'psecio-parse rules InArrayStrict'

1252) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 138
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 		if (in_array($db_name, $databases) ) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1253) /projects/emr/open-emr/src/openemr/gacl/setup.php on line 138
Evaluation using in_array should enforce type checking (third parameter should be true)
> 		if (in_array($db_name, $databases) ) {
For more information execute 'psecio-parse rules InArrayStrict'

1254) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 141
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 				if (in_array($key, $available_options) ) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1255) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 141
Evaluation using in_array should enforce type checking (third parameter should be true)
> 				if (in_array($key, $available_options) ) {
For more information execute 'psecio-parse rules InArrayStrict'

1256) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 211
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ( $this->_caching == TRUE ) {
For more information execute 'psecio-parse rules BooleanIdentity'

1257) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 371
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if (is_array($aro_group_ids) AND !empty($aro_group_ids)) {
For more information execute 'psecio-parse rules LogicalOperators'

1258) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 375
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ($axo_section_value != '' AND $axo_value != '') {
For more information execute 'psecio-parse rules LogicalOperators'

1259) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 378
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 				if (is_array($axo_group_ids) AND !empty($axo_group_ids)) {
For more information execute 'psecio-parse rules LogicalOperators'

1260) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 481
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 				if ($axo_section_value == '' AND $axo_value == '') {
For more information execute 'psecio-parse rules LogicalOperators'

1261) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 544
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>                                                 if ( isset($single_row[1]) AND $single_row[1] == 1 ) {
For more information execute 'psecio-parse rules LogicalOperators'

1262) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 552
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 				        if ( isset($row[1]) AND $row[1] == 1 ) {
For more information execute 'psecio-parse rules LogicalOperators'

1263) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 571
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 			if ($debug == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1264) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 696
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ( $this->_caching == TRUE ) {
For more information execute 'psecio-parse rules BooleanIdentity'

1265) /projects/emr/open-emr/src/openemr/gacl/gacl.class.php on line 715
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ( $this->_caching == TRUE ) {
For more information execute 'psecio-parse rules BooleanIdentity'

1266) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 221
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($acl_ids) AND $acl_ids_count > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1267) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 263
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($acl_ids) AND $acl_ids_count == 1) {
For more information execute 'psecio-parse rules LogicalOperators'

1268) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 333
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($aco_section_value !== FALSE AND $aco_value !== FALSE) {
For more information execute 'psecio-parse rules LogicalOperators'

1269) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 337
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ($aco_section_value == NULL AND $aco_value == NULL) {
For more information execute 'psecio-parse rules LogicalOperators'

1270) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 345
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($aro_section_value !== FALSE AND $aro_value !== FALSE) {
For more information execute 'psecio-parse rules LogicalOperators'

1271) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 349
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ($aro_section_value == NULL AND $aro_value == NULL) {
For more information execute 'psecio-parse rules LogicalOperators'

1272) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 357
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($axo_section_value !== FALSE AND $axo_value !== FALSE) {
For more information execute 'psecio-parse rules LogicalOperators'

1273) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 361
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ($axo_section_value == NULL AND $axo_value == NULL) {
For more information execute 'psecio-parse rules LogicalOperators'

1274) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 437
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($aro_array) AND count($aro_array) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1275) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 443
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 						if (!in_array($aro_value, $acl_array['aro'][$aro_section_value])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1276) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 443
Evaluation using in_array should enforce type checking (third parameter should be true)
> 						if (!in_array($aro_value, $acl_array['aro'][$aro_section_value])) {
For more information execute 'psecio-parse rules InArrayStrict'

1277) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 458
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($aro_group_ids) AND count($aro_group_ids) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1278) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 462
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 				if (!is_array($acl_array['aro_groups']) OR !in_array($aro_group_id, $acl_array['aro_groups'])) {
For more information execute 'psecio-parse rules LogicalOperators'

1279) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 462
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 				if (!is_array($acl_array['aro_groups']) OR !in_array($aro_group_id, $acl_array['aro_groups'])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1280) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 462
Evaluation using in_array should enforce type checking (third parameter should be true)
> 				if (!is_array($acl_array['aro_groups']) OR !in_array($aro_group_id, $acl_array['aro_groups'])) {
For more information execute 'psecio-parse rules InArrayStrict'

1281) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 472
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($axo_array) AND count($axo_array) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1282) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 477
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 					if (!in_array($axo_value, $acl_array['axo'][$axo_section_value])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1283) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 477
Evaluation using in_array should enforce type checking (third parameter should be true)
> 					if (!in_array($axo_value, $acl_array['axo'][$axo_section_value])) {
For more information execute 'psecio-parse rules InArrayStrict'

1284) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 489
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($axo_group_ids) AND count($axo_group_ids) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1285) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 492
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 				if (!is_array($acl_array['axo_groups']) OR !in_array($axo_group_id, $acl_array['axo_groups'])) {
For more information execute 'psecio-parse rules LogicalOperators'

1286) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 492
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 				if (!is_array($acl_array['axo_groups']) OR !in_array($axo_group_id, $acl_array['axo_groups'])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1287) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 492
Evaluation using in_array should enforce type checking (third parameter should be true)
> 				if (!is_array($acl_array['axo_groups']) OR !in_array($axo_group_id, $acl_array['axo_groups'])) {
For more information execute 'psecio-parse rules InArrayStrict'

1288) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 502
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($aco_array) AND count($aco_array) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1289) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 507
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 					if (!is_array($acl_array['aco'][$aco_section_value]) || !in_array($aco_value, $acl_array['aco'][$aco_section_value])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1290) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 507
Evaluation using in_array should enforce type checking (third parameter should be true)
> 					if (!is_array($acl_array['aco'][$aco_section_value]) || !in_array($aco_value, $acl_array['aco'][$aco_section_value])) {
For more information execute 'psecio-parse rules InArrayStrict'

1291) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 558
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($aro_array) AND count($aro_array) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1292) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 582
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($aro_group_ids) AND count($aro_group_ids) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1293) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 599
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($axo_array) AND count($axo_array) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1294) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 618
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($axo_group_ids) AND count($axo_group_ids) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1295) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 635
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (is_array($aco_array) AND count($aco_array) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1296) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 659
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ( $this->count_all($acl_array['aco']) == 0
> 					OR ( $this->count_all($acl_array['aro']) == 0
> 						AND ( $this->count_all($acl_array['axo']) == 0 OR $acl_array['axo'] == FALSE)
> 						AND (count($acl_array['aro_groups']) == 0 OR $acl_array['aro_groups'] == FALSE)
> 						AND (count($acl_array['axo_groups']) == 0 OR $acl_array['axo_groups'] == FALSE)
> 						) ) {
For more information execute 'psecio-parse rules LogicalOperators'

1297) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 660
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 					OR ( $this->count_all($acl_array['aro']) == 0
> 						AND ( $this->count_all($acl_array['axo']) == 0 OR $acl_array['axo'] == FALSE)
> 						AND (count($acl_array['aro_groups']) == 0 OR $acl_array['aro_groups'] == FALSE)
> 						AND (count($acl_array['axo_groups']) == 0 OR $acl_array['axo_groups'] == FALSE)
For more information execute 'psecio-parse rules LogicalOperators'

1298) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 660
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 					OR ( $this->count_all($acl_array['aro']) == 0
> 						AND ( $this->count_all($acl_array['axo']) == 0 OR $acl_array['axo'] == FALSE)
> 						AND (count($acl_array['aro_groups']) == 0 OR $acl_array['aro_groups'] == FALSE)
For more information execute 'psecio-parse rules LogicalOperators'

1299) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 660
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 					OR ( $this->count_all($acl_array['aro']) == 0
> 						AND ( $this->count_all($acl_array['axo']) == 0 OR $acl_array['axo'] == FALSE)
For more information execute 'psecio-parse rules LogicalOperators'

1300) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 661
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 						AND ( $this->count_all($acl_array['axo']) == 0 OR $acl_array['axo'] == FALSE)
For more information execute 'psecio-parse rules LogicalOperators'

1301) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 661
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 						AND ( $this->count_all($acl_array['axo']) == 0 OR $acl_array['axo'] == FALSE)
For more information execute 'psecio-parse rules BooleanIdentity'

1302) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 662
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 						AND (count($acl_array['aro_groups']) == 0 OR $acl_array['aro_groups'] == FALSE)
For more information execute 'psecio-parse rules LogicalOperators'

1303) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 662
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 						AND (count($acl_array['aro_groups']) == 0 OR $acl_array['aro_groups'] == FALSE)
For more information execute 'psecio-parse rules BooleanIdentity'

1304) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 663
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 						AND (count($acl_array['axo_groups']) == 0 OR $acl_array['axo_groups'] == FALSE)
For more information execute 'psecio-parse rules LogicalOperators'

1305) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 663
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 						AND (count($acl_array['axo_groups']) == 0 OR $acl_array['axo_groups'] == FALSE)
For more information execute 'psecio-parse rules BooleanIdentity'

1306) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 856
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 				if (is_array($axo_array) AND count($axo_array) > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1307) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 876
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 						if (is_array($conflict_result) AND !empty($conflict_result)) {
For more information execute 'psecio-parse rules LogicalOperators'

1308) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 898
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 					if (is_array($conflict_result) AND !empty($conflict_result)) {
For more information execute 'psecio-parse rules LogicalOperators'

1309) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 948
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ((empty($aro_array) || count($aro_array) == 0) AND (empty($aro_group_ids) || count($aro_group_ids) == 0)) {
For more information execute 'psecio-parse rules LogicalOperators'

1310) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 961
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (!empty($section_value)
> 			AND !$this->get_object_section_section_id(NULL, $section_value, 'ACL')) {
For more information execute 'psecio-parse rules LogicalOperators'

1311) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 982
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->get_acl($acl_id) == FALSE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1312) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1143
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules LogicalOperators'

1313) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1143
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1314) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1143
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1315) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1184
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (count($aro_array) == 0 AND count($aro_group_ids) == 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1316) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1252
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules LogicalOperators'

1317) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1252
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1318) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1252
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1319) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1436
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($name) AND empty($value) ) {
For more information execute 'psecio-parse rules LogicalOperators'

1320) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1941
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($group_id) OR empty($object_value) OR empty($object_section_value)) {
For more information execute 'psecio-parse rules LogicalOperators'

1321) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1941
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($group_id) OR empty($object_value) OR empty($object_section_value)) {
For more information execute 'psecio-parse rules LogicalOperators'

1322) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1991
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules LogicalOperators'

1323) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1991
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1324) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 1991
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1325) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2029
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($group_id) OR empty($object_value) OR empty($object_section_value)) {
For more information execute 'psecio-parse rules LogicalOperators'

1326) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2029
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($group_id) OR empty($object_value) OR empty($object_section_value)) {
For more information execute 'psecio-parse rules LogicalOperators'

1327) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2049
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules LogicalOperators'

1328) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2049
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1329) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2049
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1330) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2116
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 				if (@in_array($parent_id, $children_ids) ) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1331) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2116
Evaluation using in_array should enforce type checking (third parameter should be true)
> 				if (@in_array($parent_id, $children_ids) ) {
For more information execute 'psecio-parse rules InArrayStrict'

1332) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2175
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules LogicalOperators'

1333) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2175
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1334) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2175
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1335) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2342
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if (($child_count > 1) AND $reparent_children) {
For more information execute 'psecio-parse rules LogicalOperators'

1336) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2399
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 			case $reparent_children == TRUE:
For more information execute 'psecio-parse rules BooleanIdentity'

1337) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2515
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules LogicalOperators'

1338) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2515
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1339) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2515
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> 		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
For more information execute 'psecio-parse rules BooleanIdentity'

1340) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2576
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($return_hidden==0 AND $object_type != 'acl') {
For more information execute 'psecio-parse rules LogicalOperators'

1341) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 2819
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($section_value) AND empty($value) ) {
For more information execute 'psecio-parse rules LogicalOperators'

1342) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3036
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($order == NULL OR $order == '') {
For more information execute 'psecio-parse rules LogicalOperators'

1343) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3040
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($name) OR empty($section_value) ) {
For more information execute 'psecio-parse rules LogicalOperators'

1344) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3045
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (strlen($name) >= 255 OR strlen($value) >= 230 ) {
For more information execute 'psecio-parse rules LogicalOperators'

1345) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3137
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($object_id) OR empty($section_value) ) {
For more information execute 'psecio-parse rules LogicalOperators'

1346) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3176
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($old[0] != $value OR $old[1] != $section_value) {
For more information execute 'psecio-parse rules LogicalOperators'

1347) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3277
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 			if ($object_type == "aro" OR $object_type == "axo") {
For more information execute 'psecio-parse rules LogicalOperators'

1348) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3369
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($object_type == 'axo' OR $object_type == 'aro') {
For more information execute 'psecio-parse rules LogicalOperators'

1349) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3381
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ( ( isset($acl_ids) AND !empty($acl_ids) ) OR ( isset($groups_ids) AND !empty($groups_ids) ) ) {
For more information execute 'psecio-parse rules LogicalOperators'

1350) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3381
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ( ( isset($acl_ids) AND !empty($acl_ids) ) OR ( isset($groups_ids) AND !empty($groups_ids) ) ) {
For more information execute 'psecio-parse rules LogicalOperators'

1351) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3381
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ( ( isset($acl_ids) AND !empty($acl_ids) ) OR ( isset($groups_ids) AND !empty($groups_ids) ) ) {
For more information execute 'psecio-parse rules LogicalOperators'

1352) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3447
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if (empty($name) AND empty($value) ) {
For more information execute 'psecio-parse rules LogicalOperators'

1353) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3535
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if ($order == NULL OR $order == '') {
For more information execute 'psecio-parse rules LogicalOperators'

1354) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3768
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> 		if($object_ids AND !$erase) {
For more information execute 'psecio-parse rules LogicalOperators'

1355) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3885
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 					if (in_array($value, $tablesToClear) ) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1356) /projects/emr/open-emr/src/openemr/gacl/gacl_api.class.php on line 3885
Evaluation using in_array should enforce type checking (third parameter should be true)
> 					if (in_array($value, $tablesToClear) ) {
For more information execute 'psecio-parse rules InArrayStrict'

1357) /projects/emr/open-emr/src/openemr/common/http/HttpResponseHelper.php on line 51
'header()' calls should not use concatenation directly
>         header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1358) /projects/emr/open-emr/src/openemr/common/logging/Logger.php on line 116
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         } else if ($GLOBALS["log_level"] === "DEBUG" && in_array($level, array("DEBUG", "INFO", "WARN", "ERROR"))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1359) /projects/emr/open-emr/src/openemr/common/logging/Logger.php on line 116
Evaluation using in_array should enforce type checking (third parameter should be true)
>         } else if ($GLOBALS["log_level"] === "DEBUG" && in_array($level, array("DEBUG", "INFO", "WARN", "ERROR"))) {
For more information execute 'psecio-parse rules InArrayStrict'

1360) /projects/emr/open-emr/src/openemr/common/logging/Logger.php on line 118
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         } else if ($GLOBALS["log_level"] === "INFO" && in_array($level, array("INFO", "WARN", "ERROR"))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1361) /projects/emr/open-emr/src/openemr/common/logging/Logger.php on line 118
Evaluation using in_array should enforce type checking (third parameter should be true)
>         } else if ($GLOBALS["log_level"] === "INFO" && in_array($level, array("INFO", "WARN", "ERROR"))) {
For more information execute 'psecio-parse rules InArrayStrict'

1362) /projects/emr/open-emr/src/openemr/common/logging/Logger.php on line 120
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         } else if ($GLOBALS["log_level"] === "WARN" && in_array($level, array("WARN", "ERROR"))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1363) /projects/emr/open-emr/src/openemr/common/logging/Logger.php on line 120
Evaluation using in_array should enforce type checking (third parameter should be true)
>         } else if ($GLOBALS["log_level"] === "WARN" && in_array($level, array("WARN", "ERROR"))) {
For more information execute 'psecio-parse rules InArrayStrict'

1364) /projects/emr/open-emr/src/openemr/index.php on line 18
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die("Site ID '".htmlspecialchars($site_id, ENT_NOQUOTES)."' contains invalid characters.");
For more information execute 'psecio-parse rules ExitOrDie'

1365) /projects/emr/open-emr/src/openemr/contrib/forms/hp_tje_primary/FormHpTjePrimary.class.php on line 365
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (is_numeric($this->id) and !empty($this->checks)) {
For more information execute 'psecio-parse rules LogicalOperators'

1366) /projects/emr/open-emr/src/openemr/contrib/forms/hp_tje_primary/FormHpTjePrimary.class.php on line 377
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (is_numeric($this->id) and !empty($this->history)) {
For more information execute 'psecio-parse rules LogicalOperators'

1367) /projects/emr/open-emr/src/openemr/contrib/forms/hp_tje_primary/FormHpTjePrimary.class.php on line 394
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (is_numeric($this->id) and !empty($this->previous_accidents)) {
For more information execute 'psecio-parse rules LogicalOperators'

1368) /projects/emr/open-emr/src/openemr/contrib/forms/evaluation/FormEvaluation.class.php on line 296
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (is_numeric($this->id) and !empty($this->checks)) {
For more information execute 'psecio-parse rules LogicalOperators'

1369) /projects/emr/open-emr/src/openemr/contrib/forms/scanned_notes/new.php on line 61
Use of system functions, especially with user input, is not recommended
>             $tmp0 = exec("mkdir -p '$imagedir'", $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

1370) /projects/emr/open-emr/src/openemr/contrib/forms/scanned_notes/new.php on line 66
Use of system functions, especially with user input, is not recommended
>             exec("touch '$imagedir/index.html'");
For more information execute 'psecio-parse rules SystemFunctions'

1371) /projects/emr/open-emr/src/openemr/contrib/forms/scanned_notes/new.php on line 86
Use of system functions, especially with user input, is not recommended
>         $tmp0 = exec($cmd, $tmp1, $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

1372) /projects/emr/open-emr/src/openemr/contrib/forms/review_of_systems/FormReviewOfSystems.class.php on line 86
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (is_numeric($this->id) and !empty($this->checks)) {
For more information execute 'psecio-parse rules LogicalOperators'

1373) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 54
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
> $user = 'root';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1374) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 55
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
> $pass = '';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1375) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 66
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
> $con = mysqli_connect($host, $user, $pass, $database) or die("Some error occurred during connection. must enter Host, Username, password, and database in mysqli_connect() " . mysqli_error($con));
For more information execute 'psecio-parse rules LogicalOperators'

1376) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 66
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
> $con = mysqli_connect($host, $user, $pass, $database) or die("Some error occurred during connection. must enter Host, Username, password, and database in mysqli_connect() " . mysqli_error($con));
For more information execute 'psecio-parse rules ExitOrDie'

1377) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 143
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     $query = mysqli_query($con, $removeSS) or print( "\n QUERY '$removeSS' DID NOT WORK.  PLEASE VERIFY THE TABLE AND COLUMN EXISTS \n");
For more information execute 'psecio-parse rules LogicalOperators'

1378) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 218
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         mysqli_query($con, $string) or die("Failed Patient Replacement");
For more information execute 'psecio-parse rules LogicalOperators'

1379) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 268
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             $update = mysqli_query($con, $string) or print("update did not work");
For more information execute 'psecio-parse rules LogicalOperators'

1380) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 297
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         mysqli_query($con, $string) or print "Error altering facility table \n";
For more information execute 'psecio-parse rules LogicalOperators'

1381) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 350
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         mysqli_query($con, $string) or print "Error altering users table \n";
For more information execute 'psecio-parse rules LogicalOperators'

1382) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 380
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     $query = mysqli_query($con, "TRUNCATE TABLE log") or print("\n\n log table not truncated \n\n");
For more information execute 'psecio-parse rules LogicalOperators'

1383) /projects/emr/open-emr/src/openemr/contrib/util/deidentification/deidentification.php on line 381
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     $query = mysqli_query($con, "TRUNCATE TABLE documents") or print("\n\n documents table not truncated \n\n");
For more information execute 'psecio-parse rules LogicalOperators'

1384) /projects/emr/open-emr/src/openemr/contrib/util/de_identification_upgrade.php on line 52
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($fd == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1385) /projects/emr/open-emr/src/openemr/contrib/util/de_identification_upgrade.php on line 120
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (substr($query, -1) == ';'and $proc == 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1386) /projects/emr/open-emr/src/openemr/contrib/util/de_identification_upgrade.php on line 142
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>     die(xlt("Cannot read") . " " . text($sqldir));
For more information execute 'psecio-parse rules ExitOrDie'

1387) /projects/emr/open-emr/src/openemr/contrib/util/de_identification_upgrade.php on line 169
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($dbh == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1388) /projects/emr/open-emr/src/openemr/contrib/util/de_identification_upgrade.php on line 175
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     generic_sql_select_db($sqlconf['dbase']) or die(text(getSqlLastError()));
For more information execute 'psecio-parse rules LogicalOperators'

1389) /projects/emr/open-emr/src/openemr/contrib/util/de_identification_upgrade.php on line 176
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (sqlStatement("GRANT FILE ON *.* TO '$login'@'$loginhost'") == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1390) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 62
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     $rootpass = "";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1391) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 77
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($dbh == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1392) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 87
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (mysql_query("create database $dbname", $dbh) == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1393) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 97
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (mysql_query("GRANT ALL PRIVILEGES ON $dbname.* TO '$login'@'$loginhost' IDENTIFIED BY '$pass'", $dbh) == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1394) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 118
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if ($dbh == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1395) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 128
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> if (mysql_select_db("$dbname", $dbh) == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1396) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 142
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($fd == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1397) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 179
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     $iuser = "admin";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1398) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 183
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (mysql_query("INSERT INTO `groups` (id, name, user) VALUES (1,'$igroup','$iuser')") == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1399) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 191
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (mysql_query("INSERT INTO users (id, username, password, authorized, lname,fname) VALUES (1,'$iuser','9d4e1e23bd5b727046a9e3b4b7db57bd8d6ee684',1,'$iuname','')") == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1400) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 205
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($fd == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1401) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 215
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     fwrite($fd, $string) or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1402) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 216
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     fwrite($fd, "\$host\t= '$host';\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1403) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 217
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     fwrite($fd, "\$port\t= '$port';\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1404) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 218
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     fwrite($fd, "\$login\t= '$login';\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1405) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 219
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     fwrite($fd, "\$pass\t= '$pass';\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1406) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 220
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     fwrite($fd, "\$dbase\t= '$dbname';\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1407) /projects/emr/open-emr/src/openemr/contrib/util/express.php on line 242
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     fwrite($fd, $string) or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1408) /projects/emr/open-emr/src/openemr/contrib/util/dupecheck/mergerecords.php on line 125
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($commitchanges == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1409) /projects/emr/open-emr/src/openemr/contrib/util/dupecheck/mergerecords.php on line 134
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($commitchanges == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1410) /projects/emr/open-emr/src/openemr/contrib/util/dupecheck/mergerecords.php on line 140
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($commitchanges == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1411) /projects/emr/open-emr/src/openemr/contrib/util/dupecheck/mergerecords.php on line 145
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($commitchanges == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1412) /projects/emr/open-emr/src/openemr/contrib/util/dupecheck/mergerecords.php on line 165
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($commitchanges == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1413) /projects/emr/open-emr/src/openemr/contrib/util/dupecheck/mergerecords.php on line 179
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($commitchanges == false) : ?>
For more information execute 'psecio-parse rules BooleanIdentity'

1414) /projects/emr/open-emr/src/openemr/contrib/util/dupecheck/mergerecords.php on line 199
Evaluation with booleans should use strict type checking (ex: if $foo === false)
> <?php if ($commitchanges == true) : ?>
For more information execute 'psecio-parse rules BooleanIdentity'

1415) /projects/emr/open-emr/src/openemr/ccdaservice/ccda_gateway.php on line 25
'header()' calls should not use concatenation directly
>         header('Location: ' . $landingpage);
For more information execute 'psecio-parse rules SetHeaderWithInput'

1416) /projects/emr/open-emr/src/openemr/ccdaservice/ccda_gateway.php on line 74
Avoid the use of an output method (echo, print, etc) directly with a variable
> print_r($h . $ccdaxml . $h);
For more information execute 'psecio-parse rules OutputWithVariable'

1417) /projects/emr/open-emr/src/openemr/ccdaservice/ccda_gateway.php on line 98
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $result = curl_exec($ch) or die(curl_error($ch));
For more information execute 'psecio-parse rules LogicalOperators'

1418) /projects/emr/open-emr/src/openemr/ccdaservice/ccda_gateway.php on line 123
Use of system functions, especially with user input, is not recommended
>             exec($cmd . " > /dev/null &");
For more information execute 'psecio-parse rules SystemFunctions'

1419) /projects/emr/open-emr/src/openemr/library/custom_template/personalize.php on line 314
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>                         $user_sql = "SELECT DISTINCT(tu.tu_user_id),u.fname,u.lname FROM template_users AS tu LEFT OUTER JOIN users AS u ON tu.tu_user_id=u.id WHERE tu.tu_user_id!=?";
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1420) /projects/emr/open-emr/src/openemr/library/custom_template/ajax_code.php on line 123
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     $users ='';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1421) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 209
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if ((strlen($currvalue) == 0 && $lrow ['is_default']) || (strlen($currvalue) > 0 && in_array($lrow ['option_id'], $selectedValues))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1422) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 209
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if ((strlen($currvalue) == 0 && $lrow ['is_default']) || (strlen($currvalue) > 0 && in_array($lrow ['option_id'], $selectedValues))) {
For more information execute 'psecio-parse rules InArrayStrict'

1423) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 261
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($lrow_backup ['option_id'], $selectedValues)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1424) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 261
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($lrow_backup ['option_id'], $selectedValues)) {
For more information execute 'psecio-parse rules InArrayStrict'

1425) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 835
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($option_id, $avalue)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1426) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 835
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($option_id, $avalue)) {
For more information execute 'psecio-parse rules InArrayStrict'

1427) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 1663
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($option_id, $avalue)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1428) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 1663
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($option_id, $avalue)) {
For more information execute 'psecio-parse rules InArrayStrict'

1429) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 2190
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 $checked = in_array($option_id, $avalue);
For more information execute 'psecio-parse rules TypeSafeInArray'

1430) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 2190
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 $checked = in_array($option_id, $avalue);
For more information execute 'psecio-parse rules InArrayStrict'

1431) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 2567
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($option_id, $avalue)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1432) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 2567
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($option_id, $avalue)) {
For more information execute 'psecio-parse rules InArrayStrict'

1433) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 2894
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             $srcvalue = in_array($itemid, $tmp);
For more information execute 'psecio-parse rules TypeSafeInArray'

1434) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 2894
Evaluation using in_array should enforce type checking (third parameter should be true)
>             $srcvalue = in_array($itemid, $tmp);
For more information execute 'psecio-parse rules InArrayStrict'

1435) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 2917
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             $condition = $srcvalue == true;
For more information execute 'psecio-parse rules BooleanIdentity'

1436) /projects/emr/open-emr/src/openemr/library/options.inc.php on line 3534
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(htmlspecialchars(xl('ERROR: Field') . " '$field_id' " . xl('is too long'), ENT_NOQUOTES) .
>         ":<br />&nbsp;<br />".htmlspecialchars($value, ENT_NOQUOTES));
For more information execute 'psecio-parse rules ExitOrDie'

1437) /projects/emr/open-emr/src/openemr/library/ajax/payment_ajax.php on line 72
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo text(strlen($_POST['insurance_text_ajax'])).'~`~`'.$StringForAjax;
For more information execute 'psecio-parse rules OutputWithVariable'

1438) /projects/emr/open-emr/src/openemr/library/ajax/payment_ajax.php on line 154
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo text(strlen($patient_code_complete)).'~`~`'.$StringForAjax;
For more information execute 'psecio-parse rules OutputWithVariable'

1439) /projects/emr/open-emr/src/openemr/library/ajax/adminacl_ajax.php on line 47
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (is_array($_POST["selection"]) && in_array("Emergency Login", $_POST["selection"])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1440) /projects/emr/open-emr/src/openemr/library/ajax/adminacl_ajax.php on line 47
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (is_array($_POST["selection"]) && in_array("Emergency Login", $_POST["selection"])) {
For more information execute 'psecio-parse rules InArrayStrict'

1441) /projects/emr/open-emr/src/openemr/library/ajax/adminacl_ajax.php on line 97
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if ($gacl_protect && in_array("Administrators", $_POST["selection"])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1442) /projects/emr/open-emr/src/openemr/library/ajax/adminacl_ajax.php on line 97
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if ($gacl_protect && in_array("Administrators", $_POST["selection"])) {
For more information execute 'psecio-parse rules InArrayStrict'

1443) /projects/emr/open-emr/src/openemr/library/ajax/adminacl_ajax.php on line 297
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if ((!$username_acl_groups) || (!(in_array($value, $username_acl_groups)))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1444) /projects/emr/open-emr/src/openemr/library/ajax/adminacl_ajax.php on line 297
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if ((!$username_acl_groups) || (!(in_array($value, $username_acl_groups)))) {
For more information execute 'psecio-parse rules InArrayStrict'

1445) /projects/emr/open-emr/src/openemr/library/ajax/adminacl_ajax.php on line 405
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (!array_key_exists($key, $active_aco_objects) || !in_array($value2, $active_aco_objects[$key])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1446) /projects/emr/open-emr/src/openemr/library/ajax/adminacl_ajax.php on line 405
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (!array_key_exists($key, $active_aco_objects) || !in_array($value2, $active_aco_objects[$key])) {
For more information execute 'psecio-parse rules InArrayStrict'

1447) /projects/emr/open-emr/src/openemr/library/ajax/adminacl_ajax.php on line 489
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (!in_array($ret, $returns)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1448) /projects/emr/open-emr/src/openemr/library/ajax/adminacl_ajax.php on line 489
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (!in_array($ret, $returns)) {
For more information execute 'psecio-parse rules InArrayStrict'

1449) /projects/emr/open-emr/src/openemr/library/ajax/ccr_import_ajax.php on line 63
Avoid the use of an output method (echo, print, etc) directly with a variable
>             echo xlt('The requested document is not present at the expected location on the filesystem or there are not sufficient permissions to access it') . '.' . $temp_url;
For more information execute 'psecio-parse rules OutputWithVariable'

1450) /projects/emr/open-emr/src/openemr/library/FeeSheet.class.php on line 946
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($copay_update == true && $update_session_id != '' && $mod0 != '') {
For more information execute 'psecio-parse rules BooleanIdentity'

1451) /projects/emr/open-emr/src/openemr/library/FeeSheet.class.php on line 1094
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                         die(xlt("Insufficient inventory for product ID") . " \"" . text($drug_id) . "\".");
For more information execute 'psecio-parse rules ExitOrDie'

1452) /projects/emr/open-emr/src/openemr/library/FeeSheet.class.php on line 1226
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>                     $newmauser = '1';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1453) /projects/emr/open-emr/src/openemr/library/edihistory/edih_segments.php on line 128
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             $bterr = (in_array($stsegct, $erseg)) ? 'bterr' : 'btseg';
For more information execute 'psecio-parse rules TypeSafeInArray'

1454) /projects/emr/open-emr/src/openemr/library/edihistory/edih_segments.php on line 128
Evaluation using in_array should enforce type checking (third parameter should be true)
>             $bterr = (in_array($stsegct, $erseg)) ? 'bterr' : 'btseg';
For more information execute 'psecio-parse rules InArrayStrict'

1455) /projects/emr/open-emr/src/openemr/library/edihistory/edih_segments.php on line 403
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             $bterr = (in_array($stsegct, $erseg)) ? 'bterr' : 'btseg';
For more information execute 'psecio-parse rules TypeSafeInArray'

1456) /projects/emr/open-emr/src/openemr/library/edihistory/edih_segments.php on line 403
Evaluation using in_array should enforce type checking (third parameter should be true)
>             $bterr = (in_array($stsegct, $erseg)) ? 'bterr' : 'btseg';
For more information execute 'psecio-parse rules InArrayStrict'

1457) /projects/emr/open-emr/src/openemr/library/edihistory/edih_segments.php on line 895
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             $bterr = (in_array($stsegct, $erseg)) ? 'bterr' : 'btseg';
For more information execute 'psecio-parse rules TypeSafeInArray'

1458) /projects/emr/open-emr/src/openemr/library/edihistory/edih_segments.php on line 895
Evaluation using in_array should enforce type checking (third parameter should be true)
>             $bterr = (in_array($stsegct, $erseg)) ? 'bterr' : 'btseg';
For more information execute 'psecio-parse rules InArrayStrict'

1459) /projects/emr/open-emr/src/openemr/library/edihistory/edih_segments.php on line 1163
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 } elseif (in_array($claimid, $st['acct'])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1460) /projects/emr/open-emr/src/openemr/library/edihistory/edih_segments.php on line 1163
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 } elseif (in_array($claimid, $st['acct'])) {
For more information execute 'psecio-parse rules InArrayStrict'

1461) /projects/emr/open-emr/src/openemr/library/edihistory/edih_segments.php on line 1173
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 } elseif (in_array($claimid, $st['bht03'])) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1462) /projects/emr/open-emr/src/openemr/library/edihistory/edih_segments.php on line 1173
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 } elseif (in_array($claimid, $st['bht03'])) {
For more information execute 'psecio-parse rules InArrayStrict'

1463) /projects/emr/open-emr/src/openemr/library/edihistory/edih_uploads.php on line 404
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($fa['type'], $m_types)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1464) /projects/emr/open-emr/src/openemr/library/edihistory/edih_uploads.php on line 404
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($fa['type'], $m_types)) {
For more information execute 'psecio-parse rules InArrayStrict'

1465) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_inc.php on line 448
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die('Failed to create csv folder... ' . text($archive_dir));
For more information execute 'psecio-parse rules ExitOrDie'

1466) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_inc.php on line 480
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die('Setup failed: cannot write to ' . text($basedir));
For more information execute 'psecio-parse rules ExitOrDie'

1467) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_inc.php on line 485
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die('Failed to create history folder... ' . text($edihist_dir));
For more information execute 'psecio-parse rules ExitOrDie'

1468) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_inc.php on line 913
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if (empty($params) || csv_singlerecord_test($params) == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1469) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_inc.php on line 2057
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                         if (!in_array($data[3], array('1', '2', '3', '19', '20', '21'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1470) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_inc.php on line 2057
Evaluation using in_array should enforce type checking (third parameter should be true)
>                         if (!in_array($data[3], array('1', '2', '3', '19', '20', '21'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1471) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_inc.php on line 2062
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (!in_array($data[3], array('1', '2', '3', '19', '20', '21'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1472) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_inc.php on line 2062
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (!in_array($data[3], array('1', '2', '3', '19', '20', '21'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1473) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_data.php on line 113
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                         if (in_array($claim['Status'], array('1', '2', '3', '19', '20', '21'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1474) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_data.php on line 113
Evaluation using in_array should enforce type checking (third parameter should be true)
>                         if (in_array($claim['Status'], array('1', '2', '3', '19', '20', '21'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1475) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_data.php on line 308
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array('f837', $rtypes)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1476) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_data.php on line 308
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array('f837', $rtypes)) {
For more information execute 'psecio-parse rules InArrayStrict'

1477) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_data.php on line 337
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array('f997', $rtypes)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1478) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_data.php on line 337
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array('f997', $rtypes)) {
For more information execute 'psecio-parse rules InArrayStrict'

1479) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_data.php on line 365
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array('f277', $rtypes)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1480) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_data.php on line 365
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array('f277', $rtypes)) {
For more information execute 'psecio-parse rules InArrayStrict'

1481) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_data.php on line 393
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array('f835', $rtypes)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1482) /projects/emr/open-emr/src/openemr/library/edihistory/edih_csv_data.php on line 393
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array('f835', $rtypes)) {
For more information execute 'psecio-parse rules InArrayStrict'

1483) /projects/emr/open-emr/src/openemr/library/edihistory/edih_archive.php on line 308
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($row['FileName'], $filename_array)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1484) /projects/emr/open-emr/src/openemr/library/edihistory/edih_archive.php on line 308
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($row['FileName'], $filename_array)) {
For more information execute 'psecio-parse rules InArrayStrict'

1485) /projects/emr/open-emr/src/openemr/library/edihistory/edih_archive.php on line 941
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (in_array($fa, $types_ar)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1486) /projects/emr/open-emr/src/openemr/library/edihistory/edih_archive.php on line 941
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (in_array($fa, $types_ar)) {
For more information execute 'psecio-parse rules InArrayStrict'

1487) /projects/emr/open-emr/src/openemr/library/edihistory/edih_x12file_class.php on line 145
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if (!$file_text || is_string($file_text) == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1488) /projects/emr/open-emr/src/openemr/library/edihistory/edih_x12file_class.php on line 658
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (!in_array(substr($sn, 0, 3), $chk_segs)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1489) /projects/emr/open-emr/src/openemr/library/edihistory/edih_x12file_class.php on line 658
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (!in_array(substr($sn, 0, 3), $chk_segs)) {
For more information execute 'psecio-parse rules InArrayStrict'

1490) /projects/emr/open-emr/src/openemr/library/edihistory/edih_997_error.php on line 235
By default 'extract' overwrites variables in the local scope
>         extract($err_array['summary'], EXTR_OVERWRITE);
For more information execute 'psecio-parse rules Extract'

1491) /projects/emr/open-emr/src/openemr/library/edihistory/test_edih_sftp_files.php on line 67
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($fa['type'], $m_types)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1492) /projects/emr/open-emr/src/openemr/library/edihistory/test_edih_sftp_files.php on line 67
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($fa['type'], $m_types)) {
For more information execute 'psecio-parse rules InArrayStrict'

1493) /projects/emr/open-emr/src/openemr/library/edihistory/test_edih_sftp_files.php on line 233
By default 'extract' overwrites variables in the local scope
> $get_count = extract($_GET, EXTR_OVERWRITE);
For more information execute 'psecio-parse rules Extract'

1494) /projects/emr/open-emr/src/openemr/library/edihistory/test_edih_sftp_files.php on line 300
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     (((!isset($actn)) || (!(in_array($actn, array_keys($pathmap)))))) ||
For more information execute 'psecio-parse rules TypeSafeInArray'

1495) /projects/emr/open-emr/src/openemr/library/edihistory/test_edih_sftp_files.php on line 300
Evaluation using in_array should enforce type checking (third parameter should be true)
>     (((!isset($actn)) || (!(in_array($actn, array_keys($pathmap)))))) ||
For more information execute 'psecio-parse rules InArrayStrict'

1496) /projects/emr/open-emr/src/openemr/library/edihistory/test_edih_sftp_files.php on line 301
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     (((isset($sub)) && (!(in_array($sub, array_keys($submap))))))
For more information execute 'psecio-parse rules TypeSafeInArray'

1497) /projects/emr/open-emr/src/openemr/library/edihistory/test_edih_sftp_files.php on line 301
Evaluation using in_array should enforce type checking (third parameter should be true)
>     (((isset($sub)) && (!(in_array($sub, array_keys($submap))))))
For more information execute 'psecio-parse rules InArrayStrict'

1498) /projects/emr/open-emr/src/openemr/library/authentication/privDB.php on line 127
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($rez == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1499) /projects/emr/open-emr/src/openemr/library/authentication/login_operations.php on line 86
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>         $password='';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1500) /projects/emr/open-emr/src/openemr/library/authentication/login_operations.php on line 91
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     $password='';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1501) /projects/emr/open-emr/src/openemr/library/authentication/common_operations.php on line 22
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
> define("COL_PWD", "password");
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1502) /projects/emr/open-emr/src/openemr/library/sanitize.inc.php on line 112
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         die(xlt("ERROR: The following variable contains invalid characters").": ". attr($label));
For more information execute 'psecio-parse rules ExitOrDie'

1503) /projects/emr/open-emr/src/openemr/library/sanitize.inc.php on line 167
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (in_array($mimetype, $white_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1504) /projects/emr/open-emr/src/openemr/library/sanitize.inc.php on line 167
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (in_array($mimetype, $white_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

1505) /projects/emr/open-emr/src/openemr/library/sanitize.inc.php on line 172
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($categoryType. '/*', $white_list)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1506) /projects/emr/open-emr/src/openemr/library/sanitize.inc.php on line 172
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($categoryType. '/*', $white_list)) {
For more information execute 'psecio-parse rules InArrayStrict'

1507) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 387
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($dest, array('I', 'D', 'F', 'S', 'FI','FD'))) $dest = 'I';
For more information execute 'psecio-parse rules TypeSafeInArray'

1508) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 387
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($dest, array('I', 'D', 'F', 'S', 'FI','FD'))) $dest = 'I';
For more information execute 'psecio-parse rules InArrayStrict'

1509) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 832
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($this->pdf->getPage(), $this->_hideHeader)) return false;
For more information execute 'psecio-parse rules TypeSafeInArray'

1510) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 832
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($this->pdf->getPage(), $this->_hideHeader)) return false;
For more information execute 'psecio-parse rules InArrayStrict'

1511) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 1182
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($type, array('ul', 'ol'))) $type = 'ul';
For more information execute 'psecio-parse rules TypeSafeInArray'

1512) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 1182
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($type, array('ul', 'ol'))) $type = 'ul';
For more information execute 'psecio-parse rules InArrayStrict'

1513) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 1183
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($style, array('lower-alpha', 'upper-alpha', 'upper-roman', 'lower-roman', 'decimal', 'square', 'circle', 'disc', 'none'))) $style = '';
For more information execute 'psecio-parse rules TypeSafeInArray'

1514) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 1183
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($style, array('lower-alpha', 'upper-alpha', 'upper-roman', 'lower-roman', 'decimal', 'square', 'circle', 'disc', 'none'))) $style = '';
For more information execute 'psecio-parse rules InArrayStrict'

1515) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 1221
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (in_array($action['name'], array('table', 'ul', 'ol')) && !$action['close']) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1516) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 1221
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (in_array($action['name'], array('table', 'ul', 'ol')) && !$action['close']) {
For more information execute 'psecio-parse rules InArrayStrict'

1517) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 2374
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 $page    = in_array('page', $lst);
For more information execute 'psecio-parse rules TypeSafeInArray'

1518) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 2374
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 $page    = in_array('page', $lst);
For more information execute 'psecio-parse rules InArrayStrict'

1519) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 2375
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 $date    = in_array('date', $lst);
For more information execute 'psecio-parse rules TypeSafeInArray'

1520) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 2375
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 $date    = in_array('date', $lst);
For more information execute 'psecio-parse rules InArrayStrict'

1521) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 2376
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 $hour    = in_array('heure', $lst);
For more information execute 'psecio-parse rules TypeSafeInArray'

1522) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 2376
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 $hour    = in_array('heure', $lst);
For more information execute 'psecio-parse rules InArrayStrict'

1523) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 2377
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 $form    = in_array('form', $lst);
For more information execute 'psecio-parse rules TypeSafeInArray'

1524) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 2377
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 $form    = in_array('form', $lst);
For more information execute 'psecio-parse rules InArrayStrict'

1525) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 2695
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($other, array('fieldset', 'legend'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1526) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 2695
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($other, array('fieldset', 'legend'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1527) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 3181
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($param['ec'], array('L', 'M', 'Q', 'H'))) $param['ec'] = 'H';
For more information execute 'psecio-parse rules TypeSafeInArray'

1528) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 3181
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($param['ec'], array('L', 'M', 'Q', 'H'))) $param['ec'] = 'H';
For more information execute 'psecio-parse rules InArrayStrict'

1529) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 3279
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($this->parsingCss->value['id_tag'], array('fieldset', 'legend', 'div', 'table', 'tr', 'td', 'th'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1530) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 3279
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($this->parsingCss->value['id_tag'], array('fieldset', 'legend', 'div', 'table', 'tr', 'td', 'th'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1531) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 4160
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($this->_previousCall, array('_tag_close_P', '_tag_close_UL'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1532) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 4160
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($this->_previousCall, array('_tag_close_P', '_tag_close_UL'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1533) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 4426
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($this->_previousCall, array('_tag_close_P', '_tag_close_UL'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1534) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 4426
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($this->_previousCall, array('_tag_close_P', '_tag_close_UL'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1535) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 4876
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($alignObject, array('left', 'center', 'right'))) $alignObject = 'left';
For more information execute 'psecio-parse rules TypeSafeInArray'

1536) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 4876
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($alignObject, array('left', 'center', 'right'))) $alignObject = 'left';
For more information execute 'psecio-parse rules InArrayStrict'

1537) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 5039
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($k, HTML2PDF::$_tables[$param['num']]['thead']['tr'])) continue;
For more information execute 'psecio-parse rules TypeSafeInArray'

1538) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 5039
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($k, HTML2PDF::$_tables[$param['num']]['thead']['tr'])) continue;
For more information execute 'psecio-parse rules InArrayStrict'

1539) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 5040
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($k, HTML2PDF::$_tables[$param['num']]['tfoot']['tr'])) continue;
For more information execute 'psecio-parse rules TypeSafeInArray'

1540) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 5040
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($k, HTML2PDF::$_tables[$param['num']]['tfoot']['tr'])) continue;
For more information execute 'psecio-parse rules InArrayStrict'

1541) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 5346
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($other, array('td', 'th'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1542) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 5346
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($other, array('td', 'th'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1543) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 5400
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($other, array('td', 'th'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1544) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 5400
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($other, array('td', 'th'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1545) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 5869
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($param['type'], array('text', 'checkbox', 'radio', 'hidden', 'submit', 'reset', 'button'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1546) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 5869
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($param['type'], array('text', 'checkbox', 'radio', 'hidden', 'submit', 'reset', 'button'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1547) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 6385
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($lastAction, array('z', 'Z'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1548) /projects/emr/open-emr/src/openemr/library/html2pdf/html2pdf.class.php on line 6385
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($lastAction, array('z', 'Z'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1549) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/tcpdfConfig.php on line 46
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     if ((!isset($_SERVER['DOCUMENT_ROOT'])) OR (empty($_SERVER['DOCUMENT_ROOT']))) {
For more information execute 'psecio-parse rules LogicalOperators'

1550) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/tcpdfConfig.php on line 96
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     if (isset($_SERVER['HTTP_HOST']) AND (!empty($_SERVER['HTTP_HOST']))) {
For more information execute 'psecio-parse rules LogicalOperators'

1551) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/tcpdfConfig.php on line 97
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off') {
For more information execute 'psecio-parse rules LogicalOperators'

1552) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/tcpdfConfig.php on line 97
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off') {
For more information execute 'psecio-parse rules LogicalOperators'

1553) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 181
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($tagName, array('tr', 'td', 'th', 'thead', 'tbody', 'tfoot'))) $collapse = false;
For more information execute 'psecio-parse rules TypeSafeInArray'

1554) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 181
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($tagName, array('tr', 'td', 'th', 'thead', 'tbody', 'tfoot'))) $collapse = false;
For more information execute 'psecio-parse rules InArrayStrict'

1555) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 213
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($tagName, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1556) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 213
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($tagName, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1557) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 217
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($tagName, array('input', 'select', 'textarea'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1558) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 217
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($tagName, array('input', 'select', 'textarea'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1559) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 236
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($tagName, array('blockquote', 'div', 'fieldset'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1560) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 236
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($tagName, array('blockquote', 'div', 'fieldset'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1561) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 240
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($tagName, array('fieldset', 'legend'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1562) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 240
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($tagName, array('fieldset', 'legend'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1563) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 256
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($tagName, array('ul', 'li'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1564) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 256
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($tagName, array('ul', 'li'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1565) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 261
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($tagName, array('tr', 'td'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1566) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 261
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($tagName, array('tr', 'td'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1567) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 627
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     $this->value['font-underline']   = (in_array('underline', $val));
For more information execute 'psecio-parse rules TypeSafeInArray'

1568) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 627
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     $this->value['font-underline']   = (in_array('underline', $val));
For more information execute 'psecio-parse rules InArrayStrict'

1569) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 628
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     $this->value['font-overline']    = (in_array('overline', $val));
For more information execute 'psecio-parse rules TypeSafeInArray'

1570) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 628
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     $this->value['font-overline']    = (in_array('overline', $val));
For more information execute 'psecio-parse rules InArrayStrict'

1571) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 629
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     $this->value['font-linethrough'] = (in_array('line-through', $val));
For more information execute 'psecio-parse rules TypeSafeInArray'

1572) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 629
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     $this->value['font-linethrough'] = (in_array('line-through', $val));
For more information execute 'psecio-parse rules InArrayStrict'

1573) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 637
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (!in_array($val, array('none', 'capitalize', 'uppercase', 'lowercase'))) $val = 'none';
For more information execute 'psecio-parse rules TypeSafeInArray'

1574) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 637
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (!in_array($val, array('none', 'capitalize', 'uppercase', 'lowercase'))) $val = 'none';
For more information execute 'psecio-parse rules InArrayStrict'

1575) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 659
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (!in_array($val, array('left', 'right', 'center', 'justify', 'li_right'))) $val = 'left';
For more information execute 'psecio-parse rules TypeSafeInArray'

1576) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 659
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (!in_array($val, array('left', 'right', 'center', 'justify', 'li_right'))) $val = 'left';
For more information execute 'psecio-parse rules InArrayStrict'

1577) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 683
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (!in_array($val, array(0, -90, 90, 180, 270, -180, -270))) $val = null;
For more information execute 'psecio-parse rules TypeSafeInArray'

1578) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 683
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (!in_array($val, array(0, -90, 90, 180, 270, -180, -270))) $val = null;
For more information execute 'psecio-parse rules InArrayStrict'

1579) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 689
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (!in_array($val, array('visible', 'hidden'))) $val = 'visible';
For more information execute 'psecio-parse rules TypeSafeInArray'

1580) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 689
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (!in_array($val, array('visible', 'hidden'))) $val = 'visible';
For more information execute 'psecio-parse rules InArrayStrict'

1581) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 776
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                         if (!in_array($valV, array('solid', 'dotted', 'dashed'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1582) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 776
Evaluation using in_array should enforce type checking (third parameter should be true)
>                         if (!in_array($valV, array('solid', 'dotted', 'dashed'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1583) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 788
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (in_array($val, array('solid', 'dotted', 'dashed'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1584) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 788
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (in_array($val, array('solid', 'dotted', 'dashed'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1585) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 794
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (in_array($val, array('solid', 'dotted', 'dashed'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1586) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 794
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (in_array($val, array('solid', 'dotted', 'dashed'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1587) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 800
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (in_array($val, array('solid', 'dotted', 'dashed'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1588) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 800
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (in_array($val, array('solid', 'dotted', 'dashed'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1589) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 806
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (in_array($val, array('solid', 'dotted', 'dashed')))
For more information execute 'psecio-parse rules TypeSafeInArray'

1590) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 806
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (in_array($val, array('solid', 'dotted', 'dashed')))
For more information execute 'psecio-parse rules InArrayStrict'

1591) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 1036
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if ($noWidth && in_array($tagName, array('div', 'blockquote', 'fieldset')) && $this->value['position']!='absolute') {
For more information execute 'psecio-parse rules TypeSafeInArray'

1592) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 1036
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if ($noWidth && in_array($tagName, array('div', 'blockquote', 'fieldset')) && $this->value['position']!='absolute') {
For more information execute 'psecio-parse rules InArrayStrict'

1593) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 1041
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (!in_array($tagName, array('table', 'div', 'blockquote', 'fieldset', 'hr'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1594) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 1041
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (!in_array($tagName, array('table', 'div', 'blockquote', 'fieldset', 'hr'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1595) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 1045
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($tagName, array('th', 'td'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1596) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 1045
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($tagName, array('th', 'td'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1597) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 1317
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             } else if (in_array($value, array('solid', 'dotted', 'dashed', 'double'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1598) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingCss.class.php on line 1317
Evaluation using in_array should enforce type checking (third parameter should be true)
>             } else if (in_array($value, array('solid', 'dotted', 'dashed', 'double'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1599) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/myPdf.class.php on line 587
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (!$rtloff AND $this->rtl) {
For more information execute 'psecio-parse rules LogicalOperators'

1600) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/myPdf.class.php on line 607
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (!$rtloff AND $this->rtl) {
For more information execute 'psecio-parse rules LogicalOperators'

1601) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/myPdf.class.php on line 631
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if (!$rtloff AND $this->rtl) {
For more information execute 'psecio-parse rules LogicalOperators'

1602) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingHtml.class.php on line 111
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (!in_array($res['name'], $tagsNotClosed)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1603) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingHtml.class.php on line 111
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (!in_array($res['name'], $tagsNotClosed)) {
For more information execute 'psecio-parse rules InArrayStrict'

1604) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingHtml.class.php on line 206
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if ($k>0 && in_array($actions[$k - 1]['name'], $tagsToClean))
For more information execute 'psecio-parse rules TypeSafeInArray'

1605) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingHtml.class.php on line 206
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if ($k>0 && in_array($actions[$k - 1]['name'], $tagsToClean))
For more information execute 'psecio-parse rules InArrayStrict'

1606) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingHtml.class.php on line 210
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if ($k < $nb - 1 && in_array($actions[$k + 1]['name'], $tagsToClean))
For more information execute 'psecio-parse rules TypeSafeInArray'

1607) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingHtml.class.php on line 210
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if ($k < $nb - 1 && in_array($actions[$k + 1]['name'], $tagsToClean))
For more information execute 'psecio-parse rules InArrayStrict'

1608) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingHtml.class.php on line 433
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($name, array('ul', 'ol', 'table')) && !$close) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1609) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingHtml.class.php on line 433
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($name, array('ul', 'ol', 'table')) && !$close) {
For more information execute 'psecio-parse rules InArrayStrict'

1610) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingHtml.class.php on line 444
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (in_array($name, array('ul', 'ol', 'table')) && $close) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1611) /projects/emr/open-emr/src/openemr/library/html2pdf/_class/parsingHtml.class.php on line 444
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (in_array($name, array('ul', 'ol', 'table')) && $close) {
For more information execute 'psecio-parse rules InArrayStrict'

1612) /projects/emr/open-emr/src/openemr/library/appointments.inc.php on line 239
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                     if ($excluded == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1613) /projects/emr/open-emr/src/openemr/library/appointments.inc.php on line 310
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                         if ($excluded == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1614) /projects/emr/open-emr/src/openemr/library/core/src/Header.php on line 138
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if ($autoload === true || in_array($k, $selectedAssets) || ($loadInFile && $loadInFile === self::getCurrentFile())) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1615) /projects/emr/open-emr/src/openemr/library/core/src/Header.php on line 138
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if ($autoload === true || in_array($k, $selectedAssets) || ($loadInFile && $loadInFile === self::getCurrentFile())) {
For more information execute 'psecio-parse rules InArrayStrict'

1616) /projects/emr/open-emr/src/openemr/library/core/src/Header.php on line 140
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (in_array("no_" . $k, $selectedAssets)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1617) /projects/emr/open-emr/src/openemr/library/core/src/Header.php on line 140
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (in_array("no_" . $k, $selectedAssets)) {
For more information execute 'psecio-parse rules InArrayStrict'

1618) /projects/emr/open-emr/src/openemr/library/formdata.inc.php on line 235
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                     die("<br><span style='color:red;font-weight:bold;'>".xlt("There was an OpenEMR SQL Escaping ERROR of the following string")." ".text($s)."</span><br>");
For more information execute 'psecio-parse rules ExitOrDie'

1619) /projects/emr/open-emr/src/openemr/library/sql_upgrade_fx.php on line 581
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>     if ($fd == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1620) /projects/emr/open-emr/src/openemr/library/sql_upgrade_fx.php on line 851
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (in_array($t, $tables_skip_migration)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1621) /projects/emr/open-emr/src/openemr/library/sql_upgrade_fx.php on line 851
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (in_array($t, $tables_skip_migration)) {
For more information execute 'psecio-parse rules InArrayStrict'

1622) /projects/emr/open-emr/src/openemr/library/dated_reminder_functions.php on line 246
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     if (is_numeric($rID) and $rID > 0) {
For more information execute 'psecio-parse rules LogicalOperators'

1623) /projects/emr/open-emr/src/openemr/library/dated_reminder_functions.php on line 306
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>         preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $dueDate) and
> // ------- check priority, only allow 1-3
>         intval($priority) <= 3 and
> // ------- check message, only up to 255 characters
>         strlen($message) <= 255 and strlen($message) > 0 and
> // ------- check if PatientID is set and in numeric
>         is_numeric($patID)
For more information execute 'psecio-parse rules LogicalOperators'

1624) /projects/emr/open-emr/src/openemr/library/dated_reminder_functions.php on line 306
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>         preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $dueDate) and
> // ------- check priority, only allow 1-3
>         intval($priority) <= 3 and
> // ------- check message, only up to 255 characters
>         strlen($message) <= 255 and strlen($message) > 0 and
For more information execute 'psecio-parse rules LogicalOperators'

1625) /projects/emr/open-emr/src/openemr/library/dated_reminder_functions.php on line 306
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>         preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $dueDate) and
> // ------- check priority, only allow 1-3
>         intval($priority) <= 3 and
> // ------- check message, only up to 255 characters
>         strlen($message) <= 255 and strlen($message) > 0 and
For more information execute 'psecio-parse rules LogicalOperators'

1626) /projects/emr/open-emr/src/openemr/library/dated_reminder_functions.php on line 306
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>         preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $dueDate) and
> // ------- check priority, only allow 1-3
>         intval($priority) <= 3 and
For more information execute 'psecio-parse rules LogicalOperators'

1627) /projects/emr/open-emr/src/openemr/library/dated_reminder_functions.php on line 306
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         !empty($sendTo) and
> // ------- check dueDate, only allow valid dates, todo -> enhance date checker
>         preg_match('/\d{4}[-]\d{2}[-]\d{2}/', $dueDate) and
For more information execute 'psecio-parse rules LogicalOperators'

1628) /projects/emr/open-emr/src/openemr/library/dated_reminder_functions.php on line 393
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     if (isset($_GET['processed']) and !isset($_GET['pending'])) {
For more information execute 'psecio-parse rules LogicalOperators'

1629) /projects/emr/open-emr/src/openemr/library/dated_reminder_functions.php on line 395
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     } elseif (!isset($_GET['processed']) and isset($_GET['pending'])) {
For more information execute 'psecio-parse rules LogicalOperators'

1630) /projects/emr/open-emr/src/openemr/library/dated_reminder_functions.php on line 401
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     if (isset($_GET['sd']) and $_GET['sd'] != '') {
For more information execute 'psecio-parse rules LogicalOperators'

1631) /projects/emr/open-emr/src/openemr/library/dated_reminder_functions.php on line 406
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     if (isset($_GET['ed']) and $_GET['ed'] != '') {
For more information execute 'psecio-parse rules LogicalOperators'

1632) /projects/emr/open-emr/src/openemr/library/oeUI/src/OemrUI.php on line 276
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $help_modal. "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1633) /projects/emr/open-emr/src/openemr/library/oeUI/src/OemrUI.php on line 298
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $jquery_draggable. "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1634) /projects/emr/open-emr/src/openemr/library/oeUI/src/OemrUI.php on line 367
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $header_expand_js ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1635) /projects/emr/open-emr/src/openemr/library/oeUI/src/OemrUI.php on line 389
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $action_top_js ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1636) /projects/emr/open-emr/src/openemr/library/oeUI/src/OemrUI.php on line 419
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $action_bot_js . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1637) /projects/emr/open-emr/src/openemr/library/gen_x12_837i.inc.php on line 777
The third parameter should be set (and be true) on in_array to avoid type switching issues
>     if (! $claim->providerNPI() && in_array($claim->providerNumberType(), array('0B', '1G', 'G2', 'LU'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1638) /projects/emr/open-emr/src/openemr/library/gen_x12_837i.inc.php on line 777
Evaluation using in_array should enforce type checking (third parameter should be true)
>     if (! $claim->providerNPI() && in_array($claim->providerNumberType(), array('0B', '1G', 'G2', 'LU'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1639) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 144
The third parameter should be set (and be true) on in_array to avoid type switching issues
>             if (in_array($urow['id'], $facilities)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1640) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 144
Evaluation using in_array should enforce type checking (third parameter should be true)
>             if (in_array($urow['id'], $facilities)) {
For more information execute 'psecio-parse rules InArrayStrict'

1641) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 425
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                         if ($results==false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1642) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 483
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                     if ($results==false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1643) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 654
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                     if ($results==false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1644) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 729
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                     if (($v <= 0) || (empty($event['providers'])) || (!in_array($k, $all_providers))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1645) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 729
Evaluation using in_array should enforce type checking (third parameter should be true)
>                     if (($v <= 0) || (empty($event['providers'])) || (!in_array($k, $all_providers))) {
For more information execute 'psecio-parse rules InArrayStrict'

1646) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 754
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                         if ($results==false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1647) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 805
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                     if ($results==false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1648) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 957
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                     if ($results==false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1649) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 1142
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if ($needle===$value or (is_array($value) && $this->recursive_array_search($needle, $value))) {
For more information execute 'psecio-parse rules LogicalOperators'

1650) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 1259
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                     if ($excluded == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1651) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 1318
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                         if ($excluded == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1652) /projects/emr/open-emr/src/openemr/library/MedEx/API.php on line 2113
Avoid the use of an output method (echo, print, etc) directly with a variable
>                                     <div class="col-sm-<?php echo $col_width." ".$last_col_width; ?> text-center" >
For more information execute 'psecio-parse rules OutputWithVariable'

1653) /projects/emr/open-emr/src/openemr/library/classes/fpdf/fpdf.php on line 505
The third parameter should be set (and be true) on in_array to avoid type switching issues
> 		if(in_array($family,$this->CoreFonts))
For more information execute 'psecio-parse rules TypeSafeInArray'

1654) /projects/emr/open-emr/src/openemr/library/classes/fpdf/fpdf.php on line 505
Evaluation using in_array should enforce type checking (third parameter should be true)
> 		if(in_array($family,$this->CoreFonts))
For more information execute 'psecio-parse rules InArrayStrict'

1655) /projects/emr/open-emr/src/openemr/library/classes/fpdf/fpdf.php on line 1008
'header()' calls should not use concatenation directly
> 				header('Content-Disposition: inline; '.$this->_httpencode('filename',$name,$isUTF8));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1656) /projects/emr/open-emr/src/openemr/library/classes/fpdf/fpdf.php on line 1018
'header()' calls should not use concatenation directly
> 			header('Content-Disposition: attachment; '.$this->_httpencode('filename',$name,$isUTF8));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1657) /projects/emr/open-emr/src/openemr/library/classes/InsuranceCompany.class.php on line 280
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($found == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1658) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 263
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($sql_results_temp == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1659) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 278
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($fd == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1660) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 342
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($this->execute_sql("UPDATE version SET v_major = '" . $this->escapeSql($v_major) . "', v_minor = '" . $this->escapeSql($v_minor) . "', v_patch = '" . $this->escapeSql($v_patch) . "', v_realpatch = '" . $this->escapeSql($v_realpatch) . "', v_tag = '" . $this->escapeSql($v_tag) . "', v_database = '" . $this->escapeSql($v_database) . "', v_acl = '" . $this->escapeSql($v_acl) . "'") == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1661) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 353
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($this->execute_sql("INSERT INTO `groups` (id, name, user) VALUES (1,'" . $this->escapeSql($this->igroup) . "','" . $this->escapeSql($this->iuser) . "')") == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1662) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 359
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>         $password_hash = "NoLongerUsed";  // This is the value to insert into the password column in the "users" table. password details are now being stored in users_secure instead.
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1663) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 362
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($this->execute_sql("INSERT INTO users (id, username, password, authorized, lname, fname, facility_id, calendar, cal_ui) VALUES (1,'" . $this->escapeSql($this->iuser) . "','" . $this->escapeSql($password_hash) . "',1,'" . $this->escapeSql($this->iuname) . "','" . $this->escapeSql($this->iufname) . "',3,1,3)") == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1664) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 369
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($this->execute_sql("INSERT INTO users_secure (id, username, password, salt) VALUES (1,'" . $this->escapeSql($this->iuser) . "','" . $this->escapeSql($hash) . "','" . $this->escapeSql($salt) . "')") == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1665) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 379
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($this->execute_sql("INSERT INTO login_mfa_registrations (user_id, name, method, var1, var2) VALUES (1, 'App Based 2FA', 'TOTP', '".$this->escapeSql($secret)."', '')") == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1666) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 387
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($this->load_file($this->additional_users, "Additional Official Users") == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1667) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 444
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         fwrite($fd, $string) or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1668) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 445
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         fwrite($fd, "\$host\t= '$this->server';\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1669) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 446
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         fwrite($fd, "\$port\t= '$this->port';\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1670) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 447
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         fwrite($fd, "\$login\t= '$this->login';\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1671) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 448
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         fwrite($fd, "\$pass\t= '$this->pass';\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1672) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 449
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         fwrite($fd, "\$dbase\t= '$this->dbname';\n\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1673) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 450
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         fwrite($fd, "//Added ability to disable\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1674) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 451
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         fwrite($fd, "//utf8 encoding - bm 05-2009\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1675) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 452
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         fwrite($fd, "global \$disable_utf8_flag;\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1676) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 453
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         fwrite($fd, "\$disable_utf8_flag = false;\n") or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1677) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 475
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     fwrite($fd, $string) or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1678) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 476
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>     fclose($fd) or $it_died++;
For more information execute 'psecio-parse rules LogicalOperators'

1679) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 821
Use of system functions, especially with user input, is not recommended
>         $tmp0 = exec($cmd, $tmp1 = array(), $tmp2);
For more information execute 'psecio-parse rules SystemFunctions'

1680) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 823
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("Error $tmp2 running \"$cmd\": $tmp0 " . implode(' ', $tmp1));
For more information execute 'psecio-parse rules ExitOrDie'

1681) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 898
Avoid the use of an output method (echo, print, etc) directly with a variable
>                     echo $div_start . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1682) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 899
Avoid the use of an output method (echo, print, etc) directly with a variable
>                     echo $img_div . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1683) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 906
Avoid the use of an output method (echo, print, etc) directly with a variable
>                     echo $img_div . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1684) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 910
Avoid the use of an output method (echo, print, etc) directly with a variable
>                     echo $img_div . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1685) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 911
Avoid the use of an output method (echo, print, etc) directly with a variable
>                     echo $div_end . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1686) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 916
Avoid the use of an output method (echo, print, etc) directly with a variable
>                     echo $div_start . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1687) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 918
Avoid the use of an output method (echo, print, etc) directly with a variable
>                     echo $div_end . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1688) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 946
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $display_selected_theme_div . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1689) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 970
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $display_selected_theme_div . "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1690) /projects/emr/open-emr/src/openemr/library/classes/Installer.class.php on line 1019
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $setup_help_modal  ."\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1691) /projects/emr/open-emr/src/openemr/library/classes/RXList.class.php on line 178
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             if ($pos === ($record + 1) and ($ending != "")) {
For more information execute 'psecio-parse rules LogicalOperators'

1692) /projects/emr/open-emr/src/openemr/library/classes/Document.class.php on line 221
Use of system functions, especially with user input, is not recommended
>             $mimetype = exec($command);
For more information execute 'psecio-parse rules SystemFunctions'

1693) /projects/emr/open-emr/src/openemr/library/classes/ORDataObject.class.php on line 33
The third parameter should be set (and be true) on in_array to avoid type switching issues
>                 if (in_array($field, $pkeys)  && empty($val)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1694) /projects/emr/open-emr/src/openemr/library/classes/ORDataObject.class.php on line 33
Evaluation using in_array should enforce type checking (third parameter should be true)
>                 if (in_array($field, $pkeys)  && empty($val)) {
For more information execute 'psecio-parse rules InArrayStrict'

1695) /projects/emr/open-emr/src/openemr/library/classes/TreeMenu.php on line 700
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         if ($this->maxDepth > 0 and $currentDepth == $this->maxDepth) {
For more information execute 'psecio-parse rules LogicalOperators'

1696) /projects/emr/open-emr/src/openemr/library/classes/ClinicalTypes/Characteristic.php on line 14
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     const TOBACCO_USER = 'char_tobacco_user';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1697) /projects/emr/open-emr/src/openemr/library/classes/ClinicalTypes/Characteristic.php on line 15
Avoid hard-coding sensitive values (ex. "username", "password", etc.)
>     const TOBACCO_NON_USER = 'char_tobacco_non_user';
For more information execute 'psecio-parse rules HardcodedSensitiveValues'

1698) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 61
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1699) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 61
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1700) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 122
Don't use eval. Ever.
>             eval($ar_string);
For more information execute 'psecio-parse rules EvalFunction'

1701) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 156
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1702) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 156
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1703) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 169
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1704) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 169
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1705) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 188
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $this->_db->Execute($sql) or die("Error: $sql" . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1706) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 188
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $this->_db->Execute($sql) or die("Error: $sql" . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1707) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 207
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1708) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 207
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1709) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 210
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("You cannot add a node with the name '" . $name ."' because one already exists under parent " . $parent_id . "<br>");
For more information execute 'psecio-parse rules ExitOrDie'

1710) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 214
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1711) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 214
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1712) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 223
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1713) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 223
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1714) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 225
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1715) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 225
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1716) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 232
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $this->_db->Execute($sql) or die("Error: $sql :: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1717) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 232
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $this->_db->Execute($sql) or die("Error: $sql :: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1718) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 251
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $result = $this->_db->Execute($sql) or die(xlt('Error') . ": " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1719) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 251
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $result = $this->_db->Execute($sql) or die(xlt('Error') . ": " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1720) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 253
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>               die(xlt('This name already exists under this parent.') . "<br>");
For more information execute 'psecio-parse rules ExitOrDie'

1721) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 259
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $this->_db->Execute($sql) or die(xlt('Error') . ": " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1722) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 259
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $this->_db->Execute($sql) or die(xlt('Error') . ": " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1723) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 274
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1724) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 274
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $result = $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1725) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 288
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1726) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 288
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1727) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 292
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1728) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 292
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1729) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 296
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1730) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 296
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1731) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 302
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>             $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1732) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 302
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1733) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 307
Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules LogicalOperators'

1734) /projects/emr/open-emr/src/openemr/library/classes/Tree.class.php on line 307
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>         $this->_db->Execute($sql) or die("Error: " . $this->_db->ErrorMsg());
For more information execute 'psecio-parse rules ExitOrDie'

1735) /projects/emr/open-emr/src/openemr/library/classes/Controller.class.php on line 129
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 if ($c_obj->_state == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1736) /projects/emr/open-emr/src/openemr/library/classes/Controller.class.php on line 141
Avoid the use of an output method (echo, print, etc) directly with a variable
>                 echo "The action trying to be performed: " . $c_action ." does not exist controller: ". $name;
For more information execute 'psecio-parse rules OutputWithVariable'

1737) /projects/emr/open-emr/src/openemr/library/classes/smtp/smtp.php on line 727
Remove any use of ereg functions, deprecated as of PHP 5.3.0. Use preg_
> 			$output=ereg_replace("(^|\n)\\.","\\1..",ereg_replace("\r([^\n]|\$)","\r\n\\1",ereg_replace("(^|[^\r])\n","\\1\r\n",ereg_replace("\n\n|\r\r","\r\n\r\n",$data))));
For more information execute 'psecio-parse rules EregFunctions'

1738) /projects/emr/open-emr/src/openemr/library/classes/smtp/smtp.php on line 727
Remove any use of ereg functions, deprecated as of PHP 5.3.0. Use preg_
> 			$output=ereg_replace("(^|\n)\\.","\\1..",ereg_replace("\r([^\n]|\$)","\r\n\\1",ereg_replace("(^|[^\r])\n","\\1\r\n",ereg_replace("\n\n|\r\r","\r\n\r\n",$data))));
For more information execute 'psecio-parse rules EregFunctions'

1739) /projects/emr/open-emr/src/openemr/library/classes/smtp/smtp.php on line 727
Remove any use of ereg functions, deprecated as of PHP 5.3.0. Use preg_
> 			$output=ereg_replace("(^|\n)\\.","\\1..",ereg_replace("\r([^\n]|\$)","\r\n\\1",ereg_replace("(^|[^\r])\n","\\1\r\n",ereg_replace("\n\n|\r\r","\r\n\r\n",$data))));
For more information execute 'psecio-parse rules EregFunctions'

1740) /projects/emr/open-emr/src/openemr/library/classes/smtp/smtp.php on line 727
Remove any use of ereg functions, deprecated as of PHP 5.3.0. Use preg_
> 			$output=ereg_replace("(^|\n)\\.","\\1..",ereg_replace("\r([^\n]|\$)","\r\n\\1",ereg_replace("(^|[^\r])\n","\\1\r\n",ereg_replace("\n\n|\r\r","\r\n\r\n",$data))));
For more information execute 'psecio-parse rules EregFunctions'

1741) /projects/emr/open-emr/src/openemr/library/classes/Pharmacy.class.php on line 165
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         if ($found == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1742) /projects/emr/open-emr/src/openemr/library/classes/rulesets/Amc/reports/AMC_314g_1_2_20/Numerator.php on line 39
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!in_array($patient->id, $this->patArr)) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1743) /projects/emr/open-emr/src/openemr/library/classes/rulesets/Amc/reports/AMC_314g_1_2_20/Numerator.php on line 39
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!in_array($patient->id, $this->patArr)) {
For more information execute 'psecio-parse rules InArrayStrict'

1744) /projects/emr/open-emr/src/openemr/library/billing/src/Claim.php on line 1403
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                         if ($strip_periods==true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1745) /projects/emr/open-emr/src/openemr/library/billing/src/X12_5010_837P.php on line 862
The third parameter should be set (and be true) on in_array to avoid type switching issues
>         if (!$claim->providerNPI() && in_array($claim->providerNumberType(), array('0B', '1G', 'G2', 'LU'))) {
For more information execute 'psecio-parse rules TypeSafeInArray'

1746) /projects/emr/open-emr/src/openemr/library/billing/src/X12_5010_837P.php on line 862
Evaluation using in_array should enforce type checking (third parameter should be true)
>         if (!$claim->providerNPI() && in_array($claim->providerNumberType(), array('0B', '1G', 'G2', 'LU'))) {
For more information execute 'psecio-parse rules InArrayStrict'

1747) /projects/emr/open-emr/src/openemr/library/billing/src/BillingUtilities.php on line 1655
Avoid the use of an output method (echo, print, etc) directly with a variable
>             $newdigs = sprintf('%0' . strlen($matches[2]) . 'd', $matches[2] + 1);
For more information execute 'psecio-parse rules OutputWithVariable'

1748) /projects/emr/open-emr/src/openemr/library/translation.inc.php on line 98
Avoid the use of an output method (echo, print, etc) directly with a variable
>             echo $prepend.$constant.$append;
For more information execute 'psecio-parse rules OutputWithVariable'

1749) /projects/emr/open-emr/src/openemr/library/translation.inc.php on line 118
Avoid the use of an output method (echo, print, etc) directly with a variable
>             echo $prepend.$constant.$append;
For more information execute 'psecio-parse rules OutputWithVariable'

1750) /projects/emr/open-emr/src/openemr/library/translation.inc.php on line 139
Avoid the use of an output method (echo, print, etc) directly with a variable
>             echo $prepend.$constant.$append;
For more information execute 'psecio-parse rules OutputWithVariable'

1751) /projects/emr/open-emr/src/openemr/library/translation.inc.php on line 160
Avoid the use of an output method (echo, print, etc) directly with a variable
>             echo $prepend.$constant.$append;
For more information execute 'psecio-parse rules OutputWithVariable'

1752) /projects/emr/open-emr/src/openemr/library/translation.inc.php on line 182
Avoid the use of an output method (echo, print, etc) directly with a variable
>             echo $prepend.$constant.$append;
For more information execute 'psecio-parse rules OutputWithVariable'

1753) /projects/emr/open-emr/src/openemr/library/translation.inc.php on line 204
Avoid the use of an output method (echo, print, etc) directly with a variable
>             echo $prepend.$constant.$append;
For more information execute 'psecio-parse rules OutputWithVariable'

1754) /projects/emr/open-emr/src/openemr/library/menu/src/PatientMenuRole.php on line 54
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("\nJSON ERROR: " . json_last_error());
For more information execute 'psecio-parse rules ExitOrDie'

1755) /projects/emr/open-emr/src/openemr/library/menu/src/PatientMenuRole.php on line 180
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $str_top. "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1756) /projects/emr/open-emr/src/openemr/library/menu/src/PatientMenuRole.php on line 202
Avoid the use of an output method (echo, print, etc) directly with a variable
>             echo $list. "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1757) /projects/emr/open-emr/src/openemr/library/menu/src/PatientMenuRole.php on line 211
Avoid the use of an output method (echo, print, etc) directly with a variable
>         echo $str_bot. "\r\n";
For more information execute 'psecio-parse rules OutputWithVariable'

1758) /projects/emr/open-emr/src/openemr/library/menu/src/MainMenuRole.php on line 55
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("\nJSON ERROR: " . json_last_error());
For more information execute 'psecio-parse rules ExitOrDie'

1759) /projects/emr/open-emr/src/openemr/controllers/C_Prescription.class.php on line 144
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($rxn == false) {
For more information execute 'psecio-parse rules BooleanIdentity'

1760) /projects/emr/open-emr/src/openemr/controllers/C_Prescription.class.php on line 146
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             } elseif ($rxn == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1761) /projects/emr/open-emr/src/openemr/controllers/C_Prescription.class.php on line 983
Use of system functions, especially with user input, is not recommended
>                 exec($cmd . $args);
For more information execute 'psecio-parse rules SystemFunctions'

1762) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 317
Avoid the use of an output method (echo, print, etc) directly with a variable
>                 echo xl('The requested document is not present at the expected location on the filesystem or there are not sufficient permissions to access it.', '', '', ' ') . $temp_url;
For more information execute 'psecio-parse rules OutputWithVariable'

1763) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 540
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($disable_exit == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1764) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 550
'header()' calls should not use concatenation directly
>                 header('Content-Disposition: attachment; filename="' . basename_international("/encrypted_aes_".$d->get_url_file()) . '"');
For more information execute 'psecio-parse rules SetHeaderWithInput'

1765) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 552
'header()' calls should not use concatenation directly
>                 header("Content-Length: " . strlen($ciphertext));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1766) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 555
'header()' calls should not use concatenation directly
>                 header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . basename_international($d->get_url()) . "\"");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1767) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 556
'header()' calls should not use concatenation directly
>                 header("Content-Type: " . $d->get_mimetype());
For more information execute 'psecio-parse rules SetHeaderWithInput'

1768) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 557
'header()' calls should not use concatenation directly
>                 header("Content-Length: " . strlen($filetext));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1769) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 593
Use of system functions, especially with user input, is not recommended
>                 exec("convert -density 200 " . escapeshellarg($from_file_tmp_name) . " -append -resize 850 " . escapeshellarg($to_file_tmp_name));
For more information execute 'psecio-parse rules SystemFunctions'

1770) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 615
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>             if ($disable_exit == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1771) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 621
'header()' calls should not use concatenation directly
>             header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . basename_international($url) . "\"");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1772) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 623
'header()' calls should not use concatenation directly
>             header("Content-Length: " . strlen($filetext));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1773) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 665
Avoid the use of an output method (echo, print, etc) directly with a variable
>             echo xl('The requested document is not present at the expected location on the filesystem or there are not sufficient permissions to access it.', '', '', ' ') . $url;
For more information execute 'psecio-parse rules OutputWithVariable'

1774) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 674
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 if ($disable_exit == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1775) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 684
'header()' calls should not use concatenation directly
>                     header('Content-Disposition: attachment; filename="' . basename_international("/encrypted_aes_".$d->get_url_file()) . '"');
For more information execute 'psecio-parse rules SetHeaderWithInput'

1776) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 686
'header()' calls should not use concatenation directly
>                     header("Content-Length: " . strlen($ciphertext));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1777) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 689
'header()' calls should not use concatenation directly
>                     header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . basename_international($d->get_url()) . "\"");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1778) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 690
'header()' calls should not use concatenation directly
>                     header("Content-Type: " . $d->get_mimetype());
For more information execute 'psecio-parse rules SetHeaderWithInput'

1779) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 691
'header()' calls should not use concatenation directly
>                     header("Content-Length: " . strlen($filetext));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1780) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 711
Use of system functions, especially with user input, is not recommended
>                         exec("convert -density 200 " . escapeshellarg($from_file_tmp_name) . " -append -resize 850 " . escapeshellarg($to_file_tmp_name));
For more information execute 'psecio-parse rules SystemFunctions'

1781) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 724
Use of system functions, especially with user input, is not recommended
>                         exec("convert -density 200 " . escapeshellarg($originalUrl) . " -append -resize 850 " . escapeshellarg($url));
For more information execute 'psecio-parse rules SystemFunctions'

1782) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 737
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>                 if ($disable_exit == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

1783) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 743
'header()' calls should not use concatenation directly
>                 header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . basename_international($url) . "\"");
For more information execute 'psecio-parse rules SetHeaderWithInput'

1784) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 745
'header()' calls should not use concatenation directly
>                 header("Content-Length: " . strlen($filetext));
For more information execute 'psecio-parse rules SetHeaderWithInput'

1785) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 1015
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>                 die("process is '" . text($_POST['process']) . "', expected 'true'");
For more information execute 'psecio-parse rules ExitOrDie'

1786) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 1050
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("process is '" . $_POST['process'] . "', expected 'true'");
For more information execute 'psecio-parse rules ExitOrDie'

1787) /projects/emr/open-emr/src/openemr/controllers/C_Document.class.php on line 1345
Avoid the use of `exit` or `die` with strings as it could lead to injection issues (direct output)
>             die("process is '" . text($_POST['process']) . "', expected 'true'");
For more information execute 'psecio-parse rules ExitOrDie'

1788) /projects/emr/open-emr/src/openemr/controllers/C_InsuranceCompany.class.php on line 76
'header()' calls should not use concatenation directly
>         header('Location:'.$GLOBALS['webroot']."/controller.php?" . "practice_settings&insurance_company&action=list");//Z&H
For more information execute 'psecio-parse rules SetHeaderWithInput'

1789) /projects/emr/open-emr/src/openemr/controllers/C_InsuranceNumbers.class.php on line 137
'header()' calls should not use concatenation directly
>             header('Location:'.$GLOBALS['webroot']."/controller.php?" . "practice_settings&insurance_numbers&action=list");//Z&H
For more information execute 'psecio-parse rules SetHeaderWithInput'

1790) /projects/emr/open-emr/src/openemr/controllers/C_X12Partner.class.php on line 77
'header()' calls should not use concatenation directly
>         header('Location:'.$GLOBALS['webroot']."/controller.php?" . "practice_settings&x12_partner&action=list");//Z&H
For more information execute 'psecio-parse rules SetHeaderWithInput'

1791) /projects/emr/open-emr/src/openemr/controllers/C_Pharmacy.class.php on line 85
'header()' calls should not use concatenation directly
>         header('Location:'.$GLOBALS['webroot']."/controller.php?" . "practice_settings&pharmacy&action=list");//Z&H
For more information execute 'psecio-parse rules SetHeaderWithInput'

1792) /projects/emr/open-emr/src/openemr/services/ProductRegistrationService.php on line 69
Evaluation with booleans should use strict type checking (ex: if $foo === false)
>         } else if (!empty($optOut) && $optOut == true) {
For more information execute 'psecio-parse rules BooleanIdentity'

FAILURES!
Scanned: 2727, Errors: 1, Issues: 1792.
```
