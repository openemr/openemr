<?php

/**
 * A re-save that names no category must not clear the stored one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Questionnaire;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\QuestionnaireService;
use PHPUnit\Framework\TestCase;

class QuestionnaireResaveCategoryTest extends TestCase
{
    private const NAME = 'Resave Category Test Questionnaire';

    protected function setUp(): void
    {
        $this->removeRows();
    }

    protected function tearDown(): void
    {
        $this->removeRows();
    }

    public function testResaveWithoutACategoryKeepsTheStoredCategory(): void
    {
        $service = new QuestionnaireService();
        $id = $service->saveQuestionnaireResource(self::resource(), self::NAME, category: 'survey');

        $service->saveQuestionnaireResource(self::resource(), self::NAME);

        $this->assertSame('survey', $service->fetchQuestionnaireById($id)['category']);
    }

    public function testResaveWithACategoryChangesIt(): void
    {
        $service = new QuestionnaireService();
        $id = $service->saveQuestionnaireResource(self::resource(), self::NAME, category: 'survey');

        $service->saveQuestionnaireResource(self::resource(), self::NAME, category: 'social-history');

        $this->assertSame('social-history', $service->fetchQuestionnaireById($id)['category']);
    }

    /**
     * @return array<string, mixed>
     */
    private static function resource(): array
    {
        return [
            'resourceType' => 'Questionnaire',
            'status' => 'active',
            'title' => self::NAME,
            'item' => [['linkId' => 'q1', 'text' => 'Question', 'type' => 'string']],
        ];
    }

    private function removeRows(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE ca FROM `content_assignments` ca
               JOIN `questionnaire_repository` qr ON qr.`id` = ca.`resource_id`
              WHERE qr.`name` = ?',
            [self::NAME]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `questionnaire_repository` WHERE `name` = ?', [self::NAME]);
    }
}
