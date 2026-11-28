<?php

/**
 * Hands out made-up email addresses, first.last@domain, unique.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

final class EmailPool
{
    /** @var array<string, true> */
    private array $used = [];

    /**
     * @param list<string> $reserved addresses the seed file uses itself
     */
    public function __construct(private readonly string $domain, array $reserved)
    {
        foreach ($reserved as $address) {
            $this->used[strtolower($address)] = true;
        }
    }

    public function next(string $firstName, string $lastName): string
    {
        $local = self::part($firstName) . '.' . self::part($lastName);
        $candidate = $local . '@' . $this->domain;
        for ($n = 2; isset($this->used[$candidate]); $n++) {
            $candidate = $local . $n . '@' . $this->domain;
        }
        $this->used[$candidate] = true;
        return $candidate;
    }

    private static function part(string $name): string
    {
        $ascii = strtolower(preg_replace('/[^A-Za-z]/', '', $name) ?? '');
        return $ascii === '' ? 'patient' : $ascii;
    }
}
