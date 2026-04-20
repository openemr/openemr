<?php

/**
 * A helper class that is MenuItemInterface-compliant.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2023 Providence Healthtech
 * @copyright Copyright (c) 2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

class BaseMenuItem implements MenuItemInterface
{
    private string $displayText = '';

    private string $id = '';

    private string $target = '';

    private string $url = '';

    private ?MenuItems $children = null;

    private int $requirements = 0;

    /** @var array<mixed> */
    private array $acl = [];

    /** @var array<mixed> */
    private array $globalReqStrict = [];

    /** @var array<mixed>|string */
    private array|string $globalReq = [];

    private string $preTextContent = '';

    private string $postTextContent = '';

    /** @var array<mixed> */
    private array $linkClassList = [];

    /** @var array<mixed> */
    private array $linkContainerClassList = [];

    /** @var array<mixed> */
    private array $attributes = [];

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

        if ($this->children === null) {
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
        return $this->requirements;
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
    public function getGlobalReqStrict(): array
    {
        return $this->globalReqStrict;
    }

    /**
     * @inheritDoc
     */
    public function getGlobalReq(): array|string
    {
        return $this->globalReq;
    }

    /**
     * @inheritDoc
     */
    public function getPreTextContent(): string|bool
    {
        return $this->preTextContent;
    }

    /**
     * @inheritDoc
     */
    public function getPostTextContent(): string|bool
    {
        return $this->postTextContent;
    }

    /**
     * @inheritDoc
     */
    public function getLinkClassList(): array
    {
        return $this->linkClassList;
    }

    /**
     * @inheritDoc
     */
    public function getLinkContainerClassList(): array
    {
        return $this->linkContainerClassList;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
