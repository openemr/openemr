<?php
/**
 * Filtreatment Class
 * 
 * This class can be use to sanitize user inputs and prevent
 * most of known vulnerabilities
 * @author Cristian Năvălici <lemonsoftware@gmail.com> http://www.lemonsoftware.eu
 * @version 1.2
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Filtreatment
 */

class Filtreatment {

/**
 * class constructor
 *
 * do some settings at init
 *
 * @param 
 * @return void
 */
function filtreatment () {
    if ( get_magic_quotes_gpc() ) {
        define ('MAGICQUOTES', true);
    } else {
        define ('MAGICQUOTES', false);
    }
}

/**
 * GENERAL FUNCTION FOR FILTERING
 *
 * it calls different functions to perform the operations required 
 * - the FLAG is important here
 * possible values are:
 * <br />INT - $param1 - min value $param2 - max value
 * <br />FLOAT - $param1 - min value $param2 - max value
 * <br />HTML - $param1 is an array with allowed tags
 * <br />STRING - $param1 is a regex expression with allowed values ; $param2 - a flag for verifying OR clean up
 * <br />EMAIL - no parameters
 * <br />SQL - $param1 is database type (mysql/postgresql)
 * <br />XSS - $param1 is character set
 * - A valid value in some cases can be confounded with 'false' (invalid value) so this function return a predefined string for more safety in result interpretation 
 *
 * @access public
 * @uses _fInteger()
 * @uses _fFloat()
 * @uses _fHtml()
 * @uses _fString()
 * @uses _fEmail()
 * @uses _fSql()
 * @uses _fXss()
 * @param mixed $input - variable to sanitize
 * @param string $flag - flag to switching functions
 * @param mixed $param1 - possible parameter
 * @param mixed $param2 - possible parameter
 * @return mixed|bool processed/cleaned up value if it's ok / false if it's not
 */
function doTreatment($input, $flag, $param1 = '', $param2 = '') {
    $input = trim($input); 

    switch ($flag) {
        case 'INT':
            $input_f = $this->_fInteger($input, $param1, $param2); 
        break; 

        case 'FLOAT': 
            $input_f = $this->_fFloat($input, $param1, $param2);
        break;	

        case 'HTML': 
            $input_f = $this->_fHtml($input, $param1);
        break; 

        case 'STRING':
             $input_f = $this->_fString($input, $param1, $param2);
        break;

        case 'EMAIL': 
            $input_f = $this->_fEmail($input);
        break;

        case 'SQL':
            $input_f = $this->_fSql($input, $param1='MYSQL');
        break; 

        case 'XSS':
            $input_f = $this->_fXss($input, $param1); 
        break; 

        case 'DATE':
            $input_f = $this->ft_validdate($input); 
        break; 
        
        default: $input_f = false;
    }

    // INVALID is a predefined conventionally string
    return (is_bool($input_f)) ? 'INVALID' : $input_f;
}


/**
 * TREATMENT ONLY FOR INTEGERS
 *
 * make sure that $input is an integer and optionally, check its boundaries 
 * <br />min and max values must be both provided or neither.
 * 
 * @access private
 * @example example_01.php 
 * @param string|int $value - variable to sanitize
 * @param int $val_min - minimum value (included in comparision)
 * @param int $val_max - maximum value (included in comparision)
 * @return mixed|bool input value if it's ok (it passed the conditions) FALSE otherwise
 */
function _fInteger($value, $val_min = null, $val_max = null) {
    if ( !ctype_alnum ) return false;

    $val_int = intval($value);
    if ( $val_min && $val_max ) {
        $val_min = intval($val_min);
        $val_max = intval($val_max);
        return ( ($val_int <= $val_max) && ($val_int >= $val_min) ) ? $val_int : false; 
    } else {
        return $val_int;
    }
}

/**
 * TREATMENT ONLY FOR FLOAT
 *
 * make sure that $input is an integer and optionally, check its boundaries 
 * <br />min and max values must be both provided or neither.
 * 
 * @access private
 * @example example_02.php 
 * @param string|float $value - variable to sanitize
 * @param int $val_min - minimum value (included in comparision)
 * @param int $val_max - maximum value (included in comparision)
 * @return mixed|bool input value if it's ok (it passed the conditions) FALSE otherwise
 */
function _fFloat($value, $val_min = null, $val_max = null) {
    if ( !is_numeric($value) ) return false;

    $val_float = floatval ($value);
    if ( $val_min && $val_max ) {
        $val_min = floatval ($val_min);
        $val_max = floatval ($val_max);
        return ( ($val_float <= $val_max) && ($val_float >= $val_min) ) ? $val_float : false; 
    } else {
        return $val_float;
    }
}


/**
 * TREATMENT FOR HTML STRINGS
 *
 * clean up all Php/HTML tags, or less the allowed ones
 * 
 * @access private
 * @example example_03.php 
 * @param string $value - variable to sanitize
 * @param string $allowed_tags - string with allowed tags, separated by comma
 * @return string $val_str
 */
function _fHtml($value, $allowed_tags) {
    $val_str = strip_tags ($value,$allowed_tags);
    return $val_str;
}


/**
 * TREATMENT FOR STRING WITH SPECIAL REGEXP EXPRESSIONS
 *
 * check a string for specified characters
 * 
 * @access private
 * @example example_04.php 
 * @param string $value - variable to sanitize
 * @param string $regex - is in a special form detailed below:
 * <br />it contains ONLY allowed characters, ANY other characters making invalid string
 * <br />it must NOT contain begin/end delimitators  /[... ]/
 * <br />eg: 0-9, 0-9A-Za-z, AERS
 * @param int $cv - 1 or 2
 * @return string|bool return string if check succeed ($cv = 1) or string with replaced chars 
 * <br />OR false if check failed ($cv = 2)
 */
function _fString ($value, $regex, $cv) {
    $s = true; //var control
    $regexfull = "/[^" . $regex . "]/";

    // function of $cv might be a clean up operation, or just verifying
    switch ($cv) {
        // verify the string
        case '1': 
            if ( preg_match($regexfull, $value) ) $s = false;
        break;

        // cleanup the string
        case '2':
            $value = preg_replace($regexfull,'',$value);
        break;

        // if $cv is not specified
        default: if ( preg_match($regexfull, $value) ) $s = false;
    }

    if ($s) return $value; else return false;
}


/**
 * SPECIALIZED FUNCTION FOR EMAIL VERIFICATION
 *
 * validate an email address (implies RFC 2822)
 * 
 * @access private
 * @example example_05.php 
 * @param string $value - email to validate
 * @return string|bool false if verification fails or email address itself if everything is ok
 */
function _fEmail ($value) {
    if (MAGICQUOTES) {
        $value = stripslashes ($value);
    }

    // check for @ symbol and maximum allowed lengths
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $value)) { return false; }
 
    // split for sections
    $email_array = explode("@", $value);
    $local_array = explode(".", $email_array[0]);
 
    for ($i = 0; $i < sizeof($local_array); $i++) {
        if ( !ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i]) ) { return false; }
    }
 
    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { 
    // verify if domain is IP. If not, it must be a valid domain name 
        $domain_array = explode(".", $email_array[1]);
        if (sizeof($domain_array) < 2) { return false; }

        for ($i = 0; $i < sizeof($domain_array); $i++) {
            if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) { return false; }	
        }
 
    } // if 

    return $value;
}

