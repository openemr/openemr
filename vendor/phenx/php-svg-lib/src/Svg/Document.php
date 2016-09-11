<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Svg;

use Svg\Surface\SurfaceInterface;
use Svg\Tag\AbstractTag;
use Svg\Tag\Anchor;
use Svg\Tag\Circle;
use Svg\Tag\Ellipse;
use Svg\Tag\Group;
use Svg\Tag\Image;
use Svg\Tag\Line;
use Svg\Tag\LinearGradient;
use Svg\Tag\Path;
use Svg\Tag\Polygon;
use Svg\Tag\Polyline;
use Svg\Tag\Rect;
use Svg\Tag\Stop;
use Svg\Tag\Text;

class Document extends AbstractTag
{
    protected $filename;
    protected $inDefs = false;

    protected $x;
    protected $y;
    protected $width;
    protected $height;

    protected $subPathInit;
    protected $pathBBox;
    protected $viewBox;

    /** @var resource */
    protected $parser;

    /** @var SurfaceInterface */
    protected $surface;

    /** @var AbstractTag[] */
    protected $stack = array();

    /** @var AbstractTag[] */
    protected $defs = array();

    public function loadFile($filename)
    {
        $this->filename = $filename;
    }

    protected function initParser() {
        $parser = xml_parser_create("utf-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler(
            $parser,
            array($this, "_tagStart"),
            array($this, "_tagEnd")
        );
        xml_set_character_data_handler(
            $parser,
            array($this, "_charData")
        );

        return $this->parser = $parser;
    }

    public function __construct() {

    }

    /**
     * @return SurfaceInterface
     */
    public function getSurface()
    {
        return $this->surface;
    }

    public function getStack()
    {
        return $this->stack;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getDimensions() {
        $rootAttributes = null;

        $parser = xml_parser_create("utf-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler(
            $parser,
            function ($parser, $name, $attributes) use (&$rootAttributes) {
                if ($name === "svg" && $rootAttributes === null) {
                    $attributes = array_change_key_case($attributes, CASE_LOWER);

                    $rootAttributes = $attributes;
                }
            },
            function ($parser, $name) {}
        );

        $fp = fopen($this->filename, "r");
        while ($line = fread($fp, 8192)) {
            xml_parse($parser, $line, false);

            if ($rootAttributes !== null) {
                break;
            }
        }

        xml_parser_free($parser);

        return $this->handleSizeAttributes($rootAttributes);
    }

    public function handleSizeAttributes($attributes){
        if ($this->width === null) {
            if (isset($attributes["width"])) {
                $width  = (int)$attributes["width"];
                $this->width  = $width;
            }

            if (isset($attributes["height"])) {
                $height = (int)$attributes["height"];
                $this->height = $height;
            }

            if (isset($attributes['viewbox'])) {
                $viewBox = preg_split('/[\s,]+/is', trim($attributes['viewbox']));
                if (count($viewBox) == 4) {
                    $this->x = $viewBox[0];
                    $this->y = $viewBox[1];

                    if (!$this->width) {
                        $this->width = $viewBox[2];
                    }
                    if (!$this->height) {
                        $this->height = $viewBox[3];
                    }
                }
            }
        }

        return array(
            0        => $this->width,
            1        => $this->height,

            "width"  => $this->width,
            "height" => $this->height,
        );
    }

    protected function getDocument(){
        return $this;
    }

    protected function before($attribs)
    {
        $surface = $this->getSurface();

        $style = new DefaultStyle();
        $style->inherit($this);
        $style->fromAttributes($attribs);

        $this->setStyle($style);

        $surface->setStyle($style);
    }

    public function render(SurfaceInterface $surface)
    {
        $this->inDefs = false;
        $this->surface = $surface;

        $parser = $this->initParser();

        if ($this->x || $this->y) {
            $surface->translate(-$this->x, -$this->y);
        }

        $fp = fopen($this->filename, "r");
        while ($line = fread($fp, 8192)) {
            xml_parse($parser, $line, false);
        }

        xml_parse($parser, "", true);

        xml_parser_free($parser);
    }

    protected function svgOffset($attributes)
    {
        $this->attributes = $attributes;

        $this->handleSizeAttributes($attributes);
    }

    private function _tagStart($parser, $name, $attributes)
    {
        $this->x = 0;
        $this->y = 0;

        $tag = null;

        $attributes = array_change_key_case($attributes, CASE_LOWER);

        switch (strtolower($name)) {
            case 'defs':
                $this->inDefs = true;
                return;

            case 'svg':
                if (count($this->attributes)) {
                    $tag = new Group($this);
                }
                else {
                    $tag = $this;
                    $this->svgOffset($attributes);
                }
                break;

            case 'path':
                $tag = new Path($this);
                break;

            case 'rect':
                $tag = new Rect($this);
                break;

            case 'circle':
                $tag = new Circle($this);
                break;

            case 'ellipse':
                $tag = new Ellipse($this);
                break;

            case 'image':
                $tag = new Image($this);
                break;

            case 'line':
                $tag = new Line($this);
                break;

            case 'polyline':
                $tag = new Polyline($this);
                break;

            case 'polygon':
                $tag = new Polygon($this);
                break;

            case 'lineargradient':
                $tag = new LinearGradient($this);
                break;

            case 'radialgradient':
                $tag = new LinearGradient($this);
                break;

            case 'stop':
                $tag = new Stop($this);
                break;

            case 'a':
                $tag = new Anchor($this);
                break;

            case 'g':
                $tag = new Group($this);
                break;

            case 'text':
                $tag = new Text($this);
                break;
        }

        if ($tag) {
            if (!$this->inDefs) {
                $this->stack[] = $tag;
                $tag->handle($attributes);
            }
            else {
                if (isset($attributes["id"])) {
                    $this->defs[$attributes["id"]] = $tag;
                }
            }
        } else {
            echo "Unknown: '$name'\n";
        }
    }

    function _charData($parser, $data)
    {
        $stack_top = end($this->stack);

        if ($stack_top instanceof Text) {
            $stack_top->appendText($data);
        }
    }

    function _tagEnd($parser, $name)
    {
        /** @var AbstractTag $tag */
        $tag = null;
        switch (strtolower($name)) {
            case 'defs':
                $this->inDefs = false;
                return;

            case 'svg':
            case 'path':
            case 'rect':
            case 'circle':
            case 'ellipse':
            case 'image':
            case 'line':
            case 'polyline':
            case 'polygon':
            case 'radialgradient':
            case 'lineargradient':
            case 'stop':
            case 'text':
            case 'g':
            case 'a':
                if (!$this->inDefs) {
                    $tag = array_pop($this->stack);
                }
                break;
        }

        if ($tag) {
            $tag->handleEnd();
        }
    }
} 