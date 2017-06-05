<?php

class HTMLPurifier_HTMLModule_TargetNoreferrerTest extends HTMLPurifier_HTMLModuleHarness
{

    public function setUp()
    {
        parent::setUp();
        $this->config->set('HTML.TargetNoreferrer', true);
        $this->config->set('HTML.TargetNoopener', false);
        $this->config->set('Attr.AllowedFrameTargets', '_blank');
    }

    public function testNoreferrer()
    {
        $this->assertResult(
            '<a href="http://google.com" target="_blank">x</a>',
            '<a href="http://google.com" target="_blank" rel="noreferrer">x</a>'
        );
    }

    public function testNoreferrerNoDupe()
    {
        $this->config->set('Attr.AllowedRel', 'noreferrer');
        $this->assertResult(
            '<a href="http://google.com" target="_blank" rel="noreferrer">x</a>',
            '<a href="http://google.com" target="_blank" rel="noreferrer">x</a>'
        );
    }

    public function testTargetBlankNoreferrer()
    {
        $this->config->set('HTML.TargetBlank', true);
        $this->assertResult(
            '<a href="http://google.com">x</a>',
            '<a href="http://google.com" target="_blank" rel="noreferrer">x</a>'
        );
    }

    public function testNoTarget()
    {
        $this->assertResult(
            '<a href="http://google.com">x</a>',
            '<a href="http://google.com">x</a>'
        );
    }


}

// vim: et sw=4 sts=4
