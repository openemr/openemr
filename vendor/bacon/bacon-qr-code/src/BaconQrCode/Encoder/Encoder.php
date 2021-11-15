<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

use BaconQrCode\Common\BitArray;
use BaconQrCode\Common\CharacterSetEci;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Common\Mode;
use BaconQrCode\Common\ReedSolomonCodec;
use BaconQrCode\Common\Version;
use BaconQrCode\Exception;
use SplFixedArray;

/**
 * Encoder.
 */
class Encoder
{
    /**
     * Default byte encoding.
     */
    const DEFAULT_BYTE_MODE_ECODING = 'ISO-8859-1';

    /**
     * The original table is defined in the table 5 of JISX0510:2004 (p.19).
     *
     * @var array
     */
    protected static $alphanumericTable = array(
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,  // 0x00-0x0f
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,  // 0x10-0x1f
        36, -1, -1, -1, 37, 38, -1, -1, -1, -1, 39, 40, -1, 41, 42, 43,  // 0x20-0x2f
        0,   1,  2,  3,  4,  5,  6,  7,  8,  9, 44, -1, -1, -1, -1, -1,  // 0x30-0x3f
        -1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24,  // 0x40-0x4f
        25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, -1, -1, -1, -1, -1,  // 0x50-0x5f
    );

    /**
     * Codec cache.
     *
     * @var array
     */
    protected static $codecs = array();

    /**
     * Encodes "content" with the error correction level "ecLevel".
     *
     * @param  string               $content
     * @param  ErrorCorrectionLevel $ecLevel
     * @param  ?                    $hints
     * @return QrCode
     */
    public static function encode($content, ErrorCorrectionLevel $ecLevel, $encoding = self::DEFAULT_BYTE_MODE_ECODING)
    {
        // Pick an encoding mode appropriate for the content. Note that this
        // will not attempt to use multiple modes / segments even if that were
        // more efficient. Would be nice.
        $mode = self::chooseMode($content, $encoding);

        // This will store the header information, like mode and length, as well
        // as "header" segments like an ECI segment.
        $headerBits = new BitArray();

        // Append ECI segment if applicable
        if ($mode->get() === Mode::BYTE && $encoding !== self::DEFAULT_BYTE_MODE_ECODING) {
            $eci = CharacterSetEci::getCharacterSetEciByName($encoding);

            if ($eci !== null) {
                self::appendEci($eci, $headerBits);
            }
        }

        // (With ECI in place,) Write the mode marker
        self::appendModeInfo($mode, $headerBits);

        // Collect data within the main segment, separately, to count its size
        // if needed. Don't add it to main payload yet.
        $dataBits = new BitArray();
        self::appendBytes($content, $mode, $dataBits, $encoding);

        // Hard part: need to know version to know how many bits length takes.
        // But need to know how many bits it takes to know version. First we
        // take a guess at version by assuming version will be the minimum, 1:
        $provisionalBitsNeeded = $headerBits->getSize()
                               + $mode->getCharacterCountBits(Version::getVersionForNumber(1))
                               + $dataBits->getSize();
        $provisionalVersion = self::chooseVersion($provisionalBitsNeeded, $ecLevel);

        // Use that guess to calculate the right version. I am still not sure
        // this works in 100% of cases.
        $bitsNeeded = $headerBits->getSize()
                    + $mode->getCharacterCountBits($provisionalVersion)
                    + $dataBits->getSize();
        $version = self::chooseVersion($bitsNeeded, $ecLevel);

        $headerAndDataBits = new BitArray();
        $headerAndDataBits->appendBitArray($headerBits);

        // Find "length" of main segment and write it.
        $numLetters = ($mode->get() === Mode::BYTE ? $dataBits->getSizeInBytes() : strlen($content));
        self::appendLengthInfo($numLetters, $version, $mode, $headerAndDataBits);

        // Put data together into the overall payload.
        $headerAndDataBits->appendBitArray($dataBits);
        $ecBlocks     = $version->getEcBlocksForLevel($ecLevel);
        $numDataBytes = $version->getTotalCodewords() - $ecBlocks->getTotalEcCodewords();

        // Terminate the bits properly.
        self::terminateBits($numDataBytes, $headerAndDataBits);

        // Interleave data bits with error correction code.
        $finalBits = self::interleaveWithEcBytes(
            $headerAndDataBits,
            $version->getTotalCodewords(),
            $numDataBytes,
            $ecBlocks->getNumBlocks()
        );

        $qrCode = new QrCode();
        $qrCode->setErrorCorrectionLevel($ecLevel);
        $qrCode->setMode($mode);
        $qrCode->setVersion($version);

        // Choose the mask pattern and set to "qrCode".
        $dimension   = $version->getDimensionForVersion();
        $matrix      = new ByteMatrix($dimension, $dimension);
        $maskPattern = self::chooseMaskPattern($finalBits, $ecLevel, $version, $matrix);
        $qrCode->setMaskPattern($maskPattern);

        // Build the matrix and set it to "qrCode".
        MatrixUtil::buildMatrix($finalBits, $ecLevel, $version, $maskPattern, $matrix);
        $qrCode->setMatrix($matrix);

        return $qrCode;
    }

