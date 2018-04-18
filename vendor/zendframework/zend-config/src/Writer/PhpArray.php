<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config\Writer;

use Zend\Config\Exception;

class PhpArray extends AbstractWriter
{
    /**
     * @var string
     */
    const INDENT_STRING = '    ';

    /**
     * @var bool
     */
    protected $useBracketArraySyntax = false;

    /**
     * @var bool
     */
    protected $useClassNameScalars = false;

    /**
     * processConfig(): defined by AbstractWriter.
     *
     * @param  array $config
     * @return string
     */
    public function processConfig(array $config)
    {
        $arraySyntax = [
            'open' => $this->useBracketArraySyntax ? '[' : 'array(',
            'close' => $this->useBracketArraySyntax ? ']' : ')'
        ];

        return "<?php\n" .
        "return " . $arraySyntax['open'] . "\n" . $this->processIndented($config, $arraySyntax) .
        $arraySyntax['close'] . ";\n";
    }

    /**
     * Sets whether or not to use the PHP 5.4+ "[]" array syntax.
     *
     * @param  bool $value
     * @return self
     */
    public function setUseBracketArraySyntax($value)
    {
        $this->useBracketArraySyntax = $value;
        return $this;
    }

    /**
     * Sets whether or not to render resolvable FQN strings as scalars, using PHP 5.5+ class-keyword
     *
     * @param boolean $value
     * @return self
     */
    public function setUseClassNameScalars($value)
    {
        $this->useClassNameScalars = $value;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getUseClassNameScalars()
    {
        return $this->useClassNameScalars;
    }

    /**
     * toFile(): defined by Writer interface.
     *
     * @see    WriterInterface::toFile()
     * @param  string  $filename
     * @param  mixed   $config
     * @param  bool $exclusiveLock
     * @return void
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function toFile($filename, $config, $exclusiveLock = true)
    {
        if (empty($filename)) {
            throw new Exception\InvalidArgumentException('No file name specified');
        }

        $flags = 0;
        if ($exclusiveLock) {
            $flags |= LOCK_EX;
        }

        set_error_handler(
            function ($error, $message = '') use ($filename) {
                throw new Exception\RuntimeException(
                    sprintf('Error writing to "%s": %s', $filename, $message),
                    $error
                );
            },
            E_WARNING
        );

        try {
            // for Windows, paths are escaped.
            $dirname = str_replace('\\', '\\\\', dirname($filename));

            $string  = $this->toString($config);
            $string  = str_replace("'" . $dirname, "__DIR__ . '", $string);

            file_put_contents($filename, $string, $flags);
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }

        restore_error_handler();
    }

    /**
     * Recursively processes a PHP config array structure into a readable format.
     *
     * @param  array $config
     * @param  array $arraySyntax
     * @param  int   $indentLevel
     * @return string
     */
    protected function processIndented(array $config, array $arraySyntax, &$indentLevel = 1)
    {
        $arrayString = "";

        foreach ($config as $key => $value) {
            $arrayString .= str_repeat(self::INDENT_STRING, $indentLevel);
            $arrayString .= (is_int($key) ? $key : $this->processStringKey($key)) . ' => ';

            if (is_array($value)) {
                if ($value === []) {
                    $arrayString .= $arraySyntax['open'] . $arraySyntax['close'] . ",\n";
                } else {
                    $indentLevel++;
                    $arrayString .= $arraySyntax['open'] . "\n"
                        . $this->processIndented($value, $arraySyntax, $indentLevel)
                        . str_repeat(self::INDENT_STRING, --$indentLevel) . $arraySyntax['close'] . ",\n";
                }
            } elseif (is_object($value)) {
                $arrayString .= var_export($value, true) . ",\n";
            } elseif (is_string($value)) {
                $arrayString .= $this->processStringValue($value) . ",\n";
            } elseif (is_bool($value)) {
                $arrayString .= ($value ? 'true' : 'false') . ",\n";
            } elseif ($value === null) {
                $arrayString .= "null,\n";
            } else {
                $arrayString .= $value . ",\n";
            }
        }

        return $arrayString;
    }

    /**
     * Process a string configuration value
     *
     * @param string $value
     * @return string
     */
    protected function processStringValue($value)
    {
        if ($this->useClassNameScalars && false !== ($fqnValue = $this->fqnStringToClassNameScalar($value))) {
            return $fqnValue;
        }

        return var_export($value, true);
    }

    /**
     * Process a string configuration key
     *
     * @param string $key
     * @return string
     */
    protected function processStringKey($key)
    {
        if ($this->useClassNameScalars && false !== ($fqnKey = $this->fqnStringToClassNameScalar($key))) {
            return $fqnKey;
        }

        return "'" . addslashes($key) . "'";
    }

    /**
     * Attempts to convert a FQN string to class name scalar.
     * Returns false if string is not a valid FQN or can not be resolved to an existing name.
     *
     * @param string $string
     * @return bool|string
     */
    protected function fqnStringToClassNameScalar($string)
    {
        if (strlen($string) < 1) {
            return false;
        }

        if ($string[0] !== '\\') {
            $string = '\\' . $string;
        }

        if ($this->checkStringIsFqn($string)) {
            return $string . '::class';
        }

        return false;
    }

    /**
     * Check whether a string represents a resolvable FQCN
     *
     * @param string $string
     * @return bool
     */
    protected function checkStringIsFqn($string)
    {
        if (!preg_match('/^(?:\x5c[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+$/', $string)) {
            return false;
        }

        return class_exists($string) || interface_exists($string) || trait_exists($string);
    }
}
