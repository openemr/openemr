<?php
/**
 * FILTREATMENT CLASS FILE
 * 
 * 
 * @author Cristian Năvălici {@link http://www.lemonsoftware.eu} lemonsoftware [at] gmail [.] com
 * @version 1.31 17 March 2008
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Filtreatment
 * 
 */

//error_reporting(E_ALL); 

/**
 * constant used in float comparisions
 */
define('EPSILON', 1.0e-8);

/**
 * CLASS DEFINITION
 *
 * This class can be used to sanitize user inputs and prevent
 * most of known vulnerabilities
 * it requires at least PHP 5.0
 *
 * @package Filtreatment
 */
class Filtreatment {

public $minval = 0;
public $maxval = 0;
public $error = '';

/**
 * CONSTRUCTOR
 *
 * do some settings at init
 *
 * @param none
 * @return void
 */
function __construct() {
    if ( get_magic_quotes_gpc() ) {
        if ( !defined('MAGICQUOTES') ) define ('MAGICQUOTES', TRUE);
    } else {
        if ( !defined('MAGICQUOTES') ) define ('MAGICQUOTES', FALSE);
    }
}

//-----------------------------------------------------------------------------
/**
 * CHECKS FOR AN INTEGER
 * 
 * if the $minval and|or $maxval are set, a comparison will be performed
 * 
 * NOTE: because the function can return 0 also as a valid result, check with === the return value
 * @param int $input - what to check/transform
 * @return int|bool
 */
public function ft_integer($input) {
    $input_c    = (int)$input;
    $mnval      = (int)$this->minval;
    $mxval      = (int)$this->maxval;

    if ( !$mnval && !$mxval ) {
        return $input_c;
    } else if ( $mnval && $mxval ) {
        // check if they are in order (min <  max)
        if ( $mnval > $mxval ) {
            $temp   = $mnval;
            $mnval  = $mxval;
            $mxval  = $temp;
        }

        // and then check if the value is between these values
        return (($input >= $mnval) && ($input <= $mxval)) ? $input_c : FALSE;
    } else { 
        // only one value set
        if ( $mnval ) return (($input >= $mnval) ? $input_c : FALSE );
        if ( $mxval ) return (($input <= $mxval) ? $input_c : FALSE );
    }

}


//-----------------------------------------------------------------------------
/**
 * CHECKS FOR A FLOAT
 * 
 * if the $minval and|or $maxval are set, a comparison will be performed
 * 
 * @param int $input - what to check/transform
 * @return int|bool
 */
public function ft_float($input) {
    $input_c    = (float)$input;
    $mnval      = (float)$this->minval;
    $mxval      = (float)$this->maxval;

    if ( !$mnval && !$mxval ) {
        return $input_c;
    } else if ( $mnval && $mxval ) {
        // check if they are in order (min <  max)
        if ( $this->ft_realcmp($mnval, $mxval) > 0 ) {
            $temp   = $mnval;
            $mnval  = $mxval;
            $mxval  = $temp;
        }

        // and then check if the value is between these values
        $lt = $this->ft_realcmp($input, $mxval); //-1 or 0 for true
        if ( $lt === -1 || $lt === 0 ) $lt = $input_c; else $lt = FALSE;

        $gt = $this->ft_realcmp($input, $mnval); //1 or 0 for true
        if ( $gt === 1 || $gt === 0 ) $gt = TRUE; else $gt = FALSE;
  
        return (( $lt && $gt ) ? $input_c : FALSE);
    } else { 
        // only one value set
        if ( $mnval ) {
            $gt = $this->ft_realcmp($input, $mnval); //1 or 0 for true
            return ( $gt === 1 || $gt === 0 ) ? $input_c : FALSE;
        }

        if ( $mxval ) {
            $lt = $this->ft_realcmp($input, $mxval); //-1 or 0 for true
            return ( $lt === -1 || $lt === 0 ) ? $input_c : FALSE;
        }
    }

}

//-----------------------------------------------------------------------------
/**
 * VALIDATES A DATE
 * 
 * must be in YYYY-MM-DD format
 * 
 * @param string $str - date in requested format
 * @return string|bool - the string itselfs only for valid date
 */
public function ft_validdate($str) {
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

//-----------------------------------------------------------------------------
/**
 * VALIDATES AN EMAIL
 * 
 * implies RFC 2822
 * 
 * @param string $str - email to validate
 * @return string|bool - the string itselfs only for valid email
 */
public function ft_email($email) {
    if (MAGICQUOTES) {
        $value = stripslashes($email);
    }

    // check for @ symbol and maximum allowed lengths
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) { return FALSE; }

    // split for sections
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);