    /**
     * Gets the alphanumeric code for a byte.
     *
     * @param  string|integer $code
     * @return integer
     */
    protected static function getAlphanumericCode($code)
    {
        $code = (is_string($code) ? ord($code) : $code);

        if (isset(self::$alphanumericTable[$code])) {
            return self::$alphanumericTable[$code];
        }

        return -1;
    }

    /**
     * Chooses the best mode for a given content.
     *
     * @param  string $content
     * @param  string $encoding
     * @return Mode
     */
    protected static function chooseMode($content, $encoding = null)
    {
        if (strcasecmp($encoding, 'SHIFT-JIS') === 0) {
            return self::isOnlyDoubleByteKanji($content) ? new Mode(Mode::KANJI) : new Mode(Mode::BYTE);
        }

        $hasNumeric      = false;
        $hasAlphanumeric = false;
        $contentLength   = strlen($content);

        for ($i = 0; $i < $contentLength; $i++) {
            $char = $content[$i];

            if (ctype_digit($char)) {
                $hasNumeric = true;
            } elseif (self::getAlphanumericCode($char) !== -1) {
                $hasAlphanumeric = true;
            } else {
                return new Mode(Mode::BYTE);
            }
        }

        if ($hasAlphanumeric) {
            return new Mode(Mode::ALPHANUMERIC);
        } elseif ($hasNumeric) {
            return new Mode(Mode::NUMERIC);
        }

        return new Mode(Mode::BYTE);
    }

    /**
     * Calculates the mask penalty for a matrix.
     *
     * @param  ByteMatrix $matrix
     * @return integer
     */
    protected static function calculateMaskPenalty(ByteMatrix $matrix)
    {
        return (
            MaskUtil::applyMaskPenaltyRule1($matrix)
            + MaskUtil::applyMaskPenaltyRule2($matrix)
            + MaskUtil::applyMaskPenaltyRule3($matrix)
            + MaskUtil::applyMaskPenaltyRule4($matrix)
        );
    }

    /**
     * Chooses the best mask pattern for a matrix.
     *
     * @param  BitArray             $bits
     * @param  ErrorCorrectionLevel $ecLevel
     * @param  Version              $version
     * @param  ByteMatrix           $matrix
     * @return integer
     */
    protected static function chooseMaskPattern(
        BitArray $bits,
        ErrorCorrectionLevel $ecLevel,
        Version $version,
        ByteMatrix $matrix
    ) {
        $minPenality     = PHP_INT_MAX;
        $bestMaskPattern = -1;

        for ($maskPattern = 0; $maskPattern < QrCode::NUM_MASK_PATTERNS; $maskPattern++) {
            MatrixUtil::buildMatrix($bits, $ecLevel, $version, $maskPattern, $matrix);
            $penalty = self::calculateMaskPenalty($matrix);

            if ($penalty < $minPenality) {
                $minPenality     = $penalty;
                $bestMaskPattern = $maskPattern;
            }
        }

        return $bestMaskPattern;
    }

    /**
     * Chooses the best version for the input.
     *
     * @param  integer              $numInputBits
     * @param  ErrorCorrectionLevel $ecLevel
     * @return Version
     * @throws Exception\WriterException
     */
    protected static function chooseVersion($numInputBits, ErrorCorrectionLevel $ecLevel)
    {
        for ($versionNum = 1; $versionNum <= 40; $versionNum++) {
            $version  = Version::getVersionForNumber($versionNum);
            $numBytes = $version->getTotalCodewords();

            $ecBlocks   = $version->getEcBlocksForLevel($ecLevel);
            $numEcBytes = $ecBlocks->getTotalEcCodewords();

            $numDataBytes    = $numBytes - $numEcBytes;
            $totalInputBytes = intval(($numInputBits + 8) / 8);

            if ($numDataBytes >= $totalInputBytes) {
                return $version;
            }
        }

        throw new Exception\WriterException('Data too big');
    }

