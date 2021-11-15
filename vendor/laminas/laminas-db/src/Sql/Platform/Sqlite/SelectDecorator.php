<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Platform\Sqlite;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sql\Platform\PlatformDecoratorInterface;
use Laminas\Db\Sql\Select;

class SelectDecorator extends Select implements PlatformDecoratorInterface
{
    /**
     * @var Select
     */
    protected $subject = null;

    /**
     * Set Subject
     *
     * @param Select $select
     * @return self Provides a fluent interface
     */
    public function setSubject($select)
    {
        $this->subject = $select;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function localizeVariables()
    {
        parent::localizeVariables();
        $this->specifications[self::COMBINE] = '%1$s %2$s';
    }

    /**
     * {@inheritDoc}
     */
    protected function processStatementStart(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        return '';
    }

    protected function processLimit(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if ($this->limit === null && $this->offset !== null) {
            return [''];
        }
        if ($this->limit === null) {
            return;
        }
        if ($parameterContainer) {
            $paramPrefix = $this->processInfo['paramPrefix'];
            $parameterContainer->offsetSet($paramPrefix . 'limit', $this->limit, ParameterContainer::TYPE_INTEGER);
            return [$driver->formatParameterName('limit')];
        }

        return [$this->limit];
    }

    protected function processOffset(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if ($this->offset === null) {
            return;
        }
        if ($parameterContainer) {
            $paramPrefix = $this->processInfo['paramPrefix'];
            $parameterContainer->offsetSet($paramPrefix . 'offset', $this->offset, ParameterContainer::TYPE_INTEGER);
            return [$driver->formatParameterName('offset')];
        }

        return [$this->offset];
    }

    /**
     * {@inheritDoc}
     */
    protected function processStatementEnd(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        return '';
    }
}
