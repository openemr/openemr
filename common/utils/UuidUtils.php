<?php
/**
 * Created by PhpStorm.
 * User: Gerhard
 * Date: 06-Apr-19
 * Time: 16:17
 */

namespace OpenEMR\Common\Utils;

use Ramsey\Uuid\Uuid;

class UuidUtils
{

    const MAX_TRIES = 100;

    /**
     * @param string $table
     *
     * @return string
     * @throws \Exception
     */
    public static function createUuid(string $table): string
    {
        $uuid4 = Uuid::uuid4();

        $try = 1;
        while (self::findCollision($table, $uuid4)) {
            if ($try > self::MAX_TRIES) {
                throw new \Exception('Reached maximum amount of tries (max amount: '.self::MAX_TRIES.')');
            }

            //$uuid4 = Uuid::uuid4();
            $try++;
        }

        return $uuid4->toString();
    }

    /**
     * @param $table
     * @param $uuid
     *
     * @return bool
     */
    public static function findCollision(string $table, string $uuid): bool
    {
        $query = "SELECT uuid FROM ".$table." WHERE uuid = ?";

        if (sqlQuery($query, $uuid)) {
            return true;
        }

        return false;
    }
}
