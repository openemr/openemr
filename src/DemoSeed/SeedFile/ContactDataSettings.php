<?php

/**
 * How made-up phone numbers and emails look (`contact_data`).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class ContactDataSettings
{
    /**
     * @param non-empty-list<string> $areaCodes three-digit area codes
     */
    public function __construct(
        public array $areaCodes,
        public string $emailDomain,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['area_codes', 'email_domain']);
        $areaCodes = [];
        foreach ($map->list('area_codes') as $index => $code) {
            $text = is_int($code) ? (string) $code : $code;
            if (!is_string($text) || preg_match('/^[2-9]\d{2}$/', $text) !== 1) {
                throw SeedFileException::at($map->pathOf('area_codes') . '[' . $index . ']', 'must be a three-digit area code');
            }
            $areaCodes[] = $text;
        }
        if ($areaCodes === []) {
            throw SeedFileException::at($map->pathOf('area_codes'), 'needs at least one area code');
        }
        $domain = $map->string('email_domain');
        if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $domain) !== 1) {
            throw SeedFileException::at($map->pathOf('email_domain'), 'must be a domain like example.com');
        }
        return new self($areaCodes, strtolower($domain));
    }
}
