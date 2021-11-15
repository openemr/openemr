<?php

namespace Mpdf\Pdf;

use Mpdf\Pdf\Protection\UniqidGenerator;

class Protection
{

	/**
	 * @var string
	 */
	private $lastRc4Key;

	/**
	 * @var string
	 */
	private $lastRc4KeyC;

	/**
	 * @var bool
	 */
	private $useRC128Encryption;

	/**
	 * @var string
	 */
	private $encryptionKey;

	/**
	 * @var string
	 */
	private $padding;

	/**
	 * @var string
	 */
	private $uniqid;

	/**
	 * @var string
	 */
	private $oValue;

	/**
	 * @var string
	 */
	private $uValue;

	/**
	 * @var string
	 */
	private $pValue;

	/**
	 * @var int[] Array of permission => byte representation
	 */
	private $options;

	/**
	 * @var \Mpdf\Pdf\Protection\UniqidGenerator
	 */
	private $uniqidGenerator;

	public function __construct(UniqidGenerator $uniqidGenerator)
	{
		if (!function_exists('random_int') || !function_exists('random_bytes')) {
			throw new \Mpdf\MpdfException(
				'Unable to set PDF file protection, CSPRNG Functions are not available. '
				. 'Use paragonie/random_compat polyfill or upgrade to PHP 7.'
			);
		}

		$this->uniqidGenerator = $uniqidGenerator;

		$this->lastRc4Key = '';

		$this->padding = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08" .
			"\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";

		$this->useRC128Encryption = false;

		$this->options = [
			'print' => 4, // bit 3
			'modify' => 8, // bit 4
			'copy' => 16, // bit 5
			'annot-forms' => 32, // bit 6
			'fill-forms' => 256, // bit 9
			'extract' => 512, // bit 10
			'assemble' => 1024, // bit 11
			'print-highres' => 2048 // bit 12
		];
	}

	/**
	 * @param array $permissions
	 * @param string $user_pass
	 * @param string $owner_pass
	 * @param int $length
	 *
	 * @return bool
	 */
	public function setProtection($permissions = [], $user_pass = '', $owner_pass = null, $length = 40)
	{
		if (is_string($permissions) && strlen($permissions) > 0) {
			$permissions = [$permissions];
		} elseif (!is_array($permissions)) {
			return false;
		}

		$protection = $this->getProtectionBitsFromOptions($permissions);

		if ($length === 128) {
			$this->useRC128Encryption = true;
		} elseif ($length !== 40) {
			throw new \Mpdf\MpdfException('PDF protection only allows lenghts of 40 or 128');
		}

		if ($owner_pass === null) {
			$owner_pass = bin2hex(random_bytes(23));
		}

		$this->generateEncryptionKey($user_pass, $owner_pass, $protection);

		return true;
	}

	/**
	 * Compute key depending on object number where the encrypted data is stored
	 *
	 * @param int $n
	 *
	 * @return string
	 */
	public function objectKey($n)
	{
		if ($this->useRC128Encryption) {
			$len = 16;
		} else {
			$len = 10;
		}

		return substr($this->md5toBinary($this->encryptionKey . pack('VXxx', $n)), 0, $len);
	}

	/**
	 * RC4 is the standard encryption algorithm used in PDF format
	 *
	 * @param string $key
	 * @param string $text
	 *
	 * @return string
	 */
	public function rc4($key, $text)
	{
		if ($this->lastRc4Key != $key) {
			$k = str_repeat($key, 256 / strlen($key) + 1);
			$rc4 = range(0, 255);
			$j = 0;
			for ($i = 0; $i < 256; $i++) {
				$t = $rc4[$i];
				$j = ($j + $t + ord($k[$i])) % 256;
				$rc4[$i] = $rc4[$j];
				$rc4[$j] = $t;
			}
			$this->lastRc4Key = $key;
			$this->lastRc4KeyC = $rc4;
		} else {
			$rc4 = $this->lastRc4KeyC;
		}

		$len = strlen($text);
		$a = 0;
		$b = 0;
		$out = '';
		for ($i = 0; $i < $len; $i++) {
			$a = ($a + 1) % 256;
			$t = $rc4[$a];
			$b = ($b + $t) % 256;
			$rc4[$a] = $rc4[$b];
			$rc4[$b] = $t;
			$k = $rc4[($rc4[$a] + $rc4[$b]) % 256];
			$out .= chr(ord($text[$i]) ^ $k);
		}

		return $out;
	}

	/**
	 * @return mixed
	 */
	public function getUseRC128Encryption()
	{
		return $this->useRC128Encryption;
	}

