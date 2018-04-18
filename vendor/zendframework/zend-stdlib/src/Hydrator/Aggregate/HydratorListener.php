<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator\Aggregate;

use Zend\Hydrator\Aggregate\HydratorListener as BaseHydratorListener;

/**
 * Aggregate listener wrapping around a hydrator. Listens
 * to {@see \Zend\Stdlib\Hydrator\Aggregate::EVENT_HYDRATE} and
 * {@see \Zend\Stdlib\Hydrator\Aggregate::EVENT_EXTRACT}
 *
 * @deprecated Use Zend\Hydrator\Aggregate\HydratorListener from zendframework/zend-hydrator instead.
 */
class HydratorListener extends BaseHydratorListener
{
}