    /**
     * Terminates the bits in a bit array.
     *
     * @param  integer  $numDataBytes
     * @param  BitArray $bits
     * @throws Exception\WriterException
     */
    protected static function terminateBits($numDataBytes, BitArray $bits)
    {
        $capacity = $numDataBytes << 3;

        if ($bits->getSize() > $capacity) {
            throw new Exception\WriterException('Data bits cannot fit in the QR code');
        }

        for ($i = 0; $i < 4 && $bits->getSize() < $capacity; $i++) {
            $bits->appendBit(false);
        }

        $numBitsInLastByte = $bits->getSize() & 0x7;

        if ($numBitsInLastByte > 0) {
            for ($i = $numBitsInLastByte; $i < 8; $i++) {
                $bits->appendBit(false);
            }
        }

        $numPaddingBytes = $numDataBytes - $bits->getSizeInBytes();

        for ($i = 0; $i < $numPaddingBytes; $i++) {
            $bits->appendBits(($i & 0x1) === 0 ? 0xec : 0x11, 8);
        }

        if ($bits->getSize() !== $capacity) {
            throw new Exception\WriterException('Bits size does not equal capacity');
        }
    }

    /**
     * Gets number of data- and EC bytes for a block ID.
     *
     * @param  integer $numTotalBytes
     * @param  integer $numDataBytes
     * @param  integer $numRsBlocks
     * @param  integer $blockId
     * @return array
     * @throws Exception\WriterException
     */
    protected static function getNumDataBytesAndNumEcBytesForBlockId(
        $numTotalBytes,
        $numDataBytes,
        $numRsBlocks,
        $blockId
    ) {
        if ($blockId >= $numRsBlocks) {
            throw new Exception\WriterException('Block ID too large');
        }

        $numRsBlocksInGroup2   = $numTotalBytes % $numRsBlocks;
        $numRsBlocksInGroup1   = $numRsBlocks - $numRsBlocksInGroup2;
        $numTotalBytesInGroup1 = intval($numTotalBytes / $numRsBlocks);
        $numTotalBytesInGroup2 = $numTotalBytesInGroup1 + 1;
        $numDataBytesInGroup1  = intval($numDataBytes / $numRsBlocks);
        $numDataBytesInGroup2  = $numDataBytesInGroup1 + 1;
        $numEcBytesInGroup1    = $numTotalBytesInGroup1 - $numDataBytesInGroup1;
        $numEcBytesInGroup2    = $numTotalBytesInGroup2 - $numDataBytesInGroup2;

        if ($numEcBytesInGroup1 !== $numEcBytesInGroup2) {
            throw new Exception\WriterException('EC bytes mismatch');
        }

        if ($numRsBlocks !== $numRsBlocksInGroup1 + $numRsBlocksInGroup2) {
            throw new Exception\WriterException('RS blocks mismatch');
        }

        if ($numTotalBytes !==
            (($numDataBytesInGroup1 + $numEcBytesInGroup1) * $numRsBlocksInGroup1)
            + (($numDataBytesInGroup2 + $numEcBytesInGroup2) * $numRsBlocksInGroup2)
        ) {
            throw new Exception\WriterException('Total bytes mismatch');
        }

        if ($blockId < $numRsBlocksInGroup1) {
            return array($numDataBytesInGroup1, $numEcBytesInGroup1);
        } else {
            return array($numDataBytesInGroup2, $numEcBytesInGroup2);
        }
    }

