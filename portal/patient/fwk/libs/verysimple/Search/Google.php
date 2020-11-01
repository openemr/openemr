<?php

/** @package    verysimple::Search */

/**
 * include required files
 */
require_once("SearchEngine.php");
require_once('SOAP/Client.php'); // PEAR::SOAP::Client

/**
 * This is an implmentation of a SearchRank/SearchEngine for Google.
 * It provides
 * a convenient way to execute search queries, get results, ranking and inbound
 * links from google
 *
 * example
 * $google = new Google($API_KEY); // Google API Key must be obtained from Google
 *
 * $rank = $google->GetRank($url,$searchterm,$MAX_RESULTS);
 * $links = $google->GetLinks($url);
 *
 * @package verysimple::Search
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class Google extends SearchEngine
{

    /**
     * Returns the inbound links for the given url
     *
     * @param string $url
     *          the url for which you want to find the rank
     * @param string $max
     *          the maximum number to return
     * @return SearchResult
     */
    function GetLinks($url, $max = 10)
    {
        return $this->DoSearch("link:" . $url, 0, $max);
    }

    /**
     * Returns the estimated number of inbound links for the given url
     *
     * @param string $url
     *          the url for which you want to find the rank
     * @return int
     */
    function GetLinkCount($url)
    {
        return $this->GetLinks($url, 1)->estimatedTotalResultsCount;
    }

    /**
     * Given a url and result returned by DoSearch, returns the rank that url appears on the page
     *
     * @param string $url
     *          the url for which you want to find the rank
     * @param string $query
     *          the search query
     * @param int $maxresults
     *          (default 30) the max number of results to search for.
     * @return SearchRank
     */
    function GetRank($url, $query, $maxresults = 30)
    {
        $current_page = 0;
        $result_counter = 0;
        $page_size = 10;

        $rank = new SearchRank();

        $rank->Query = $query;

        while ($rank->Position == 0 && $result_counter <= $maxresults) {
            $current_page++;

            $result = $this->DoSearch($query, $result_counter, $page_size);

            // only loop through as many results as we have
            if ($result->estimatedTotalResultsCount < $maxresults) {
                $maxresults = $result->estimatedTotalResultsCount;
            }

            if ($current_page == 1 && $result->estimatedTotalResultsCount > 0) {
                $rank->EstimatedResults = $result->estimatedTotalResultsCount;
                $rank->TopRankedUrl = $result->resultElements [0]->URL;
                $rank->TopRankedSnippet = $result->resultElements [0]->snippet;
                $rank->TopRankedTitle = $result->resultElements [0]->title;
            }

            $cp_rank = $this->GetPositionOnPage($url, $result);

            if ($cp_rank->Position) {
                // we found a match
                $rank->Page = $current_page;
                $rank->Position = $cp_rank->Position * $current_page;
                $rank->Title = $cp_rank->Title;
                $rank->Snippet = $cp_rank->Snippet;
                $rank->Url = $cp_rank->Url;
            }

            $result_counter += $page_size;
            $result = null;
        }

        return $rank;
    }

    /**
     * Given a url and result returned by DoSearch, returns the rank that url appears on the page
     *
     * @param string $url
     * @param Result $result
     * @return SearchRank
     */
    function GetPositionOnPage($url, &$result)
    {
        $rank = new SearchRank();
        $counter = 0;

        foreach ($result->resultElements as $element) {
            $counter++;
            // print "<div>$url :: $counter = ".$element->URL."</div>";
            $normalizedurl = str_replace("/", "\\/", $url);

            if ($rank->Position == 0 && preg_match("/$normalizedurl/i", $element->URL)) {
                $rank->Position = (int) $counter;
                $rank->Title = $element->title;
                $rank->Snippet = $element->snippet;
                $rank->Url = $element->URL;
            }
        }

        return $rank;
    }

    /**
     * Queries google for the specified search query
     *
     * @param string $query
     * @param int $start
     * @param int $max
     * @param bool $filter
     * @param bool $restrict
     * @param bool $safe
     * @param string $lr
     * @param string $ie
     * @param string $oe
     * @throws Exception
     */
    function DoSearch($query, $start = 0, $max = 10, $filter = false, $restrict = "", $safe = false, $lr = "", $ie = "", $oe = "")
    {
        $result = null;
        $continue = true;
        $counter = 0;

        while ($continue) {
            $counter++;
            try {
                $result = $this->_googleSoap($query, $start, $max, $filter, $restrict, $safe, $lr, $ie, $oe);
                $continue = false;
            } catch (exception $ex) {
                $this->FailedRequests ++;

                if (preg_match("/Daily limit/i", $ex->getMessage()) || preg_match("/Invalid/i", $ex->getMessage())) {
                    throw $ex;
                }

                if ($counter > 2) {
                    throw new Exception("Tried to contact Google API $counter times without success:" . $ex->getMessage());
                }
            }
        }

        return $result;
    }

    /**
     * Makes SOAP request to google
     */
    private function _googleSoap($query, $start = 0, $max = 10, $filter = false, $restrict = "", $safe = false, $lr = "", $ie = "", $oe = "")
    {
        $wsdl = new SOAP_WSDL('http://api.google.com/GoogleSearch.wsdl');
        $soapclient = $wsdl->getProxy();

        if (get_class($soapclient) != "SOAP_Fault") {
            $result = $soapclient->doGoogleSearch($this->Key, $query, $start, $max, $filter, $restrict, $safe, $lr, $ie, $oe);
        } else {
            throw new Exception("SOAP_Fault: " . $soapclient->message);
        }

        if (PEAR::isError($result)) {
            throw new Exception("PEAR Exception: " . $result->message);
        } else {
            return $result;
        }
    }
}
