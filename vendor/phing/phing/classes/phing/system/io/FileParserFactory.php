<?php
/*
 *  $Id: 43943059b164e043e0426abf4f1365cf17b7c5c1 $
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
include_once 'phing/system/io/IniFileParser.php';
include_once 'phing/system/io/FileParserFactoryInterface.php';
include_once 'phing/system/io/YamlFileParser.php';

/**
 * The factory to create fileParsers based on extension name from
 * PhingFile->getFileExtension()
 *
 * @author Mike Lohmann <mike.lohmann@deck36.de>
 * @package phing.system.io
 */
class FileParserFactory implements FileParserFactoryInterface
{
    /**
     * @const string
     */
    const YAMLFILEEXTENSION = 'yml';

    /**
   * @const string
   */
    const YAMLFILEEXTENSIONLONG = 'yaml';

    /**
     * {@inheritDoc}
     */
    public function createParser($fileExtension)
    {
        if (phpversion() >= 5.3) {
            switch ($fileExtension) {
                case self::YAMLFILEEXTENSION:
                case self::YAMLFILEEXTENSIONLONG:
                    $fileParser = new YamlFileParser();
                    break;
                default:
                    $fileParser = new IniFileParser();
            }
        } else {
            $fileParser = new IniFileParser();
        }

        return $fileParser;
    }
}