    /**
     * Interleaves data with EC bytes.
     *
     * @param  BitArray $bits
     * @param  integer  $numTotalBytes
     * @param  integer  $numDataBytes
     * @param  integer  $numRsBlocks
     * @return BitArray
     * @throws Exception\WriterException
     */
    protected static function interleaveWithEcBytes(BitArray $bits, $numTotalBytes, $numDataBytes, $numRsBlocks)
    {
        if ($bits->getSizeInBytes() !== $numDataBytes) {
            throw new Exception\WriterException('Number of bits and data bytes does not match');
        }

        $dataBytesOffset = 0;
        $maxNumDataBytes = 0;
        $maxNumEcBytes   = 0;

        $blocks = new SplFixedArray($numRsBlocks);

        for ($i = 0; $i < $numRsBlocks; $i++) {
            list($numDataBytesInBlock, $numEcBytesInBlock) = self::getNumDataBytesAndNumEcBytesForBlockId(
                $numTotalBytes,
                $numDataBytes,
                $numRsBlocks,
                $i
            );

            $size       = $numDataBytesInBlock;
            $dataBytes  = $bits->toBytes(8 * $dataBytesOffset, $size);
            $ecBytes    = self::generateEcBytes($dataBytes, $numEcBytesInBlock);
            $blocks[$i] = new BlockPair($dataBytes, $ecBytes);

            $maxNumDataBytes  = max($maxNumDataBytes, $size);
            $maxNumEcBytes    = max($maxNumEcBytes, count($ecBytes));
            $dataBytesOffset += $numDataBytesInBlock;
        }

        if ($numDataBytes !== $dataBytesOffset) {
            throw new Exception\WriterException('Data bytes does not match offset');
        }

        $result = new BitArray();

        for ($i = 0; $i < $maxNumDataBytes; $i++) {
            foreach ($blocks as $block) {
                $dataBytes = $block->getDataBytes();

                if ($i < count($dataBytes)) {
                    $result->appendBits($dataBytes[$i], 8);
                }
            }
        }

        for ($i = 0; $i < $maxNumEcBytes; $i++) {
            foreach ($blocks as $block) {
                $ecBytes = $block->getErrorCorrectionBytes();

                if ($i < count($ecBytes)) {
                    $result->appendBits($ecBytes[$i], 8);
                }
            }
        }

        if ($numTotalBytes !== $result->getSizeInBytes()) {
            throw new Exception\WriterException('Interleaving error: ' . $numTotalBytes . ' and ' . $result->getSizeInBytes() . ' differ');
        }

        return $result;
    }

    /**
     * Generates EC bytes for given data.
     *
     * @param  SplFixedArray $dataBytes
     * @param  integer       $numEcBytesInBlock
     * @return SplFixedArray
     */
    protected static function generateEcBytes(SplFixedArray $dataBytes, $numEcBytesInBlock)
    {
        $numDataBytes = count($dataBytes);
        $toEncode     = new SplFixedArray($numDataBytes + $numEcBytesInBlock);

        for ($i = 0; $i < $numDataBytes; $i++) {
            $toEncode[$i] = $dataBytes[$i] & 0xff;
        }

        $ecBytes = new SplFixedArray($numEcBytesInBlock);
        $codec   = self::getCodec($numDataBytes, $numEcBytesInBlock);
        $codec->encode($toEncode, $ecBytes);

        return $ecBytes;
    }

    /**
     * Gets an RS codec and caches it.
     *
     * @param  integer $numDataBytes
     * @param  integer $numEcBytesInBlock
     * @return ReedSolomonCodec
     */
    protected static function getCodec($numDataBytes, $numEcBytesInBlock)
    {
        $cacheId = $numDataBytes . '-' . $numEcBytesInBlock;

        if (!isset(self::$codecs[$cacheId])) {
            self::$codecs[$cacheId] = new ReedSolomonCodec(
                8,
                0x11d,
                0,
                1,
                $numEcBytesInBlock,
                255 - $numDataBytes - $numEcBytesInBlock
            );
        }

        return self::$codecs[$cacheId];
    }

    /**
     * Appends mode information to a bit array.
     *
     * @param  Mode     $mode
     * @param  BitArray $bits
     * @return void
     */
    protected static function appendModeInfo(Mode $mode, BitArray $bits)
    {
        $bits->appendBits($mode->get(), 4);
    }

    /**
     * Appends length information to a bit array.
     *
     * @param  integer  $numLetters
     * @param  Version  $version
     * @param  Mode     $mode
     * @param  BitArray $bits
     * @return void
     * @throws Exception\WriterException
     */
    protected static function appendLengthInfo($numLetters, Version $version, Mode $mode, BitArray $bits)
    {
        $numBits = $mode->getCharacterCountBits($version);

        if ($numLetters >= (1 << $numBits)) {
            throw new Exception\WriterException($numLetters . ' is bigger than ' . ((1 << $numBits) - 1));
        }

        $bits->appendBits($numLetters, $numBits);
    }

    /**
     * Appends bytes to a bit array in a specific mode.
     *
     * @param  stirng   $content
     * @param  Mode     $mode
     * @param  BitArray $bits
     * @param  string   $encoding
     * @return void
     * @throws Exception\WriterException
     */
    protected static function appendBytes($content, Mode $mode, BitArray $bits, $encoding)
    {
        switch ($mode->get()) {
            case Mode::NUMERIC:
                self::appendNumericBytes($content, $bits);
                break;

            case Mode::ALPHANUMERIC:
                self::appendAlphanumericBytes($content, $bits);
                break;

            case Mode::BYTE:
                self::append8BitBytes($content, $bits, $encoding);
                break;

            case Mode::KANJI:
                self::appendKanjiBytes($content, $bits);
                break;

            default:
                throw new Exception\WriterException('Invalid mode: ' . $mode->get());
        }
    }

