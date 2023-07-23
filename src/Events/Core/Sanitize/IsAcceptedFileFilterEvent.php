<?php

/**
 * IsAcceptedFileFilterEvent is used to filter the mime type sanitization functions in sanitize.inc.php  Event consumers
 * should consider the tradeoffs of using this event versus updating the files_white_list List in list_options. If the
 * accepted mime types may change dynamically then this Event should be utilized, otherwise if the mime types are static
 * the database list should be updated instead.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2022 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Core\Sanitize;

use Symfony\Contracts\EventDispatcher\Event;

class IsAcceptedFileFilterEvent extends Event
{
    /**
     * This event fires in the sanitize.inc.php::isWhiteFile method when the initial mime type list is loaded.
     * It allows you to change what mime type files are allowed to be created as OpenEMR documents.
     * If your allowed files will change dynamically within a single page request you should use
     * the self::EVENT_FILTER_IS_ACCEPTED_FILE to be able to modify the acceptable mime types on a file by file basis.
     */
    const EVENT_GET_ACCEPTED_LIST = 'sanitize.isWhiteFile.getAcceptedList';

    /**
     * This event fires in the sanitize.inc.php::isWhiteFile method when every mime check has failed
     * Event Listeners can add dynamically additional mime types that are accepted using this event name.
     * If your mime types do not change throughout the page request life cycle (or running in a DAEMON setting) you should
     * use the self::EVENT_GET_ACCEPTED_LIST event name in order to set your allowed mime types just once.
     *
     * Callers could use this event to turn off mimetype checking automatically.  This should ONLY be done if you are
     * sure that the file you are loading will not prevent a security risk to users who will view/use the file.
     */
    const EVENT_FILTER_IS_ACCEPTED_FILE = 'sanitize.isWhiteFile.filterIsAccepted';

    /**
     * @var $array The list of accepted mime types
     */
    private $acceptedList;

    /**
     * @var string the name of the file
     */
    private $file;
    /**
     * @var bool Whether the file's mime type is allowed or not
     */
    private $allowedFile;

    /**
     * @var string The mime type of the file if known
     */
    private $mimeType;

    public function __construct($file = null, $acceptedList = [])
    {
        $this->setFile($file);
        $this->setAcceptedList($acceptedList);
    }

    /**
     * @return This
     */
    public function getAcceptedList(): array
    {
        return $this->acceptedList;
    }

    /**
     * @param This $acceptedList
     * @return IsAcceptedFileFilterEvent
     */
    public function setAcceptedList(array $acceptedList): IsAcceptedFileFilterEvent
    {
        $this->acceptedList = $acceptedList;
        return $this;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @return IsAcceptedFileFilterEvent
     */
    public function setFile(string $file): IsAcceptedFileFilterEvent
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowedFile(): bool
    {
        return $this->allowedFile;
    }

    /**
     * @param bool $allowedFile
     * @return IsAcceptedFileFilterEvent
     */
    public function setAllowedFile(bool $allowedFile): IsAcceptedFileFilterEvent
    {
        $this->allowedFile = $allowedFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     * @return IsAcceptedFileFilterEvent
     */
    public function setMimeType(string $mimeType): IsAcceptedFileFilterEvent
    {
        $this->mimeType = $mimeType;
        return $this;
    }
}
