<?php

/**
 * generate_select_list() with a stored value that isn't in its list: the
 * option it adds for that value is the one selected, with a warning beside
 * the list, so saving the form unchanged keeps the value.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Common\Layouts;

use DOMDocument;
use DOMElement;
use DOMXPath;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SelectListStoredValueTest extends TestCase
{
    private const LIST_ID = 'test_select_list_stored_value';
    private const BACKUP_LIST_ID = 'test_select_list_stored_backup';
    private const SENTINEL = '__test_select_list_stored__';

    /** @var array<mixed> */
    private array $savedPost = [];

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../../library/options.inc.php';
    }

    protected function setUp(): void
    {
        $this->savedPost = $_POST;
        $this->deleteRows();
        $rows = [
            [self::LIST_ID, 'opt1', 'Alpha', 1, 1],
            [self::LIST_ID, 'opt2', 'Beta', 2, 1],
            [self::LIST_ID, 'old', 'Retired', 3, 0],
            // A comment option made inactive, as a site might retire comment_OTH.
            [self::LIST_ID, 'comment_x', 'Something else, please describe', 4, 0],
            // An inactive option whose own id holds a "|", which the list editor allows.
            [self::LIST_ID, 'x|y', 'Piped', 5, 0],
            [self::BACKUP_LIST_ID, 'legacy', 'Legacy title', 1, 1],
        ];
        foreach ($rows as [$listId, $optionId, $title, $seq, $activity]) {
            QueryUtils::sqlInsert(
                'INSERT INTO list_options (list_id, option_id, title, seq, activity, notes) VALUES (?, ?, ?, ?, ?, ?)',
                [$listId, $optionId, $title, $seq, $activity, self::SENTINEL]
            );
        }
    }

    protected function tearDown(): void
    {
        $_POST = $this->savedPost;
        $this->deleteRows();
        $this->assertSame([], QueryUtils::fetchRecords(
            'SELECT option_id FROM list_options WHERE notes = ?',
            [self::SENTINEL]
        ));
    }

    /**
     * @return array<string, array{string, string, string, string, string, bool}>
     *     stored value, empty option label, backup list, then the selected value, its label
     *     and whether the list is flagged
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function storedValues(): array
    {
        return [
            'not in the list, with an empty option' => ['gone', ' ', '', 'gone', 'gone', true],
            'not in the list, with no empty option' => ['gone', '', '', 'gone', 'gone', true],
            'not in the list or its backup list' => ['gone', ' ', self::BACKUP_LIST_ID, 'gone', 'gone', true],
            'found in the backup list' => ['legacy', ' ', self::BACKUP_LIST_ID, 'legacy', 'Legacy title', false],
            'in the list' => ['opt2', ' ', '', 'opt2', 'Beta', false],
            'an inactive option of the list' => ['old', ' ', '', 'old', 'Retired', false],
            'an inactive option whose id holds a "|"' => ['x|y', ' ', '', 'x|y', 'Piped', false],
            'a value with a "|" whose first part is inactive' => ['old|x', ' ', '', 'old|x', 'old|x', true],
            'nothing stored' => ['', ' ', '', '', ' ', false],
        ];
    }

    #[DataProvider('storedValues')]
    public function testTheStoredValueIsSelected(
        string $stored,
        string $emptyName,
        string $backupList,
        string $selectedValue,
        string $selectedLabel,
        bool $flagged,
    ): void {
        $html = generate_select_list('form_test', self::LIST_ID, $stored, 'Test', $emptyName, '', '', '', null, false, $backupList);
        $xpath = self::parse($html);

        $selected = self::selectedOption($xpath);
        $this->assertSame($selectedValue, $selected->getAttribute('value'));
        $this->assertSame($selectedLabel, $selected->textContent);

        $warnings = $xpath->query('//select/following-sibling::span[contains(@class, "text-danger")]');
        $this->assertNotFalse($warnings);
        $this->assertSame($flagged ? 1 : 0, $warnings->length);
        if ($flagged) {
            $this->assertSame('Fix this!', self::element($xpath, '//select/following-sibling::span')->textContent);
        }
    }

    /**
     * @return array<string, array{string, string, string, string, bool}>
     *     the stored option, the value and label the list selects for it, the posted choice, and whether
     *     the list is flagged
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function commentOptions(): array
    {
        return [
            'made inactive' => ['comment_x', 'comment_x', 'Something else, please describe', 'comment_x', false],
            'deleted' => ['comment_gone', 'comment_gone|Queer', 'comment_gone|Queer', 'comment_gone', true],
        ];
    }

    /**
     * A list-with-comment field (data type 46, as demographics' sexual orientation and gender identity
     * are) whose stored comment option has been made inactive or deleted: the list selects it (by its
     * title when inactive, flagged when deleted), and saved unchanged, or with the comment edited, it
     * keeps one choice and one comment.
     */
    #[DataProvider('commentOptions')]
    public function testACommentFieldWhoseOptionLeftTheListKeepsOneComment(
        string $option,
        string $selectedValue,
        string $selectedLabel,
        string $savedOption,
        bool $flagged,
    ): void {
        $frow = [
            'data_type' => 46,
            'field_id' => 'test_comment_field',
            'list_id' => self::LIST_ID,
            'list_backup_id' => '',
            'title' => 'Test',
            'description' => 'Test',
            'edit_options' => '',
            'form_id' => 'TEST',
            'fld_length' => 20,
            'max_length' => 100,
        ];
        ob_start();
        generate_form_field($frow, "$option|Queer");
        $xpath = self::parse((string) ob_get_clean());
        $selected = self::selectedOption($xpath);
        $this->assertSame($selectedValue, $selected->getAttribute('value'));
        $this->assertSame($selectedLabel, $selected->textContent);
        $warnings = $xpath->query('//select/following-sibling::span[contains(@class, "text-danger")]');
        $this->assertNotFalse($warnings);
        $this->assertSame($flagged ? 1 : 0, $warnings->length);
        $comment = self::element($xpath, '//input[@name="form_text_test_comment_field"]')->getAttribute('value');
        $this->assertSame('Queer', $comment);

        $_POST['form_test_comment_field'] = $selected->getAttribute('value');
        $_POST['form_text_test_comment_field'] = $comment;
        $this->assertSame("$savedOption|Queer", get_layout_form_value($frow));

        $_POST['form_text_test_comment_field'] = 'Gay';
        $this->assertSame("$savedOption|Gay", get_layout_form_value($frow));

        // A comment option picked from the list, and any other option, save as before.
        $_POST['form_test_comment_field'] = 'comment_x';
        $this->assertSame('comment_x|Gay', get_layout_form_value($frow));
        $_POST['form_test_comment_field'] = 'opt1';
        $this->assertSame('opt1', get_layout_form_value($frow));
    }

    private static function parse(string $html): DOMXPath
    {
        $doc = new DOMDocument();
        $doc->loadHTML('<!DOCTYPE html><html><body>' . $html . '</body></html>', LIBXML_NOERROR);
        return new DOMXPath($doc);
    }

    /**
     * The option a browser selects in a single select: the last one marked
     * selected (the HTML standard's selectedness setting algorithm), or the
     * first when none is.
     */
    private static function selectedOption(DOMXPath $xpath): DOMElement
    {
        $chosen = null;
        $first = null;
        $options = $xpath->query('//option');
        self::assertNotFalse($options);
        foreach ($options as $option) {
            if (!$option instanceof DOMElement) {
                continue;
            }
            $first ??= $option;
            if ($option->hasAttribute('selected')) {
                $chosen = $option;
            }
        }
        $chosen ??= $first;
        self::assertInstanceOf(DOMElement::class, $chosen);
        return $chosen;
    }

    private static function element(DOMXPath $xpath, string $query): DOMElement
    {
        $nodes = $xpath->query($query);
        $node = $nodes === false ? null : $nodes->item(0);
        self::assertInstanceOf(DOMElement::class, $node, $query);
        return $node;
    }

    private function deleteRows(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM list_options WHERE list_id IN (?, ?) AND notes = ?',
            [self::LIST_ID, self::BACKUP_LIST_ID, self::SENTINEL]
        );
    }
}