    for ($i = 0; $i < sizeof($local_array); $i++) {
        if ( !ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i]) ) {
             return FALSE;
        }
    }

    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { 
    // verify if domain is IP. If not, it must be a valid domain name 
        $domain_array = explode(".", $email_array[1]);
        if (sizeof($domain_array) < 2) { return FALSE; }

        for ($i = 0; $i < sizeof($domain_array); $i++) {
            if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) { 
                return false; 
            }	
        }
    } // if 

    return $email;
}

//-----------------------------------------------------------------------------
/**
 * PREPARES THE INPUT FOR DATABASE
 * 
 * works with mysql/postgresql
 * NOTE: mysql_real_escape_string() requires that a valid mysql connection (mysql_connect()) exists to work
 * 
 * @param string $value - email to validate
 * @param string $db_type - allow two constants MYSQL | PGSQL
 * @return string|bool $value sanitized value
 */
public function ft_dbsql($value, $db_type = 'MYSQL') {
   if (MAGICQUOTES) {
        $value = stripslashes($value);
    }

    // Quote if not a number or a numeric string
    if (!is_numeric($value)) {
        /*switch ($db_type) {
            case 'MYSQL': $value = "'" . mysql_real_escape_string($value) . "'"; break;
            case 'PGSQL': $value = "'" . pg_escape_string($value) . "'"; break;
        }*/
        // trick to not modify the openemr genuine code who put the string (already quoted) in quotes!
        switch ($db_type) {
            case 'MYSQL': $value = mysql_real_escape_string($value); break;
            case 'PGSQL': $value = pg_escape_string($value); break;
        }
    }

    return $value;
}


//-----------------------------------------------------------------------------
/**
 * WORKS ON A STRING WITH REGEX EXPRESSION
 * 
 * checks a string for specified characters
 * 
 * @param string $value - variable to sanitize
 * @param string $regex - is in a special form detailed below:
 * it contains ONLY allowed characters, ANY other characters making invalid string
 * it must NOT contain begin/end delimitators  /[... ]/
 * eg: 0-9, 0-9A-Za-z, AERS
 * @param int $cv - 1 or 2
 * @return string|bool return string if check succeed ($cv = 1) or string with replaced chars 

 */
public function ft_strregex($value, $regex, $cv = 1) {
    $s = TRUE; //var control
    $regexfull = "/[^" . $regex . "]/";

    // function of $cv might be a clean up operation, or just verifying
    switch ($cv) {
        // verify the string
        case '1': 
            $s = ( preg_match($regexfull, $value) ? FALSE : TRUE ); 
        break;

        // cleanup the string
        case '2':
            $value = preg_replace($regexfull,'',$value);
        break;

        // if $cv is not specified or it's wrong
        default: if ( preg_match($regexfull, $value) ) $s = FALSE;
    }

    return ( $s ? $value : FALSE );
}



//-----------------------------------------------------------------------------
/**
 * CLEANS AGAINST XSS
 * 
 * NOTE all credits goes to codeigniter.com
 * @param string $str - string to check
 * @param string $charset - character set (default ISO-8859-1)
 * @return string|bool $value sanitized string
 */
public function ft_xss($str, $charset = 'ISO-8859-1') {
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
                html_entity_decode($matches['1'][$i], ENT_COMPAT, $charset), $str);
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

//-----------------------------------------------------------------------------
/**
 * DISPLAY ERRORS
 * 
 * @param int $mode - if 1 then echo the string; if 2 then echo the string
 * @return void
 */
public function display_error($mode = 1) {
    $errstr = ( $this->error ) ? $this->error : '';
    if ( $mode == 1 ) {
        echo '<br />' .$this->ft_xss($errstr) . '<br />';
    } else {
        return $this->ft_xss($errstr);
    }
}


//-----------------------------------------------------------------------------
/**
 * REAL COMPARASION BETWEEN FLOATS
 * 
 * 0 - for ==, 1 for r1 > r2, -1 for r1 '<' r2
 *
 * @param float $r1
 * @param float $r2
 * @return int 
 */
private function ft_realcmp($r1, $r2) {
    $diff = $r1 - $r2;

    if ( abs($diff) < EPSILON ) return 0;
    else return $diff < 0 ? -1 : 1;
}


//-----------------------------------------------------------------------------
} // class

?>