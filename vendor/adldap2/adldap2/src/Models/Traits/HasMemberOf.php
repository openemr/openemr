<?php

namespace Adldap\Models\Traits;

use Adldap\Utilities;
use Adldap\Models\Group;
use Illuminate\Support\Collection;

trait HasMemberOf
{
    /**
     * Adds the current model to the specified group.
     *
     * @param string|Group $group
     *
     * @return bool
     */
    public function addGroup($group)
    {
        if (is_string($group)) {
            // If the group is a string, we'll assume the dev is passing
            // in a DN string of the group. We'll try to locate it.
            $group = $this->query->newInstance()->findByDn($group);
        }

        if ($group instanceof Group) {
            // If the group is Group model instance, we can
            // add the current models DN to the group.
            return $group->addMember($this->getDn());
        }

        return false;
    }

    /**
     * Removes the current model from the specified group.
     *
     * @param string|Group $group
     *
     * @return bool
     */
    public function removeGroup($group)
    {
        if (is_string($group)) {
            // If the group is a string, we'll assume the dev is passing
            // in a DN string of the group. We'll try to locate it.
            $group = $this->query->newInstance()->findByDn($group);
        }

        if ($group instanceof Group) {
            // If the group is Group model instance, we can
            // remove the current models DN from the group.
            return $group->removeMember($this->getDn());
        }

        return false;
    }

    /**
     * Returns the models groups that it is apart of.
     *
     * If a recursive option is given, groups of groups
     * are retrieved and then merged with
     * the resulting collection.
     *
     * https://msdn.microsoft.com/en-us/library/ms677099(v=vs.85).aspx
     *
     * @param array $fields
     * @param bool  $recursive
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGroups($fields = [], $recursive = false)
    {
        $dns = $this->getAttribute($this->schema->memberOf());

        $dns = is_array($dns) ? $dns : [];

        $query = $this->query->newInstance();

        $groups = $query->newCollection($dns)->map(function ($dn) use ($query, $fields) {
            return $query->select($fields)->findByDn($dn);
        })->filter(function ($group) {
            return $group instanceof Group;
        });

        if ($recursive) {
            // If recursive results are requested, we'll ask each group
            // for their groups, and merge the resulting collection.
            foreach ($groups as $group) {
                $groups = $groups->merge($group->getGroups($fields, $recursive));
            }
        }

        return $groups;
    }

    /**
     * Returns the models groups names in a single dimension array.
     *
     * If a recursive option is given, groups of groups
     * are retrieved and then merged with
     * the resulting collection.
     *
     * @param bool $recursive
     *
     * @return array
     */
    public function getGroupNames($recursive = false)
    {
        $names = $this->getGroups([$this->schema->commonName()], $recursive)->map(function (Group $group) {
            return $group->getCommonName();
        })->toArray();

        return array_unique($names);
    }

    /**
     * Determine if the current model is a member of the specified group(s).
     *
     * @param mixed $group
     * @param bool  $recursive
     *
     * @return bool
     */
    public function inGroup($group, $recursive = false)
    {
        $memberOf = $this->getGroups([], $recursive);

        if ($group instanceof Collection) {
            // If we've been given a collection then we'll convert
            // it to an array to normalize the value.
            $group = $group->toArray();
        }

        $groups = is_array($group) ? $group : [$group];

        foreach ($groups as $group) {
            // We need to iterate through each given group that the
            // model must be apart of, then go through the models
            // actual groups and perform validation.
            $exists = $memberOf->filter(function (Group $parent) use ($group) {
                return $this->validateGroup($group, $parent);
            })->count() !== 0;

            if (!$exists) {
                // If the current group isn't at all contained
                // in the memberOf collection, we'll
                // return false here.
                return false;
            }
        }

        return true;
    }

    /**
     * Validates if the specified group is the given parent instance.
     *
     * @param Group|string $group
     * @param Group        $parent
     *
     * @return bool
     */
    protected function validateGroup($group, Group $parent)
    {
        if ($group instanceof Group) {
            // We've been given a group instance, we'll compare their DNs.
            return $parent->getDn() === $group->getDn();
        }

        if (Utilities::explodeDn($group)) {
            // We've been given a DN, we'll compare it to the parents.
            return $parent->getDn() === $group;
        }

        if (!empty($group)) {
            // We've been given just a string, we'll
            // compare it to the parents name.
            return $parent->getCommonName() === $group;
        }

        return false;
    }
}
