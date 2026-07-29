<?php

namespace OpenEMR\Common\Database {
    trait DatabaseQueryTrait
    {
        /**
         * @param list<mixed> $binds
         * @return list<mixed>
         */
        protected function fetchRecords(string $sql, array $binds = []): array
        {
            return [];
        }
    }
}

namespace {

    use OpenEMR\Common\Database\DatabaseQueryTrait;

    // Anonymous class to avoid PHPStan class-discovery requirements in the
    // RuleTestCase environment.
    $repo = new class {
        use DatabaseQueryTrait;

        public function listByRank(): array
        {
            // Bare reserved word via trait method. Must flag.
            return $this->fetchRecords('SELECT * FROM contact_telecom ORDER BY rank ASC');
        }
    };
}
