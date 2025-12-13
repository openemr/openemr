<?php

/**
 * CDAPostParseEvent.php
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
 * once for each component in the list of components and occurs after the core
 * processing has occurred. It can return the manipulated templateData.
 */
final class CDAPostParseEvent extends Event
{
    public const EVENT_HANDLE = 'cda.component.post.parse';

    /**
     *
     * @param string $componentName
     * @param string $oid The OID of the template
     * @param array $component
     * @param array $templateData
     */
    public function __construct(
        private string $componentName,
        private string $oid,
        private array $component,
        private array $templateData
    ) {
    }

    /**
     * @return string The name of the component. allergies, encounters, etc.
     */
    public function getComponentName(): string
    {
        return $this->componentName;
    }

    /**
     * @return array The component of the CDA document, see parseCDAEntryComponents() in CdaTemplateParse
     */
    public function getComponent(): array
    {
        return $this->component;
    }

    /**
     * @return array Should equal the $templateData property in CdaTemplateParse
     */
    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    /**
     * @return string The OID of the template
     */
    public function getOid(): string
    {
        return $this->oid;
    }

    /**
     * @param string $componentName The name of the component. allergies, encounters, etc.
     */
    public function setComponentName(string $componentName): void
    {
        $this->componentName = $componentName;
    }

    /**
     * @param string $component
     */
    public function setComponent(array $component): void
    {
        $this->component = $component;
    }

    /**
     * @param array $templateData
     */
    public function setTemplateData($templateData): void
    {
        $this->templateData = $templateData;
    }

    /**
     * Set the OID
     */
    public function setOid(string $oid): void
    {
        $this->oid = $oid;
    }
}
