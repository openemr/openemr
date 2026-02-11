<?php

declare(strict_types=1);

namespace Db\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211203419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
        parent::down($schema);
    }
}

