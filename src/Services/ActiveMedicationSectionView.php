<?php

/**
 * Renders one section of the printable medication list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use Twig\Environment;

final class ActiveMedicationSectionView
{
    /**
     * The Twig environment that renders the section template.
     */
    public function __construct(private readonly Environment $twig)
    {
    }

    /**
     * One medication section: a heading, an empty line, or a table.
     *
     * @param literal-string $heading
     * @param list<array{title: string, dose: string, start: ?string, end: ?string, comments: string}> $rows
     * @param callable(literal-string): string $translate
     * @param callable(?string): string $formatDate
     */
    public function render(
        string $heading,
        array $rows,
        callable $translate,
        callable $formatDate,
        string $tableClass,
    ): string {
        $prepared = [];
        foreach ($rows as $row) {
            $prepared[] = [
                'title' => $row['title'],
                'dose' => $row['dose'],
                'start' => $formatDate($row['start']),
                'end' => $formatDate($row['end']),
                'comments' => $row['comments'],
            ];
        }
        $labels = [];
        foreach (['Medication', 'Dose', 'Start', 'End', 'Comments'] as $label) {
            $labels[] = $translate($label);
        }

        return $this->twig->render(
            'patient/active_medication_section.html.twig',
            [
                'heading' => $translate($heading),
                'emptyLabel' => $translate('None{{Issues}}'),
                'rows' => $prepared,
                'columnLabels' => $labels,
                'tableClass' => $tableClass,
            ]
        );
    }
}
