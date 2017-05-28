<?php


// mPDF 6
// Function only available PHP >=5.5.0
if(!function_exists('imagepalettetotruecolor')) {
    function imagepalettetotruecolor(&$src) {
        if(imageistruecolor($src)) {
            return(true);
        }
        $dst = imagecreatetruecolor(imagesx($src), imagesy($src));

        imagecopy($dst, $src, 0, 0, 0, 0, imagesx($src), imagesy($src));
        imagedestroy($src);

        $src = $dst;

        return(true);
    }
}

// mPDF 5.7
// Replace a section of an array with the elements in reverse
function array_splice_reverse(&$arr, $offset, $length) {
	$tmp = (array_reverse(array_slice($arr, $offset, $length)));
	array_splice($arr, $offset, $length, $tmp);
}


// mPDF 5.7.4 URLs
function urldecode_parts($url) {
	$file=$url;
	$query='';
	if (preg_match('/[?]/',$url)) {
		$bits = preg_split('/[?]/',$url,2);
		$file=$bits[0];
		$query='?'.$bits[1];
	}
	$file = rawurldecode($file);
	$query = urldecode($query);
	return $file.$query;
}


function _strspn($str1, $str2, $start=null, $length=null) {
	$numargs = func_num_args();
	if ($numargs == 2) {
		return strspn($str1, $str2);
	}
	else if ($numargs == 3) {
		return strspn($str1, $str2, $start);
	}
	else {
		return strspn($str1, $str2, $start, $length);
	}
}


function _strcspn($str1, $str2, $start=null, $length=null) {
	$numargs = func_num_args();
	if ($numargs == 2) {
		return strcspn($str1, $str2);
	}
	else if ($numargs == 3) {
		return strcspn($str1, $str2, $start);
	}
	else {
		return strcspn($str1, $str2, $start, $length);
	}
}

function _fgets (&$h, $force=false) {
	$startpos = ftell($h);
	$s = fgets($h, 1024);
	if ($force && preg_match("/^([^\r\n]*[\r\n]{1,2})(.)/",trim($s), $ns)) {
		$s = $ns[1];
		fseek($h,$startpos+strlen($s));
	}
	return $s;
}


// For PHP4 compatability
if(!function_exists('str_ireplace')) {
  function str_ireplace($search,$replace,$subject) {
	$search = preg_quote($search, "/");
	return preg_replace("/".$search."/i", $replace, $subject);
  }
}
if(!function_exists('htmlspecialchars_decode')) {
	function htmlspecialchars_decode ($str) {
		return strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}

function PreparePreText($text,$ff='//FF//') {
	$text = htmlspecialchars($text);
	if ($ff) { $text = str_replace($ff,'</pre><formfeed /><pre>',$text); }
	return ('<pre>'.$text.'</pre>');
}

if(!function_exists('strcode2utf')){
  function strcode2utf($str,$lo=true) {
	//converts all the &#nnn; and &#xhhh; in a string to Unicode
	// mPDF 5.7
	if ($lo) {
		$str = preg_replace_callback('/\&\#([0-9]+)\;/m', 'code2utf_lo_callback', $str);
		$str = preg_replace_callback('/\&\#x([0-9a-fA-F]+)\;/m', 'codeHex2utf_lo_callback', $str);
	}
	else {
		$str = preg_replace_callback('/\&\#([0-9]+)\;/m', 'code2utf_callback', $str);
		$str = preg_replace_callback('/\&\#x([0-9a-fA-F]+)\;/m', 'codeHex2utf_callback', $str);
	}
	return $str;
  }
}
function code2utf_callback($matches) {
	return code2utf($matches[1], 0);
}
function code2utf_lo_callback($matches) {
	return code2utf($matches[1], 1);
}
function codeHex2utf_callback($matches) {
	return codeHex2utf($matches[1], 0);
}
function codeHex2utf_lo_callback($matches) {
	return codeHex2utf($matches[1], 1);
}


if(!function_exists('code2utf')){
  function code2utf($num,$lo=true){
	//Returns the utf string corresponding to the unicode value
	if ($num<128) {
		if ($lo) return chr($num);
		else return '&#'.$num.';';
	}
	if ($num<2048) return chr(($num>>6)+192).chr(($num&63)+128);
	if ($num<65536) return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
	if ($num<2097152) return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128) .chr(($num&63)+128);
	return '?';
  }
}


if(!function_exists('codeHex2utf')){
  function codeHex2utf($hex,$lo=true){
	$num = hexdec($hex);
	if (($num<128) && !$lo) return '&#x'.$hex.';';
	return code2utf($num,$lo);
  }
}
