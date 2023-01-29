<?php
/**
 *
 */

namespace OpenEMR\Menu;

class BaseMenuItem implements MenuItemInterface
{
    private $displayText;

    private $id;

    private $target;

    private $url;

    private $children;

    private $requirements;

    private $acl;

    public function __construct($opts)
    {
        foreach ($opts as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
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
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getAcl(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getGlobalReqStrict(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getGlobalReq(): array|string
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getPreTextContent(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getPostTextContent(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getLinkClassList(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getLinkContainerClassList(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return [];
    }
}
