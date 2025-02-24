<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)  Juggernaut Systems Express
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Services\Help;

class HelpServices
{
    private mixed $enableHelp;
    public function __construct()
    {
        $this->enableHelp = (int) $GLOBALS['enable_help'];
    }
    public function getHelpIcon(): string
    {
        return match ($this->enableHelp) {
            1 => $this->generateHelpIcon(
                "Click to view Help",
                "#676666"
            ),
            0 => $this->generateHelpIcon(
                "To enable help - Go to Administration > Globals > Features > Enable Help Modal",
                "#DCD6D0 !important"
            ),
            default => '',
        };
    }

    private function generateHelpIcon($title, $color): string
    {
        return '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:' . $color . '" title="' . xla($title) . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    }
}
