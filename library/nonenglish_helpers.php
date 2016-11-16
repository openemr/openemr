<?php
/**
 * User: shaharzi
 * Date: 02/11/16
 * Time: 10:05
 */


/**
 * Basename functionality for nonenglish languages (without this, basename function ommits nonenglish characters).
 * @param $path
 * @return $string
 */
function basename_nonenglish($path){
    $parts = preg_split('~[\\\\/]~', $path);
    foreach ($parts as $key => $value){
        $encoded = urlencode($value);
        $parts[$key] = $encoded;
    }
    $encoded_path = implode("/", $parts);
    $encoded_file_name = basename($encoded_path);
    $decoded_file_name = urldecode($encoded_file_name);

    return $decoded_file_name;
}

