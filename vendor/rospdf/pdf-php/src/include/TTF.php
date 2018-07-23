<?php

/*
    TTF.php: TrueType font file reader and writer
    Copyright (C) 2012 Thanos Efraimidis (4real.gr)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class TTF
{
    // Bit flags used for composite glyphs
    const ARG_1_AND_2_ARE_WORDS = 1;
    const ARGS_ARE_XY_VALUES = 2;
    const ROUND_XY_TO_GRID = 4;
    const WE_HAVE_A_SCALE = 8;
    const MORE_COMPONENTS = 32;
    const WE_HAVE_AN_X_AND_Y_SCALE = 64;
    const WE_HAVE_A_TWO_BY_TWO = 128;
    const WE_HAVE_INSTRUCTIONS = 256;
    const USE_MY_METRICS = 512;

    // For debugging
    const VERBOSE = false;
    
    private $b; // Array of bytes
    private $tables; // Tables

    // Constructor: parses the table directory
    public function __construct($b)
    {
        $this->b = $b;

        $off = 0;
        $version = self::getFixed($b, $off); // sfnt version
        $numTables = self::getUshort($b, $off); // number of tables
        $searchRange = self::getUshort($b, $off);
        $entrySelector = self::getUshort($b, $off);
        $rangeShift = self::getUshort($b, $off);
        $this->tables = array();
        for ($i = 0; $i < $numTables; $i++) {
            $name = self::getRaw($b, $off, 4);
            $checksum = self::getUlong($b, $off);
            $offset = self::getUlong($b, $off);
            $length = self::getUlong($b, $off);
            $this->tables[$name] = array('offset' => $offset, 'length' => $length);
        }
        if (self::VERBOSE) {
            echo sprintf("==== Table directory\n");
            echo sprintf("Version: %s, number of tables: %d\n", $version, $numTables);
            foreach ($this->tables as $name => $value) {
                echo sprintf("%s %10d %10d\n", $name, $value['offset'], $value['length']);
            }
            echo "\n";
        }
    }

    // Get raw bytes of table, or null if table does not exist
    public function getTableRaw($name)
    {
        if (isset($this->tables[$name])) {
            $entry = $this->tables[$name];
            return substr($this->b, $entry['offset'], $entry['length']);
        }
        return null;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // Unmarshal - marshal functions follow
    ////////////////////////////////////////////////////////////////////////////////
    public function unmarshalName()
    {
        $name = array();
        $b = $this->getTableRaw('name');
        $off = 0;
        $name['format'] = self::getUshort($b, $off);
        $name['count'] = self::getUshort($b, $off);
        $name['offset'] = self::getUshort($b, $off);
        $name['nameRecords'] = array();

        $tmp = $name['offset'];
        for ($i = 0; $i < $name['count']; ++$i) {
            $name['nameRecords'][$i] = array();
            $name['nameRecords'][$i]['platformID'] = self::getUshort($b, $off);
            $name['nameRecords'][$i]['platformSpecificID'] = self::getUshort($b, $off);
            $name['nameRecords'][$i]['languageID'] = self::getUshort($b, $off);
            $name['nameRecords'][$i]['nameID'] = self::getUshort($b, $off);
            $name['nameRecords'][$i]['length'] = self::getUshort($b, $off);
            $name['nameRecords'][$i]['offset'] = self::getUshort($b, $off);
            $name['nameRecords'][$i]['value'] = self::getRaw($b, $tmp, $name['nameRecords'][$i]['length'] * 2);
            $tmp += $name['nameRecords'][$i]['length'] + 3;
        }

        return $name;
    }
    
    public function unmarshalHead()
    {
        $head = array(); // To return
        $b = $this->getTableRaw('head'); // Get raw bytes for 'head' table
        $off = 0;
        $head['version'] = self::getRaw($b, $off, 4); // This is actually fixed
        $head['revision'] = self::getRaw($b, $off, 4); // This is actually fixed
        $off += 4; // Skip checksum adjustment
        $off += 4; // Skip magic number
        $head['flags'] = self::getUshort($b, $off);
        $head['unitsPerEm'] = self::getUshort($b, $off);
        $head['created'] = self::getRaw($b, $off, 8); // This is actually longdatetime
        $head['modified'] = self::getRaw($b, $off, 8); // This is actually longdatetime
        $head['xMin'] = self::getFword($b, $off);
        $head['yMin'] = self::getFword($b, $off);
        $head['xMax'] = self::getFword($b, $off);
        $head['yMax'] = self::getFword($b, $off);
        $head['macStyle'] = self::getUshort($b, $off);
        $head['lowestRecPPEM'] = self::getUshort($b, $off);
        $head['fontDirectionHint'] = self::getShort($b, $off);
        $head['indexToLocFormat'] = self::getShort($b, $off);
        $head['glyphDataFormat'] = self::getShort($b, $off);
        return $head;
    }

    public static function marshalHead($head)
    {
        $b = str_repeat(chr(0), 54); // Size of 'head' is 54 bytes
        $off = 0;
        self::setRaw($b, $off, $head['version'], 4); // This is actually fixed
        self::setRaw($b, $off, $head['revision'], 4); // This is actually fixed
        self::setUlong($b, $off, 0); // Checksum Adjustment - will be calculated later
        self::setUlong($b, $off, 0x5F0F3CF5); // Magic Number
        self::setUshort($b, $off, $head['flags']);
        self::setUshort($b, $off, $head['unitsPerEm']);
        self::setRaw($b, $off, $head['created'], 8); // This is actually longdatetime
        self::setRaw($b, $off, $head['modified'], 8); // This is actually longdatetime
        self::setFword($b, $off, $head['xMin']);
        self::setFword($b, $off, $head['yMin']);
        self::setFword($b, $off, $head['xMax']);
        self::setFword($b, $off, $head['yMax']);
        self::setUshort($b, $off, $head['macStyle']);
        self::setUshort($b, $off, $head['lowestRecPPEM']);
        self::setShort($b, $off, $head['fontDirectionHint']);
        self::setShort($b, $off, $head['indexToLocFormat']);
        self::setShort($b, $off, $head['glyphDataFormat']);
        return $b;
    }

    public function unmarshalHhea()
    {
        $hhea = array(); // To return
        $b = $this->getTableRaw('hhea'); // Get raw bytes for 'hhea' table
        $off = 0;
        $hhea['version'] = self::getRaw($b, $off, 4); // This is actually fixed
        $hhea['ascender'] = self::getFword($b, $off);
        $hhea['descender'] = self::getFword($b, $off);
        $hhea['lineGap'] = self::getFword($b, $off);
        $hhea['advanceWidthMax'] = self::getUFword($b, $off);
        $hhea['minLeftSideBearing'] = self::getFword($b, $off);
        $hhea['minRightSideBearing'] = self::getFword($b, $off);
        $hhea['xMaxExtent'] = self::getFword($b, $off);
        $hhea['caretSlopeRise'] = self::getShort($b, $off);
        $hhea['caretSlopeRun'] = self::getShort($b, $off);
        $off += 10; // Skip reserved
        $hhea['metricDataFormat'] = self::getShort($b, $off);
        $hhea['numberOfHMetrics'] = self::getUShort($b, $off);
        return $hhea;
    }

    public static function marshalHhea($hhea)
    {
        $b = str_repeat(chr(0), 36); // Size of 'hhea' is 36 bytes
        $off = 0;
        self::setRaw($b, $off, $hhea['version'], 4); // This is actually fixed
        self::setFword($b, $off, $hhea['ascender']);
        self::setFword($b, $off, $hhea['descender']);
        self::setFword($b, $off, $hhea['lineGap']);
        self::setUFword($b, $off, $hhea['advanceWidthMax']);
        self::setFword($b, $off, $hhea['minLeftSideBearing']);
        self::setFword($b, $off, $hhea['minRightSideBearing']);
        self::setFword($b, $off, $hhea['xMaxExtent']);
        self::setShort($b, $off, $hhea['caretSlopeRise']);
        self::setShort($b, $off, $hhea['caretSlopeRun']);
        $off += 10; // Skip reserved
        self::setShort($b, $off, $hhea['metricDataFormat']);
        self::setUshort($b, $off, $hhea['numberOfHMetrics']);
        return $b;
    }

    public function unmarshalMaxp()
    {
        $maxp = array(); // To return
        $b = $this->getTableRaw('maxp'); // Get raw bytes for 'maxp' table
        $off = 0;
        $maxp['version'] = self::getRaw($b, $off, 4); // This is actually fixed
        $maxp['numGlyphs'] = self::getUshort($b, $off);
        $maxp['maxPoints'] = self::getUshort($b, $off);
        $maxp['maxContours'] = self::getUshort($b, $off);
        $maxp['maxCompositePoints'] = self::getUshort($b, $off);
        $maxp['maxCompositeContours'] = self::getUshort($b, $off);
        $maxp['maxZones'] = self::getUshort($b, $off);
        $maxp['maxTwilightPoints'] = self::getUshort($b, $off);
        $maxp['maxStorage'] = self::getUshort($b, $off);
        $maxp['maxFunctionDefs'] = self::getUshort($b, $off);
        $maxp['maxInstructionDefs'] = self::getUshort($b, $off);
        $maxp['maxStackElements'] = self::getUshort($b, $off);
        $maxp['maxSizeOfInstructions'] = self::getUshort($b, $off);
        $maxp['maxComponentElements'] = self::getUshort($b, $off);
        $maxp['maxComponentDepth'] = self::getUshort($b, $off);
        return $maxp;
    }

    public static function marshalMaxp($maxp)
    {
        $b = str_repeat(chr(0), 32); // Size of 'maxp' is 32 bytes
        $off = 0;
        self::setRaw($b, $off, $maxp['version'], 4); // This is actually fixed
        self::setUshort($b, $off, $maxp['numGlyphs']);
        self::setUshort($b, $off, $maxp['maxPoints']);
        self::setUshort($b, $off, $maxp['maxContours']);
        self::setUshort($b, $off, $maxp['maxCompositePoints']);
        self::setUshort($b, $off, $maxp['maxCompositeContours']);
        self::setUshort($b, $off, $maxp['maxZones']);
        self::setUshort($b, $off, $maxp['maxTwilightPoints']);
        self::setUshort($b, $off, $maxp['maxStorage']);
        self::setUshort($b, $off, $maxp['maxFunctionDefs']);
        self::setUshort($b, $off, $maxp['maxInstructionDefs']);
        self::setUshort($b, $off, $maxp['maxStackElements']);
        self::setUshort($b, $off, $maxp['maxSizeOfInstructions']);
        self::setUshort($b, $off, $maxp['maxComponentElements']);
        self::setUshort($b, $off, $maxp['maxComponentDepth']);
        return $b;
    }

    public function unmarshalLoca($indexToLocFormat, $numGlyphs)
    {
        $loca = array(); // To return
        $b = $this->getTableRaw('loca'); // Get raw bytes for 'loca' table
        $off = 0;
        if ($indexToLocFormat == 0) {
            for ($i = 0; $i < $numGlyphs + 1; $i++) {
                $loca[] = 2 * self::getUshort($b, $off);
            }
        } else {
            for ($i = 0; $i < $numGlyphs + 1; $i++) {
                $loca[] = self::getUlong($b, $off);
            }
        }
        return $loca;
    }

    public static function marshalLoca($loca)
    {
        $cnt = count($loca);
        if ($loca[$cnt - 1] <= 0x20000) {
            // Short offsets
            $b = str_repeat(chr(0), 2 * $cnt);
            $off = 0;
            for ($i = 0; $i < $cnt; $i++) {
                self::setUshort($b, $off, $loca[$i] / 2);
            }
        } else {
            // Long offsets
            $b = str_repeat(chr(0), 4 * $cnt);
            $off = 0;
            for ($i = 0; $i < $cnt; $i++) {
                self::setUlong($b, $off, $loca[$i]);
            }
        }
        return $b;
    }

    public function unmarshalHmtx($numberOfHMetrics, $numGlyphs)
    {
        $metrics = array(); // To return
        $lsbs = array(); // To return
        $b = $this->getTableRaw('hmtx'); // Get raw bytes for 'hmtx' table
        $off = 0;
        for ($i = 0; $i < $numberOfHMetrics; $i++) {
            $advanceWidth = self::getUFword($b, $off);
            $lsb = self::getFword($b, $off);
            $metrics[] = array($advanceWidth, $lsb);
        }
        for ($i = $numberOfHMetrics; $i < $numGlyphs; $i++) {
            $lsb = self::getFword($b, $off);
            $lsbs[] = $lsb;
        }
        return array('metrics' => $metrics, 'lsbs' => $lsbs);
    }

    public static function marshalHmtx($metrics, $lsbs)
    {
        $cntMetrics = count($metrics);
        $cntLsbs = count($lsbs);
        $b = str_repeat(chr(0), 4 * $cntMetrics + 2 * $cntLsbs);
        $off = 0;
        for ($i = 0; $i < $cntMetrics; $i++) {
            $advanceWidth = $metrics[$i][0];
            $lsb = $metrics[$i][1];
            self::setUFword($b, $off, $advanceWidth);
            self::setFword($b, $off, $lsb);
        }
        for ($i = 0; $i < $cntLsbs; $i++) {
            $lsb = $lsbs[$i];
            self::setFword($b, $off, $lsb);
        }
        return $b;
    }

    public function unmarshalGlyf($loca)
    {
        $glyf = array(); // To return
        $b = $this->getTableRaw('glyf'); // Get raw bytes for 'glyf' table

        $num = count($loca) - 1;
        for ($i = 0; $i < $num; $i++) {
            $glyf[] = substr($b, $loca[$i], $loca[$i + 1] - $loca[$i]);
        }
        return $glyf;
    }

    public static function marshalGlyf($glyf)
    {
        $b = '';
        $num = count($glyf);
        for ($i = 0; $i < $num; $i++) {
            $b .= $glyf[$i];
        }
        return $b;
    }

    public function unmarshalCmap()
    {
        $cmap = array(); // To return
        $b = $this->getTableRaw('cmap'); // Get raw bytes for 'cmap' table
        $off = 0;
        $cmap['version'] = self::getUshort($b, $off);
        $cmap['numTables'] = self::getUshort($b, $off);
        $cmap['tables'] = array();
        $numTables = $cmap['numTables'];
        $platformIDs = array();
        $platformSpecificIDs = array();
        $offsets = array();
        for ($i = 0; $i < $numTables; $i++) {
            $platformIDs[] = self::getUshort($b, $off);
            $platformSpecificIDs[] = self::getUshort($b, $off);
            $offsets[] = self::getUlong($b, $off);
        }
        for ($i = 0; $i < $numTables; $i++) {
            $off0 = $off = $offsets[$i];
            $format = self::getUshort($b, $off);
            $length = self::getUshort($b, $off);
            $version = self::getUshort($b, $off);
            if ($format == 0) {
                $glyphIdArray = array();
                for ($cid = 0; $cid < 256; $cid++) {
                    $glyphIdArray[] = self::getByte($b, $off);
                }
                $cmap['tables'][] = array('platformID' => $platformIDs[$i],
                      'platformSpecificID' => $platformSpecificIDs[$i],
                      'format' => $format,
                      'length' => $length,
                      'version' => $version,
                      'glyphIdArray' => $glyphIdArray);
            } elseif ($format == 2) {
                throw new Exception('cmap format is 2');
            } elseif ($format == 4) {
                $segCountX2 = self::getUshort($b, $off);
                $searchRange = self::getUshort($b, $off);
                $entrySelector = self::getUshort($b, $off);
                $rangeShift = self::getUshort($b, $off);

                $segCount = $segCountX2 / 2;
                $endCountArray = array();
                $startCountArray = array();
                $idDeltaArray = array();
                $idRangeOffsetArray = array();
                $glyphIdArray = array();
                for ($seg = 0; $seg < $segCount; $seg++) {
                        $endCountArray[] = self::getUshort($b, $off);
                }
                $off += 2; // Skip reserved
                for ($seg = 0; $seg < $segCount; $seg++) {
                    $startCountArray[] = self::getUshort($b, $off);
                }
                for ($seg = 0; $seg < $segCount; $seg++) {
                    $idDeltaArray[] = self::getUshort($b, $off);
                }
                for ($seg = 0; $seg < $segCount; $seg++) {
                    $idRangeOffsetArray[] = self::getUshort($b, $off);
                }
                while ($off < $off0 + $length) {
                    $glyphIdArray[] = self::getUshort($b, $off);
                }
                $cmap['tables'][] = array('platformID' => $platformIDs[$i],
                  'platformSpecificID' => $platformSpecificIDs[$i],
                  'format' => $format,
                  'length' => $length,
                  'version' => $version,
                  'segCount' => $segCount,
                  'endCountArray' => $endCountArray,
                  'startCountArray' => $startCountArray,
                  'idDeltaArray' => $idDeltaArray,
                  'idRangeOffsetArray' => $idRangeOffsetArray,
                  'glyphIdArray' => $glyphIdArray);
            } elseif ($format == 6) {
                $firstCode = self::getUshort($b, $off);
                $entryCount = self::getUshort($b, $off);
                $glyphIdArray = array();
                for ($cid = $firstCode; $cid < $firstCode + $entryCount; $cid++) {
                        $glyphIdArray[] = self::getUshort($b, $off);
                }
                $cmap['tables'][] = array('platformID' => $platformIDs[$i],
                  'platformSpecificID' => $platformSpecificIDs[$i],
                  'format' => $format,
                  'length' => $length,
                  'version' => $version,
                  'firstCode' => $firstCode,
                  'entryCount' => $entryCount,
                  'glyphIdArray' => $glyphIdArray);
            } else {
                $off -= 6; // go back and check for 8.0, 10.0 and 12.0 formats
                $format = self::getFixed($b, $off);
                $length = self::getUlong($b, $off);
                $language = self::getUlong($b, $off);
                if ($format == '8.0') {
                        throw new Exception('cmap format is 8.0');
                } elseif ($format == '10.0') {
                    throw new Exception('cmap format is 10.0');
                } elseif ($format == '12.0') {
                    $nGroups = self::getUlong($b, $off);
                    $startCharCodes = array();
                    $endCharCodes = array();
                    $startGlyphCodes = array();
                    for ($grp = 0; $grp < $nGroups; $grp++) {
                        $startCharCodes[] = self::getUlong($b, $off);
                        $endCharCodes[] = self::getUlong($b, $off);
                        $startGlyphCodes[] = self::getUlong($b, $off);
                    }
                    $cmap['tables'][] = array('platformID' => $platformIDs[$i],
                          'platformSpecificID' => $platformSpecificIDs[$i],
                          'format' => $format,
                          'length' => $length,
                          'version' => $version,
                          'startCharCodes' => $startCharCodes,
                          'endCharCodes' => $endCharCodes,
                          'startGlyphCodes' => $startGlyphCodes);
                } else {
                    throw new Exception('Internal error: unknwon cmap format');
                }
            }
        }
        return $cmap;
    }

    public static function marshalCmap($cmap)
    {
        $lengths = array(); // To hold the length of each table
    
        $sz = 4 + 8 * count($cmap['tables']);
        foreach ($cmap['tables'] as $table) {
            $format = $table['format'];
            if ($format == 0) {
                $length = 6 + 256; // Size for format 0 table
            } elseif ($format == 4) {
                $cnt1 = count($table['startCountArray']);
                $cnt2 = count($table['glyphIdArray']);
                $length = 14 + 4 * 2 * $cnt1 + 2 + 2 * $cnt2; // Size for format 4 table
            } elseif ($format == 6) {
                $cnt = count($table['glyphIdArray']);
                $length = 10 + 2 * $cnt; // Size for format 6 table
            } elseif ($format == '12.0') {
                $cnt = count($table['startCharCodes']);
                $length = 16 + 12 * $cnt; // Size for format 12.0 table
            } else {
                throw new Exception('Internal error');
            }
                $sz += $length;
                $lengths[] = $length;
        }

        $b = str_repeat(chr(0), $sz);
        $off = 0;
        self::setUshort($b, $off, $cmap['version']);
        self::setUshort($b, $off, $cmap['numTables']);
    
        $offset = 4 + 8 * count($cmap['tables']);
        $i = 0;
        foreach ($cmap['tables'] as $table) {
            self::setUshort($b, $off, $table['platformID']);
            self::setUshort($b, $off, $table['platformSpecificID']);
            self::setUlong($b, $off, $offset);
            $offset += $lengths[$i++];
        }
        $i = 0;
        $offset = 4 + 8 * count($cmap['tables']);
        foreach ($cmap['tables'] as $table) {
            $off = $offset;
        
            $format = $table['format'];
            $length = $lengths[$i];
            $version = $table['version'];
            if ($format == 0) {
                self::setUshort($b, $off, $format);
                self::setUshort($b, $off, $length);
                self::setUshort($b, $off, $version);
                $glyphIdArray = $table['glyphIdArray'];
                for ($cid = 0; $cid < count($glyphIdArray); $cid++) {
                    self::setByte($b, $off, $glyphIdArray[$cid]);
                }
            } elseif ($format == 4) {
                $segCount = $table['segCount'];
                $endCountArray = $table['endCountArray'];
                $startCountArray = $table['startCountArray'];
                $idDeltaArray = $table['idDeltaArray'];
                $idRangeOffsetArray = $table['idRangeOffsetArray'];
                $glyphIdArray = $table['glyphIdArray'];
        
                // Calculate searchRange, entrySelector and rangeShift
                $binarySearchRegisters = self::calculateBinarySearchRegisters($segCount, 2, 1);

                self::setUshort($b, $off, $format);
                self::setUshort($b, $off, $length);
                self::setUshort($b, $off, $version);
                self::setUshort($b, $off, 2 * $segCount); // segCountX2
                self::setUshort($b, $off, $binarySearchRegisters['SearchRange']);
                self::setUshort($b, $off, $binarySearchRegisters['EntrySelector']);
                self::setUshort($b, $off, $binarySearchRegisters['RangeShift']);
                for ($seg = 0; $seg < $segCount; $seg++) {
                    self::setUshort($b, $off, $endCountArray[$seg]);
                }
                self::setUshort($b, $off, 0); // Reserved
                for ($seg = 0; $seg < $segCount; $seg++) {
                    self::setUshort($b, $off, $startCountArray[$seg]);
                }
                for ($seg = 0; $seg < $segCount; $seg++) {
                    self::setUshort($b, $off, $idDeltaArray[$seg]);
                }
                for ($seg = 0; $seg < $segCount; $seg++) {
                    self::setUshort($b, $off, $idRangeOffsetArray[$seg]);
                }
                for ($cid = 0; $cid < count($glyphIdArray); $cid++) {
                    self::setUshort($b, $off, $glyphIdArray[$cid]);
                }
            } elseif ($format == 6) {
                self::setUshort($b, $off, $format);
                self::setUshort($b, $off, $length);
                self::setUshort($b, $off, $version);
                self::setUshort($b, $off, $table['firstCode']);
                self::setUshort($b, $off, $table['entryCount']);
                $glyphIdArray = $table['glyphIdArray'];
                for ($cid = 0; $cid < count($glyphIdArray); $cid++) {
                        self::setShort($b, $off, $glyphIdArray[$cid]);
                }
            } elseif ($format == '12.0') {
                $startCharCodes = $table['startCharCodes'];
                $endCharCodes = $table['endCharCodes'];
                $startGlyphCodes = $table['startGlyphCodes'];
                $nGroups = count($startCharCodes);
                self::setFixed($b, $off, '12.0');
                self::setUlong($b, $off, $length);
                self::setUlong($b, $off, 0);
                self::setUlong($b, $off, $nGroups);
                for ($grp = 0; $grp < $nGroups; $grp++) {
                        self::setUlong($b, $off, $startCharCodes[$grp]);
                        self::setUlong($b, $off, $endCharCodes[$grp]);
                        self::setUlong($b, $off, $startGlyphCodes[$grp]);
                }
            } else {
                throw new Exception('Internal error');
            }
                $offset += $lengths[$i++];
        }
        return $b;
    }

    public function unmarshalPost()
    {
        $post = array(); // To return
        $b = $this->getTableRaw('post'); // Get raw bytes for 'post' table
        $off = 0;
    // Collect standard header
        $post['formatType'] = self::getFixed($b, $off);
        $post['italicAngle'] = self::getFixed($b, $off);
        $post['underlinePosition'] = self::getFword($b, $off);
        $post['underlineThickness'] = self::getFword($b, $off);
        $post['isFixedPitch'] = self::getUlong($b, $off);
        $post['minMemType42'] = self::getUlong($b, $off);
        $post['maxMemType42'] = self::getUlong($b, $off);
        $post['minMemType1'] = self::getUlong($b, $off);
        $post['maxMemType1'] = self::getUlong($b, $off);

        if ($post['formatType'] == '1.0') {
            ; // Nothing more
        } elseif ($post['formatType'] == '2.0') {
            // Collect numGlyphs, glyphNameIndex array and glyphNames (Pascal strings)
            $numGlyphs = self::getUshort($b, $off);
            $glyphNameIndex = array();
            for ($i = 0; $i < $numGlyphs; $i++) {
                $glyphNameIndex[] = self::getUshort($b, $off);
            }
            $glyphNames = array();
            while ($off < strlen($b)) {
                $len = self::getByte($b, $off);
                $name = self::getRaw($b, $off, $len);
                $glyphNames[] = $name;
            }

            // 'gn' will contain either a number (for Macintosh standard order glyph name)
            // or a string (otherwise)
            $gn = array();
            for ($i = 0; $i < count($glyphNameIndex); $i++) {
                $index = $glyphNameIndex[$i];
                if ($index >= 0 && $index <= 257) {
                    $gn[] = $index;
                } elseif ($index >= 258 && $index <= 32767) {
                    $gn[] = $glyphNames[$index - 258];
                } else {
                    throw new Exception(sprintf('Internal error - glyphNameIndex is %d', $index));
                }
            }
            $post['glyphNames'] = $gn;
        } elseif ($post['formatType'] == '3.0') {
            ; // Nothing more
        } else {
            throw new Exception(sprintf('Internal error - formatType is %s', $post['formatType']));
        }
        return $post;
    }

    public static function marshalPost($post)
    {
    // Calculate size
        $sz = 32; // Standard header for all formatTypes
        if ($post['formatType'] == '1.0') {
            ; // Nothing more
        } elseif ($post['formatType'] == '2.0') {
            $gn = $post['glyphNames'];
            $sz += 2; // for numberOfGlyphs
            $sz += 2 * count($gn); // for glyphNameIndex
            for ($i = 0; $i < count($gn); $i++) {
                if (is_string($gn[$i])) {
                    $sz += 1 + strlen($gn[$i]);
                }
            }
        } elseif ($post['formatType'] == '3.0') {
            ; // Nothing more
        } else {
            throw new Exception(sprintf('Internal error - formatType is %s', $post['formatType']));
        }

        $b = str_repeat(chr(0), $sz);
        $off = 0;
        self::setFixed($b, $off, $post['formatType']);
        self::setFixed($b, $off, $post['italicAngle']);
        self::setFword($b, $off, $post['underlinePosition']);
        self::setFword($b, $off, $post['underlineThickness']);
        self::setUlong($b, $off, $post['isFixedPitch']);
        self::setUlong($b, $off, $post['minMemType42']);
        self::setUlong($b, $off, $post['maxMemType42']);
        self::setUlong($b, $off, $post['minMemType1']);
        self::setUlong($b, $off, $post['maxMemType1']);
        if ($post['formatType'] == '1.0') {
            ; // Nothing more
        } elseif ($post['formatType'] == '2.0') {
            $gn = $post['glyphNames'];
            $numGlyphs = count($gn);
            $glyphNames = array();
            self::setUshort($b, $off, $numGlyphs); // Push numGlyphs
            for ($i = 0; $i < $numGlyphs; $i++) {
                if (is_string($gn[$i])) {
                    self::setUshort($b, $off, count($glyphNames) + 258);
                    $glyphNames[] = $gn[$i];
                } else {
                    // Macintosh standard order glyph name
                    self::setUshort($b, $off, $gn[$i]);
                }
            }
            for ($i = 0; $i < count($glyphNames); $i++) {
                $len = strlen($glyphNames[$i]);
                self::setByte($b, $off, $len);
                self::setRaw($b, $off, $glyphNames[$i], $len);
            }
        } elseif ($post['formatType'] == '3.0') {
            ; // Nothing more
        } else {
            throw new Exception(sprintf('Internal error - formatType is %s', $post['formatType']));
        }
        return $b;
    }

    private static $tableNamesOrderedByRank = array
    ('head', 'hhea', 'maxp', 'OS/2', 'hmtx', 'LTSH', 'VDMX', 'hdmx', 'cmap', 'fpgm',
     'prep', 'cvt ', 'loca', 'glyf', 'kern', 'name', 'post', 'gasp', 'PCLT', 'GDEF',
     'GPOS', 'GSUB', 'JSTF', 'DSIG');
    
    private static $tableNamesOrderedByName = array
    ('DSIG', 'GDEF', 'GPOS', 'GSUB', 'JSTF', 'LTSH', 'OS/2', 'PCLT', 'VDMX', 'cmap',
     'cvt ', 'fpgm', 'gasp', 'glyf', 'hdmx', 'head', 'hhea', 'hmtx', 'kern', 'loca',
     'maxp', 'name', 'post', 'prep');

    public static function marshalAll($tables)
    {
        $numTables = count($tables);

    // Arrays to hold for each table, the checksum, the offset and the length
        $checksums = array();
        $offsets = array();
        $lengths = array();

        $sb = str_repeat(chr(0), 12 + $numTables * 16); // Allocate room for table directory
        foreach (self::$tableNamesOrderedByRank as $tableName) {
            if (isset($tables[$tableName])) {
                $data = $tables[$tableName];

            // Special handling for 'head' table - set checksum adjustment to zero
                if ($tableName == 'head') {
                    $off = 8;
                    self::setUlong($data, $off, 0);
                }
        
            // Calculate checksums, offsets, lengths
                $checksums[$tableName] = self::calculateTableChecksum($data);
                $offsets[$tableName] = strlen($sb);
                $lengths[$tableName] = strlen($data);
            // Append data and right pad with '0' (align on four byte boundary)
                $sb .= $data;
                while ((strlen($sb) % 4) != 0) {
                    $sb .= chr(0);
                }
            }
        }

    // Dump the table directory
        $off = 0;
        self::setUlong($sb, $off, 0x00010000); // This is actually fixed
        self::setUshort($sb, $off, $numTables);
    // Calculate the binary search registers
        $binarySearchRegisters = self::calculateBinarySearchRegisters($numTables, 16, 4);
        self::setUshort($sb, $off, $binarySearchRegisters['SearchRange']);
        self::setUshort($sb, $off, $binarySearchRegisters['EntrySelector']);
        self::setUshort($sb, $off, $binarySearchRegisters['RangeShift']);
        foreach (self::$tableNamesOrderedByName as $tableName) {
            if (isset($tables[$tableName])) {
                self::setRaw($sb, $off, $tableName, 4);
                self::setUlong($sb, $off, $checksums[$tableName]);
                self::setUlong($sb, $off, $offsets[$tableName]);
                self::setUlong($sb, $off, $lengths[$tableName]);
            }
        }

    // Calculate the checksum adjustment for 'head' table
        $checksum = self::calculateTableChecksum(substr($sb, 0, 12 + 16 * $numTables));
        foreach ($checksums as $chk) {
            $checksum = bcadd($checksum, $chk);
        }
        $checksum = bcsub('2981146554', $checksum); // This is "0xB1B0AFBA"
        while (bccomp($checksum, '0') < 0) {
            $checksum = bcadd($checksum, '4294967296'); // This is "0x100000000"
        }
        $off = $offsets['head'] + 8;
        self::setUlong($sb, $off, $checksum);
        return $sb;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // Helper functions follow
    ////////////////////////////////////////////////////////////////////////////////

    // Search "cmap" for an encoding table having given "platformID" and "platformSpecificID"
    // and return it. Return null if no such table exists
    public static function getEncodingTable($cmap, $platformID, $platformSpecificID)
    {
        foreach ($cmap['tables'] as $table) {
            if ($table['platformID'] == 3 && $table['platformSpecificID'] == 1) {
                return $table;
            }
        }
        return null;
    }

    // Map character "charCode" to index using the encoding table "encodingTable"
    public static function characterToIndex($encodingTable, $charCode)
    {
        $format = $encodingTable['format'];
        if ($format == 0) {
            $glyphIdArray = $encodingTable['glyphIdArray'];
            if ($charCode >= 0 && $charCode < 256) {
                return $glyphIdArray[$charCode];
            }
        } elseif ($format == 4) {
            $segCount = $encodingTable['segCount'];
            $endCountArray = $encodingTable['endCountArray'];
            $startCountArray = $encodingTable['startCountArray'];
            $idDeltaArray = $encodingTable['idDeltaArray'];
            $idRangeOffsetArray = $encodingTable['idRangeOffsetArray'];
            $glyphIdArray = $encodingTable['glyphIdArray'];
    
            for ($seg = 0; $seg < $segCount; $seg++) {
                $endCount = $endCountArray[$seg];
                $startCount = $startCountArray[$seg];
                $idDelta = $idDeltaArray[$seg];
                $idRangeOffset = $idRangeOffsetArray[$seg];
                if ($charCode >= $startCount && $charCode <= $endCount) {
                    if ($idRangeOffset != 0) {
                        $j = $charCode - $startCount + $seg + $idRangeOffset / 2 - $segCount;
                        $gid = $glyphIdArray[$j];
                    } else {
                        $gid = $idDelta + $charCode;
                    }
                        return $gid %= 65536;
                }
            }
        } elseif ($format == 6) {
            $firstCode = $encodingTable['firstCode'];
            $entryCount = $encodingTable['entryCount'];
            $glyphIdArray = $encodingTable['glyphIdArray'];
            if ($charCode >= $firstCode && $charCode < $firstCode + $entryCount) {
                return $glyphIdArray[$charCode - $firstCode];
            }
        } else {
            throw new Exception('Internal error');
        }
        return -1;
    }

    private static function indexToCharacter($encodingTable, $gid)
    {
        $format = $encodingTable['format'];
        if ($format == 0) {
            $glyphIdArray = $encodingTable['glyphIdArray'];
            for ($charCode = 0; $charCode < count($glyphIdArray); $charCode++) {
                $gid0 = $glyphIdArray[$i];
                if ($gid == $gid0) {
                    return sprintf("%d", $charCode);
                }
            }
        } elseif ($format == 4) {
            $segCount = $encodingTable['segCount'];
            $endCountArray = $encodingTable['endCountArray'];
            $startCountArray = $encodingTable['startCountArray'];
            $idDeltaArray = $encodingTable['idDeltaArray'];
            $idRangeOffsetArray = $encodingTable['idRangeOffsetArray'];
            $glyphIdArray = $encodingTable['glyphIdArray'];
    
            for ($seg = 0; $seg < $segCount; $seg++) {
                $endCount = $endCountArray[$seg];
                $startCount = $startCountArray[$seg];
                $idDelta = $idDeltaArray[$seg];
                $idRangeOffset = $idRangeOffsetArray[$seg];
                for ($charCode = $startCount; $charCode <= $endCount; $charCode++) {
                    if ($idRangeOffset != 0) {
                        $j = $charCode - $startCount + $seg + $idRangeOffset / 2 - $segCount;
                        $gid0 = $glyphIdArray[$j];
                    } else {
                        $gid0 = $idDelta + $charCode;
                    }
                        $gid0 %= 65536;
                    if ($gid == $gid0) {
                        return sprintf("%d", $charCode);
                    }
                }
            }
        } elseif ($format == 6) {
            $firstCode = $encodingTable['firstCode'];
            $entryCount = $encodingTable['entryCount'];
            $glyphIdArray = $encodingTable['glyphIdArray'];
            for ($charCode = $firstCode; $charCode < $firstCode + $entryCount; $charCode++) {
                $gid0 = $glyphIdArray[$charCode - $firstCode];
                if ($gid == $gid0) {
                    return sprintf("%d", $charCode);
                }
            }
        } else {
            throw new Exception('Internal error');
        }
        return null;
    }

    // Get the horizontal metrics (advance width and left side bearing) for
    // glyph with index "index"
    public static function getHMetrics($hmtx, $numberOfHMetrics, $index)
    {
        $metrics = $hmtx['metrics'];
        $lsbs = $hmtx['lsbs'];
        if ($index < $numberOfHMetrics) {
            return $metrics[$index];
        } else {
            // Get advance width from last element of metrics
            return array($metrics[$numberOfHMetrics - 1][0], $lsbs[$index - $numberOfHMetrics]);
        }
    }

    // Given the glyph description, parse it and return a PHP array
    public static function getGlyph($description)
    {
        $off = 0;

        $numberOfContours = self::getShort($description, $off);
        $xMin = self::getFword($description, $off);
        $yMin = self::getFword($description, $off);
        $xMax = self::getFword($description, $off);
        $yMax = self::getFword($description, $off);
        if ($numberOfContours >= 0) {
            // Collect the endPoints of contours. Save the last endPoint
            $endPointsOfContours = array();
            for ($i = 0; $i < $numberOfContours; $i++) {
                $lastEndPoint = self::getUshort($description, $off);
                $endPointsOfContours[] = $lastEndPoint;
            }
        
            // Collect the instructions
            $instructionLength = self::getUshort($description, $off);
            $instructions = self::getRaw($description, $off, $instructionLength);

            // Collect the flags
            $flags = array();
            while (count($flags) <= $lastEndPoint) {
                $flag = ord($description{$off});
                $off++;
                if (($flag & 0x08) != 0) {
                    $num = ord($description{$off}) + 1;
                    $off++;
                } else {
                    $num = 1;
                }
                for ($j = 0; $j < $num; $j++) {
                    $flags[] = $flag;
                }
            }

            // Collect the x coordinates
            $xs = self::getCoordinates($description, $off, $flags, 0x02, 0x10);

            // Collect the y coordinates
            $ys = self::getCoordinates($description, $off, $flags, 0x04, 0x20);
        
            return array('numberOfContours' => $numberOfContours,
             'xMin' => $xMin, 'yMin' => $yMin,
             'xMax' => $xMax, 'yMax' => $yMax,
             'endPointsOfContours' => $endPointsOfContours,
             'instructions' => $instructions,
             'flags' => $flags, 'xs' => $xs, 'ys' => $ys);
        } else {
            $components = array();
        
            do {
                $flags = self::getUshort($description, $off);
                $glyphIndex = self::getUshort($description, $off);
        
                $argument1 = $argument2 = $arg1and2 = '';
                $scale = $xscale = $yscale = $scale01 = $scale10 = '';
        
                if (($flags & self::ARG_1_AND_2_ARE_WORDS) != 0) {
                    $argument1 = self::getShort($description, $off);
                    $argument2 = self::getShort($description, $off);
                } else {
                    $arg1and2 = self::getUshort($description, $off);
                }
                if (($flags & self::WE_HAVE_A_SCALE) != 0) {
                    $scale = self::getF2Dot14($description, $off);
                } elseif (($flags & self::WE_HAVE_AN_X_AND_Y_SCALE) != 0) {
                    $xscale = self::getF2Dot14($description, $off);
                    $yscale = self::getF2Dot14($description, $off);
                } elseif (($flags & self::WE_HAVE_A_TWO_BY_TWO) != 0) {
                    $xscale = self::getF2Dot14($description, $off);
                    $scale01 = self::getF2Dot14($description, $off);
                    $scale10 = self::getF2Dot14($description, $off);
                    $yscale = self::getF2Dot14($description, $off);
                }

                if (self::VERBOSE) {
                    echo sprintf("arg1=[%s], arg2=[%s], arg1and2=[%s]\n", $argument1, $argument2, $arg1and2);
                    echo sprintf("flags=0x%02x, glyphIndex=%d\n", $flags, $glyphIndex);
                    if (($flags & self::ARG_1_AND_2_ARE_WORDS) != 0) {
                        echo " arg1and2areWords";
                    }
                    if (($flags & self::ARGS_ARE_XY_VALUES) != 0) {
                        echo " argsAreXyValues";
                    }
                    if (($flags & self::ROUND_XY_TO_GRID) != 0) {
                        echo " roundXyToGrid";
                    }
                    if (($flags & self::WE_HAVE_A_SCALE) != 0) {
                        echo " weHaveAScale";
                    }
                    if (($flags & self::MORE_COMPONENTS) != 0) {
                        echo " moreComponents";
                    }
                    if (($flags & self::WE_HAVE_AN_X_AND_Y_SCALE) != 0) {
                        echo " weHaveAnXandYscale";
                    }
                    if (($flags & self::WE_HAVE_A_TWO_BY_TWO) != 0) {
                        echo " weHaveATwoByTwo";
                    }
                    if (($flags & self::WE_HAVE_INSTRUCTIONS) != 0) {
                        echo " weHaveInstructions";
                    }
                    if (($flags & self::USE_MY_METRICS) != 0) {
                        echo " useMyMetrics";
                    }
                    echo "\n\n";
                }

                $components[] = array('flags' => $flags, 'glyphIndex' => $glyphIndex,
                      'argument1' => $argument1, 'argument2' => $argument2, 'arg1and2' => $arg1and2,
                      'scale' => $scale, 'xscale' => $xscale, 'yscale' => $yscale, 'scale01' => $scale01, 'scale10' => $scale10);
            } while (($flags & self::MORE_COMPONENTS) != 0);
            if (($flags & self::WE_HAVE_INSTRUCTIONS) != 0) {
                $numInstr = self::getUshort($description, $off);
                $instructions = self::getRaw($description, $off, $numInstr);
            } else {
                $instructions = '';
            }
                return array('numberOfContours' => $numberOfContours,
                 'xMin' => $xMin, 'yMin' => $yMin,
                 'xMax' => $xMax, 'yMax' => $yMax,
                 'components' => $components,
                 'instructions' => $instructions);
        }
    }

    // Replace glyph indices of components of composite glyph
    public static function replaceComponentsOfCompositeGlyph($description, $replacements)
    {
        $off = 0;

        $numberOfContours = self::getShort($description, $off);
        if ($numberOfContours >= 0) {
            return $description;
        }
        $off += 8; // Skip xMin, yMin, xMax, yMax
        do {
            $flags = self::getUshort($description, $off);
            $glyphIndex = self::getUshort($description, $off);
            if (isset($replacements[$glyphIndex])) {
                $from = $glyphIndex;
                $to = $replacements[$from];
                $off -= 2; // Go back and replace
                self::setUshort($description, $off, $to);
            }
            // Skip arguments
            if (($flags & self::ARG_1_AND_2_ARE_WORDS) != 0) {
                $off += 4;
            } else {
                $off += 2;
            }
            if (($flags & self::WE_HAVE_A_SCALE) != 0) {
                $off += 2;
            } elseif (($flags & self::WE_HAVE_AN_X_AND_Y_SCALE) != 0) {
                $off += 4;
            } elseif (($flags & self::WE_HAVE_A_TWO_BY_TWO) != 0) {
                $off += 8;
            }
        } while (($flags & self::MORE_COMPONENTS) != 0);
        return $description;
    }


    // Calculate searchRange, entrySelector and rangeShift
    private static function calculateBinarySearchRegisters($count, $size, $logSize)
    {
        $entrySelector = -$logSize;
        $searchRange = 1;
        while (2 * $searchRange < $count * $size) {
            $entrySelector++;
            $searchRange *= 2;
        }
        $rangeShift = $count * $size - $searchRange;
        return array('SearchRange' => $searchRange, 'EntrySelector' => $entrySelector, 'RangeShift' => $rangeShift);
    }

    private static function calculateTableChecksum($data)
    {
        $ret = '0';

    // "Right" pad with zeros
        while ((strlen($data) % 4) != 0) {
            $data .= chr(0);
        }
        $off = 0;
        $len = strlen($data);
        while ($off < $len) {
            $ret += self::getUlong($data, $off);
        }
        $ret = bcmod($ret, '4294967296');
        return $ret;
    }

    //////////////////// Function to get and set bytes, shorts, longs, etc ////////////////////
    private static function getByte($b, &$off)
    {
        return ord($b[$off++]);
    }

    private static function setByte(&$b, &$off, $val)
    {
        $b{$off++} = chr($val);
    }

    private static function getUshort($b, &$off)
    {
        $num = ord($b[$off++]);
        $num = 256 * $num + ord($b[$off++]);
        return $num;
    }

    private static function setUshort(&$b, &$off, $val)
    {
        $b{$off++} = chr($val / 256);
        $b{$off++} = chr($val % 256);
    }

    private static function getShort($b, &$off)
    {
        $num = self::getUshort($b, $off);
        return $num < 32768 ? $num : $num - 65536;
    }

    private static function setShort(&$b, &$off, $val)
    {
        $b{$off++} = chr(($val >> 8) & 0xff);
        $b{$off++} = chr($val & 0xff);
    }

    private static function getUlong($b, &$off)
    {
        $ret = '0';
        $ret = bcadd($ret, bcmul(ord($b[$off++]), '16777216'));
        $ret = bcadd($ret, bcmul(ord($b[$off++]), '65536'));
        $ret = bcadd($ret, bcmul(ord($b[$off++]), '256'));
        $ret = bcadd($ret, ord($b[$off++]));
        return $ret;
    }

    private static function setUlong(&$b, &$off, $val)
    {
        $b{$off++} = chr(bcmod(bcdiv($val, '16777216', 0), '256'));
        $b{$off++} = chr(bcmod(bcdiv($val, '65536', 0), '256'));
        $b{$off++} = chr(bcmod(bcdiv($val, '256', 0), '256'));
        $b{$off++} = chr(bcmod($val, '256'));
    }

    private static function getLong($b, &$off)
    {
        $ret = self::getUlong($b, $off);
        return bccomp($ret, '2147483648') <  0 ? $ret : bcsub($ret, '4294967296');
    }

    private static function getFixed($b, &$off)
    {
        $b1 = ord($b[$off++]);
        $b2 = ord($b[$off++]);
        $b3 = ord($b[$off++]);
        $b4 = ord($b[$off++]);
    
        $mantissa = $b1 * 256 + $b2;
        if ($mantissa >= 32768) {
            $mantissa -= 65536;
        }
        $fraction = $b3 * 256 + $b4;

        if ($fraction == 0) {
            return sprintf("%d.0", $mantissa); // Append one zero
        } else {
            $tmp = sprintf("%.6f", $fraction / 65536);
            $tmp = substr($tmp, 2); // Remove leading "0."
            return sprintf("%d.%s", $mantissa, $tmp);
        }
    }
    
    private static function setFixed(&$b, &$off, $val)
    {
        if ($val{0} == '-') {
            $sign = -1;
            $val = substr($val, 1);
        } else {
            $sign = +1;
        }
        if (($idx = strpos($val, '.')) === false) {
            $mantissa = intval($val);
            $fraction = 0;
        } else {
            $mantissa = intval(substr($val, 0, $idx));
            $fraction = intval(substr($val, $idx + 1));
        }
        $mantissa *= $sign;

        $b{$off++} = chr(($mantissa >> 8) & 0xff);
        $b{$off++} = chr(($mantissa >> 0) & 0xff);
        $b{$off++} = chr(($fraction >> 8) & 0xff);
        $b{$off++} = chr(($fraction >> 0) & 0xff);
    }

    private static function getFword($b, &$off)
    {
        return self::getShort($b, $off);
    }

    private static function setUFword(&$b, &$off, $val)
    {
        self::setUshort($b, $off, $val);
    }

    private static function getUFword($b, &$off)
    {
        return self::getUshort($b, $off);
    }

    private static function setFword(&$b, &$off, $val)
    {
        self::setShort($b, $off, $val);
    }

    private static function getF2dot14($b, &$off)
    {
        $val1 = ord($b{$off});
        $val2 = ord($b{$off + 1});
        $val = 256 * $val1 + $val2;

        $mantissa = ($val >> 14) & 0x03;
        if ($mantissa >= 2) {
            $mantissa -= 4;
        }
        $fraction = $val & 0x3fff;
    
        if ($fraction == 0) {
            // Append only one zero
            $ret = sprintf("%d.0", $mantissa);
        } else {
            $tmp = sprintf("%.6f", $fraction / 16384);
            $tmp = substr($tmp, 2); // Remove leading "0."
            $ret = sprintf("%d.%s", $mantissa, $tmp);
        }
        return $ret;
    }

    private static function getRaw($b, &$off, $num)
    {
        $ret = substr($b, $off, $num);
        $off += $num;
        return $ret;
    }

    private static function setRaw(&$b, &$off, $val, $num)
    {
        $i = 0;
        while ($i < $num) {
            $b{$off++} = $val{$i++};
        }
    }

    private static function parseFixed($val)
    {
        $b1 = ord($val[0]);
        $b2 = ord($val[1]);
        $b3 = ord($val[2]);
        $b4 = ord($val[3]);
    
        $mantissa = $b1 * 256 + $b2;
        if ($mantissa >= 32768) {
            $mantissa -= 65536;
        }
        $fraction = $b3 * 256 + $b4;

        if ($fraction == 0) {
            // Append only one zero
            return sprintf("%d.0", $mantissa);
        } else {
            $tmp = sprintf("%.6f", $fraction / 65536);
            $tmp = substr($tmp, 2); // Remove leading ".0"
            return sprintf("%d.%s", $mantissa, $tmp);
        }
    }

    private static function getCoordinates($code, &$off, $flags, $mask1, $mask2)
    {
        $ret = array();
        for ($i = 0; $i < count($flags); $i++) {
            $flag = $flags[$i];
            $bit1 = $flag & $mask1;
            $bit4 = $flag & $mask2;
            if ($bit1 != 0) {
                $b = ord($code{$off++});
                if ($bit4 != 0) {
                    // Positive 8-bit
                    $val = $b;
                } else {
                    // Negative 8-bit
                    $val = -$b;
                }
            } else {
                if ($bit4 != 0) {
                    // Same as previous (delta=0)
                    $val = 0;
                } else {
                    // Signed 16-bit
                    $b1 = ord($code{$off++});
                    $b2 = ord($code{$off++});
                    $b = 256 * $b1 + $b2;
                    if ($b >= 32768) {
                        $b -= 65536;
                    }
                    $val = $b;
                }
            }
                $ret[] = $val;
        }
        return $ret;
    }
}
