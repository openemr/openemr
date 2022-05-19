<?php

/**
 * View is a mustache helper trait with various helper methods for dealing with medication frequencies.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Qrda\Helpers;

use Mustache_Context;

trait View
{
    protected $submission_program;
    protected $_qrda_guid;

    public function measures()
    {
        // Limit the pollution of the context by only passing the values we need
        return array_map(
            function ($measure) {
                return [
                    'hqmf_id' => $measure['hqmf_id'] ?? null,
                    'hqmf_set_id' => $measure['hqmf_set_id'] ?? null,
                    'description' => $measure['description'] ?? null
                ];
            },
            $this->_measures
        );
    }

    public function random_id()
    {
        return $this->_qrda_guid;
    }

    public function as_id(Mustache_Context $context)
    {
        return $context->get('value');
    }

    public function object_id()
    {
        return substr(sha1((string)random_bytes(512)), -24);
    }

    public function submission_program()
    {
        return $this->submission_program;
    }
}
