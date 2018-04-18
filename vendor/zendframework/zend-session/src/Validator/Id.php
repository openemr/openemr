<?php
/**
 * @link      http://github.com/zendframework/zend-session for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Validator;

/**
 * session_id validator
 */
class Id implements ValidatorInterface
{
    /**
     * Session identifier.
     *
     * @var string
     */
    protected $id;

    /**
     * Constructor
     *
     * Allows passing the current session_id; if none provided, uses the PHP
     * session_id() function to retrieve it.
     *
     * @param null|string $id
     */
    public function __construct($id = null)
    {
        if (empty($id)) {
            $id = session_id();
        }

        $this->id = $id;
    }

    /**
     * Is the current session identifier valid?
     *
     * Tests that the identifier does not contain invalid characters.
     *
     * @return bool
     */
    public function isValid()
    {
        $id = $this->id;
        $saveHandler = ini_get('session.save_handler');
        if ($saveHandler == 'cluster') { // Zend Server SC, validate only after last dash
            $dashPos = strrpos($id, '-');
            if ($dashPos) {
                $id = substr($id, $dashPos + 1);
            }
        }

        // Get the hash_bits_per_character INI setting, using 5 if unavailable
        $hashBitsPerChar = ini_get('session.hash_bits_per_character') ?: 5;

        switch ($hashBitsPerChar) {
            case 4:
                $pattern = '#^[0-9a-f]*$#';
                break;
            case 6:
                $pattern = '#^[0-9a-zA-Z-,]*$#';
                break;
            case 5:
                // intentionally fall-through
            default:
                $pattern = '#^[0-9a-v]*$#';
                break;
        }

        return (bool) preg_match($pattern, $id);
    }

    /**
     * Retrieve token for validating call (session_id)
     *
     * @return string
     */
    public function getData()
    {
        return $this->id;
    }

    /**
     * Return validator name
     *
     * @return string
     */
    public function getName()
    {
        return __CLASS__;
    }
}
