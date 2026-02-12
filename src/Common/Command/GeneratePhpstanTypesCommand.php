<?php

/**
 * Generate PHPStan type aliases from database.sql CREATE TABLE statements
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use iamcal\SQLParser; // dev dependency, guarded in execute()
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'openemr:generate-phpstan-types',
    description: 'Generate PHPStan type aliases from database.sql CREATE TABLE statements'
)]
class GeneratePhpstanTypesCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('sql-file', InputArgument::REQUIRED, 'Path to database.sql file')
            ->addArgument('tables', InputArgument::IS_ARRAY, 'Table names to generate types for')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Generate types for all tables')
            ->addOption('list', null, InputOption::VALUE_NONE, 'List all available table names')
            ->setHelp(<<<'HELP'
                Generate PHPStan @phpstan-type aliases from CREATE TABLE statements in database.sql.

                Examples:
                  <info>%command.name% sql/database.sql patient_data users</info>
                  <info>%command.name% sql/database.sql --all > src/Common/Database/TableTypes.php</info>
                  <info>%command.name% sql/database.sql --list</info>
                HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // SQLParser is a dev dependency - provide helpful error if missing
        // Use string literal to avoid triggering autoload before the check
        if (!class_exists('iamcal\\SQLParser')) {
            $io->error([
                'iamcal/sql-parser is not installed.',
                'This tool requires dev dependencies. Run: composer install --dev',
            ]);
            return Command::FAILURE;
        }

        /** @var string $sqlFile */
        $sqlFile = $input->getArgument('sql-file');
        if (!file_exists($sqlFile)) {
            $io->error("File not found: $sqlFile");
            return Command::FAILURE;
        }

        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            $io->error("Could not read file: $sqlFile");
            return Command::FAILURE;
        }

        // Preprocess SQL to handle types not supported by the parser
        $sql = $this->preprocessSql($sql);

        $parser = new SQLParser();
        $parser->parse($sql);

        /** @var array<string, array{name: string, fields: list<array{name: string, type: string, null?: bool, auto_increment?: bool}>}> $tables */
        $tables = $parser->tables;

        // Handle --list option
        if ($input->getOption('list')) {
            $tableNames = array_keys($tables);
            sort($tableNames);
            foreach ($tableNames as $table) {
                $output->writeln($table);
            }
            return Command::SUCCESS;
        }

        // Determine which tables to process
        /** @var list<string> $requestedTables */
        $requestedTables = $input->getArgument('tables');
        if ($input->getOption('all')) {
            $requestedTables = array_keys($tables);
        }

        if ($requestedTables === []) {
            $io->error('No tables specified. Use --all or provide table names.');
            return Command::FAILURE;
        }

        $generatedCode = $this->generatePhpFile($tables, $requestedTables, $io);
        if ($generatedCode === null) {
            return Command::FAILURE;
        }

        $output->write($generatedCode);
        return Command::SUCCESS;
    }

    /**
     * Preprocess SQL to handle types not supported by the parser
     *
     * SERIAL is MySQL shorthand for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE
     */
    private function preprocessSql(string $sql): string
    {
        // Replace SERIAL with BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
        // The parser doesn't support SERIAL, but it's just an alias
        return preg_replace(
            '/\bSERIAL\b/i',
            'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
            $sql
        ) ?? $sql;
    }

    /**
     * Map SQL types to PHPStan types
     *
     * Note: ADODB returns all values as strings by default, but we type them
     * based on their semantic meaning for better static analysis.
     */
    private function sqlTypeToPhpType(string $sqlType, bool $nullable): string
    {
        $phpType = match ($sqlType) {
            'BIGINT', 'INT', 'INTEGER', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'YEAR' => 'int',
            'DECIMAL', 'NUMERIC', 'DEC', 'FIXED' => 'string', // PHP/PDO returns decimals as strings
            'FLOAT', 'DOUBLE', 'DOUBLE PRECISION', 'REAL' => 'float',
            'VARCHAR', 'CHAR', 'CHARACTER VARYING' => 'string',
            'TEXT', 'TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT' => 'string',
            'BLOB', 'TINYBLOB', 'MEDIUMBLOB', 'LONGBLOB' => 'string',
            'BINARY', 'VARBINARY' => 'string',
            'DATE', 'DATETIME', 'TIMESTAMP', 'TIME' => 'string',
            'ENUM', 'SET' => 'string',
            'JSON' => 'string',
            'BIT' => 'string',
            'BOOLEAN', 'BOOL' => 'int', // MySQL stores as TINYINT(1)
            default => 'mixed',
        };

        return $nullable ? "?$phpType" : $phpType;
    }

    /**
     * Convert table name to PascalCase type name
     */
    private function tableNameToTypeName(string $tableName): string
    {
        return str_replace('_', '', ucwords($tableName, '_')) . 'Row';
    }

    /**
     * Generate PHPStan type alias block for a single table
     *
     * @param list<array{name: string, type: string, null?: bool, auto_increment?: bool}> $fields
     */
    private function generateTypeAlias(string $tableName, array $fields): string
    {
        $typeName = $this->tableNameToTypeName($tableName);

        $output = " * @phpstan-type $typeName array{\n";

        $entries = [];
        foreach ($fields as $field) {
            // Determine nullability: explicit null property, or default to nullable if not specified
            $nullable = $field['null'] ?? true;

            // auto_increment columns are never null in practice
            if (isset($field['auto_increment']) && $field['auto_increment']) {
                $nullable = false;
            }

            $phpType = $this->sqlTypeToPhpType($field['type'], $nullable);
            $entries[] = " *   {$field['name']}: $phpType";
        }

        $output .= implode(",\n", $entries) . "\n";
        $output .= " * }";

        return $output;
    }

    /**
     * Generate complete PHP file with all type aliases
     *
     * @param array<string, array{name: string, fields: list<array{name: string, type: string, null?: bool, auto_increment?: bool}>}> $allTables
     * @param list<string> $requestedTables
     */
    private function generatePhpFile(array $allTables, array $requestedTables, SymfonyStyle $io): ?string
    {
        $typeAliases = [];

        foreach ($requestedTables as $tableName) {
            if (!isset($allTables[$tableName])) {
                $io->warning("Table '$tableName' not found, skipping");
                continue;
            }
            $table = $allTables[$tableName];
            $typeAliases[] = $this->generateTypeAlias($tableName, $table['fields']);
        }

        if ($typeAliases === []) {
            $io->error('No tables found');
            return null;
        }

        $typeAliasBlock = implode("\n *\n", $typeAliases);

        return <<<PHP
            <?php

            /**
             * PHPStan Type Aliases for OpenEMR Database Tables
             *
             * THIS FILE IS GENERATED - DO NOT EDIT MANUALLY
             *
             * Regenerate with: composer generate-phpstan-types
             *
             * @package   OpenEMR
             * @link      http://www.open-emr.org
             * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
             */

            namespace OpenEMR\Common\Database;

            /**
             * Type definitions for database row arrays
             *
             * Import this interface and reference types with @see annotations:
             *   @return TableTypes::PatientDataRow|false
             *
            $typeAliasBlock
             */
            interface TableTypes
            {
                // This interface exists only to hold PHPStan type definitions.
            }

            PHP;
    }
}
