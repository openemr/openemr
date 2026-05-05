<?php

namespace OpenEMR\Modules\MedEx\Services;

class TemplateService
{
    public function getTemplates(int $providerId): array
    {
        $sql = "SELECT t.*,
            cat.pc_catname as category_name,
            CONCAT(u.fname, ' ', u.lname) as created_by_name
        FROM medex_schedule_templates t
        LEFT JOIN openemr_postcalendar_categories cat ON t.preferred_category_id = cat.pc_catid
        LEFT JOIN users u ON t.created_by = u.id
        WHERE t.provider_id = ? AND t.is_active = 1
        ORDER BY t.day_of_week, t.start_time";

        $result = sqlStatement($sql, [$providerId]);
        $templates = [];
        while ($row = sqlFetchArray($result)) {
            $templates[] = $row;
        }
        return $templates;
    }

    public function applyTemplate(int $templateId, string $startDate, string $endDate): array
    {
        $template = sqlQuery("SELECT * FROM medex_schedule_templates WHERE template_id = ?", [$templateId]);

        if (!$template) {
            return ['success' => false, 'error' => 'Template not found'];
        }

        $conflicts = [];
        $created = [];

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        while ($start <= $end) {
            if ($start->format('w') == $template['day_of_week']) {
                // Check for conflicts
                $existingEvent = sqlQuery(
                    "SELECT pc_eid FROM openemr_postcalendar_events
                     WHERE pc_aid = ? AND pc_eventDate = ?
                     AND pc_startTime = ? AND pc_catid = 2",
                    [$template['provider_id'], $start->format('Y-m-d'), $template['start_time']]
                );

                if (!$existingEvent) {
                    // Create In Office block with preferred category
                    $duration = (strtotime($template['end_time']) - strtotime($template['start_time'])) / 60;

                    sqlInsert(
                        "INSERT INTO openemr_postcalendar_events SET
                        pc_catid = 2,
                        pc_aid = ?,
                        pc_title = 'In Office',
                        pc_eventDate = ?,
                        pc_startTime = ?,
                        pc_endTime = ?,
                        pc_duration = ?,
                        pc_prefcatid = ?,
                        pc_alldayevent = 0,
                        pc_time = NOW()",
                        [
                            $template['provider_id'],
                            $start->format('Y-m-d'),
                            $template['start_time'],
                            $template['end_time'],
                            $duration * 60,
                            $template['preferred_category_id']
                        ]
                    );
                    $created[] = $start->format('Y-m-d');
                } else {
                    $conflicts[] = $start->format('Y-m-d');
                }
            }
            $start->modify('+1 day');
        }

        sqlStatement(
            "UPDATE medex_schedule_templates SET last_applied = NOW() WHERE template_id = ?",
            [$templateId]
        );

        return [
            'success' => true,
            'created' => count($created),
            'conflicts' => count($conflicts),
            'dates' => $created
        ];
    }

    public function createTemplate(array $data): array
    {
        $result = sqlInsert(
            "INSERT INTO medex_schedule_templates SET
            provider_id = ?,
            template_name = ?,
            day_of_week = ?,
            start_time = ?,
            end_time = ?,
            preferred_category_id = ?,
            slot_duration = ?,
            created_by = ?",
            [
                $data['provider_id'],
                $data['template_name'],
                $data['day_of_week'],
                $data['start_time'],
                $data['end_time'],
                $data['preferred_category_id'],
                $data['slot_duration'] ?? 15,
                $_SESSION['authUserID']
            ]
        );

        return ['success' => true, 'template_id' => $result];
    }
}
