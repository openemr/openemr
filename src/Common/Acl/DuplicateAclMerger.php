<?php

/**
 * Folds a group's duplicate ACLs for one return value into a single ACL.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Acl;

use OpenEMR\Gacl\GaclApi;

/**
 * The ACL admin screens and AclExtended::updateAcl() act only on the first
 * ACL a group holds for a return value, so objects granted by a second one
 * cannot be edited. Installs from 8.2.0 through 8.4.x hold two Clinicians
 * 'write' ACLs.
 */
final readonly class DuplicateAclMerger
{
    public function __construct(private GaclApi $gacl)
    {
    }

    /**
     * Moves the objects of every duplicate into the lowest-numbered ACL and
     * deletes the duplicate. A duplicate that differs in anything but its
     * objects and note, such as granting another group, is left alone.
     *
     * @return list<int> IDs of the deleted duplicates
     */
    public function merge(string $groupTitle, string $returnValue): array
    {
        $found = $this->gacl->search_acl(false, false, false, false, $groupTitle, false, false, false, $returnValue);
        if (!is_array($found)) {
            return [];
        }
        $aclIds = array_map(intval(...), array_filter($found, is_numeric(...)));
        sort($aclIds);
        $keepId = array_shift($aclIds);
        if ($keepId === null) {
            return [];
        }
        $keep = $this->gacl->get_acl($keepId);
        if (!is_array($keep)) {
            return [];
        }

        $merged = [];
        foreach ($aclIds as $aclId) {
            $duplicate = $this->gacl->get_acl($aclId);
            if (!is_array($duplicate) || self::grant($duplicate) !== self::grant($keep)) {
                continue;
            }
            $objects = $duplicate['aco'] ?? [];
            if (!is_array($objects)) {
                continue;
            }
            if ($objects !== [] && !$this->gacl->append_acl($keepId, null, null, null, null, $objects)) {
                continue;
            }
            if ($this->gacl->del_acl($aclId)) {
                $merged[] = $aclId;
            }
        }
        return $merged;
    }

    /**
     * @param array<mixed> $acl get_acl() result
     * @return array<mixed> everything that says who the ACL applies to and how
     */
    private static function grant(array $acl): array
    {
        unset($acl['acl_id'], $acl['aco'], $acl['note']);
        return $acl;
    }
}
