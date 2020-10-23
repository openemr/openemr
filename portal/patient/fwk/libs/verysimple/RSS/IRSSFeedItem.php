<?php

/** @package    verysimple::Phreeze */

/**
 * ICache defines an interface for objects that can be rendered by Phreeze controller
 *
 * @package verysimple::RSS
 * @author VerySimple Inc.
 * @copyright 1997-2011 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
interface IRSSFeedItem
{
    /**
     * Returns a string to use as the RSS Title for this item
     *
     * @access public
     * @return string
     */
    public function GetRSSTitle();

    /**
     * Returns a string to use as the RSS Description
     *
     * @access public
     * @return string
     */
    public function GetRSSDescription();

    /**
     * Returns a string to use as the link to the full article for this item
     *
     * @param
     *          string the base url which phreeze will pass in to the current server
     * @return string
     */
    public function GetRSSLink($base_url);

    /**
     * Returns a unique GUID for this item.
     *
     * @return string
     */
    public function GetRSSGUID();

    /**
     * Returns a timestamp value indicating the Publish date of this item.
     *
     * @return int (timestamp) strtotime() might be a good function to use
     */
    public function GetRSSPublishDate();

    /**
     * Returns a string with the author name for this item
     *
     * @return string
     */
    public function GetRSSAuthor();
}
