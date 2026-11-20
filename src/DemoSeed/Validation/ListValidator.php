<?php

/**
 * Checks that every list value in a seed file is an active option id of
 * its OpenEMR list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Validation;

use OpenEMR\DemoSeed\SeedFile\ListReference;
use OpenEMR\DemoSeed\SeedFile\SeedFileException;

final readonly class ListValidator
{
    public function __construct(private ListOptionsSource $options)
    {
    }

    /**
     * @param list<ListReference> $references
     * @throws SeedFileException naming every value that isn't an option
     */
    public function validate(array $references): void
    {
        $problems = [];
        foreach ($references as $reference) {
            if (!in_array($reference->value, $this->options->optionIds($reference->listId), true)) {
                $problems[] = sprintf(
                    '%s: "%s" is not an option id of list %s',
                    $reference->path,
                    $reference->value,
                    $reference->listId,
                );
            }
        }
        if ($problems !== []) {
            throw new SeedFileException(implode("\n", $problems));
        }
    }
}
