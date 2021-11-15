<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Platform\SqlServer\Ddl;

use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Platform\PlatformDecoratorInterface;

class CreateTableDecorator extends CreateTable implements PlatformDecoratorInterface
{
    /**
     * @var CreateTable
     */
    protected $subject;

    /**
     * @param CreateTable $subject
     * @return self Provides a fluent interface
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @param PlatformInterface $adapterPlatform
     * @return array
     */
    protected function processTable(PlatformInterface $adapterPlatform = null)
    {
        $table = ($this->isTemporary ? '#' : '') . ltrim($this->table, '#');
        return [
            '',
            $adapterPlatform->quoteIdentifier($table),
        ];
    }
}
