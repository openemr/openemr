<?php
/*
 *  $Id: 0e78856d8464d655550baa5a20b235b4049625f8 $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/parser/AbstractSAXParser.php';
include_once 'phing/parser/ExpatParseException.php';
include_once 'phing/system/io/IOException.php';
include_once 'phing/system/io/FileReader.php';

/**
 * This class is a wrapper for the PHP's internal expat parser.
 *
 * It takes an XML file represented by a abstract path name, and starts
 * parsing the file and calling the different "trap" methods inherited from
 * the AbstractParser class.
 *
 * Those methods then invoke the represenatative methods in the registered
 * handler classes.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: 0e78856d8464d655550baa5a20b235b4049625f8 $
 * @package   phing.parser
 */

class ExpatParser extends AbstractSAXParser
{

    /** @var resource */
    private $parser;

    /** @var Reader */
    private $reader;

    private $file;

    private $buffer = 4096;

    private $error_string = "";

    private $line = 0;

    /** @var Location Current cursor pos in XML file. */
    private $location;

    /**
     * Constructs a new ExpatParser object.
     *
     * The constructor accepts a PhingFile object that represents the filename
     * for the file to be parsed. It sets up php's internal expat parser
     * and options.
     *
     * @param  Reader    $reader   The Reader Object that is to be read from.
     * @param  string    $filename Filename to read.
     * @throws Exception if the given argument is not a PhingFile object
     */
    public function __construct(Reader $reader, $filename = null)
    {

        $this->reader = $reader;
        if ($filename !== null) {
            $this->file = new PhingFile($filename);
        }
        $this->parser = xml_parser_create();
        $this->buffer = 4096;
        $this->location = new Location();
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, array($this, "startElement"), array($this, "endElement"));
        xml_set_character_data_handler($this->parser, array($this, "characters"));
    }

    /**
     * Override PHP's parser default settings, created in the constructor.
     *
     * @param $opt
     * @param $val
     * @internal param the $string option to set
     * @return boolean true if the option could be set, otherwise false
     */
    public function parserSetOption($opt, $val)
    {
        return xml_parser_set_option($this->parser, $opt, $val);
    }

    /**
     * Returns the location object of the current parsed element. It describes
     * the location of the element within the XML file (line, char)
     *
     * @return object the location of the current parser
     */
    public function getLocation()
    {
        if ($this->file !== null) {
            $path = $this->file->getAbsolutePath();
        } else {
            $path = $this->reader->getResource();
        }
        $this->location = new Location($path, xml_get_current_line_number($this->parser), xml_get_current_column_number(
            $this->parser
        ));

        return $this->location;
    }

    /**
     * Starts the parsing process.
     *
     * @param  string  the option to set
     * @return int                 1 if the parsing succeeded
     * @throws ExpatParseException if something gone wrong during parsing
     * @throws IOException         if XML file can not be accessed
     */
    public function parse()
    {

        while (($data = $this->reader->read()) !== -1) {
            if (!xml_parse($this->parser, $data, $this->reader->eof())) {
                $error = xml_error_string(xml_get_error_code($this->parser));
                $e = new ExpatParseException($error, $this->getLocation());
                xml_parser_free($this->parser);
                throw $e;
            }
        }
        xml_parser_free($this->parser);

        return 1;
    }
}
