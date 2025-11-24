<?php

/**
 * LogoFilterEvent is intended to be used to filter the web url path for where a logo can be found.  It is dispatched
 * in the LogoService class and allows for the web path to be modified by other modules or services.
 *
 * This is most likely to be used by a theme module to change the logo path for a specific type of logo, such as core/menu/primary/logo.png.
 * If multiple modules are registered to this event, the last one to set the web path will be the one that is used.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Services;

use OpenEMR\Services\BaseService;
use Symfony\Contracts\EventDispatcher\Event;

class LogoFilterEvent extends Event
{
    /**
     * This event is triggered after a record has been created, and an assoc
     * array containing the POST of new record data is passed to the event object
     */
    const EVENT_NAME = 'logo.filter.url';

    /**
     * @param string $logoType The type of logo to filter on, such as core/menu/primary/
     * @param string $filePath The file path to the logo, such as /var/www/openemr/sites/default/logos/core/menu/primary/logo.png
     * @param string $webPath The web path to the logo, such as /sites/default/logos/core/menu/primary/logo.png
     */
    public function __construct(
        private readonly string $logoType,
        private readonly string $filePath,
        private string $webPath
    ) {
    }

    /**
     * @return string
     */
    public function getLogoType(): string
    {
        return $this->logoType;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }


    /**
     * @return string
     */
    public function getWebPath(): string
    {
        return $this->webPath;
    }

    /**
     * @param string $webPath
     * @return LogoFilterEvent
     */
    public function setWebPath(string $webPath): LogoFilterEvent
    {
        $this->webPath = $webPath;
        return $this;
    }
}
