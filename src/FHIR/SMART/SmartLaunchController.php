<?php


namespace OpenEMR\FHIR\SMART;


use OpenEMR\Events\PatientDemographics\RenderEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SmartLaunchController
{

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public function __construct(EventDispatcher $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }

    public function registerContextEvents() {
        $this->dispatcher->addListener(RenderEvent::EVENT_SECTION_LIST_RENDER_AFTER, [$this, 'renderPatientSmartLaunchSection']);
    }

    public function renderPatientSmartLaunchSection(RenderEvent $event)
    {
        $patientId = $event->getPid();
        ?>
        <section>
            <?php
            // Billing expand collapse widget
            $widgetTitle = xl("SMART Enabled Apps");
            $widgetLabel = "smart";
            $widgetButtonLabel = xl("Edit");
            $widgetButtonLink = ""; // "return newEvt();";
            $widgetButtonClass = "";
            $linkMethod = "javascript";
            $bodyClass = "notab";
            $widgetAuth = false;
            $fixedWidth = false;
            $forceExpandAlways = false;

            expand_collapse_widget(
                $widgetTitle,
                $widgetLabel,
                $widgetButtonLabel,
                $widgetButtonLink,
                $widgetButtonClass,
                $linkMethod,
                $bodyClass,
                $widgetAuth,
                $fixedWidth,
                $forceExpandAlways
            );
            ?>
            <div>
                <ul>
                    <li>
                        App Name 1 <button>Launch</button>
                    </li>
                </ul>
            </div>
        </section>
        <?php
    }

}