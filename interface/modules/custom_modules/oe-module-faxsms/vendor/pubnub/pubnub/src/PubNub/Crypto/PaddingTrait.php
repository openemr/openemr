<?php

namespace PubNub\Crypto;

trait PaddingTrait
{
    /**
     * Pad $text to multiple of $blockSize lenght using PKCS5Padding schema
     *
     * @param string $text
     * @param int $blockSize
     * @return string
     */
    public function pad(string $text, int $blockSize)
    {
        $pad = $blockSize - (strlen($text) % $blockSize);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * Remove padding from $text using PKCS5Padding schema
     *
     * @param string $text
     * @param int $blockSize
     * @return string
     */
    public function depad($data, $blockSize)
    {
        $length = strlen($data);
        if ($length == 0) {
            return $data;
        }

        $padLength = substr($data, -1);

        if (ord($padLength) <= $blockSize) {
            for ($i = $length - 2; $i > 0; $i--) {
                if (ord($data [$i] != $padLength)) {
                    break;
                }
            }
            return substr($data, 0, $i + 1);
        }
        return $data;
    }
}