/**
 * SPECIALIZED FUNCTION STRINGS TREATMENT FOR MYSQL/POSTGRESQL DATABASES INPUT
 *
 * it defends agains SQL injection tehniques
 * 
 * @access private
 * @example example_06.php 
 * @param string $value - email to validate
 * @param string $db_type - allow two constants MYSQL | PGSQL
 * @return string|bool $value sanitized value
 */
function _fSql ($value, $db_type) {
    if (MAGICQUOTES) {
        $value = stripslashes($value);
    }

    // Quote if not a number or a numeric string
    if (!is_numeric($value)) {
        switch ($db_type) {
            case 'MYSQL': $value = "'" . mysql_real_escape_string($value) . "'"; break;
            case 'PGSQL': $value = "'" . pg_escape_string($value) . "'"; break;
        }
    }

    return $value;
}

/**
 * SPECIALIZED FUNCTION STRINGS TREATMENT FOR MYSQL/POSTGRESQL DATABASES INPUT
 *
 * it defends agains SQL injection tehniques
 * 
 * @access private
 * @example example_07.php 
 * @since ver 1.1. 09 februarie 2007
 * @uses _html_entity_decode();
 * @param string $str - string to check
 * @param string $charset - character set (default ISO-8859-1)
 * @return string|bool $value sanitized value
 */
