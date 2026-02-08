<?php

/**
 * A CardModel which implements CardInterface
 *
 * Represents a Card which can be injected into a Section.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Robert Down <robertdown@live.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Patient\Summary\Card;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CardModel implements CardInterface
{
    private $backgroundColorClass = '';

    private $textColorClass = '';

    private $title = '';

    private $identifier = '';

    private $acl = [];

    private $initiallyCollapsed = false;

    private $add = true;

    private $edit = false;

    private $collapse = true;

    private $templateFile;

    private $templateVariables;

    private $dispatcher;

    public function __construct(array $opts)
    {
        if (!empty($opts['dispatcher']) && $opts['dispatcher'] instanceof EventDispatcherInterface) {
            $this->dispatcher = $opts['dispatcher'];
            unset($opts['dispatcher']);
        } else {
            $this->dispatcher = $GLOBALS['kernel']->getEventDispatcher();
        }
        foreach ($opts as $prop => $val) {
            if (property_exists($this, $prop)) {
                $this->$prop = $val;
            }
        }
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function getBackgroundColorClass(): string
    {
        return $this->backgroundColorClass;
    }

    /**
     * @inheritDoc
     */
    public function getTextColorClass(): string
    {
        return $this->textColorClass;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @inheritDoc
     */
    public function getAcl(): array
    {
        return $this->acl;
    }

    /**
     * @inheritDoc
     */
    public function isInitiallyCollapsed(): bool
    {
        return $this->initiallyCollapsed;
    }

    /**
     * @inheritDoc
     */
    public function canAdd(): bool
    {
        return $this->add;
    }

    /**
     * @inheritDoc
     */
    public function canEdit(): bool
    {
        return $this->edit;
    }

    /**
     * @inheritDoc
     */
    public function canCollapse(): bool
    {
        return $this->collapse;
    }

    /**
     * @inheritDoc
     */
    public function getTemplateFile(): string
    {
        return $this->templateFile;
    }

    /**
     * @inheritDoc
     */
    public function getTemplateVariables(): array
    {
        return $this->templateVariables;
    }

    /**
     * @param mixed $backgroundColorClass
     */
    public function setBackgroundColorClass($backgroundColorClass): void
    {
        $this->backgroundColorClass = $backgroundColorClass;
    }

    /**
     * @param mixed $textColorClass
     */
    public function setTextColorClass($textColorClass): void
    {
        $this->textColorClass = $textColorClass;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @param mixed $identifier
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @param mixed $acl
     */
    public function setAcl($acl): void
    {
        $this->acl = $acl;
    }

    /**
     * @param mixed $initiallyCollapsed
     */
    public function setInitiallyCollapsed($initiallyCollapsed): void
    {
        $this->initiallyCollapsed = $initiallyCollapsed;
    }

    /**
     * @param mixed $add
     */
    public function setAdd($add): void
    {
        $this->add = $add;
    }

    /**
     * @param mixed $edit
     */
    public function setEdit($edit): void
    {
        $this->edit = $edit;
    }

    /**
     * @param mixed $collapse
     */
    public function setCollapse($collapse): void
    {
        $this->collapse = $collapse;
    }

    /**
     * @param mixed $templateFile
     */
    public function setTemplateFile($templateFile): void
    {
        $this->templateFile = $templateFile;
    }

    /**
     * @param mixed $templateVariables
     */
    public function setTemplateVariables($templateVariables): void
    {
        $this->templateVariables = $templateVariables;
    }
}
