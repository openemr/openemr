<?php

class HTMLPurifier_AttrDefHarness extends HTMLPurifier_Harness
{

    protected $def;
    protected $context, $config;

    public function setUp()
    {
        $this->config = HTMLPurifier_Config::createDefault();
        $this->context = new HTMLPurifier_Context();
    }

    // cannot be used for accumulator
    public function assertDef($string, $expect = true, $or_false = false)
    {
        // $expect can be a string or bool
        $result = $this->def->validate($string, $this->config, $this->context);
        if ($expect === true) {
            if (!($or_false && $result === false)) {
                $this->assertIdentical($string, $result);
            }
        } else {
            if (!($or_false && $result === false)) {
                $this->assertIdentical($expect, $result);
            }
        }
    }

}

// vim: et sw=4 sts=4
