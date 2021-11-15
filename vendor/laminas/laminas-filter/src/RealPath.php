<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

use Laminas\Stdlib\ErrorHandler;
use Traversable;

class RealPath extends AbstractFilter
{
    /**
     * @var array $options
     */
    protected $options = [
        'exists' => true
    ];

    /**
     * Class constructor
     *
     * @param  bool|Traversable $existsOrOptions Options to set
     */
    public function __construct($existsOrOptions = true)
    {
        if ($existsOrOptions !== null) {
            if (! static::isOptions($existsOrOptions)) {
                $this->setExists($existsOrOptions);
            } else {
                $this->setOptions($existsOrOptions);
            }
        }
    }

    /**
     * Sets if the path has to exist
     * TRUE when the path must exist
     * FALSE when not existing paths can be given
     *
     * @param  bool $flag Path must exist
     * @return self
     */
    public function setExists($flag = true)
    {
        $this->options['exists'] = (bool) $flag;
        return $this;
    }

    /**
     * Returns true if the filtered path must exist
     *
     * @return bool
     */
    public function getExists()
    {
        return $this->options['exists'];
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns realpath($value)
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @param  string $value
     * @return string|mixed
     */
    public function filter($value)
    {
        if (! is_string($value)) {
            return $value;
        }
        $path = (string) $value;

        if ($this->options['exists']) {
            return realpath($path);
        }

        ErrorHandler::start();
        $realpath = realpath($path);
        ErrorHandler::stop();
        if ($realpath) {
            return $realpath;
        }

        $drive = '';
        if (stripos(PHP_OS, 'WIN') === 0) {
            $path = preg_replace('/[\\\\\/]/', DIRECTORY_SEPARATOR, $path);
            if (preg_match('/([a-zA-Z]\:)(.*)/', $path, $matches)) {
                list(, $drive, $path) = $matches;
            } else {
                $cwd   = getcwd();
                $drive = substr($cwd, 0, 2);
                if (strpos($path, DIRECTORY_SEPARATOR) !== 0) {
                    $path = substr($cwd, 3) . DIRECTORY_SEPARATOR . $path;
                }
            }
        } elseif (strpos($path, DIRECTORY_SEPARATOR) !== 0) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        $stack = [];
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($parts as $dir) {
            if ($dir !== '' && $dir !== '.') {
                if ($dir === '..') {
                    array_pop($stack);
                } else {
                    $stack[] = $dir;
                }
            }
        }

        return $drive . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $stack);
    }
}
