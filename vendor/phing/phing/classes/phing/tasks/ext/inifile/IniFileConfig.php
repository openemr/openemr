<?php
/**
 * INI file modification task for Phing, the PHP build tool.
 *
 * Based on http://ant-contrib.sourceforge.net/tasks/tasks/inifile.html
 *
 * PHP version 5
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link     http://www.phing.info/
 */

/**
 * Class for reading/writing ini config file
 *
 * This preserves comments etc, unlike parse_ini_file and is based heavily on
 * a solution provided at:
 * stackoverflow.com/questions/9594238/good-php-classes-that-manipulate-ini-files
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link     http://www.phing.info/
 */
class IniFileConfig
{
    /**
     * Lines of ini file
     *
     * @var array
     */
    protected $lines = array();

    /**
     * Read ini file
     *
     * @param string $file filename
     *
     * @return void
     */
    public function read($file)
    {
        $this->lines = array();

        $section = '';

        foreach (file($file) as $line) {
            if (preg_match('/^\s*(;.*)?$/', $line)) {
                // comment or whitespace
                $this->lines[] = array(
                    'type' => 'comment',
                    'data' => $line,
                    'section' => $section
                );
            } elseif (preg_match('/^\s?\[(.*)\]/', $line, $match)) {
                // section
                $section = $match[1];
                $this->lines[] = array(
                    'type' => 'section',
                    'data' => $line,
                    'section' => $section
                );
            } elseif (preg_match('/^\s*(.*?)\s*=\s*(.*?)\s*$/', $line, $match)) {
                // entry
                $this->lines[] = array(
                    'type' => 'entry',
                    'data' => $line,
                    'section' => $section,
                    'key' => $match[1],
                    'value' => $match[2]
                );
            }
        }
    }

    /**
     * Get value of given key in specified section
     *
     * @param string $section Section
     * @param string $key     Key
     *
     * @return void
     */
    public function get($section, $key)
    {
        foreach ($this->lines as $line) {
            if ($line['type'] != 'entry') {
                continue;
            }
            if ($line['section'] != $section) {
                continue;
            }
            if ($line['key'] != $key) {
                continue;
            }
            return $line['value'];
        }

        throw new RuntimeException('Missing Section or Key');
    }

    /**
     * Set key to value in specified section
     *
     * @param string $section Section
     * @param string $key     Key
     * @param string $value   Value
     *
     * @return void
     */
    public function set($section, $key, $value)
    {
        foreach ($this->lines as &$line) {
            if ($line['type'] != 'entry') {
                continue;
            }
            if ($line['section'] != $section) {
                continue;
            }
            if ($line['key'] != $key) {
                continue;
            }
            $line['value'] = $value;
            $line['data'] = $key . " = " . $value . PHP_EOL;
            return;
        }

        throw new RuntimeException('Missing Section or Key');
    }

    /**
     * Remove key/section from file.
     *
     * If key is not specified, then the entire section will be removed.
     *
     * @param string $section Section to manipulate/remove
     * @param string $key     Name of key to remove, might be null/empty
     *
     * @return void
     */
    public function remove($section, $key)
    {
        if ($section == '') {
            throw new RuntimeException("Section not set.");
        }
        if (is_null($key) || ($key == '')) {
            // remove entire section
            foreach ($this->lines as $linenum => $line) {
                if ($line['section'] == $section) {
                    unset($this->lines[$linenum]);
                }
            }
        } else {
            foreach ($this->lines as $linenum => $line) {
                if (($line['section'] == $section)
                    && (isset($line['key']))
                    && ($line['key'] == $key)
                ) {
                    unset($this->lines[$linenum]);
                }
            }
        }
    }

    /**
     * Write contents out to file
     *
     * @param string $file filename
     *
     * @return void
     */
    public function write($file)
    {
        if (file_exists($file) && !is_writable($file)) {
            throw new RuntimeException("$file is not writable");
        }
        $fp = fopen($file, 'w');
        foreach ($this->lines as $line) {
            fwrite($fp, $line['data']);
        }
        fclose($fp);
    }
}
