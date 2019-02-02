<?php
/**
 * @see       https://github.com/zendframework/zend-uri for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-uri/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Uri;

/**
 * File URI handler
 *
 * The 'file:...' scheme is loosely defined in RFC-1738
 */
class File extends Uri
{
    protected static $validSchemes = ['file'];

    /**
     * Check if the URI is a valid File URI
     *
     * This applies additional specific validation rules beyond the ones
     * required by the generic URI syntax.
     *
     * @return bool
     * @see    Uri::isValid()
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
        if (substr($path, 0, 1) == '/') {
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
