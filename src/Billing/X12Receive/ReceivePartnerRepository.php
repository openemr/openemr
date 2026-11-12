<?php

/**
 * Reads the X12 partners that are set up to receive files.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

use OpenEMR\Common\Database\QueryUtils;

final class ReceivePartnerRepository
{
    private const WORKED_COLUMN = 'x12_sftp_receive_worked_dir';

    /**
     * The receive folder columns a saved partner form left empty.
     *
     * The partner form's save writes only fields that have a value, so a
     * folder that was emptied to stop receiving would otherwise keep its old
     * value.
     *
     * @param array<array-key, mixed> $posted the submitted form
     * @return list<string>
     */
    public static function emptiedFolderColumns(array $posted): array
    {
        $emptied = [];
        foreach (self::folderColumns() as $column) {
            if (is_string($posted[$column] ?? null) && trim($posted[$column]) === '') {
                $emptied[] = $column;
            }
        }
        return $emptied;
    }

    /**
     * @param list<string> $columns from emptiedFolderColumns(); anything else is ignored
     */
    public function clearFolders(int $partnerId, array $columns): void
    {
        foreach (array_intersect(self::folderColumns(), $columns) as $column) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `x12_partners` SET `' . $column . '` = NULL WHERE `id` = ?',
                [$partnerId]
            );
        }
    }

    /**
     * A folder as entered, without a trailing slash; the root stays "/".
     */
    private static function folder(string $entered): string
    {
        if ($entered === '') {
            return '';
        }
        $trimmed = rtrim($entered, '/');
        return $trimmed === '' ? '/' : $trimmed;
    }

    /**
     * @return list<string>
     */
    private static function folderColumns(): array
    {
        $columns = array_map(static fn(X12FileType $type): string => $type->partnerColumn(), X12FileType::cases());
        $columns[] = self::WORKED_COLUMN;
        return $columns;
    }

    /**
     * @return list<ReceivePartner> partners with an SFTP host and one or more receive folders
     */
    public function partnersWithFolders(): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `id`, `x12_sftp_host`, `x12_sftp_port`, `x12_sftp_login`, `x12_sftp_pass`,'
            . ' `x12_sftp_receive_dir_835`, `x12_sftp_receive_dir_277`, `x12_sftp_receive_dir_999`,'
            . ' `x12_sftp_receive_worked_dir`'
            . " FROM `x12_partners` WHERE `x12_sftp_host` IS NOT NULL AND `x12_sftp_host` != ''"
            . ' ORDER BY `id`'
        );
        $partners = [];
        foreach ($rows as $row) {
            $partner = self::fromRow($row);
            if ($partner !== null) {
                $partners[] = $partner;
            }
        }
        return $partners;
    }

    /**
     * @param array<array-key, mixed> $row an x12_partners row
     */
    public static function fromRow(array $row): ?ReceivePartner
    {
        $text = static fn(string $column): string => is_string($row[$column] ?? null) ? trim($row[$column]) : '';
        $folders = [];
        foreach (X12FileType::cases() as $type) {
            $folder = self::folder($text($type->partnerColumn()));
            if ($folder !== '') {
                $folders[$type->value] = $folder;
            }
        }
        $id = $row['id'] ?? null;
        if ($folders === [] || !is_numeric($id) || $text('x12_sftp_host') === '') {
            return null;
        }
        $port = $text('x12_sftp_port');
        $password = $row['x12_sftp_pass'] ?? null;
        return new ReceivePartner(
            id: (int) $id,
            host: $text('x12_sftp_host'),
            port: ctype_digit($port) ? (int) $port : 22,
            login: $text('x12_sftp_login'),
            encryptedPassword: is_string($password) ? $password : null,
            folders: $folders,
            workedFolder: self::folder($text(self::WORKED_COLUMN)),
        );
    }
}
