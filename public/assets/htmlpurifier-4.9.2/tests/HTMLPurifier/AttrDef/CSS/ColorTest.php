<?php

class HTMLPurifier_AttrDef_CSS_ColorTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_CSS_Color();

        $this->assertDef('#F00');
        $this->assertDef('#fff');
        $this->assertDef('#eeeeee');
        $this->assertDef('#808080');

        $this->assertDef('rgb(255, 0, 0)', 'rgb(255,0,0)'); // rm spaces
        $this->assertDef('rgb(100%,0%,0%)');
        $this->assertDef('rgb(50.5%,23.2%,43.9%)'); // decimals okay
        $this->assertDef('rgb(-5,0,0)', 'rgb(0,0,0)'); // negative values
        $this->assertDef('rgb(295,0,0)', 'rgb(255,0,0)'); // max values
        $this->assertDef('rgb(12%,150%,0%)', 'rgb(12%,100%,0%)'); // percentage max values

        $this->assertDef('rgba(255, 0, 0, 0)', 'rgba(255,0,0,0)'); // rm spaces
        $this->assertDef('rgba(100%,0%,0%,.4)');
        $this->assertDef('rgba(38.1%,59.7%,1.8%,0.7)', 'rgba(38.1%,59.7%,1.8%,.7)'); // decimals okay

        $this->assertDef('hsl(275, 45%, 81%)', 'hsl(275,45%,81%)'); // rm spaces
        $this->assertDef('hsl(100,0%,0%)');
        $this->assertDef('hsl(38,59.7%,1.8%)', 'hsl(38,59.7%,1.8%)'); // decimals okay
        $this->assertDef('hsl(-11,-15%,25%)', 'hsl(0,0%,25%)'); // negative values
        $this->assertDef('hsl(380,125%,0%)', 'hsl(360,100%,0%)'); // max values

        $this->assertDef('hsla(100, 74%, 29%, 0)', 'hsla(100,74%,29%,0)'); // rm spaces
        $this->assertDef('hsla(154,87%,21%,.4)');
        $this->assertDef('hsla(45,94.3%,4.1%,0.7)', 'hsla(45,94.3%,4.1%,.7)'); // decimals okay

        $this->assertDef('#G00', false);
        $this->assertDef('cmyk(40, 23, 43, 23)', false);
        $this->assertDef('rgb(0%, 23, 68%)', false); // no mixed type
        $this->assertDef('rgb(231, 144, 28.2%)', false); // no mixed type
        $this->assertDef('hsl(18%,12%,89%)', false); // integer, percentage, percentage

        // clip numbers outside sRGB gamut
        $this->assertDef('rgb(200%, -10%, 0%)', 'rgb(100%,0%,0%)');
        $this->assertDef('rgb(256,-23,34)', 'rgb(255,0,34)');

        // color keywords, of course
        $this->assertDef('red', '#FF0000');

        // malformed hex declaration
        $this->assertDef('808080', '#808080');
        $this->assertDef('000000', '#000000');
        $this->assertDef('fed', '#fed');

        // maybe hex transformations would be another nice feature
        // at the very least transform rgb percent to rgb integer

    }

}

// vim: et sw=4 sts=4