function _fXss($str, $charset = 'ISO-8859-1') {
    /*
    * Remove Null Characters
    *
    * This prevents sandwiching null characters
    * between ascii characters, like Java\0script.
    *
    */
    $str = preg_replace('/\0+/', '', $str);
    $str = preg_replace('/(\\\\0)+/', '', $str);

    /*
    * Validate standard character entities
    *
    * Add a semicolon if missing.  We do this to enable
    * the conversion of entities to ASCII later.
    *
    */
    $str = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u',"\\1;",$str);
		
    /*
    * Validate UTF16 two byte encoding (x00)
    *
    * Just as above, adds a semicolon if missing.
    *
    */
    $str = preg_replace('#(&\#x*)([0-9A-F]+);*#iu',"\\1\\2;",$str);

    /*
    * URL Decode
    *
    * Just in case stuff like this is submitted:
    *
    * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
    *
    * Note: Normally urldecode() would be easier but it removes plus signs
    *
    */	
    $str = preg_replace("/%u0([a-z0-9]{3})/i", "&#x\\1;", $str);
    $str = preg_replace("/%([a-z0-9]{2})/i", "&#x\\1;", $str);		
				
    /*
    * Convert character entities to ASCII
    *
    * This permits our tests below to work reliably.
    * We only convert entities that are within tags since
    * these are the ones that will pose security problems.
    *
    */
    if (preg_match_all("/<(.+?)>/si", $str, $matches)) {		
        for ($i = 0; $i < count($matches['0']); $i++) {
            $str = str_replace($matches['1'][$i],
                $this->_html_entity_decode($matches['1'][$i], $charset), $str);
        }
    }
	
    /*
    * Convert all tabs to spaces
    *
    * This prevents strings like this: ja	vascript
    * Note: we deal with spaces between characters later.
    *
    */		
    $str = preg_replace("#\t+#", " ", $str);
	
    /*
    * Makes PHP tags safe
    *
    *  Note: XML tags are inadvertently replaced too:
    *
    *	<?xml
    *
    * But it doesn't seem to pose a problem.
    *
    */		
    $str = str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
	
    /*
    * Compact any exploded words
    *
    * This corrects words like:  j a v a s c r i p t
    * These words are compacted back to their correct state.
    *
    */		
    $words = array('javascript', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
    foreach ($words as $word) {
        $temp = '';
        for ($i = 0; $i < strlen($word); $i++) {
            $temp .= substr($word, $i, 1)."\s*";
        }
	
        $temp = substr($temp, 0, -3);
        $str = preg_replace('#'.$temp.'#s', $word, $str);
        $str = preg_replace('#'.ucfirst($temp).'#s', ucfirst($word), $str);
    }

    /*
    * Remove disallowed Javascript in links or img tags
    */		
    $str = preg_replace("#<a.+?href=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>.*?</a>#si", "", $str);
            $str = preg_replace("#<img.+?src=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>#si","", $str);
    $str = preg_replace("#<(script|xss).*?\>#si", "", $str);

    /*
    * Remove JavaScript Event Handlers
    *
    * Note: This code is a little blunt.  It removes
    * the event handler and anything up to the closing >,
    * but it's unlikely to be a problem.
    *
    */		
    $str = preg_replace('#(<[^>]+.*?)(onblur|onchange|onclick|onfocus|onload|onmouseover|onmouseup|onmousedown|onselect|onsubmit|onunload|onkeypress|onkeydown|onkeyup|onresize)[^>]*>#iU',"\\1>",$str);
    
    /*
    * Sanitize naughty HTML elements
    *
    * If a tag containing any of the words in the list
    * below is found, the tag gets converted to entities.
            *
    * So this: <blink>
    * Becomes: &lt;blink&gt;
    *
    */		
    $str = preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "&lt;\\1\\2\\3&gt;", $str);
            
    /*
    * Sanitize naughty scripting elements
    *
    * Similar to above, only instead of looking for
    * tags it looks for PHP and JavaScript commands
    * that are disallowed.  Rather than removing the
    * code, it simply converts the parenthesis to entities
    * rendering the code un-executable.
    *
    * For example:	eval('some code')
    * Becomes:		eval&#40;'some code'&#41;
    *
    */
    $str = preg_replace('#(alert|cmd|passthru|eval|exec|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);
                                            
    /*
    * Final clean up
    *
    * This adds a bit of extra precaution in case
    * something got through the above filters
    *
    */	

    $bad = array(
            'document.cookie'	=> '',
            'document.write'	=> '',
            'window.location'	=> '',
            "javascript\s*:"	=> '',
            "Redirect\s+302"	=> '',
            '<!--'			=> '&lt;!--',
            '-->'			=> '--&gt;'
    );
    
    foreach ($bad as $key => $val)	{
            $str = preg_replace("#".$key."#i", $val, $str);
    }

    return $str;
}


/**
 * HTML ENTITIES DECODE
 * merit goes to CodeIgniter - www.codeigniter.com
 *
 * This function is a replacement for html_entity_decode()
 * 
 * In some versions of PHP the native function does not work
 * when UTF-8 is the specified character set, so this gives us
 * a work-around.  More info here: http://bugs.php.net/bug.php?id=25670
 *
 * NOTE: html_entity_decode() has a bug in some PHP versions when UTF-8 is the
 * character set, and the PHP developers said they were not back porting the
 * fix to versions other than PHP 5.x.
 * @access private
 * @since ver 1.1. 09 februarie 2007
 * @param string $str - string to check
 * @param string $charset - character set (default ISO-8859-1)
 * @return string|bool $value sanitized value
 */
function _html_entity_decode($str, $charset='ISO-8859-1') {
    if (stristr($str, '&') === FALSE) return $str;

    // The reason we are not using html_entity_decode() by itself is because
    // while it is not technically correct to leave out the semicolon
    // at the end of an entity most browsers will still interpret the entity
    // correctly.  html_entity_decode() does not convert entities without
    // semicolons, so we are left with our own little solution here. Bummer.

    if (function_exists('html_entity_decode') && (strtolower($charset) != 'utf-8' OR version_compare(phpversion(), '5.0.0', '>='))) {
            $str = html_entity_decode($str, ENT_COMPAT, $charset);
            $str = preg_replace('~&#x([0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
            return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
    }

    // Numeric Entities
    $str = preg_replace('~&#x([0-9a-f]{2,5});{0,1}~ei', 'chr(hexdec("\\1"))', $str);
    $str = preg_replace('~&#([0-9]{2,4});{0,1}~e', 'chr(\\1)', $str);

    // Literal Entities - Slightly slow so we do another check
    if (stristr($str, '&') === FALSE) {
            $str = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES)));
    }

    return $str;
}

//-----------------------------------------------------------------------------
/**
 * VALIDATES A DATE
 * 
 * must be in YYYY-MM-DD format
 *  NEW FUNCTION FROM 1.31 version - a 'patch' for this!
 * 
 * @param string $str - date in requested format
 * @return string|bool - the string itselfs only for valid date
 */
function ft_validdate($str) {
    if ( preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $str) ) {
        $arr = split("-",$str);     // splitting the array
        $yy = $arr[0];            // first element of the array is year
        $mm = $arr[1];            // second element is month
        $dd = $arr[2];            // third element is days
        return ( checkdate($mm, $dd, $yy) ? $str : FALSE );
    } else {
        return FALSE;
    }
}

} // class

?>