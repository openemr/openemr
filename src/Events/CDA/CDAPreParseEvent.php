<?php

/**
 * CDAPreParseEvent.php
 *
 * @package     OpenEMR
 * @subpackage  CareCoordination
 * @author      Robert Down <robertdown@live.com>
 * @copyright   2023 Robert Down <robertdown@live.com>
 * @copyright   2023 Providence Healthtech
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\CDA;

use Symfony\Contracts\EventDispatcher\Event;

/**
 *
 * This event is dispatched when the CDA document is parsed. It is dispatched
 * prior to any processing of the data. It accepts the component data and returns
 * data in the same shape as the component data
 */
final class CDAPreParseEvent extends Event
{
    public const EVENT_HANDLE = 'cda.component.pre.parse';

    /**
     * @var array Should equal the $templateData property in CdaTemplateParse
     */
    private $components;

    /**
     *
     * @param array $components
     */
    public function __construct(array $components = [])
    {
        $this->components = $components;
    }

    /**
     * @return array The components of the CDA document, see parseCDAEntryComponentss() in CdaTemplateParse
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * @param string $components
     */
    public function setComponents(array $components): void
    {
        $this->components = $components;
    }
}
