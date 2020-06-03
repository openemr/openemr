<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event object template for creating bound filter events
 *
 * @package OpenEMR\Events
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
abstract class AbstractBoundFilterEvent extends Event
{
    /**
     * @var string
     *
     * This is the custom filter that can add to the appointment fetching query
     */
    private $boundFilter = null;

    /**
     * AppointmentsFilterEvent constructor.
     * @param string $boundFilter
     */
    public function __construct(BoundFilter $boundFilter)
    {
        $this->boundFilter = $boundFilter;
    }

    /**
     * @return string
     */
    public function getBoundFilter()
    {
        return $this->boundFilter;
    }

    /**
     * @param $customWhereFilter
     *
     * Add a custom filter to the WHERE clause of patient finder query
     */
    public function setBoundFilter(BoundFilter $boundFilter)
    {
        $this->boundFilter = $boundFilter;
    }
}
