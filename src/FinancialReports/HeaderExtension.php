<?php
/**
 * Header Extension.
 * This file goes in the twig/twig/src folder
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Twig;

use Twig\Extension\AbstractExtension;

class HeaderExtension extends AbstractExtension
{
    public function getFunctions() {
        return [
            new TwigFunction(
                'header_setup',
                [\OpenEMR\Core\Header::class, 'setupHeader']
            ),
            // add more if needed
        ];
    }
}
