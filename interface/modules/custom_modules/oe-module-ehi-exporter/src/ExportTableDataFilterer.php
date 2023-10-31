<?php

namespace OpenEMR\Modules\EhiExporter;

class ExportTableDataFilterer
{
    public function generateSelectQueryForTableFromMetadata(ExportTableDefinition $tableDef, \SimpleXMLElement $metaNode)
    {
        // grab the table node with the attribute name of the table
        // grab all of the column nodes where the element has an attribute of exclude='true'
        // create a select query where all of the table columns are selected but the exclude columns are set to null
        // on the retrieval so that they are not included in the export.

        $xpathColumnsToExclude = $metaNode->xpath("//table[@name='" . $tableDef->table . "']/column[@exclude='true']");
        if (!empty($xpathColumnsToExclude)) {
            $columns = $tableDef->getColumnNames();
            $hashMap = array_combine($columns, $columns);
            foreach ($xpathColumnsToExclude as $excludeColumn) {
                $columnName = (string)($excludeColumn->attributes()['name']) ?? "";
                if (!empty($columnName) && !empty($hashMap[$columnName])) {
                    $hashMap[$columnName] = "null as " . $columnName;
                }
            }
            $selectClause = implode(',', $hashMap);
            $tableDef->setSelectClause($selectClause);
        }
    }
}
