<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database;

use ADORecordSet;
use ADORecordSet_empty;
use ADODB_mysqli_log;
use ADOConnection;
use OpenEMR\Common\Database\Exception\DatabaseQueryException;
use OpenEMR\Common\Database\Exception\DatabaseResultException;
use OpenEMR\Common\Database\Exception\NonUniqueDatabaseResultException;
use OpenEMR\Common\Database\Exception\NoResultDatabaseResultException;
use Webmozart\Assert\InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * Usage:
 *   DatabaseFactory::getInstance()->insert()
 *
 * @todo Rename to DatabaseManager
 */
class Database
{
    public function __construct(
        private readonly ADODB_mysqli_log|ADOConnection $connection,
    ) {
    }

    /**
     * Usage:
     *   $user_id = $this->insert('users', ['uuid' => $uuid, 'fname' => 'Igor', 'lname' => 'Mukhin']);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function insert(string $table, array $data): int
    {
        Assert::minCount($data, 1);

        return $this->getLastInsertId(
            sprintf(
                'INSERT INTO `%s` SET %s',
                $this->escapeIdentifier($table),
                implode(', ', array_map(
                    fn (string $field): string => sprintf('`%s` = ?', $this->escapeIdentifier($field)),
                    array_keys($data),
                )),
            ),
            array_values($data),
        );
    }

    /**
     * Usage:
     *   $affectedRows = $this->update('users', ['address' => 'New address'], ['uuid' => $uuid]);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function update(string $table, array $data, array $condition): int
    {
        Assert::minCount($data, 1);

        return $this->getAffectedRows(
            trim(sprintf(
                'UPDATE `%s` SET %s %s',
                $this->escapeIdentifier($table),
                implode(', ', array_map(
                    fn (string $field): string => sprintf('`%s` = ?', $this->escapeIdentifier($field)),
                    array_keys($data),
                )),
                $this->buildWhere($condition),
            )),
            array_merge(
                array_values($data),
                array_values($condition),
            ),
        );
    }

    /**
     * Usage:
     *   $usersCount = $this->count('users');
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function count(string $table): int
    {
        return (int) $this->getSingleScalarResult(
            trim(sprintf(
                'SELECT COUNT(*) AS cnt FROM `%s`',
                $this->escapeIdentifier($table),
            )),
        );
    }

    /**
     * Usage:
     *   $nursesCount = $this->countBy('users', ['specialty' => 'Nursing']);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function countBy(string $table, array $condition): int
    {
        return (int) $this->getSingleScalarResult(
            trim(sprintf(
                'SELECT COUNT(*) AS cnt FROM `%s` %s',
                $this->escapeIdentifier($table),
                $this->buildWhere($condition),
            )),
            array_values($condition),
        );
    }

    /**
     * Usage:
     *   $adminPassword = $this->getSingleScalarResultBy('users', 'password', ['username' => 'admin']);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function getSingleScalarResultBy(string $table, string $column, array $condition, string|int|null $default = null): string|int|null
    {
        Assert::notEmpty($condition);

        return $this->getSingleScalarResult(
            trim(sprintf(
                'SELECT %s FROM `%s` %s',
                $this->escapeIdentifier($column),
                $this->escapeIdentifier($table),
                $this->buildWhere($condition),
            )),
            array_values($condition),
            $default
        );
    }

    /**
     * Usage:
     *   $adminsUsernames = $this->getSingleColumnResultBy('users', 'username', ['group' => 'Admins']);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function getSingleColumnResultBy(string $table, string $column, array $condition, array $orderBy = []): array
    {
        Assert::notEmpty($condition);

        return $this->getSingleColumnResult(
            trim(sprintf(
                'SELECT %s FROM `%s` %s %s',
                $this->escapeIdentifier($column),
                $this->escapeIdentifier($table),
                $this->buildWhere($condition),
                $this->buildOrderBy($orderBy),
            )),
            array_values($condition),
        );
    }

    /**
     * Usage:
     *   $user = $this->find('users', $userId);
     *
     * @throws DatabaseQueryException
     * @throws NonUniqueDatabaseResultException
     */
    public function find(string $table, string|int $id, string $idFieldName = 'id'): null|array
    {
        return $this->findOneBy($table, [$idFieldName => $id]);
    }

