<?php

/**
 * EncounterFormsListRenderEvent is used to launch different rendering action points that developers can output their
 * own HTML content during the forms.php encounter list sequence.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Discover and Change, Inc. <snielson@dicsoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Encounter;

class EncounterFormsListRenderEvent
{
    /**
     * Allows screen output after all of the encounter forms have been rendered for the encounter/forms.php screen
     */
    const EVENT_SECTION_RENDER_PRE = 'forms.encounter.list.render.pre';

    /**
     * Allows screen output after all of the encounter forms have been rendered for the encounter/forms.php screen
     */
    const EVENT_SECTION_RENDER_POST = "forms.encounter.list.render.post";

    public function __construct(
        /** @var int|null The encounter id that the encounter is for (matches the forms.encounter db id) */
        private ?int $encounter = null,
        /** @var string $attendantType 'gid' or 'pid' The type of encounter that is being rendered, a group encounter (gid), or an individual patient encounter (pid) */
        private string $attendantType = 'pid',
        /** @var int|null The patient pid that the encounter is for (matches the patient_data.pid column) */
        private ?int $pid = null,
        /** @var int|null $groupId The group id that the encounter is for */
        private ?int $groupId = null
    ) {
    }

    public function getAttendantType(): string
    {
        return $this->attendantType;
    }

    /**
     * @param string $attendantType 'gid' or 'pid' The type of encounter that is being rendered, a group encounter (gid), or an individual patient encounter (pid)
     */
    public function setAttendantType(string $attendantType): void
    {
        $this->attendantType = $attendantType == 'gid' ? 'gid' : 'pid';
    }

    /**
     * @param int|null $groupId The group id that the encounter is for
     */
    public function setGroupId(?int $groupId): void
    {
        $this->groupId = $groupId;
    }

    /**
     * @return int|null
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @return int|null
     */
    public function getPid(): ?int
    {
        return $this->pid;
    }

    /**
     * @param int|null $pid
     * @return EncounterFormsListRenderEvent
     */
    public function setPid(?int $pid): EncounterFormsListRenderEvent
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getEncounter(): ?int
    {
        return $this->encounter;
    }

    /**
     * @param int|null $encounter
     * @return EncounterFormsListRenderEvent
     */
    public function setEncounter(?int $encounter): EncounterFormsListRenderEvent
    {
        $this->encounter = $encounter;
        return $this;
    }
}
