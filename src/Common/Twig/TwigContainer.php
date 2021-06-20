<?php

/**
 * TwigContainer class.
 *
 * OpenEMR central container interface for twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Twig;

use OpenEMR\Common\Twig\TwigExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigContainer
{
    private $path;  // path in /templates

    public function __construct($path = null)
    {
        $this->path = $GLOBALS['fileroot'] . '/templates';
        if (!empty($path)) {
            $this->path = $this->path . '/' . $path;
        }
    }

    public function getTwig()
    {
        $twigLoader = new FilesystemLoader($this->path);
        $twigEnv = new Environment($twigLoader, ['autoescape' => false]);
        $twigEnv->addExtension(new TwigExtension());
        return $twigEnv;
    }
}
