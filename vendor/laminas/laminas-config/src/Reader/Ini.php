<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Reader;

use Laminas\Config\Exception;

use function array_merge_recursive;
use function array_replace_recursive;
use function array_shift;
use function dirname;
use function explode;
use function is_array;
use function is_file;
use function is_readable;
use function parse_ini_file;
use function parse_ini_string;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function strpos;

use const E_WARNING;

/**
 * INI config reader.
 */
class Ini implements ReaderInterface
{
    /**
     * Separator for nesting levels of configuration data identifiers.
     *
     * @var string
     */
    protected $nestSeparator = '.';

    /**
     * Directory of the file to process.
     *
     * @var string
     */
    protected $directory;

    /**
     * Flag which determines whether sections are processed or not.
     *
     * @see https://www.php.net/parse_ini_file
     * @var bool
     */
    protected $processSections = true;

    /**
     * Flag which determines whether boolean, null, and integer values should be
     * returned as their proper types.
     *
     * @see https://www.php.net/parse_ini_file
     * @var bool
     */
    protected $typedMode = false;

    /**
     * Set nest separator.
     *
     * @param  string $separator
     * @return self
     */
    public function setNestSeparator($separator)
    {
        $this->nestSeparator = $separator;
        return $this;
    }

    /**
     * Get nest separator.
     *
     * @return string
     */
    public function getNestSeparator()
    {
        return $this->nestSeparator;
    }

    /**
     * Marks whether sections should be processed.
     * When sections are not processed,section names are stripped and section
     * values are merged
     *
     * @see https://www.php.net/parse_ini_file
     * @param bool $processSections
     * @return $this
     */
    public function setProcessSections($processSections)
    {
        $this->processSections = (bool) $processSections;
        return $this;
    }

    /**
     * Get if sections should be processed
     * When sections are not processed,section names are stripped and section
     * values are merged
     *
     * @see https://www.php.net/parse_ini_file
     * @return bool
     */
    public function getProcessSections()
    {
        return $this->processSections;
    }

    /**
     * Set whether boolean, null, and integer values should be returned as their proper types.
     * When set to false, all values will be returned as strings.
     *
     * @see https://www.php.net/parse_ini_file
     */
    public function setTypedMode(bool $typedMode): self
    {
        $this->typedMode = $typedMode;
        return $this;
    }

    /**
     * Get whether boolean, null, and integer values should be returned as their proper types.
     * When set to false, all values will be returned as strings.
     *
     * @see https://www.php.net/parse_ini_file
     */
    public function getTypedMode(): bool
    {
        return $this->typedMode;
    }

    /**
     * Get the scanner-mode constant value to be used with the built-in parse_ini_file function.
     * Either INI_SCANNER_NORMAL or INI_SCANNER_TYPED depending on $typedMode.
     *
     * @see https://www.php.net/parse_ini_file
     */
    public function getScannerMode(): int
    {
        return $this->getTypedMode() ? INI_SCANNER_TYPED : INI_SCANNER_NORMAL;
    }

    /**
     * fromFile(): defined by Reader interface.
     *
     * @see    ReaderInterface::fromFile()
     * @param  string $filename
     * @return array
     * @throws Exception\RuntimeException
     */
    public function fromFile($filename)
    {
        if (! is_file($filename) || ! is_readable($filename)) {
            throw new Exception\RuntimeException(sprintf(
                "File '%s' doesn't exist or not readable",
                $filename
            ));
        }

        $this->directory = dirname($filename);

        set_error_handler(
            function ($error, $message = '') use ($filename) {
                throw new Exception\RuntimeException(
                    sprintf('Error reading INI file "%s": %s', $filename, $message),
                    $error
                );
            },
            E_WARNING
        );
        $ini = parse_ini_file($filename, $this->getProcessSections(), $this->getScannerMode());
        restore_error_handler();

        return $this->process($ini);
    }

    /**
     * fromString(): defined by Reader interface.
     *
     * @param  string $string
     * @return array|bool
     * @throws Exception\RuntimeException
     */
    public function fromString($string)
    {
        if (empty($string)) {
            return [];
        }
        $this->directory = null;

        set_error_handler(
            function ($error, $message = '') {
                throw new Exception\RuntimeException(
                    sprintf('Error reading INI string: %s', $message),
                    $error
                );
            },
            E_WARNING
        );
        $ini = parse_ini_string($string, $this->getProcessSections(), $this->getScannerMode());
        restore_error_handler();

        return $this->process($ini);
    }

    /**
     * Process data from the parsed ini file.
     *
     * @param  array $data
     * @return array
     */
    protected function process(array $data)
    {
        $config = [];

        foreach ($data as $section => $value) {
            if (is_array($value)) {
                if (strpos($section, $this->nestSeparator) !== false) {
                    $sections = explode($this->nestSeparator, $section);
                    $config = array_merge_recursive($config, $this->buildNestedSection($sections, $value));
                } else {
                    $config[$section] = $this->processSection($value);
                }
            } else {
                $this->processKey($section, $value, $config);
            }
        }

        return $config;
    }

    /**
     * Process a nested section
     *
     * @param array $sections
     * @param mixed $value
     * @return array
     */
    private function buildNestedSection($sections, $value)
    {
        if (! $sections) {
            return $this->processSection($value);
        }

        $nestedSection = [];

        $first = array_shift($sections);
        $nestedSection[$first] = $this->buildNestedSection($sections, $value);

        return $nestedSection;
    }

    /**
     * Process a section.
     *
     * @param  array $section
     * @return array
     */
    protected function processSection(array $section)
    {
        $config = [];

        foreach ($section as $key => $value) {
            $this->processKey($key, $value, $config);
        }

        return $config;
    }

    /**
     * Process a key.
     *
     * @param  string $key
     * @param  string $value
     * @param  array  $config
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function processKey($key, $value, array &$config)
    {
        if (strpos($key, $this->nestSeparator) !== false) {
            $pieces = explode($this->nestSeparator, $key, 2);

            if ($pieces[0] === '' || $pieces[1] === '') {
                throw new Exception\RuntimeException(sprintf('Invalid key "%s"', $key));
            }

            if (! isset($config[$pieces[0]])) {
                if ($pieces[0] === '0' && ! empty($config)) {
                    $config = [$pieces[0] => $config];
                } else {
                    $config[$pieces[0]] = [];
                }
            } elseif (! is_array($config[$pieces[0]])) {
                throw new Exception\RuntimeException(
                    sprintf('Cannot create sub-key for "%s", as key already exists', $pieces[0])
                );
            }

            $this->processKey($pieces[1], $value, $config[$pieces[0]]);
        } else {
            if ($key === '@include') {
                if ($this->directory === null) {
                    throw new Exception\RuntimeException('Cannot process @include statement for a string config');
                }

                $reader  = clone $this;
                $include = $reader->fromFile($this->directory . '/' . $value);
                $config  = array_replace_recursive($config, $include);
            } else {
                $config[$key] = $value;
            }
        }
    }
}
