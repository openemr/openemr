<?php
function formData($name, $type='P') {
  if ($type == 'P')
    $s = isset($_POST[$name]) ? $_POST[$name] : '';
  else if (type == 'G')
    $s = isset($_GET[$name]) ? $_GET[$name] : '';
  else
    $s = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
  if (!get_magic_quotes_gpc()) $s = addslashes($s);
  return $s;
}
?>
