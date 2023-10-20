<?php

namespace OpenEMR\Modules\EhiExporter;

class ExportKeyDefinition
{
    public string $foreignKeyTable;
    public string $foreignKeyColumn;

    public string $localTable;
    public string $localColumn;

    /**
     * @var "parent"|"child"
     */
    public string $keyType;
}
