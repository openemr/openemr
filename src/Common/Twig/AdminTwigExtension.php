<?php

/**
 * TODO: @adunsulag fill out copyright header
 */

namespace OpenEMR\Common\Twig;

use OpenEMR\Common\Acl\AclExtended;
use Twig\TwigFunction;

class AdminTwigExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        // other form specific functions can go here
        return [
            new TwigFunction('genAcoHtmlOptions', function ($aco_spec, $acoOptions = null) {
                echo AclExtended::genAcoHtmlOptions($aco_spec, $acoOptions);
            })
        ];
    }
}
