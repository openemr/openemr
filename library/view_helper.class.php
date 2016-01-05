<?php

class ViewHelper {

  public static function stylesheetTag($paths) {
    if (is_array($paths)) {
      foreach ($paths as $path) {
        echo '<link rel="stylesheet" href="' . $GLOBALS['webroot'] . $path . '" type="text/css">' . PHP_EOL;
      }
    } else {
      echo '<link rel="stylesheet" href="' . $GLOBALS['webroot'] . $paths . '" type="text/css">' . PHP_EOL;
    }
  }

  public static function scriptTag($paths) {
    if (is_array($paths)) {
      foreach ($paths as $path) {
        echo '<script type="text/javascript" src="' . $GLOBALS['webroot'] . $path . '"></script>' . PHP_EOL;
      }
    } else {
      echo '<script type="text/javascript" src="' . $GLOBALS['webroot'] . $paths . '"></script>' . PHP_EOL;
    }
  }

  public static function inputTag($type, $name, $value, $size = null) {
    $input = '<input type="'.$type.'" name="'.$name.'" id="'.$name.'"';
    if (!empty($size)) {
      $input .= ' size="'.$size.'"';
    }
    $input .= ' value="'.$value.'" >';
    return $input;
  }

  public static function textTag($name, $value, $size = null) {
    return ViewHelper::inputTag('text', $name, $value, $size);
  }

  public static function checkboxTag($name, $checked = false) {
    $input = '<input type="checkbox" name="'.$name.'" id="'.$name.'"';
    if ($checked) {
      $input .= ' checked';
    }
    $input .= ' >';
    return $input;
  }
}

?>