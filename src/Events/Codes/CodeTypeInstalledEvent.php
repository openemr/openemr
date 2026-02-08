<?php

/**
 * CodeTypeInstalledEvent class is fired when a code type has been installed in the OpenEMR system. Currently it is fired
 * during the External Data Load process for code types found in standard_tables_capture.inc.php  Consumers can hook into
 * the pre install event and post install event.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Codes;

use Symfony\Contracts\EventDispatcher\Event;

class CodeTypeInstalledEvent extends Event
{
    /**
     * This event is triggered before the code system is installed
     */
    const EVENT_INSTALLED_PRE = 'external_codes.installed.pre';

    /**
     * This event is triggered after the code system is installed
     */
    const EVENT_INSTALLED_POST = 'external_codes.installed.post';

    /**
     * @param string $code_type The code type system that was installed
     * @param mixed[] $details Additional details for the specific code type that was installed.
     */
    public function __construct(
        private $code_type,
        private $details
    ) {
    }

    /**
     * @return string
     */
    public function getCodeType(): string
    {
        return $this->code_type;
    }

    /**
     * @param string $code_type
     * @return CodeTypeInstalledEvent
     */
    public function setCodeType(string $code_type): CodeTypeInstalledEvent
    {
        $this->code_type = $code_type;
        return $this;
    }

    /**
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param array $details
     * @return CodeTypeInstalledEvent
     */
    public function setDetails(array $details): CodeTypeInstalledEvent
    {
        $this->details = $details;
        return $this;
    }
}
