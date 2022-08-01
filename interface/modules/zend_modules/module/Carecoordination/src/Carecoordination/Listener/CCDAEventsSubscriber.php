<?php

/**
 * CCDAEventsSubscriber.php  Listens to events to retrieve, generate, manipulate CCD-A documents.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Listener;

use Carecoordination\Model\CcdaGenerator;
use Carecoordination\Model\CcdaGlobalsConfiguration;
use Carecoordination\Model\CcdaUserPreferencesTransformer;
use DOMDocument;
use HTML_TreeNode;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Events\PatientDocuments\PatientDocumentCreateCCDAEvent;
use OpenEMR\Events\PatientDocuments\PatientDocumentTreeViewFilterEvent;
use OpenEMR\Services\CDADocumentService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenEMR\Events\PatientDocuments\PatientDocumentViewCCDAEvent;
use XSLTProcessor;

class CCDAEventsSubscriber implements EventSubscriberInterface
{
    /**
     * @var CcdaGenerator
     */
    private $generator;

    /**
     * @var string The url that users will be sent to inside OpenEMR to view a CCDA
     */
    private $viewCcdaUrl;

    public function __construct(CcdaGenerator $generator)
    {
        $this->generator = $generator;
        $this->viewCcdaUrl = $GLOBALS['webroot'] . "/interface/modules/zend_modules/public/encountermanager/previewDocument";
    }

    public static function getSubscribedEvents()
    {
        return [
            PatientDocumentCreateCCDAEvent::EVENT_NAME_CCDA_CREATE => 'onCCDACreateEvent',
            PatientDocumentViewCCDAEvent::EVENT_NAME => 'onCCDAViewEvent',
            GlobalsInitializedEvent::EVENT_HANDLE => 'setupUserGlobalSettings',
            PatientDocumentTreeViewFilterEvent::EVENT_NAME => 'onPatientDocumentTreeViewFilter'
        ];
    }

    /**
     * Receives an event request to generate a ccda document.  Generates the document and then stores it back in the
     * event for consumers to use.
     * @param PatientDocumentCreateCCDAEvent $event
     * @return PatientDocumentCreateCCDAEvent
     */
    public function onCCDACreateEvent(PatientDocumentCreateCCDAEvent $event)
    {
        $dates = [];
        if (!empty($event->getDateFrom())) {
            $dates['date_start'] = $event->getDateFrom()->format("Y-m-d H:i:s");
            $dates['filter_content'] = true;
        }

        if (!empty($event->getDateTo())) {
            $dates['date_end'] = $event->getDateTo()->format("Y-m-d H:i:s");
            $dates['filter_content'] = true;
        }

        try {
            $result = $this->generator->generate(
                $event->getPid(),
                null,
                '',
                false,
                false,
                false,
                $event->getComponentsAsString(),
                $event->getSectionsAsString(),
                '',
                [], // params appears to be used for the informationRecipient pieces, so we leaves this alone
                $event->getDocumentType(),
                '',
                $dates
            );

            // the generator just returns the content...
            $cdaService = new CDADocumentService();
            $cdaResult = $cdaService->search(['id' => $result->getId()]);
            if ($cdaResult->hasData()) {
                $event->setCcdaId($result->getId());
                $fileUrl = $cdaResult->getData()[0]['ccda_data'];
                $event->setFileUrl($fileUrl);
            }
        } catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()
                , 'pid' => $event->getPid(), 'components' => $event->getComponentsAsString(), 'sections' => $event->getSectionsAsString()
                , 'from' => $event->getDateFrom(), 'to' => $event->getDateTo()]);
        }
        return $event;
    }

    /**
     * When a CCDA is viewed in the system (in the module or outside of it), grab the CCDA and transform it based upon
     * the user's ccda display preferences.
     * @param PatientDocumentViewCCDAEvent $event
     * @return PatientDocumentViewCCDAEvent
     */
    public function onCCDAViewEvent(PatientDocumentViewCCDAEvent $event)
    {
        try {
            // transform the xml content
            $ccdaGlobalsConfiguration = new CcdaGlobalsConfiguration();

            // user preferences can truncate, sort, etc so we handle those here
            if (!$event->shouldIgnoreUserPreferences()) {
                $ccdaUserPreferencesTransformer = new CcdaUserPreferencesTransformer(
                    $ccdaGlobalsConfiguration->getMaxSections(),
                    $ccdaGlobalsConfiguration->getSectionDisplayOrder()
                );
                $updatedContent = $ccdaUserPreferencesTransformer->transform($event->getContent());
            } else {
                $updatedContent = $event->getContent();
            }
            $type = $event->getCcdaType();

            $format = $event->getFormat();
            if ($format == 'html') {
                // time to use our stylesheets
                $stylesheet = dirname(__FILE__) . "/../../../../../public/xsl/";

                // from original ccr/display.php code
                if ($type == 'CCR') {
                    $stylesheet .= "ccr.xsl";
                } else if ($type == "CCD") {
                    $stylesheet .= "cda.xsl";
                }
                if (!file_exists($stylesheet)) {
                    throw new \RuntimeException("Could not find stylesheet file at location: " . $stylesheet);
                }
                $xmlDom = new DOMDocument();
                $xmlDom->loadXML($updatedContent);
                $ss = new DOMDocument();
                $ss->load($stylesheet);
                $proc = new XSLTProcessor();
                $proc->importStylesheet($ss);
                $event->setStylesheetPath($stylesheet);
                $updatedContent = $proc->transformToXml($xmlDom);
            }
            $event->setContent($updatedContent);
            return $event;
        } catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()
                , 'documentId' => $event->getDocumentId(), 'ccdaId' => $event->getCcdaId(), 'type' => $event->getCcdaType()]);
        }
        return $event;
    }

    /**
     * When the global configuration is initialized setup our CCDA specific settings
     * @param GlobalsInitializedEvent $event
     */
    public function setupUserGlobalSettings(GlobalsInitializedEvent $event)
    {
        $service = $event->getGlobalsService();
        $ccdaGlobalsConfiguration = new CcdaGlobalsConfiguration();
        $ccdaGlobalsConfiguration->setupGlobalSections($service);
    }

    public function onPatientDocumentTreeViewFilter(PatientDocumentTreeViewFilterEvent $event)
    {
        if ($event->getHtmlTreeNode() != null) {
            $categoryInfo = $event->getCategoryInfo();
            // we are going to setup our onclick event to launch our
            // TODO: do we want to look at our LOINC codes here as that seems to be more accurate than if we went with just names...
            if (in_array(strtoupper(trim($categoryInfo['name'] ?? "")), ["CCR","CCDA","CCD"])) {
                $htmlNode = $event->getHtmlTreeNode();
                $url = $this->viewCcdaUrl . "?docId=" . attr_url($event->getDocumentId());
                $htmlNode->events = [
                    'onClick' => "javascript:newwindow=window.open('" . $url . "','_blank');"
                ];
            }
        }
        return $event;
    }
}
