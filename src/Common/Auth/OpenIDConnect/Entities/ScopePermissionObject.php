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


class ScopePermissionObject {

    public function __construct(?string $permissionString)
    {
        $this->read = false;
        $this->create = false;
        $this->update = false;
        $this->delete = false;
        $this->search = false;
        $this->v1Read = false;
        $this->v1Write = false;
        if (!empty($permissionString)) {
            if ($permissionString === 'read') {
                $this->v1Read = true;
            } elseif ($permissionString === 'write') {
                $this->v1Write = true;
            }
            $matches = [];
            if (preg_match("/^[cruds]{1}[ruds]?[uds]?[ds]?[s]?/", $permissionString, $matches) !== 1) {
                throw new \InvalidArgumentException("Invalid permission string: " . $permissionString);
            }
            // need to make this more efficient
            $this->create = str_contains($permissionString, 'c');
            $this->read = str_contains($permissionString, 'r');
            $this->update = str_contains($permissionString, 'u');
            $this->delete = str_contains($permissionString, 'd');
            $this->search = str_contains($permissionString, 's');
            $queryPos = strpos($permissionString, "?");
            if ($queryPos !== false) {
                $query = substr($permissionString, $queryPos + 1);
                $constraints = http_parse_params($query);
                // convert stdClass to array
                $this->constraints = (array)$constraints ?? [];
            }
        }
    }

    public bool $v1Read = false;
    public bool $v1Write = false;
    public bool $create = false;
    public bool $read = false;
    public bool $update = false;
    public bool $delete = false;
    public bool $search = false;

    private array $constraints = [];

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function addConstraints(array $constraints) : void
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
