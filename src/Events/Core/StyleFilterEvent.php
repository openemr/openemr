<?php

/**
 * StyleFilterEvent class is fired from pages inside OpenEMR and is used to add / remove / filter styles that are
 * to be included inside a <link rel='stylesheet' href=''> tag.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Core;

use OpenEMR\Core\ModulesApplication;

class StyleFilterEvent extends TemplatePageEvent
{
    public const EVENT_NAME = "html.head.style.filter";

    /**
     * @var array
     */
    private $styles;

    /**
     * constructor.
     * @param string $pageName Name of the page that is rendering the scripts
     */
    public function __construct(string $pageName)
    {
        parent::__construct($pageName);
        $this->styles = [];
    }

    /**
     * @return array
     */
    public function getStyles(): array
    {
        return $this->styles;
    }

    /**
     * @param array $scripts
     * @return StyleFilterEvent
     */
    public function setStyles(array $styles): StyleFilterEvent
    {
        $safeStyles = ModulesApplication::filterSafeLocalModuleFiles($styles);
        $this->styles = $safeStyles;
        return $this;
    }
}
