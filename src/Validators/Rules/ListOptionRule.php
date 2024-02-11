<?php

/**
 * ListOptionRule - Validates that a list option exists in the list_options table.
 * This class validates that a list option exists in the list_options table.  It is used by the OpenEMRParticleValidator class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators\Rules;

use OpenEMR\Services\ListService;
use Particle\Validator\Rule;

class ListOptionRule extends Rule
{
    const INVALID_LIST_OPTION = 'ListOptionRule::INVALID_LIST_OPTION';

    protected $messageTemplates = [
        self::INVALID_LIST_OPTION => '{{ listId }} does not have an option_id of "{{ name }}"',
    ];

    private $listId;

    /**
     * @param $listId The id of the list to validate against from the list_options.list_id table
     */
    public function __construct($listId)
    {
        $this->listId = $listId;
    }

    public function validate($value)
    {
        if ($value === null) {
            return $this->error(self::INVALID_LIST_OPTION);
        }
        $listService = new ListService();
        if ($listService->getListOption($this->listId, $value) == null) {
            return $this->error(self::INVALID_LIST_OPTION);
        }
        return true;
    }

    // these variables can be used in error messages.
    protected function getMessageParameters()
    {
        return array_merge(parent::getMessageParameters(), [
            'listId' => $this->listId,
        ]);
    }
}
