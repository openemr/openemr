<?php
 // $Id$
 // $Author$
 //
 // $Log$
 // Revision 1.4  2008/05/09 20:10:28  cfapress
 // Changes to handle to HTML returned by rxlist.com
 //
 // Revision 1.3  2007/06/19 02:34:45  sunsetsystems
 // drug lookup fix from Sam Rajan
 //
 // Revision 1.2  2005/12/27 00:45:19  sunsetsystems
 // fix broken url
 //
 // Revision 1.1.1.3  2005/06/23 05:25:49  drbowen
 // Initial import.
 //
 // Revision 1.1.1.2  2005/03/28 00:44:54  drbowen
 // Initial import.
 //
 // Revision 1.1.1.1  2005/03/09 19:59:43  wpennington
 // First import of OpenEMR
 //
 // Revision 1.2  2002/08/06 13:49:08  rufustfirefly
 // updated rxlist class for changes in RxList.com
 //
 // Revision 1.1  2002/07/08 14:42:25  rufustfirefly
 // RxList.com prescription "module" for formulary
 //

if (!defined('__CLASS_RXLIST_PHP__')) {
define('__CLASS_RXLIST_PHP__', true);

class RxList {

	function getPage ( $query ) {
		$url = "http://www.rxlist.com/cgi/rxlist.cgi?drug=".
		// $url = "http://129.250.146.18/cgi/rxlist.cgi?drug=".
			urlencode($query);

		if (!($fp = fopen($url, "r"))) {
			// If we fail to get the page, return false
			return false;
		} else {
			// Get the page
			$buffer = 0;
			while (!feof($fp)) {
				$buffer .= fgets($fp, 4096);
			}
			fclose ($fp);
			return $buffer;
		} // end checking for successful open
	} // end function RxList::getPage

	function get_list ( $query ) {
		$page = RxList::getPage($query);
		$tokens = RxList::parse2tokens($page);
		$hash = RxList::tokens2hash($tokens);
		foreach ($hash AS $index => $data) {
			unset($my_data);
			foreach ($data AS $k => $v) {
				$my_data[$k] = $v;
			}
			$list[trim($my_data[brand_name])." (".trim($my_data[generic_name]).")"] =
				trim($my_data[brand_name]);
		}
		return $list;
	} // end function RxList::get_list

        /* break the web page into a collection of TAGS 
         * such as <input ..> or <img ... >
         */
	function parse2tokens( $page ) {
		$pos = 0; $token = 0; unset ($tokens);
		$in_token = false;
		while ($pos < strlen($page)) {
			switch(substr($page, $pos, 1)) {
				case "<":
				    if ($in_token) {
				        $token++;
					$in_token = false;
				    }
				    $tokens[$token] .= substr($page,$pos,1);
				    $in_token = true;
				    break;

				case ">":
				    $tokens[$token] .= substr($page,$pos,1);
				    $in_token = false;
				    $token++;
				    break;

				default:
				    $tokens[$token] .= substr($page,$pos,1);
				    $in_token = false;
				    break;

			} // end decide what to do
			$pos++;
		} // end looping through string
		return $tokens;
	} // end function RxList::parse2tokens


        /* WTF does this crap do? */
	function tokens2hash ( $tokens ) {
		$record = false; $current = 0; unset($hash); unset($all);
		for ($pos=0; $pos<count($tokens); $pos++) {

			if (!(strpos($tokens[$pos], "Brand Name") === false)){
                            // found a brand line 'token'
				$type = "brand_name";
				$record = $pos;
				$ending = "</a>";
			}

			if (!(strpos($tokens[$pos], "Generic Name") === false)){
                            // found a generic line 'token'
				$type = "generic_name";
				//print "generic_name record start at $pos<BR>\n";
				$ending = "</a>";
				$record = $pos;
			}

			if (!(strpos($tokens[$pos], "Drug Class") === false)){
                            // found a drug-class 'token'
				$type = "drug_class";
				//print "drug_class record start at $pos<BR>\n";
				$ending = "</font>";
				$record = $pos;
			}

			// Handle the ending (assume when all fields are set
			// that we're done)
                        //
                        // May 2008 - RXList doesn't always return all three types of 'tokens'
                        // for a drug, so I replaced the 'and's with 'or's -- JRM
			//if (isset($hash["drug_class"]) and isset($hash["brand_name"]) and isset($hash["generic_name"])) {
			if (isset($hash["drug_class"]) or isset($hash["brand_name"]) or isset($hash["generic_name"])) {
				// Reset record
				$record = false;
				// Add hash to all
				$all[] = $hash;
				// Unset hash and type
				unset($hash); $type = ""; $ending = "";
			}

			if ((($pos == ($record + 1)) or
                            ($pos == ($record + 2)) or
                            ($pos == ($record + 3)))
                            and ($ending != ""))
                        {
                            //print "tokens[$pos] = ".htmlentities($tokens[$pos])."<BR>\n";
                            if ((!(strpos(strtoupper($tokens[$pos]), "</A>"   ) === false)) or
                                (!(strpos(strtoupper($tokens[$pos]), "</FONT>") === false)) or
                                (!(strpos(strtoupper($tokens[$pos]), "</TD>"  ) === false)))
                            {
                                // Find where anchor is
                                $my_pos = strpos(strtoupper($tokens[$pos]), "<");
                                $hash[$type] = substr($tokens[$pos], 0, $my_pos);
                                $hash[$type] = str_replace("&amp;", "&", $hash[$type]);
                                //print "hash[$type] = ".htmlentities($hash[$type])."<BR>\n";
                                $type = ""; $ending = "";
                            }
			}
		} // end looping
		return $all;
	} // end function RxList::tokens2hash

} // end class RxList

} // end if not defined
/*
// TEST CRAP HERE
//$page = RxList::getPage("http://129.250.146.18/cgi/rxlist.cgi?drug=lipitor");
$page = RxList::getPage("http://www.rxlist.com/cgi/rxlist.cgi?drug=lipitor");
$tokens = RxList::parse2tokens($page);
$hash = RxList::tokens2hash($tokens);
foreach ($hash AS $k => $v) {
	print "<UL>k = ".htmlentities($k)." \n";
	foreach ($v AS $key => $value) {
		print "<LI>$key = $value\n";
	}
	print "</UL>\n";
}

print "<FORM>\n";
print html_form::select_widget("test", RxList::get_list($drug));
print "</FORM>\n";
*/
?>