    /**
     * Usage:
     *   $admin = $this->findOneBy('users', ['username' => 'admin']);
     *
     * @throws DatabaseQueryException
     * @throws NonUniqueDatabaseResultException
     */
    public function findOneBy(string $table, array $condition): null|array
    {
        Assert::notEmpty($condition);

        return $this->getOneOrNullResult(
            trim(sprintf(
                'SELECT * FROM `%s` %s',
                $this->escapeIdentifier($table),
                $this->buildWhere($condition),
            )),
            array_values($condition),
        );
    }

    /**
     * Usage:
     *   $users = $this->findAll('users');
     *   $codeTypes = $this->findAll('code_types', ['ct_seq', 'ct_key']);
     *
     * @throws DatabaseQueryException
     */
    public function findAll(string $table, array $orderBy = []): array
    {
        return $this->getResult(
            trim(sprintf(
                'SELECT * FROM `%s` %s',
                $this->escapeIdentifier($table),
                $this->buildOrderBy($orderBy),
            )),
        );
    }

    /**
     * Usage:
     *   $authorizedUsers = $this->findBy('users', ['authorized' => 1]);
     *   $activeCodeTypes = $this->findBy('code_types', ['ct_active' => 1], ['ct_seq', 'ct_key']);
     *
     * @throws DatabaseQueryException
     */
    public function findBy(string $table, array $condition, array $orderBy = []): array
    {
        Assert::notEmpty($condition);

        return $this->getResult(
            trim(sprintf(
                'SELECT * FROM `%s` %s %s',
                $this->escapeIdentifier($table),
                $this->buildWhere($condition),
                $this->buildOrderBy($orderBy),
            )),
            array_values($condition),
        );
    }

