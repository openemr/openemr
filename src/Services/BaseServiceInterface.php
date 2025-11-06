<?php

/**
 * Base Service Interface
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Validators\ProcessingResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

interface BaseServiceInterface
{
    public function getEventDispatcher(): EventDispatcher;

    public function setEventDispatcher(EventDispatcher $dispatcher);

    public function setSession(SessionInterface $session): void;

    public function getSession(): ?SessionInterface;

    public function getTable();

    public function getFields(): array;

    public function getSelectFields(string $tableAlias = '', string $columnPrefix = ""): array;

    public function getUuidFields(): array;

    public function getSelectJoinTables(): array;

    public function queryFields($map = null, $data = null);

    public function setLogger(LoggerInterface $logger);

    public function getLogger(): LoggerInterface;

    public function selectHelper($sqlUpToFromStatement, $map);

    public function search($search, $isAndCondition = true);

    public function getFreshId($idField, $table);

    public function filterData($data, $whitelistedFields = null);

    public static function throwException($message, $type = "Error");

    public static function isValidDate($dateString);

    public static function sqlCondition($condition);

    public static function getIdByUuid($uuid, $table, $field);

    public static function getUuidById($id, $table, $field);

    public static function processDateTime($date);
}
