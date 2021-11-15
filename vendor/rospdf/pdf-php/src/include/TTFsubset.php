<?php
/*
    TTFsubset.php: TrueType font file reader and writer
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

include_once('TTF.php');

class TTFsubset
{
    const VERBOSE = false; // For debugging

    // "Old" tables and fields
    protected $head;
    protected $indexToLocFormat;
    protected $hhea;
    protected $numberOfHMetrics;
    protected $maxp;
    protected $numGlyphs;
    protected $orgCvt_Raw;
    protected $orgPrepRaw;
    protected $orgFpgmRaw;
    protected $hmtx;
    protected $loca;
    protected $glyf;
    protected $cmap;
    protected $post;

    private $orgOS_2Raw;
    private $orgNameRaw;

    // "New" tables and fields
    protected $newHmtx;
    protected $newCmap;
    protected $newGlyf;
    protected $newLoca;
    protected $newPost;
    protected $newNumGlyphs;
    protected $newIndexToLocFormat;
    protected $newNumberOfHMetrics;

    protected $TTFchars;

    public function doSubset($fontFile, $chars, $gids)
    {
        $this->unmarshal($fontFile);
    
    // Initialize TTFchars array
        $this->TTFchars = [];
    // Push index 0 (missing character) anyhow
        $this->TTFchars[] = new TTFchar(null, 0, 0, $this->glyf[0]);
    // Push index 1 (null character) anyhow
        $this->TTFchars[] = new TTFchar(null, 1, 0, '');

        if ($chars != null) {
            $this->collectChars($chars);
        } elseif ($gids != null) {
            $this->collectGids($gids);
        }
        $this->pushComponentsOfCompositeGlyphs();
        $this->assignNewIndices();
        $this->replaceComponentsOfCompositeGlyphs();
        $this->constructHmtx();
        $this->constructCmap();
        $this->constructLocaAndGlyf();
        $this->constructPost();
        $this->newNumGlyphs = count($this->TTFchars);
        $this->head['indexToLocFormat'] = $this->newIndexToLocFormat;
        $this->hhea['numberOfHMetrics'] = $this->newNumberOfHMetrics;
        $this->maxp['numGlyphs'] = $this->newNumGlyphs;
        return $this->marshal();
    }

    private function unmarshal($fontFile)
    {
        $ttf = new TTF(file_get_contents($fontFile));

        $this->head = $ttf->unmarshalHead();
        $this->indexToLocFormat = $this->head['indexToLocFormat'];

        $this->hhea = $ttf->unmarshalHhea();
        $this->numberOfHMetrics = $this->hhea['numberOfHMetrics'];

        $this->maxp = $ttf->unmarshalMAXP();
        $this->numGlyphs = $this->maxp['numGlyphs'];

        $this->orgCvt_Raw = $ttf->getTableRaw('cvt ');
        $this->orgPrepRaw = $ttf->getTableRaw('prep');
        $this->orgFpgmRaw = $ttf->getTableRaw('fpgm');

        $this->hmtx = $ttf->unmarshalHmtx($this->numberOfHMetrics, $this->numGlyphs);
        $this->loca = $ttf->unmarshalLoca($this->indexToLocFormat, $this->numGlyphs);
        $this->glyf = $ttf->unmarshalGlyf($this->loca);
        $this->cmap = $ttf->unmarshalCmap();

        $this->orgOS_2Raw = $ttf->getTableRaw('OS/2');
        $this->orgNameRaw = $ttf->getTableRaw('name');
        $this->post = $ttf->unmarshalPost();
    }

    private function marshal()
    {
        $newHeadRaw = TTF::marshalHead($this->head);
        $newHheaRaw = TTF::marshalHhea($this->hhea);
        $newMaxpRaw = TTF::marshalMAXP($this->maxp);

        $newHmtxRaw = TTF::marshalHmtx($this->newHmtx['metrics'], $this->newHmtx['lsbs']);
        $newCmapRaw = TTF::marshalCmap($this->newCmap);
        $newLocaRaw = TTF::marshalLoca($this->newLoca, $this->newIndexToLocFormat, $this->newNumGlyphs);
        $newGlyfRaw = TTF::marshalGlyf($this->newGlyf);

        $newPostRaw = TTF::marshalPost($this->newPost);

        $tables = [];
        $tables['head'] = $newHeadRaw;
        $tables['hhea'] = $newHheaRaw;
        $tables['maxp'] = $newMaxpRaw;
        $tables['loca'] = $newLocaRaw;
        if ($this->orgCvt_Raw != null) {
            $tables['cvt '] = $this->orgCvt_Raw;
        }
        if ($this->orgPrepRaw != null) {
            $tables['prep'] = $this->orgPrepRaw;
        }
        $tables['glyf'] = $newGlyfRaw;
        $tables['hmtx'] = $newHmtxRaw;
        if ($this->orgFpgmRaw != null) {
            $tables['fpgm'] = $this->orgFpgmRaw;
        }
        $tables['cmap'] = $newCmapRaw;
        if ($this->orgOS_2Raw != null) {
            $tables['OS/2'] = $this->orgOS_2Raw;
        }
        if ($this->orgNameRaw != null) {
            $tables['name'] = $this->orgNameRaw;
        }
        $tables['post'] = $newPostRaw;

        return TTF::marshalAll($tables);
    }

    // Construct new hmtx table
    private function constructHmtx()
    {
        $allMetrics = [];
        foreach ($this->TTFchars as $TTFchar) {
            $allMetrics[] = TTF::getHMetrics($this->hmtx, $this->numberOfHMetrics, $TTFchar->orgIndex);
        }
    // Split to metrics and lsbs
        $numAllMetrics = count($allMetrics);
        $lastMetric = $allMetrics[$numAllMetrics - 1];
    // Looping from last to first, collect a sequence of metrics that have same advance width as last
        for ($i = $numAllMetrics - 1; $i > 0; $i--) {
            $metric = $allMetrics[$i - 1];
            if ($metric[0] != $lastMetric[0]) {
                break;
            }
        }
        if ($i == 0) {
            // All metrics have same advance width
            $this->newNumberOfHMetrics = 1;
        } elseif ($i == $numAllMetrics - 1) {
            $this->newNumberOfHMetrics = $numAllMetrics;
        } else {
            $this->newNumberOfHMetrics = $i + 1;
        }
    
        $metrics = [];
        $lsbs = [];
        for ($i = 0; $i < $numAllMetrics; $i++) {
            if ($i < $this->newNumberOfHMetrics) {
                $metrics[] = $allMetrics[$i];
            } else {
                $lsbs[] = $allMetrics[$i][1];
            }
        }
        $this->newHmtx = ['metrics' => $metrics, 'lsbs' => $lsbs];
    }

    // Construct new cmap table
    private function constructCmap()
    {
        $newTables = [];
        foreach ($this->cmap['tables'] as $table) {
            $platformID = $table['platformID'];
            $platformSpecificID = $table['platformSpecificID'];
            $format = $table['format'];
            $length = $table['length'];
            $version = $table['version'];
            if ($format == 0) {
                $glyphIdArray = $table['glyphIdArray'];
                for ($i = 0; $i < count($glyphIdArray); $i++) {
                    $glyphIdArray[$i] = $this->map($glyphIdArray[$i]);
                }
                $newTables[] = array('platformID' => $platformID,
                     'platformSpecificID' => $platformSpecificID,
                     'format' => $format,
                     'length' => 0, // To be calculated
                     'version' => $version,
                     'glyphIdArray' => $glyphIdArray);
            } elseif ($format == 4) {
                $newEndCountArray = [];
                $newStartCountArray = [];
                $newIdDeltaArray = [];
                $newIdRangeOffsetArray = [];
                $newGlyphIdArray = [];
                // Skip entries with null charCode
                $i = 0;
                $cnt = count($this->TTFchars);
                while ($i < $cnt) {
                    if ($this->TTFchars[$i]->charCode !== null) {
                        break;
                    }
                    $i++;
                }
                $newEndCountArray[] = 0;
                $newStartCountArray[] = 0;
                $newIdDeltaArray[] = 0;
                $newIdRangeOffsetArray[] = 0;
        
                while ($i < $cnt) {
                    //XXX something better here
                    // Collect a sequence with increasing charCode and newIndex
                    $j = $i;
                    while ($i < $cnt) {
                        if ($this->TTFchars[$i]->charCode - $this->TTFchars[$j]->charCode != $i - $j ||
                        $this->TTFchars[$i]->newIndex - $this->TTFchars[$j]->newIndex != $i - $j) {
                            break;
                        }
                        $i++;
                    }
                    $newEndCountArray[] = $this->TTFchars[$i - 1]->charCode;
                    $newStartCountArray[] = $this->TTFchars[$j]->charCode;
                    $newIdDeltaArray[] = 65536 + $this->TTFchars[$j]->newIndex - $this->TTFchars[$j]->charCode;
                    $newIdRangeOffsetArray[] = 0;
                }
                $newEndCountArray[] = 65535;
                $newStartCountArray[] = 65535;
                $newIdDeltaArray[] = 1;
                $newIdRangeOffsetArray[] = 0;
        
                $newSegCount = count($newEndCountArray);

                if (self::VERBOSE) {
                    echo "ARRAYS\n";
                    for ($i = 0; $i < $newSegCount; $i++) {
                        echo sprintf("%5d %5d %5d %5d\n", $newEndCountArray[$i], $newStartCountArray[$i], $newIdDeltaArray[$i], $newIdRangeOffsetArray[$i]);
                    }
                }
                $newTables[] = array('platformID' => $platformID,
                 'platformSpecificID' => $platformSpecificID,
                 'format' => $format,
                 'length' => 0, // To be calculated
                 'version' => $version,
                 'segCount' => $newSegCount,
                 'endCountArray' => $newEndCountArray,
                 'startCountArray' => $newStartCountArray,
                 'idDeltaArray' => $newIdDeltaArray,
                 'idRangeOffsetArray' => $newIdRangeOffsetArray,
                 'glyphIdArray' => $newGlyphIdArray);
            } elseif ($format == 6) {
                $glyphIdArray = $table['glyphIdArray'];
                for ($i = 0; $i < count($glyphIdArray); $i++) {
                        $glyphIdArray[$i] = $this->map($glyphIdArray[$i]);
                }
                $newTables[] = array('platformID' => $platformID,
                 'platformSpecificID' => $platformSpecificID,
                 'format' => $format,
                 'length' => 0,
                 'version' => $version,
                 'firstCode' => $table['firstCode'],
                 'entryCount' => $table['entryCount'],
                 'glyphIdArray' => $glyphIdArray);
            } elseif ($format == '12.0') {
                $startCharCodes = $table['startCharCodes'];
                $endCharCodes = $table['endCharCodes'];
                $startGlyphCodes = $table['startGlyphCodes'];
                for ($i = 0; $i < count($startGlyphCodes); $i++) {
                        $startGlyphCodes[$i] = $this->map($startGlyphCodes[$i]);
                }
                $newTables[] = array('platformID' => $platformID,
                 'platformSpecificID' => $platformSpecificID,
                 'format' => $format,
                 'length' => $length,
                 'version' => $version,
                 'startCharCodes' => $startCharCodes,
                 'endCharCodes' => $endCharCodes,
                 'startGlyphCodes' => $startGlyphCodes);
            } else {
                throw new Exception('Internal error');
            }
        }
        $this->newCmap = array('version' => $this->cmap['version'],
                   'numTables' => $this->cmap['numTables'],
                   'tables' => $newTables);
    }

    // Construct new loca and glyf tables
    private function constructLocaAndGlyf()
    {
        $this->newGlyf = [];
        $this->newLoca = [];
        $offset = 0;
        foreach ($this->TTFchars as $TTFchar) {
            $description = $TTFchar->description;
            $len = strlen($description);
            if (($len % 4) != 0) {
                $toPad = 4 - ($len % 4);
                $description .= str_repeat(chr(0), $toPad);
                $len += $toPad;
            }
            $this->newGlyf[] = $description;
            $this->newLoca[] = $offset;
            $offset += $len;
        }
        $this->newLoca[] = $offset;
        $this->newIndexToLocFormat = $offset <= 0x20000 ? 0 : 1;
    }

    // Construct new post table
    private function constructPost()
    {
        $formatType = $this->post['formatType'];
        if ($formatType == '2.0') {
            $gn = $this->post['glyphNames'];
            // 'gn2' will be the new 'glyphNames' array
            // As new indices are assigned sequentially, 'gn2' will have
            // its first indices set
            $gn2 = [];
            foreach ($this->TTFchars as $TTFchar) {
                $orgIndex = $TTFchar->orgIndex;
                $newIndex = $TTFchar->newIndex;
                $gn2[$newIndex] = $gn[$orgIndex];
            }
            $this->newPost = array
            ('formatType' => $this->post['formatType'],
             'italicAngle' => $this->post['italicAngle'],
             'underlinePosition' => $this->post['underlinePosition'],
             'underlineThickness' => $this->post['underlineThickness'],
             'isFixedPitch' => $this->post['isFixedPitch'],
             'minMemType42' => $this->post['minMemType42'],
             'maxMemType42' => $this->post['maxMemType42'],
             'minMemType1' => $this->post['minMemType1'],
             'maxMemType1' => $this->post['maxMemType1'],
             'glyphNames' => $gn2);
        } else {
            throw new Exception(sprintf('Internal error - formatType is %s', $this->post['formatType']));
        }
    }

    private function collectChars($chars)
    {
        if (($unicodeEncodingTable = TTF::getEncodingTable($this->cmap, 3, 1)) === null) {
            throw new Exception('No unicode (3,1) encoding table found');
        }
        for ($i = 0; $i < strlen($chars); $i += 2) {
            $charCode = self::ORD(substr($chars, $i, 2));
            $orgIndex = TTF::characterToIndex($unicodeEncodingTable, $charCode);
            $description = $this->glyf[$orgIndex];
            if (!$this->orgIndexAlreadyExists($orgIndex)) {
                $this->TTFchars[] = new TTFchar($charCode, $orgIndex, 0, $description);
            }
        }
    }

    private function collectGids($gids)
    {
    // Collect the unicode encoding table
        $unicodeEncodingTable = TTF::getEncodingTable($this->cmap, 3, 1);
    
        for ($i = 0; $i < count($gids); $i++) {
            $orgIndex = $gids[$i];
            $description = $this->glyf[$orgIndex];
            if (!$this->orgIndexAlreadyExists($orgIndex)) {
                $unicodeValue = $unicodeEncodingTable == null ? null : TTF::indexToCharacter($unicodeEncodingTable, $orgIndex);

            //XXXX THANOS
                $this->TTFchars[] = new TTFchar($unicodeValue, $orgIndex, 0, $description);
            }
        }
    }

    private function pushComponentsOfCompositeGlyphs()
    {
    // If there exist composite glyphs (numberOfContours < 0), we have to append the components
    // WARNING: This loop appends to $this->TTFchars (foreach will not work)
        for ($i = 0; $i < count($this->TTFchars); $i++) {
            $TTFchar = $this->TTFchars[$i];
            $description = $TTFchar->description;
            if (strlen($description) == 0) {
                continue;
            }
            $glyph = TTF::getGlyph($description);
            if ($glyph['numberOfContours'] >= 0) {
                continue;
            }
            foreach ($glyph['components'] as $component) {
                if (!$this->orgIndexAlreadyExists($component['glyphIndex'])) {
                    $orgIndex2 = $component['glyphIndex'];
                    $description2 = $this->glyf[$orgIndex2];
                    $this->TTFchars[] = new TTFchar(null, $orgIndex2, 0, $description2);
                }
            }
        }
    }

    private function assignNewIndices()
    {
        usort($this->TTFchars, ['TTFsubset', 'TTFcharComparatorOnCharCode']);

    // Assign newIndex
        $newIndex = 0;
        for ($i = 0; $i < count($this->TTFchars); $i++) {
            $this->TTFchars[$i]->newIndex = $newIndex++;
        }
    }

    private function replaceComponentsOfCompositeGlyphs()
    {

    // If there exist composite glyphs, replace the components' glyphIndices
    // First construct a from=>to array
        $replacements = [];
        foreach ($this->TTFchars as $TTFchar) {
            $orgIndex = $TTFchar->orgIndex;
            $newIndex = $TTFchar->newIndex;
            $replacements[$orgIndex] = $newIndex;
        }
        for ($i = 0; $i < count($this->TTFchars); $i++) {
            $TTFchar = $this->TTFchars[$i];
            $description = $TTFchar->description;
            if (strlen($description) == 0) {
                continue;
            }
            $glyph = TTF::getGlyph($description);
            if ($glyph['numberOfContours'] >= 0) {
                continue;
            }
            $newDescription = TTF::replaceComponentsOfCompositeGlyph($description, $replacements);
            $this->TTFchars[$i]->description = $newDescription;
        }
    
        if (self::VERBOSE) {
            foreach ($this->TTFchars as $TTFchar) {
                echo sprintf("%4d %4d %4d %4d\n", $TTFchar->charCode, $TTFchar->orgIndex, $TTFchar->newIndex, strlen($TTFchar->description));
            }
            echo sprintf("TTFchars size is %d\n", count($this->TTFchars));
        }
    }

    private function orgIndexAlreadyExists($orgIndex)
    {
        foreach ($this->TTFchars as $TTFchar) {
            if ($TTFchar->orgIndex == $orgIndex) {
                return true;
            }
        }
        return false;
    }

    private function TTFcharComparatorOnCharCode($t1, $t2)
    {
        return $t1->charCode - $t2->charCode;
    }

    private function map($index)
    {
        for ($i = 0; $i < count($this->TTFchars); $i++) {
            $TTFchar = $this->TTFchars[$i];
            if ($TTFchar->orgIndex == $index) {
                return $TTFchar->newIndex;
            }
        }
        return 0; // Map to index 0
    }

    private static function ORD($str)
    {
        $val = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $val = 256 * $val + ord($str[$i]);
        }
        return $val;
    }
}

class TTFchar
{
    public $charCode;
    public $orgIndex;
    public $newIndex;
    public $description;

    public function __construct($charCode, $orgIndex, $newIndex, $description)
    {
        $this->charCode = $charCode;
        $this->orgIndex = $orgIndex;
        $this->newIndex = $newIndex;
        $this->description = $description;
    }
}