    /**
     * Appends numeric bytes to a bit array.
     *
     * @param  string   $content
     * @param  BitArray $bits
     * @return void
     */
    protected static function appendNumericBytes($content, BitArray $bits)
    {
        $length = strlen($content);
        $i      = 0;

        while ($i < $length) {
            $num1 = (int) $content[$i];

            if ($i + 2 < $length) {
                // Encode three numeric letters in ten bits.
                $num2 = (int) $content[$i + 1];
                $num3 = (int) $content[$i + 2];
                $bits->appendBits($num1 * 100 + $num2 * 10 + $num3, 10);
                $i += 3;
            } elseif ($i + 1 < $length) {
                // Encode two numeric letters in seven bits.
                $num2 = (int) $content[$i + 1];
                $bits->appendBits($num1 * 10 + $num2, 7);
                $i += 2;
            } else {
                // Encode one numeric letter in four bits.
                $bits->appendBits($num1, 4);
                $i++;
            }
        }
    }

    /**
     * Appends alpha-numeric bytes to a bit array.
     *
     * @param  string   $content
     * @param  BitArray $bits
     * @return void
     */
    protected static function appendAlphanumericBytes($content, BitArray $bits)
    {
        $length = strlen($content);
        $i      = 0;

        while ($i < $length) {
            if (-1 === ($code1 = self::getAlphanumericCode($content[$i]))) {
                throw new Exception\WriterException('Invalid alphanumeric code');
            }

            if ($i + 1 < $length) {
                if (-1 === ($code2 = self::getAlphanumericCode($content[$i + 1]))) {
                    throw new Exception\WriterException('Invalid alphanumeric code');
                }

                // Encode two alphanumeric letters in 11 bits.
                $bits->appendBits($code1 * 45 + $code2, 11);
                $i += 2;
            } else {
                // Encode one alphanumeric letter in six bits.
                $bits->appendBits($code1, 6);
                $i++;
            }
        }
    }

    /**
     * Appends regular 8-bit bytes to a bit array.
     *
     * @param  string   $content
     * @param  BitArray $bits
     * @return void
     */
    protected static function append8BitBytes($content, BitArray $bits, $encoding)
    {
        if (false === ($bytes = @iconv('utf-8', $encoding, $content))) {
            throw new Exception\WriterException('Could not encode content to ' . $encoding);
        }

        $length = strlen($bytes);

        for ($i = 0; $i < $length; $i++) {
            $bits->appendBits(ord($bytes[$i]), 8);
        }
    }

    /**
     * Appends KANJI bytes to a bit array.
     *
     * @param  string   $content
     * @param  BitArray $bits
     * @return void
     */
    protected static function appendKanjiBytes($content, BitArray $bits)
    {
        if (strlen($content) % 2 > 0) {
            // We just do a simple length check here. The for loop will check
            // individual characters.
            throw new Exception\WriterException('Content does not seem to be encoded in SHIFT-JIS');
        }

        $length = strlen($content);

        for ($i = 0; $i < $length; $i += 2) {
            $byte1 = ord($content[$i]) & 0xff;
            $byte2 = ord($content[$i + 1]) & 0xff;
            $code  = ($byte1 << 8) | $byte2;

            if ($code >= 0x8140 && $code <= 0x9ffc) {
                $subtracted = $code - 0x8140;
            } elseif ($code >= 0xe040 && $code <= 0xebbf) {
                $subtracted = $code - 0xc140;
            } else {
                throw new Exception\WriterException('Invalid byte sequence');
            }

            $encoded = (($subtracted >> 8) * 0xc0) + ($subtracted & 0xff);

            $bits->appendBits($encoded, 13);
        }
    }

    /**
     * Appends ECI information to a bit array.
     *
     * @param  CharacterSetEci $eci
     * @param  BitArray        $bits
     * @return void
     */
    protected static function appendEci(CharacterSetEci $eci, BitArray $bits)
    {
        $mode = new Mode(Mode::ECI);
        $bits->appendBits($mode->get(), 4);
        $bits->appendBits($eci->get(), 8);
    }
}
