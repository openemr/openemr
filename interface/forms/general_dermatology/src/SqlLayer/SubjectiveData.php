<?php

declare(strict_types=1);

namespace GeneralDermatology\SqlLayer;

use GeneralDermatology\Support\FormContext;
use OpenEMR\Common\Database\QueryUtils;

/**
 * @deprecated Legacy dual-table helpers. Prefer formSubmit/formUpdate on general_dermatology.
 */
class SubjectiveData
{
    private string $data;

    public function __construct(mixed $data)
    {
        $this->data = (string) json_encode($data);
    }

    public function saveData(): int
    {
        $context = FormContext::fromRuntime();
        return (int) QueryUtils::sqlInsert(
            $this->saveStatement(),
            [
                '',
                $context->pid(),
                null,
                $context->authUser(),
                null,
                1,
                date('Y-m-d H:i:s'),
                $this->data,
            ]
        );
    }

    private function saveStatement(): string
    {
        return "INSERT INTO `form_general_dermatology`
    (`id`, `pid`, `groupname`, `user`, `authorized`, `activity`, `date`, `form_json`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    }

    public function listForm($encounter, $new, $pid, $userauthorized): void
    {
        $date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO forms (date, encounter, form_name, form_id, pid, "
            . "user, groupname, authorized, formdir, therapy_group_id) "
            . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        QueryUtils::sqlInsert($sql, [
            $date,
            $encounter,
            'General Dermatology',
            $new,
            $pid,
            $userauthorized,
            '',
            $userauthorized,
            'general_dermatology',
            '',
        ]);
    }

    public static function getMode(): string
    {
        $context = FormContext::fromRuntime();
        $result = QueryUtils::querySingleRow(
            "SELECT `id` FROM `forms` WHERE `deleted` = 0 AND `encounter` = ? AND `formdir` = ? LIMIT 1",
            [$context->encounter(), 'general_dermatology']
        ) ?: [];

        return !empty($result['id']) ? 'update' : 'new';
    }

    public function updateData(): void
    {
        // Intentionally no-op for deprecated blob table path.
        // Patient notes are updated via formUpdate('general_dermatology', ...).
    }
}
