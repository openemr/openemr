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

}

?>