<?php

/**
 * @see       https://github.com/laminas/laminas-uri for the canonical source repository
 * @copyright https://github.com/laminas/laminas-uri/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-uri/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Uri;

use function preg_match;
use function str_replace;
use function strpos;

/**
 * File URI handler
 *
 * The 'file:...' scheme is loosely defined in RFC-1738
 */
class File extends Uri
{
    /** @var array<int,string> */
    protected static $validSchemes = ['file'];

    /**
     * Check if the URI is a valid File URI
     *
     * This applies additional specific validation rules beyond the ones
     * required by the generic URI syntax.
     *
     * @see    Uri::isValid()
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->query) {
            return false;
        }

        return parent::isValid();
    }

    /**
     * User Info part is not used in file URIs
     *
     * @see    Uri::setUserInfo()
     *
     * @param  string $userInfo
     * @return File
     */
    public function setUserInfo($userInfo)
    {
        return $this;
    }

    /**
     * Fragment part is not used in file URIs
     *
     * @see    Uri::setFragment()
     *
     * @param  string $fragment
     * @return File
     */
    public function setFragment($fragment)
    {
        return $this;
    }

    /**
     * Convert a UNIX file path to a valid file:// URL
     *
     * @param  string $path
     * @return File
     */
    public static function fromUnixPath($path)
    {
        $url = new static('file:');
        if (0 === strpos($path, '/')) {
            $url->setHost('');
        }

        $url->setPath($path);
        return $url;
    }

    /**
     * Convert a Windows file path to a valid file:// URL
     *
     * @param  string $path
     * @return File
     */
    public static function fromWindowsPath($path)
    {
        $url = new static('file:');

        // Convert directory separators
        $path = str_replace(['/', '\\'], ['%2F', '/'], $path);

        // Is this an absolute path?
        if (preg_match('|^([a-zA-Z]:)?/|', $path)) {
            $url->setHost('');
        }

        $url->setPath($path);
        return $url;
    }
}
