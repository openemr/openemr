<?php

/**
 * Reads the tags from the comments at the top of a file
 *
 * The header is every comment before the first line of code. A byte order
 * mark or shebang may come first, and in PHP so may `declare`, unbraced
 * `namespace`, and `use` statements, which also catches a license on a class
 * docblock that follows the imports. Within a comment, a line that does not start a new tag continues
 * the previous one, so a wrapped `@license` value reads as one string. A blank
 * comment line ends the tag.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

final class HeaderExtractor
{
    private const C_STYLE_DELIMITERS = [['/*', '*/'], ['//', "\n"]];
    private const TWIG_DELIMITERS = [['{#', '#}']];
    private const OPENERS = ['/**', '/*', '{#', '//'];
    private const CLOSERS = ['*/', '#}'];
    private const BYTE_ORDER_MARK_HEX = 'efbbbf';

    /**
     * @return list<HeaderTag>
     */
    public function extract(FileType $type, string $source): array
    {
        $byteOrderMark = pack('H*', self::BYTE_ORDER_MARK_HEX);
        if (str_starts_with($source, $byteOrderMark)) {
            $source = substr($source, strlen($byteOrderMark));
        }
        $comments = match ($type) {
            FileType::Php => $this->phpComments($source),
            FileType::CStyle => $this->delimitedComments($source, self::C_STYLE_DELIMITERS),
            FileType::Twig => $this->delimitedComments($source, self::TWIG_DELIMITERS),
        };
        $tags = [];
        foreach ($comments as [$text, $line]) {
            array_push($tags, ...$this->parseTags($text, $line));
        }
        return $tags;
    }

    /**
     * @return list<array{string, int}> comment text and its first line
     */
    private function phpComments(string $source): array
    {
        $tokens = array_values(\PhpToken::tokenize($source));
        $count = count($tokens);
        $comments = [];
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if ($token->is([T_OPEN_TAG, T_WHITESPACE])) {
                continue;
            }
            if ($i === 0 && $token->is(T_INLINE_HTML) && str_starts_with($token->text, '#!')) {
                continue;
            }
            if ($token->is([T_COMMENT, T_DOC_COMMENT])) {
                $comments[] = [$token->text, $token->line];
                continue;
            }
            if ($token->is(T_USE)) {
                // A group use statement has braces inside it, so only `;` ends it.
                $i = $this->statementEnd($tokens, $i, [';']);
                continue;
            }
            if (!$token->is([T_DECLARE, T_NAMESPACE])) {
                break;
            }
            $i = $this->statementEnd($tokens, $i, [';', '{']);
            if (!$tokens[$i]->is(';')) {
                // A braced namespace or declare body is code, so the header has ended.
                break;
            }
        }
        return $comments;
    }

    /**
     * @param list<\PhpToken> $tokens
     * @param list<string> $terminators
     * @return int index of the first terminator at or after `$start`, or of the last token
     */
    private function statementEnd(array $tokens, int $start, array $terminators): int
    {
        $last = count($tokens) - 1;
        $i = $start;
        while ($i < $last && !$tokens[$i]->is($terminators)) {
            $i++;
        }
        return $i;
    }

    /**
     * @param list<array{non-empty-string, non-empty-string}> $delimiters opener and closer pairs
     * @return list<array{string, int}> comment text and its first line
     */
    private function delimitedComments(string $source, array $delimiters): array
    {
        $offset = 0;
        if (str_starts_with($source, '#!')) {
            $offset = strcspn($source, "\n");
        }
        $comments = [];
        while (true) {
            $offset += strspn($source, " \t\r\n", $offset);
            $rest = substr($source, $offset);
            $closer = $this->closerFor($rest, $delimiters);
            if ($closer === null) {
                break;
            }
            $parts = explode($closer, $rest, 2);
            $text = count($parts) === 2 ? $parts[0] . $closer : $rest;
            $comments[] = [$text, substr_count(substr($source, 0, $offset), "\n") + 1];
            $offset += strlen($text);
            if (count($parts) === 1) {
                break;
            }
        }
        return $comments;
    }

    /**
     * @param list<array{non-empty-string, non-empty-string}> $delimiters
     * @return ?non-empty-string the closer matching the opener `$text` starts with
     */
    private function closerFor(string $text, array $delimiters): ?string
    {
        foreach ($delimiters as [$opener, $closer]) {
            if (str_starts_with($text, $opener)) {
                return $closer;
            }
        }
        return null;
    }

    /**
     * @return list<HeaderTag>
     */
    private function parseTags(string $comment, int $firstLine): array
    {
        $tags = [];
        $name = null;
        $value = '';
        $tagLine = $firstLine;
        foreach (explode("\n", $comment) as $index => $rawLine) {
            $text = $this->stripDecoration($rawLine);
            $startsTag = str_starts_with($text, '@');
            if ($name !== null && ($startsTag || $text === '')) {
                $tags[] = new HeaderTag($name, HeaderTag::normalizeValue($value), $tagLine);
                $name = null;
            }
            if ($startsTag) {
                $nameLength = strcspn($text, " \t");
                $name = strtolower(rtrim(substr($text, 1, $nameLength - 1), ':'));
                $value = substr($text, $nameLength);
                $tagLine = $firstLine + $index;
            } elseif ($name !== null) {
                $value .= ' ' . $text;
            }
        }
        if ($name !== null) {
            $tags[] = new HeaderTag($name, HeaderTag::normalizeValue($value), $tagLine);
        }
        return $tags;
    }

    private function stripDecoration(string $line): string
    {
        $text = trim($line);
        foreach (self::OPENERS as $opener) {
            if (str_starts_with($text, $opener)) {
                $text = substr($text, strlen($opener));
                break;
            }
        }
        foreach (self::CLOSERS as $closer) {
            if (str_ends_with($text, $closer)) {
                $text = substr($text, 0, -strlen($closer));
                break;
            }
        }
        return trim(ltrim(trim($text), '*#'));
    }
}
