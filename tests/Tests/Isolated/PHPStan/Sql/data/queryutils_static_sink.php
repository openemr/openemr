<?php

namespace OpenEMR\Common\Database {
    class QueryUtils
    {
        /**
         * @param list<mixed> $binds
         * @return list<mixed>
         */
        public static function fetchRecords(string $sql, array $binds = []): array
        {
            return [];
        }
    }
}

namespace {

    use OpenEMR\Common\Database\QueryUtils;

    // Static-method sink. Must flag the reserved identifier.
    QueryUtils::fetchRecords('SELECT * FROM contact_telecom ORDER BY rank ASC');
}