	/**
	 * @return mixed
	 */
	public function getUniqid()
	{
		return $this->uniqid;
	}

	/**
	 * @return mixed
	 */
	public function getOValue()
	{
		return $this->oValue;
	}

	/**
	 * @return mixed
	 */
	public function getUValue()
	{
		return $this->uValue;
	}

	/**
	 * @return mixed
	 */
	public function getPValue()
	{
		return $this->pValue;
	}

	private function getProtectionBitsFromOptions($permissions)
	{
		// bit 31 = 1073741824
		// bit 32 = 2147483648
		// bits 13-31 = 2147479552
		// bits 13-32 = 4294963200 + 192 = 4294963392

		$protection = 4294963392; // bits 7, 8, 13-32

		foreach ($permissions as $permission) {
			if (!isset($this->options[$permission])) {
				throw new \Mpdf\MpdfException(sprintf('Invalid permission type "%s"', $permission));
			}
			if ($this->options[$permission] > 32) {
				$this->useRC128Encryption = true;
			}
			if (isset($this->options[$permission])) {
				$protection += $this->options[$permission];
			}
		}

		return $protection;
	}

	private function oValue($user_pass, $owner_pass)
	{
		$tmp = $this->md5toBinary($owner_pass);
		if ($this->useRC128Encryption) {
			for ($i = 0; $i < 50; ++$i) {
				$tmp = $this->md5toBinary($tmp);
			}
		}
		if ($this->useRC128Encryption) {
			$keybytelen = (128 / 8);
		} else {
			$keybytelen = (40 / 8);
		}
		$owner_rc4_key = substr($tmp, 0, $keybytelen);
		$enc = $this->rc4($owner_rc4_key, $user_pass);
		if ($this->useRC128Encryption) {
			$len = strlen($owner_rc4_key);
			for ($i = 1; $i <= 19; ++$i) {
				$key = '';
				for ($j = 0; $j < $len; ++$j) {
					$key .= chr(ord($owner_rc4_key[$j]) ^ $i);
				}
				$enc = $this->rc4($key, $enc);
			}
		}

		return $enc;
	}

	private function uValue()
	{
		if ($this->useRC128Encryption) {
			$tmp = $this->md5toBinary($this->padding . $this->hexToString($this->uniqid));
			$enc = $this->rc4($this->encryptionKey, $tmp);
			$len = strlen($tmp);
			for ($i = 1; $i <= 19; ++$i) {
				$key = '';
				for ($j = 0; $j < $len; ++$j) {
					$key .= chr(ord($this->encryptionKey[$j]) ^ $i);
				}
				$enc = $this->rc4($key, $enc);
			}
			$enc .= str_repeat("\x00", 16);

			return substr($enc, 0, 32);
		} else {
			return $this->rc4($this->encryptionKey, $this->padding);
		}
	}

	private function generateEncryptionKey($user_pass, $owner_pass, $protection)
	{
		// Pad passwords
		$user_pass = substr($user_pass . $this->padding, 0, 32);
		$owner_pass = substr($owner_pass . $this->padding, 0, 32);

		$this->oValue = $this->oValue($user_pass, $owner_pass);

		$this->uniqid = $this->uniqidGenerator->generate();

		// Compute encyption key
		if ($this->useRC128Encryption) {
			$keybytelen = (128 / 8);
		} else {
			$keybytelen = (40 / 8);
		}

		$prot = sprintf('%032b', $protection);

		$perms = chr(bindec(substr($prot, 24, 8)));
		$perms .= chr(bindec(substr($prot, 16, 8)));
		$perms .= chr(bindec(substr($prot, 8, 8)));
		$perms .= chr(bindec(substr($prot, 0, 8)));

		$tmp = $this->md5toBinary($user_pass . $this->oValue . $perms . $this->hexToString($this->uniqid));

		if ($this->useRC128Encryption) {
			for ($i = 0; $i < 50; ++$i) {
				$tmp = $this->md5toBinary(substr($tmp, 0, $keybytelen));
			}
		}

		$this->encryptionKey = substr($tmp, 0, $keybytelen);

		$this->uValue = $this->uValue();
		$this->pValue = $protection;
	}

	private function md5toBinary($string)
	{
		return pack('H*', md5($string));
	}

	private function hexToString($hs)
	{
		$s = '';
		$len = strlen($hs);
		if (($len % 2) != 0) {
			$hs .= '0';
			++$len;
		}
		for ($i = 0; $i < $len; $i += 2) {
			$s .= chr(hexdec($hs[$i] . $hs[($i + 1)]));
		}

		return $s;
	}
}
