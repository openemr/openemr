<?php

/**
 * A helper class that is MenuItemInterface-compliant.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2023 Providence Healthtech
 * @copyright Copyright (c) 2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

use Google\Service\CloudSearch\MenuItem;

class BaseMenuItem implements MenuItemInterface
{
    private $displayText;

    private $id;

    private $target;

    private $url;

    private $children;

    private $requirements;

    private $acl;

    private $globalReqStrict;

    private $globalReq;

    private $preTextContent;

    private $postTextContent;

    private $linkClassList;

    private $linkContainerClassList;

    private $attributes;

    /**
     * Hydrate the class with your requirements.
     *
     * $opts is an associative array where key matches the name of a private property of this class and value is the
     * value to be set.
     *
     * @param array $opts
     */
    public function __construct(array $opts)
    {
        foreach ($opts as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        if (!$this->children) {
            $this->children = new MenuItems();
        }
    }

    /**
     * @inheritDoc
     */
    public function getDisplayText(): string
    {
        return $this->displayText;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): MenuItems
    {
        return $this->children;
    }

    /**
     * @inheritDoc
     */
    public function getRequirements(): int
    {
        return $this->requirements ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function getAcl(): array
    {
        return $this->acl ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getGlobalReqStrict(): array
    {
        return $this->globalReqStrict ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getGlobalReq(): array|string
    {
        return $this->globalReq ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getPreTextContent(): string
    {
        return $this->preTextContent ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getPostTextContent(): string
    {
        return $this->postTextContent ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getLinkClassList(): array
    {
        return $this->linkClassList ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getLinkContainerClassList(): array
    {
        return $this->linkContainerClassList ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes ?? [];
    }
}
