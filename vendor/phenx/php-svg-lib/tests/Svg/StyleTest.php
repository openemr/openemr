<?php
/**
 * Created by PhpStorm.
 * User: Fabien
 * Date: 13/04/14
 * Time: 17:42
 */

namespace Svg\Tests;

include_once __DIR__."/../../src/autoload.php";

use Svg\Style;

class StyleTest extends \PHPUnit_Framework_TestCase {
  public function test_parseColor() {
    $this->assertEquals("none",               Style::parseColor("none"));
    $this->assertEquals(array(255,   0,   0), Style::parseColor("RED"));
    $this->assertEquals(array(0,     0, 255), Style::parseColor("blue"));
    $this->assertEquals(null,                 Style::parseColor("foo"));
  }

  public function test_fromAttributes() {
    $style = new Style();

    $attributes = array(
      "color"  => "blue",
      "fill"   => "#fff",
      "stroke" => "none",
    );

    $style->fromAttributes($attributes);

    $this->assertEquals(array(0,     0, 255), $style->color);
    $this->assertEquals(array(255, 255, 255), $style->fill);
    $this->assertEquals("none",               $style->stroke);
  }

    public function test_convertSize(){
        $this->assertEquals(1,  Style::convertSize(1));
        $this->assertEquals(10, Style::convertSize("10px")); // FIXME
        $this->assertEquals(10, Style::convertSize("10pt"));
        $this->assertEquals(8,  Style::convertSize("80%", 72, 10));
    }
}
 