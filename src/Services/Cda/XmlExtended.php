<?php

/**
 * implements \Laminas\Config\Reader\ReaderInterface
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Cda;

use Laminas\Config\Reader\ReaderInterface;
use Laminas\Config\Reader\Xml;
use XMLReader;

class XmlExtended extends Xml implements ReaderInterface
{
    /**
     * fromFile(): defined by Reader interface.
     *
     * @param string $filename
     * @return array
     * @throws Exception\RuntimeException
     * @see    ReaderInterface::fromFile()
     */
    public function fromFile($filename)
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new Exception\RuntimeException(sprintf(
                "File '%s' doesn't exist or not readable",
                $filename
            ));
        }
        $this->reader = new XMLReader();
        $this->reader->open($filename, null, LIBXML_XINCLUDE | LIBXML_COMPACT | LIBXML_PARSEHUGE);

        $this->directory = dirname($filename);

        set_error_handler(
            function ($error, $message = '') use ($filename) {
                throw new Exception\RuntimeException(
                    sprintf('Error reading XML file "%s": %s', $filename, $message),
                    $error
                );
            },
            E_WARNING
        );
        $return = $this->process();
        restore_error_handler();
        $this->reader->close();

        return $return;
    }

    /**
     * fromString(): defined by Reader interface.
     *
     * @param string $string
     * @return array|bool
     * @throws Exception\RuntimeException
     * @see    ReaderInterface::fromString()
     */
    public function fromString($string)
    {
        if (empty($string)) {
            return [];
        }
        $this->reader = new XMLReader();
        $this->reader->XML($string, null, LIBXML_XINCLUDE | LIBXML_COMPACT | LIBXML_PARSEHUGE);
        $this->directory = null;
        set_error_handler(
            function ($error, $message = '') {
                throw new Exception\RuntimeException(
                    sprintf('Error reading XML string: %s', $message),
                    $error
                );
            },
            E_WARNING
        );
        $return = $this->process();
        restore_error_handler();
        $this->reader->close();

        return $return;
    }
}
