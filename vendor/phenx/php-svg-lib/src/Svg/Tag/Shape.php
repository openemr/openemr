<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Svg\Tag;

use Svg\Style;

class Shape extends AbstractTag
{
    protected function before($attribs)
    {
        $surface = $this->document->getSurface();

        $surface->save();

        $style = new Style();
        $style->inherit($this);
        $style->fromAttributes($attribs);

        $this->setStyle($style);

        $surface->setStyle($style);

        $this->applyTransform($attribs);
    }

    protected function after()
    {
        $surface = $this->document->getSurface();

        if ($this->hasShape) {
            $style = $surface->getStyle();

            $fill   = $style->fill   && $style->fill   !== "none";
            $stroke = $style->stroke && $style->stroke !== "none";

            if ($fill) {
                if ($stroke) {
                    $surface->fillStroke();
                } else {
                    $surface->fill();
                }
            } elseif ($stroke) {
                $surface->stroke();
            }
            else {
                $surface->endPath();
            }
        }

        $surface->restore();
    }
} 