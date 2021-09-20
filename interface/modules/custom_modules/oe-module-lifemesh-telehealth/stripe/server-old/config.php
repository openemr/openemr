<?php
if (PHP_SAPI == 'cli-server') {
  $_SERVER['SCRIPT_NAME'] = '../../custom_modules/oe-module-lifemesh-telehealth/stripe/server/index.php';
  $url  = parse_url($_SERVER['REQUEST_URI']);
  $file = getenv('STATIC_DIR') . $url['path'];
  if (is_file($file)) {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    if ($extension == 'css') {
      header('Content-Type: text/css');
    }
    if ($extension == 'js') {
      header('Content-Type: text/javascript');
    }
    echo file_get_contents($file);
    exit;
  }
} else {
    return $_ENV;
}