    /**
     * Usage:
     *   $this->truncate('logs');
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function truncate(string $table): void
    {
        $this->execute(
            trim(sprintf(
                'TRUNCATE `%s`',
                $this->escapeIdentifier($table),
            )),
        );
    }

    /**
     * Usage:
     *   $affected = $this->removeAll('logs');
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function removeAll(string $table): int
    {
        return $this->getAffectedRows(
            trim(sprintf(
                'DELETE FROM `%s`',
                $this->escapeIdentifier($table),
            )),
        );
    }

    /**
     * Usage:
     *   $affected = $this->removeBy('users', ['uuid' => $uuid]);
     *   $affected = $this->removeBy('users', ['fname' => 'Igor', 'lname' => 'Mukhin']);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function removeBy(string $table, array $condition): int
    {
        return $this->getAffectedRows(
            trim(sprintf(
                'DELETE FROM `%s` %s',
                $this->escapeIdentifier($table),
                $this->buildWhere($condition),
            )),
            array_values($condition),
        );
    }

    /**
     * Usage:
     *   $affected = $this->remove('users', $id);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function remove(string $table, int|string $id, string $idFieldName = 'id'): int
    {
        return $this->removeBy($table, [$idFieldName => $id]);
    }

    /**
     * @throws DatabaseQueryException
     */
    public function getResult(string $statement, array $binds = []): array
    {
        $recordset = $this->execute($statement, $binds);

        $result = [];
        while (false !== ($row = $recordset->FetchRow())) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * @throws DatabaseQueryException
     */
    public function getSingleColumnResult(string $statement, array $binds): array
    {
        $recordset = $this->execute($statement, $binds);

        if (1 !== $recordset->NumCols()) {
            throw new DatabaseResultException(
                $statement,
                sprintf(
                    'Expected exactly 1 column at result, got %d',
                    $recordset->NumCols()
                )
            );
        }

        $result = [];
        while (false !== ($row = $recordset->FetchRow())) {
            $result[] = $row[array_key_first($row)];
        }

        return $result;
    }

    /**
     * @throws DatabaseQueryException
     * @throws NonUniqueDatabaseResultException
     */
    public function getOneOrNullResult(string $statement, array $binds = []): null|array
    {
        $recordset = $this->execute($statement, $binds);

        if (0 === $recordset->NumRows()) {
            return null;
        }

        if ($recordset->NumRows() > 1) {
            throw new NonUniqueDatabaseResultException($statement);
        }

        return $recordset->FetchRow();
    }

    /**
     * Used for getting aggregation results
     *
     * @throws DatabaseQueryException
     * @throws NoResultDatabaseResultException
     * @throws NonUniqueDatabaseResultException
     * @throws DatabaseResultException
     */
    public function getSingleScalarResult(string $statement, array $binds = [], int|string|null $default = null): int|string|null
    {
        $recordset = $this->execute($statement, $binds);

        if (0 === $recordset->NumRows()) {
            throw new NoResultDatabaseResultException($statement);
        }

        if ($recordset->NumRows() > 1) {
            throw new NonUniqueDatabaseResultException($statement);
        }

        if (1 !== $recordset->NumCols()) {
            throw new DatabaseResultException(
                $statement,
                sprintf(
                    'Expected exactly 1 column at result, got %d',
                    $recordset->NumCols()
                )
            );
        }

        $row = $recordset->FetchRow();
        return $row[array_key_first($row)];
    }

    /**
     * @throws DatabaseQueryException
     */
    public function getLastInsertId(string $statement, array $binds = []): int
    {
        $this->execute($statement, $binds);

        return $this->connection->Insert_ID();
    }

    /**
     * @throws DatabaseQueryException
     */
    public function getAffectedRows(string $statement, array $binds = []): int
    {
        $this->execute($statement, $binds);

        return (int) $this->connection->Affected_Rows();
    }

    /**
     * It's important to use no log execute here,
     * otherwise getLastInsertId will not work correctly as
     * will be returning ID of last log rather than what we expect
     *
     * @throws DatabaseQueryException
     */
    protected function execute(string $statement, array $binds = []): ADORecordSet|ADORecordSet_empty
    {
        $recordset = $this->connection->ExecuteNoLog(
            $statement,
            array_values($binds) ?: false,
        );

        if (false === $recordset) {
            throw new DatabaseQueryException($statement, $this->getLastError());
        }

        return $recordset;
    }

    protected function getLastError(): string
    {
        return $this->connection->ErrorMsg();
    }

    protected function getLastErrorNo(): int
    {
        return $this->connection->ErrorNo();
    }

    /**
     * Validate identifier (table name or column name)
     * Allow only letters, numbers, and underscore to prevent SQL injections
     *
     * Unlike escape_table_name & escape_sql_column_name, this will just fail
     * if some not-allowed symbols will be found (during potential sql-injection attack)
     *
     * @see escape_table_name
     * @see escape_sql_column_name
     *
     * @throws InvalidArgumentException
     */
    protected function escapeIdentifier(string $identifier): string
    {
        Assert::true(
            1 === preg_match('/^[A-Za-z0-9_]+$/', $identifier),
            sprintf(
                'Only A-Za-z0-9_ allowed for identifiers (table or column names). Got: %s',
                $identifier
            )
        );

        return $identifier;
    }

    private function buildWhere(array $condition): string
    {
        return [] === $condition ? '' : sprintf(
            'WHERE %s',
            implode(' AND ', array_map(
                fn (string $field): string => sprintf('`%s` = ?', $this->escapeIdentifier($field)),
                array_keys($condition),
            )),
        );
    }

    private function buildOrderBy(array $orderBy): string
    {
        Assert::allOneOf(array_values($orderBy), ['ASC', 'DESC']);

        return [] === $orderBy ? '' : sprintf(
            'ORDER BY %s',
            implode(
                ', ',
                array_map(
                    fn (string $field, string $direction) => sprintf(
                        '%s %s',
                        $this->escapeIdentifier($field),
                        $direction,
                    ),
                    array_keys($orderBy),
                    array_values($orderBy),
                )
            )
        );
    }
}
