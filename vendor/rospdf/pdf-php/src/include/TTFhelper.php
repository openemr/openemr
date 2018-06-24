<?php

require_once('TTFsubset.php');

class TTFhelper extends TTFsubset
{
    protected $name;
    private $CIDToGIDMap;
    private $GID2CIDMap;

    private $widths;
    private $fontSubset;

    private $isSubset;

    public function __construct($fontFile, $subset = '')
    {
        if (empty($subset)) {
            $this->isSubset = false;

            $ttf = new TTF(file_get_contents($fontFile));

            $this->head = $ttf->unmarshalHead();
            $this->name = $ttf->unmarshalName();
            $this->hhea = $ttf->unmarshalHhea();
            $this->post = $ttf->unmarshalPost(true);
            $this->maxp = $ttf->unmarshalMAXP();
            $this->cmap = $ttf->unmarshalCmap();

            $this->hmtx = $ttf->unmarshalHmtx($this->hhea['numberOfHMetrics'], $this->maxp['numGlyphs']);
        } else {
            $this->isSubset = true;

            $this->fontSubset = $this->doSubset($fontFile, $subset, null);
        }

        $this->constructCidGidMaps();
        $this->constructWidths();
    }

    private function constructCidGidMaps()
    {
        if ($this->isSubset) {
            $cmap = &$this->newCmap;
        } else {
            $cmap = $this->cmap;
        }
        // Get the Unicode encoding table
        if (($unicodeEncodingTable = TTF::getEncodingTable($cmap, 3, 1)) == null) {
            throw new Exception("No Unicode encoding table");
        }
        if ($unicodeEncodingTable['format'] != 4) {
            throw new Exception("Unicode encoding table not in format 4");
        }
        $segCount = $unicodeEncodingTable['segCount'];
        $endCountArray = $unicodeEncodingTable['endCountArray'];
        $startCountArray = $unicodeEncodingTable['startCountArray'];
        $idDeltaArray = $unicodeEncodingTable['idDeltaArray'];
        $idRangeOffsetArray = $unicodeEncodingTable['idRangeOffsetArray'];
        $glyphIdArray = $unicodeEncodingTable['glyphIdArray'];
    
        $this->CIDToGIDMap = [];
        $this->GID2CIDMap = [];
    
        for ($seg = 0; $seg < $segCount; $seg++) {
            $startCount = $startCountArray[$seg];
            $endCount = $endCountArray[$seg];
            $idDelta = $idDeltaArray[$seg];
            $idRangeOffset = $idRangeOffsetArray[$seg];
            for ($cid = $startCount; $cid <= $endCount; $cid++) {
                if ($idRangeOffset != 0) {
                    $j = $cid - $startCount + $seg + $idRangeOffset / 2 - $segCount;
                    $gid = $glyphIdArray[$j];
                } else {
                    $gid = $idDelta + $cid;
                }

                $gid = $gid % 65536;
                $this->CIDToGIDMap[$cid] = $gid;
                $this->GID2CIDMap[$gid] = $cid;
            }
        }
    }

    private function constructWidths()
    {
        if ($this->isSubset) {
            $hmtx = &$this->newHmtx;
        } else {
            $hmtx = &$this->hmtx;
        }

        $this->widths = [];
        foreach ($this->getCIDMap() as $char => $glyphIndex) {
            if ($char > 0) {
                $m = TTF::getHMetrics($hmtx, $this->hhea['numberOfHMetrics'], $glyphIndex);
                $this->widths[$char] = intval($m[0] / ($this->head['unitsPerEm'] / 1000));
            }
        }
    }

    public function getCIDMap()
    {
        return $this->CIDToGIDMap;
    }

    public function getGIDMap()
    {
        return $this->GID2CIDMap;
    }

    public function getWidths()
    {
        return $this->widths;
    }

    public function getFont()
    {
        return $this->fontSubset;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getNewHmax()
    {
        return $this->newHmtx;
    }

    public function getHead()
    {
        return $this->head;
    }

    public function getHhead()
    {
        return $this->hhea;
    }
}
