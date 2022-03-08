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
use Ramsey\Uuid\Rfc4122\UuidV4;
use function Clue\StreamFilter\fun;

trait View
{
    protected $submission_program;

    public function measures()
    {
//        // TODO: we don't know if this is the correct implementation or not, depends on how the measures are sent.
//        $hqmf_id = $this->_measures['hqmf_id'] ?? null;
//        $hqmf_set_id = $this->_measures['hqmf_set_id'] ?? null;
//        $description = $this->_measures['description'] ?? null;
//        return [
//            'hqmf_id' => $hqmf_id,
//            'hqmf_set_id' => $hqmf_set_id,
//            'description' => $description
//        ];

        return array_map(function ($measure) {
            return [
                'hqmf_id' => $measure['hqmf_id'] ?? null,
                'hqmf_set_id' => $measure['hqmf_set_id'] ?? null,
                'description' => $measure['description'] ?? null
            ];
        }, $this->_measures);

        //return $this->_measures;
    }

//    public function hqmf_id(Mustache_Context $context)
//    {
//        $hqmf_id = $context->find('hqmf_id');
//        return $hqmf_id;
//    }

//    public function hqmf_set_id(Mustache_Context $context)
//    {
//        return $context->find('hqmf_set_id');
//    }
//
//    public function description(Mustache_Context $context)
//    {
//        return $context->find('description');
//    }

    public function random_id()
    {
        return UuidV4::uuid4();
    }

    public function as_id(Mustache_Context $context)
    {
        return $context->get('value');
    }

    public function object_id()
    {
        return spl_object_hash($this); // @TODO unknown what to use here!
    }

    public function submission_program()
    {
        return $this->submission_program;
    }
}
