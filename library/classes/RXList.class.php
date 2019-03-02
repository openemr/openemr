<?php
 // $Id$
 // $Author$
 //
 // $Log$

 // Revision 1.6  2019/02/18  avanss llc
 // replaced rxlist API with rxnav.nlm.nih.gov

 // Revision 1.5  2016/02/016   sherwin gaddis
 // fix broken url

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

    class RxList
    {

        function getPage($query)
        {
            $url = "https://rxnav.nlm.nih.gov/REST/Prescribe/drugs?name=".urlencode($query);

            if (!($fp = fopen($url, "r"))) {
                // If we fail to get the page, return false
                return false;
            } else {
                // Get the page
                $buffer = 0;
                while (!feof($fp)) {
                    $buffer .= fgets($fp, 4096);
                }

                fclose($fp);
                return $buffer;
            } // end checking for successful open
        } // end function RxList::getPage

        function get_list($query)
        {
            $page = RxList::getPage($query);
            $tokens = RxList::parse2tokens($page);
            $hash = RxList::tokens2hash($tokens);
            foreach ($hash as $index => $data) {
                unset($my_data);
                foreach ($data as $k => $v) {
                    $my_data[$k] = $v;
                }

                $rxcui = '';

                if (trim($my_data['rxcui']) !== '') {
                    $rxcui = " / ".trim($my_data['rxcui']);
                }

                $synonym = '';
                if (trim($my_data['synonym']) !== '') {
                    $synonym = " == (".trim($my_data['synonym']).$rxcui.")";
                }

                $list[trim($my_data['name']).$synonym] =
                    trim($my_data['name']);
            }

            return $list;
        } // end function RxList::get_list

        /* break the web page into a collection of TAGS
         * such as <input ..> or <img ... >
         */
        function parse2tokens($page)
        {
            $pos = 0;
            $token = 0;
            unset($tokens);
            $in_token = false;
            while ($pos < strlen($page)) {
                switch (substr($page, $pos, 1)) {
                    case "<":
                        if ($in_token) {
                            $token++;
                            $in_token = false;
                        }

                        $tokens[$token] .= substr($page, $pos, 1);
                        $in_token = true;
                        break;

                    case ">":
                        $tokens[$token] .= substr($page, $pos, 1);
                        $in_token = false;
                        $token++;
                        break;

                    default:
                        $tokens[$token] .= substr($page, $pos, 1);
                        $in_token = false;
                        break;
                } // end decide what to do
                $pos++;
            } // end looping through string
            return $tokens;
        } // end function RxList::parse2tokens

        function tokens2hash($tokens)
        {
            $record = false;
            $current = 0;
            unset($hash);
            $hash = [];
            unset($all);
            for ($pos=0; $pos<count($tokens); $pos++) {
                if (!(strpos($tokens[$pos], "<name>") === false) && $pos !== 3) {
                    // found a brand line 'token'
                    $type = "name";
                    $record = $pos;
                    $ending = "</name>";
                }

                if (!(strpos($tokens[$pos], "<synonym>") === false)) {
                    // found a generic line 'token'
                    $type = "synonym";
                    //print "generic_name record start at $pos<BR>\n";
                    $ending = "</synonym>";
                    $record = $pos;
                }

                if (!(strpos($tokens[$pos], "<rxcui>") === false)) {
                    // found a drug-class 'token'
                    $type = "rxcui";
                    $ending = "</rxcui>";
                    $record = $pos;
                }

                if (isset($hash["synonym"])) {
                    // Reset record
                    $record = false;
                    // Add hash to all
                    $all[] = $hash;
                    // Unset hash and type
                    unset($hash);
                    $type = "";
                    $ending = "";
                }

                if ($pos === ($record + 1) and ($ending != "")) {
                    $my_pos = strpos(strtoupper($tokens[$pos]), "<");
                    $hash[$type] = substr($tokens[$pos], 0, $my_pos);
                    $hash[$type] = str_replace("&amp;", "&", $hash[$type]);
                    //print "hash[$type] = ".htmlentities($hash[$type])."<BR>\n";
                    $type = "";
                    $ending = "";
                }
            } // end looping
            return $all;
        } // end function RxList::tokens2hash
    } // end class RxList

} // end if not defined
