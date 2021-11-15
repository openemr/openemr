<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\Doctype;
use Psr\Container\ContainerInterface;

final class DoctypeFactory
{
    public function __invoke(ContainerInterface $container): Doctype
    {
        $helper = new Doctype();

        if (! $container->has('config')) {
            return $helper;
        }
        $config = $container->get('config');
        if (isset($config['view_helper_config']['doctype'])) {
            $helper->setDoctype($config['view_helper_config']['doctype']);
        }

        return $helper;
    }
}
