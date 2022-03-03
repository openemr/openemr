<?php


namespace OpenEMR\Services\Qrda\Helpers;


use Mustache_Context;
use Ramsey\Uuid\Rfc4122\UuidV4;

trait View
{
    protected $_submission_program;

    public function measures()
    {
        // TODO: we don't know if this is the correct implementation or not, depends on how the measures are sent.
        $hqmf_id = $this->_measures['hqmf_id'] ?? null;
        $hqmf_set_id = $this->_measures['hqmf_set_id'] ?? null;
        $description = $this->_measures['description'] ?? null;
        return json_encode(['hqmf_id' => $hqmf_id, 'hqmf_set_id' => $hqmf_set_id, 'description' => $description]);
    }

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
        return spl_object_hash();
    }

    public function submission_program()
    {
        return $this->_submission_program;
    }
}