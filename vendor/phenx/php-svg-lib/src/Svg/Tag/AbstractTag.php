<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Svg\Tag;

use Svg\Document;
use Svg\Style;

abstract class AbstractTag
{
    /** @var Document */
    protected $document;

    /** @var Style */
    protected $style;

    protected $attributes;

    protected $hasShape = true;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    protected function getDocument(){
        return $this->document;
    }

    /**
     * @return Group|null
     */
    public function getParentGroup() {
        $stack = $this->getDocument()->getStack();
        for ($i = count($stack)-2; $i >= 0; $i--) {
            $tag = $stack[$i];

            if ($tag instanceof Group || $tag instanceof Document) {
                return $tag;
            }
        }

        return null;
    }

    public final function handle($attributes)
    {
        $this->attributes = $attributes;

        $this->before($attributes);
        $this->start($attributes);
    }

    public final function handleEnd()
    {
        $this->end();
        $this->after();
    }

    protected function before($attribs)
    {
    }

    protected function start($attribs)
    {
    }

    protected function end()
    {
    }

    protected function after()
    {
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    protected function setStyle(Style $style)
    {
        $this->style = $style;
    }

    /**
     * @return \Svg\Style
     */
    public function getStyle()
    {
        return $this->style;
    }

    protected function applyTransform($attribs)
    {

        if (isset($attribs["transform"])) {
            $surface = $this->document->getSurface();

            $transform = $attribs["transform"];

            $match = array();
            preg_match_all(
                '/(matrix|translate|scale|rotate|skewX|skewY)\((.*?)\)/is',
                $transform,
                $match,
                PREG_SET_ORDER
            );

            $transformations = array();
            if (count($match[0])) {
                foreach ($match as $_match) {
                    $arguments = preg_split('/[ ,]+/', $_match[2]);
                    array_unshift($arguments, $_match[1]);
                    $transformations[] = $arguments;
                }
            }

            foreach ($transformations as $t) {
                switch ($t[0]) {
                    case "matrix":
                        $surface->transform($t[1], $t[2], $t[3], $t[4], $t[5], $t[6]);
                        break;

                    case "translate":
                        $surface->translate($t[1], isset($t[2]) ? $t[2] : 0);
                        break;

                    case "scale":
                        $surface->scale($t[1], isset($t[2]) ? $t[2] : $t[1]);
                        break;

                    case "rotate":
                        $surface->rotate($t[1]);
                        break;

                    case "skewX":
                        $surface->skewX($t[1]);
                        break;

                    case "skewY":
                        $surface->skewY($t[1]);
                        break;
                }
            }
        }
    }
} 