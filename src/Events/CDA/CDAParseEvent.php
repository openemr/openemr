<?php
/**
 *
 */

namespace OpenEMR\Events\CDA;

use Carecoordination\Model\CcdaDocumentTemplateOids;
use OpenEMR\Services\Cda\CdaTemplateParse;
use Symfony\Contracts\EventDispatcher\Event;

final class CDAParseEvent extends Event
{
    public const EVENT_HANDLE = 'cda.component.parse';

    /**
     * @var array The component of the CDA document, see parseCDAEntryComponents() in CdaTemplateParse
     */
    private $templateData;

    /**
     * @var array Should equal the $templateData property in CdaTemplateParse
     */
    private $component;

    /**
     * @var string The name of the component. allergies, encounters, etc.
     */
    private $componentName;

    /**
     * @var string The OID of the template
     */
    private $oid;

    /**
     *
     * @param string $componentName
     * @param array $component
     * @param array $templateData
     */
    public function __construct(string $componentName, string $oid, array $component, array $templateData)
    {
        $this->componentName = $componentName;
        $this->component = $component;
        $this->templateData = $templateData;
        $this->oid = $oid;
    }

    public function getComponentName() : string {
        return $this->componentName;
    }

    /**
     * @return array The component of the CDA document, see parseCDAEntryComponents() in CdaTemplateParse
     */
    public function getComponent() : array
    {
        return $this->component;
    }

    /**
     * @return array Should equal the $templateData property in CdaTemplateParse
     */
    public function getTemplateData() : array
    {
        return $this->templateData;
    }

    public function getOid() : string
    {
        return $this->oid;
    }

    /**
     * @param string $componentName The name of the component. allergies, encounters, etc.
     */
    public function setComponentName(string $componentName) : void {
        $this->componentName = $componentName;
    }

    /**
     * @param string $component
     */
    public function setComponent(array $component) : void
    {
        $this->component = $component;
    }

    /**
     * @param array $templateData
     */
    public function setTemplateData($templateData) : void
    {
        $this->templateData = $templateData;
    }

    /**
     * Set the OID
     */
    public function setOid(string $oid) : void
    {
        $this->oid = $oid;
    }

}
