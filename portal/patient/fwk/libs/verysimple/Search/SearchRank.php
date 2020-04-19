<?php

/**
 * @package    verysimple::Search
 */

/**
 * This is a base class for providing SearchRank functionality
 *
 * @package verysimple::Search
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class SearchRank
{
    public $EstimatedResults = 0;
    public $Position = 0;
    public $Url;
    public $Title;
    public $Snippet;
    public $Query;
    public $Page = 0;
    public $TopRankedUrl;
    public $TopRankedTitle;
    public $TopRankedSnippet;
}
