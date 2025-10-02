<?php

/*
 * ScopePermissionObject.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Entities;

use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ScopePermissionObject
{
    use EntityTrait;

    public function __construct(?string $identifier = null)
    {
        if (!empty($identifier)) {
            $this->setIdentifier($identifier);
        }
        $this->read = false;
        $this->create = false;
        $this->update = false;
        $this->delete = false;
        $this->search = false;
        $this->v1Read = false;
        $this->v1Write = false;
        $this->constraints = [];
    }

    /**
     * @param string $permissionString
     * @throws \InvalidArgumentException if the permission string is invalid
     * @return ScopePermissionObject
     */
    public static function createFromString(string $permissionString): ScopePermissionObject
    {
        $obj = new self($permissionString);
        if (!empty($permissionString)) {
            if ($permissionString === 'read') {
                // 'read' -> 'rs'
                $obj->v1Read = true;
                $obj->read = true;
                $obj->search = true;
            } elseif ($permissionString === 'write') {
                // write -> 'cud'
                $obj->v1Write = true;
                $obj->create = true;
                $obj->update = true;
                $obj->delete = true;
            } else {
                $matches = [];
                $parts = explode("?", $permissionString);
                $permission = $parts[0];
                $query = $parts[1] ?? '';
                if (self::isOrderedCrudString($permission) === false) {
                    throw new \InvalidArgumentException("Invalid permission string: " . $permissionString);
                }

                $obj->create = str_contains($permission, 'c');
                $obj->read = str_contains($permission, 'r');
                $obj->update = str_contains($permission, 'u');
                $obj->delete = str_contains($permission, 'd');
                $obj->search = str_contains($permission, 's');
                if (!empty($query)) {
                    $constraints = [];
                    parse_str($query, $constraints);
                    $obj->constraints = $constraints;
                }

                if ($obj->create && $obj->update && $obj->delete) {
                    $obj->v1Write = true; // if we have all three, we can consider it a v1 write
                }
                if ($obj->read && $obj->search) {
                    $obj->v1Read = true; // if we have read and search, we can consider it a v1 read
                }
            }
        }
        return $obj;
    }

    // generated using chatgpt July 31st 2025
    public static function isOrderedCrudString($input)
    {

        // we could go with a regex, but let's do it manually for clarity
//        $matches = [];
//        if (preg_match("/^(c)?(r)?(u)?(d)?(s)?$/", $input, $matches) !== 1) {
//            return false;
//        }
        $allowed = 'cruds';
        $lastPos = -1;

        $seen = [];
        $strlen = strlen((string) $input);
        for ($i = 0; $i < $strlen; $i++) {
            $char = $input[$i];
            $pos = strpos($allowed, (string) $char);
            if ($pos === false) {
                return false; // invalid character
            }
            if (in_array($char, $seen)) {
                return false; // repeat not allowed
            }
            if ($pos < $lastPos) {
                return false; // out of order
            }
            $seen[] = $char;
            $lastPos = $pos;
        }
        return true;
    }
    // end of generated code

    public bool $v1Read = false;
    public bool $v1Write = false;
    public bool $create = false;
    public bool $read = false;
    public bool $update = false;
    public bool $delete = false;
    public bool $search = false;

    private array $constraints = [];

    public function getPermissionsAsArray()
    {
        return [
            'create' => $this->create,
            'read' => $this->read,
            'update' => $this->update,
            'delete' => $this->delete,
            'search' => $this->search,
            'v1Read' => $this->v1Read,
            'v1Write' => $this->v1Write
        ];
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function addConstraints(array $constraints): void
    {
        foreach ($constraints as $key => $value) {
            if (!empty($this->constraints[$key])) {
                if (is_string($this->constraints[$key])) {
                    $this->constraints[$key] = [$this->constraints[$key]];
                    $this->constraints[$key][] = $value;
                } else {
                    // if the existing value is an array, we can just append the new value
                    $this->constraints[$key][] = $value;
                }
            } else {
                $this->constraints[$key] = $value;
            }
        }
    }
}
