<?php

$file_path = strip_tags($_REQUEST['path']);
$file_name = strip_tags($_REQUEST['name']);

$filename = $file_path;
$type = mime_content_type($filename);

header('Content-Description: File Transfer');
header('Cache-Control: public');
header('Content-Type: '.$type);
header("Content-Transfer-Encoding: binary");
header('Content-Disposition: attachment; filename='. basename($file_path));
header('Content-Length: '.filesize($file_path));
ob_clean();
flush();
readfile($file_path);

exit;