<?php

namespace OpenEMR\Modules\EhiExporter;

class ExportKeyDefinition
{
    public function __construct()
    {
        $this->keyType = "child";
    }

    public string $foreignKeyTable;
    public string $foreignKeyColumn;

    public string $localTable;
    public string $localColumn;

    /**
     * Allows a local column value to be overridden for tables such as list_options to make sure
     * we grab the entire list.
     * @var string|null
     */
    public ?string $localValueOverride = null;

    /**
     * @var "parent"|"child"
     */
    public string $keyType;

    public bool $isDenormalized = false;

    public string $denormalizedKeySeparator = '|'; // most keys are separated by a pipe when its denormalized
}
