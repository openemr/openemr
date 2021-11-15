<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\Barcode;

class Identcode extends AbstractAdapter
{
    /**
     * Allowed barcode lengths
     * @var int
     */
    protected $length = 12;

    /**
     * Allowed barcode characters
     * @var string
     */
    protected $characters = '0123456789';

    /**
     * Checksum function
     * @var string
     */
    protected $checksum = 'identcode';

    /**
     * Constructor for this barcode adapter
     */
    public function __construct()
    {
        $this->setLength(12);
        $this->setCharacters('0123456789');
        $this->setChecksum('identcode');
    }
}
