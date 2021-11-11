<?php

/**
 * ScriptHeadFilterEvent class is fired from pages inside OpenEMR and is used to add / remove / filter scripts that are
 * to be included inside a <script src=''> tag.
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

class ScriptFilterEvent extends TemplatePageEvent
{
    public const EVENT_NAME = "html.head.script.filter";

    /**
     * @var array
     */
    private $scripts;

    /**
     * ScriptHeadFilterEvent constructor.
     * @param string $pageName Name of the page that is rendering the scripts
     */
    public function __construct(string $pageName)
    {
        parent::__construct($pageName);
        $this->scripts = [];
    }

    /**
     * @return array
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }

    /**
     * @param array $scripts
     * @return ScriptFilterEvent
     */
    public function setScripts(array $scripts): ScriptFilterEvent
    {
        $safeScripts = ModulesApplication::filterSafeLocalModuleFiles($scripts);
        $this->scripts = $safeScripts;
        return $this;
    }
}
