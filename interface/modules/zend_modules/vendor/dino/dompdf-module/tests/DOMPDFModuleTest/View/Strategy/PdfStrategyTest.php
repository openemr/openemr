<?php

namespace DOMPDFModuleTest\View\Strategy;

use Zend\View\Resolver\TemplatePathStack;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\ViewEvent;
use Zend\Http\Response as HttpResponse;
use DOMPDFModuleTest\Framework\TestCase;
use DOMPDFModule\View\Model\PdfModel;
use DOMPDFModule\View\Renderer\PdfRenderer;
use DOMPDFModule\View\Strategy\PdfStrategy;

class PdfStrategyTest extends TestCase
{
    public function setUp()
    {
        $this->renderer = new PdfRenderer();
        $this->strategy = new PdfStrategy($this->renderer);
        $this->event    = new ViewEvent();
        $this->response = new HttpResponse();
        
        $this->resolver = new TemplatePathStack();
        $this->resolver->addPath(dirname(__DIR__) . '/_templates');
        
        $this->renderer->setResolver($this->resolver);
        
        $htmlRenderer = new PhpRenderer();
        $htmlRenderer->setResolver($this->resolver);
        $this->renderer->setHtmlRenderer($htmlRenderer);
        $this->renderer->setEngine($this->getServiceManager()->get('dompdf'));
    }

    public function testPdfModelSelectsPdfStrategy()
    {
        $this->event->setModel(new PdfModel());
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertSame($this->renderer, $result);
    }
    
    public function testContentTypeResponseHeader()
    {
        $model = new PdfModel();
        $model->setTemplate('basic.phtml');
        
        $this->event->setModel($model);
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($this->renderer->render($model));
        
        $this->strategy->injectResponse($this->event);
        
        $headers           = $this->event->getResponse()->getHeaders();
        $contentTypeHeader = $headers->get('content-type');
        
        $this->assertInstanceof('Zend\Http\Header\ContentType', $contentTypeHeader);
        $this->assertEquals($contentTypeHeader->getFieldValue(), 'application/pdf');
    }
    
    public function testResponseHeadersWithFileName()
    {
        $model = new PdfModel();
        $model->setTemplate('basic.phtml');
        $model->setOption('filename', 'testPdfFileName');
        
        $this->event->setModel($model);
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($this->renderer->render($model));
        
        $this->strategy->injectResponse($this->event);
        
        $headers                  = $this->event->getResponse()->getHeaders();
        $contentDispositionHeader = $headers->get('Content-Disposition');
        
        $this->assertInstanceof('Zend\Http\Header\ContentDisposition', $contentDispositionHeader);
        $this->assertEquals($contentDispositionHeader->getFieldValue(), 'attachment; filename=testPdfFileName.pdf');
    }
}
