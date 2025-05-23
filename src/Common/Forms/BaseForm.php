<?php

/**
 * BaseForm represents an encounter forms database table record used inside OpenEMR.  It sets up default properties
 * from the session but consumers can override the options.
 *
 * @see \OpenEMR\Services\FormService on how this class is saved in the database.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms;

abstract class BaseForm
{
    private ?int $id;
    private \DateTime $date;
    private int $encounter;
    private string $form_name;
    private string $form_id;
    private int $pid;
    private ?string $user;
    private ?string $groupname;
    private int $authorized;
    private string $formdir;
    private ?int $therapy_group_id;

    public function __construct()
    {
        global $attendant_type;

        $this->id = null;
        $this->authorized = 0;
        $this->date = new \DateTime();

        if ($attendant_type == 'pid') {
            $this->therapy_group_id = null;
        } else {
            $this->therapy_group_id = $_SESSION['therapy_group'] ?? null;
        }
        $this->user = $_SESSION['authUser'] ?? null;
        $this->groupname = $_SESSION['authProvider'] ?? null;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return BaseForm
     */
    public function setId(?int $id): BaseForm
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return BaseForm
     */
    public function setDate(\DateTime $date): BaseForm
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int
     */
    public function getEncounter(): int
    {
        return $this->encounter;
    }

    /**
     * @param int $encounter
     * @return BaseForm
     */
    public function setEncounter(int $encounter): BaseForm
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormName(): string
    {
        return $this->form_name;
    }

    /**
     * @param string $form_name
     * @return BaseForm
     */
    public function setFormName(string $form_name): BaseForm
    {
        $this->form_name = $form_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormId(): string
    {
        return $this->form_id;
    }

    /**
     * @param string $form_id
     * @return BaseForm
     */
    public function setFormId(string $form_id): BaseForm
    {
        $this->form_id = $form_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     * @return BaseForm
     */
    public function setPid(int $pid): BaseForm
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @param string|null $user
     * @return BaseForm
     */
    public function setUser(?string $user): BaseForm
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGroupname(): ?string
    {
        return $this->groupname;
    }

    /**
     * @param string|null $groupname
     * @return BaseForm
     */
    public function setGroupname(?string $groupname): BaseForm
    {
        $this->groupname = $groupname;
        return $this;
    }

    /**
     * @return int
     */
    public function getAuthorized(): int
    {
        return $this->authorized;
    }

    /**
     * @param int $authorized
     * @return BaseForm
     */
    public function setAuthorized(int $authorized): BaseForm
    {
        $this->authorized = $authorized;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormdir(): string
    {
        return $this->formdir;
    }

    /**
     * @param string $formdir
     * @return BaseForm
     */
    public function setFormdir(string $formdir): BaseForm
    {
        $this->formdir = $formdir;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTherapyGroupId(): ?int
    {
        return $this->therapy_group_id;
    }

    /**
     * @param int|null $therapy_group_id
     * @return BaseForm
     */
    public function setTherapyGroupId(?int $therapy_group_id): BaseForm
    {
        $this->therapy_group_id = $therapy_group_id;
        return $this;
    }



    public function getEncounterFormDataForSave(): array
    {
        $array = [
            'date' => $this->getDate()->format("Y-m-d H:i:s")
            ,'encounter' => $this->getEncounter()
            ,'form_name' => $this->getFormName()
            ,'form_id' => $this->getFormId()
            ,'pid' => $this->getPid()
            ,'user' => $this->getUser()
            ,'groupName' => $this->getGroupname()
            ,'authorized' => $this->getAuthorized()
            ,'formdir' => $this->getFormdir()
            ,'therapy_group_id' => $this->getTherapyGroupId()
        ];
        return $array;
    }

    abstract public function getFormTableDataForSave();

    abstract public function getFormTableName();
}
