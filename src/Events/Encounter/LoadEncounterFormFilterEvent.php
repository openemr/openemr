<?php

/**
 * LoadEncounterFormFilterEvent.php
 *
 * This event handles the filtering of forms that are loaded for an encounter.  This event is triggered
 * in the view_form.php, load_form.php, and forms.php files for encounter forms.
 *
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Sophisticated Acquisitions <sophisticated.acquisitions@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Encounter;

class LoadEncounterFormFilterEvent
{
    const EVENT_NAME = 'encounter.load_form_filter';
    private $formName;
    private $dir;
    private $pageName;
    private ?int $pid;
    private ?int $encounter;
    private bool $isLBF = false;

    /**
     * @param mixed $formname
     * @param string $dir
     * @param string $pageName
     */
    public function __construct(string $formname, string $dir, string $pageName)
    {
        // if the directory does not exist in the core filesystem we set the dir initially knowing it will be
        // incorrect but we will validate it later
        $this->dir = $dir;
        $this->pageName = $pageName;
        $this->setFormName($formname);
    }

    /**
     * @return mixed
     */
    public function getFormName()
    {
        return $this->formName;
    }

    /**
     * @return string
     */
    public function getPageName(): string
    {
        return $this->pageName;
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
     */
    public function setPid(?int $pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @return mixed
     */
    public function getDir()
    {
        return $this->dir;
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
     */
    public function setEncounter(?int $encounter): void
    {
        $this->encounter = $encounter;
    }

    /**
     * Will die if form name is invalid characters
     * @param mixed $formName
     */
    public function setFormName(string $formName): void
    {
        check_file_dir_name($formName);
        $this->formName = $formName;
    }

    /**
     * @param mixed $pageName
     */
    public function setPageName(string $pageName): void
    {
        $this->pageName = $pageName;
    }

    /**
     * @param string $dir  The directory as a string (note the path will be concatenated to $GLOBALS['fileroot']
     * @throws \InvalidArgumentException if the path is invalid or does not exist.  Paths must currently be within the /interface/forms/ directory or the /interface/modules/ directory
     */
    public function setDir(string $dir): void
    {
        $this->validatePath($dir);
        $this->dir = $dir;
    }

    public function getFormIncludePath()
    {
        return $this->dir . $this->pageName;
    }

    private function validatePath($path)
    {
        $path = realpath($path);
        // for now we will lock this down to just the forms directory or to the modules directory
        $inModules = strpos($path, $GLOBALS['fileroot'] . '/interface/modules/') === 0;
        $inForms = strpos($path, $GLOBALS['fileroot'] . '/interface/forms/') === 0;
        if (!(($inModules || $inForms) && file_exists($path))) {
            throw new \InvalidArgumentException('Invalid path');
        }
    }

    public function setIsLayoutBasedForm(bool $isLBF)
    {
        $this->isLBF = $isLBF;
    }
    public function isLayoutBasedForm(): bool
    {
        return $this->isLBF;
    }
}
