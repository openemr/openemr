<?php

require_once 'common.php'; // load library

require_once 'HTMLPurifier/Printer/ConfigForm.php';

$config = HTMLPurifier_Config::loadArrayFromForm($_POST, 'config');

// you can do custom configuration!
if (file_exists('allConfigForm.settings.php')) {
    include 'allConfigForm.settings.php';
}

$gen_config = HTMLPurifier_Config::createDefault();

$printer_config_form = new HTMLPurifier_Printer_ConfigForm(
    'config',
    'http://htmlpurifier.org/live/configdoc/plain.html#%s'
);

$purifier = new HTMLPurifier($config);
$html = isset($_POST['html']) ? $_POST['html'] : "";
$purified = $purifier->purify($html);

echo '<?xml version="1.0" encoding="UTF-8" ?>';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>HTML Purifier All Config Form smoketest</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
        .hp-config {margin-left:auto; margin-right:auto;}
        .HTMLPurifier_Printer table {border-collapse:collapse;
            border:1px solid #000; width:600px;
            margin:1em auto;font-family:sans-serif;font-size:75%;}
        .HTMLPurifier_Printer td, .HTMLPurifier_Printer th {padding:3px;
            border:1px solid #000;background:#CCC; vertical-align: baseline;}
        .HTMLPurifier_Printer th {text-align:left;background:#CCF;width:20%;}
        .HTMLPurifier_Printer caption {font-size:1.5em; font-weight:bold;}
        .HTMLPurifier_Printer .heavy {background:#99C;text-align:center;}
        .HTMLPurifier_Printer .unsafe {background:#C99;}
        dt {font-weight:bold;}
    </style>
    <link rel="stylesheet" href="../library/HTMLPurifier/Printer/ConfigForm.css" type="text/css" />
    <script defer="defer" type="text/javascript" src="../library/HTMLPurifier/Printer/ConfigForm.js"></script>
</head>
<body>

<h1>HTML Purifier All Config Form Smoketest</h1>

<p>This prints config form for everything we support.</p>

<form method="post" action="" name="hp-configform">
<table style="width:100%">
<tr><th>Input</th><th>Output</th>
<tr><td style="width:50%">
<textarea name="html" style="width:100%" rows="15"><?php echo htmlspecialchars($html) ?></textarea>
</td><td style="width:50%">
<textarea name="result" style="width:100%" rows="15"><?php echo htmlspecialchars($purified) ?></textarea>
</td></tr>
</table>
<input type="submit" />
<?php
    echo $printer_config_form->render($config);
?>
</form>
<pre><?php
    echo htmlspecialchars(var_export($config->getAll(), true));
?></pre>
<?php

// vim: et sw=4 sts=4
