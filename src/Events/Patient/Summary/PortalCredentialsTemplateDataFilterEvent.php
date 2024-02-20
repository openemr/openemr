<?php

/**
 * PortalCredentialsTemplateDataFilterEvent is intended to be used and dispatched when a template is about to be rendered
 * for the create_portallogin.php process.  It allows template authors to add to / modify the data that is passed to the
 * template.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Patient\Summary;

use Symfony\Contracts\EventDispatcher\Event;

class PortalCredentialsTemplateDataFilterEvent extends Event
{
    const EVENT_HANDLE = 'patient.portal-credentials.filter';

    /**
     * @var int
     */
    private $pid;

    /**
     * @var string The name of the twig template being rendered
     */
    private $templateName;

    /**
     * @var array The data that is being passed to the twig array
     */
    private $data;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     * @return PortalCredentialsTemplateDataFilterEvent
     */
    public function setPid(int $pid): PortalCredentialsTemplateDataFilterEvent
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * @param string $templateName
     * @return PortalCredentialsTemplateDataFilterEvent
     */
    public function setTemplateName(string $templateName): PortalCredentialsTemplateDataFilterEvent
    {
        $this->templateName = $templateName;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return PortalCredentialsTemplateDataFilterEvent
     */
    public function setData(array $data): PortalCredentialsTemplateDataFilterEvent
    {
        $this->data = $data;
        return $this;
    }
}
