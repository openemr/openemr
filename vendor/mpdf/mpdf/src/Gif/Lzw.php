<?php

namespace Mpdf\Gif;

/**
 * GIF Util - (C) 2003 Yamasoft (S/C)
 *
 * All Rights Reserved
 *
 * This file can be freely copied, distributed, modified, updated by anyone under the only
 * condition to leave the original address (Yamasoft, http://www.yamasoft.com) and this header.
 *
 * @link http://www.yamasoft.com
 */
class Lzw
{

	var $MAX_LZW_BITS;

	var $Fresh;
	var $CodeSize;
	var $SetCodeSize;
	var $MaxCode;
	var $MaxCodeSize;
	var $FirstCode;
	var $OldCode;

	var $ClearCode;
	var $EndCode;
	var $Next;
	var $Vals;
	var $Stack;
	var $sp;
	var $Buf;
	var $CurBit;
	var $LastBit;
	var $Done;
	var $LastByte;

	public function __construct()
	{
		$this->MAX_LZW_BITS = 12;

		unset($this->Next);
		unset($this->Vals);
		unset($this->Stack);
		unset($this->Buf);

		$this->Next = range(0, (1 << $this->MAX_LZW_BITS) - 1);
		$this->Vals = range(0, (1 << $this->MAX_LZW_BITS) - 1);
		$this->Stack = range(0, (1 << ($this->MAX_LZW_BITS + 1)) - 1);
		$this->Buf = range(0, 279);
	}

	function deCompress($data, &$datLen)
	{
		$stLen = strlen($data);
		$datLen = 0;
		$ret = "";
		$dp = 0;  // data pointer
		// INITIALIZATION
		$this->LZWCommandInit($data, $dp);

		while (($iIndex = $this->LZWCommand($data, $dp)) >= 0) {
			$ret .= chr($iIndex);
		}

		$datLen = $dp;

		if ($iIndex != -2) {
			return false;
		}

		return $ret;
	}

	function LZWCommandInit(&$data, &$dp)
	{
		$this->SetCodeSize = ord($data[0]);
		$dp += 1;

		$this->CodeSize = $this->SetCodeSize + 1;
		$this->ClearCode = 1 << $this->SetCodeSize;
		$this->EndCode = $this->ClearCode + 1;
		$this->MaxCode = $this->ClearCode + 2;
		$this->MaxCodeSize = $this->ClearCode << 1;

		$this->GetCodeInit($data, $dp);

		$this->Fresh = 1;
		for ($i = 0; $i < $this->ClearCode; $i++) {
			$this->Next[$i] = 0;
			$this->Vals[$i] = $i;
		}

		for (; $i < (1 << $this->MAX_LZW_BITS); $i++) {
			$this->Next[$i] = 0;
			$this->Vals[$i] = 0;
		}

		$this->sp = 0;
		return 1;
	}

	function LZWCommand(&$data, &$dp)
	{
		if ($this->Fresh) {
			$this->Fresh = 0;
			do {
				$this->FirstCode = $this->GetCode($data, $dp);
				$this->OldCode = $this->FirstCode;
			} while ($this->FirstCode == $this->ClearCode);

			return $this->FirstCode;
		}

		if ($this->sp > 0) {
			$this->sp--;
			return $this->Stack[$this->sp];
		}

		while (($Code = $this->GetCode($data, $dp)) >= 0) {
			if ($Code == $this->ClearCode) {
				for ($i = 0; $i < $this->ClearCode; $i++) {
					$this->Next[$i] = 0;
					$this->Vals[$i] = $i;
				}

				for (; $i < (1 << $this->MAX_LZW_BITS); $i++) {
					$this->Next[$i] = 0;
					$this->Vals[$i] = 0;
				}

				$this->CodeSize = $this->SetCodeSize + 1;
				$this->MaxCodeSize = $this->ClearCode << 1;
				$this->MaxCode = $this->ClearCode + 2;
				$this->sp = 0;
				$this->FirstCode = $this->GetCode($data, $dp);
				$this->OldCode = $this->FirstCode;

				return $this->FirstCode;
			}

			if ($Code == $this->EndCode) {
				return -2;
			}

			$InCode = $Code;
			if ($Code >= $this->MaxCode) {
				$this->Stack[$this->sp++] = $this->FirstCode;
				$Code = $this->OldCode;
			}

			while ($Code >= $this->ClearCode) {
				$this->Stack[$this->sp++] = $this->Vals[$Code];

				if ($Code == $this->Next[$Code]) { // Circular table entry, big GIF Error!
					return -1;
				}

				$Code = $this->Next[$Code];
			}

			$this->FirstCode = $this->Vals[$Code];
			$this->Stack[$this->sp++] = $this->FirstCode;

			if (($Code = $this->MaxCode) < (1 << $this->MAX_LZW_BITS)) {
				$this->Next[$Code] = $this->OldCode;
				$this->Vals[$Code] = $this->FirstCode;
				$this->MaxCode++;

				if (($this->MaxCode >= $this->MaxCodeSize) && ($this->MaxCodeSize < (1 << $this->MAX_LZW_BITS))) {
					$this->MaxCodeSize *= 2;
					$this->CodeSize++;
				}
			}

			$this->OldCode = $InCode;
			if ($this->sp > 0) {
				$this->sp--;
				return $this->Stack[$this->sp];
			}
		}

		return $Code;
	}

	function GetCodeInit(&$data, &$dp)
	{
		$this->CurBit = 0;
		$this->LastBit = 0;
		$this->Done = 0;
		$this->LastByte = 2;
		return 1;
	}

	function GetCode(&$data, &$dp)
	{
		if (($this->CurBit + $this->CodeSize) >= $this->LastBit) {
			if ($this->Done) {
				if ($this->CurBit >= $this->LastBit) {
					// Ran off the end of my bits
					return 0;
				}
				return -1;
			}

			$this->Buf[0] = $this->Buf[$this->LastByte - 2];
			$this->Buf[1] = $this->Buf[$this->LastByte - 1];

			$Count = ord($data[$dp]);
			$dp += 1;

			if ($Count) {
				for ($i = 0; $i < $Count; $i++) {
					$this->Buf[2 + $i] = ord($data[$dp + $i]);
				}
				$dp += $Count;
			} else {
				$this->Done = 1;
			}

			$this->LastByte = 2 + $Count;
			$this->CurBit = ($this->CurBit - $this->LastBit) + 16;
			$this->LastBit = (2 + $Count) << 3;
		}

		$iRet = 0;
		for ($i = $this->CurBit, $j = 0; $j < $this->CodeSize; $i++, $j++) {
			$iRet |= (($this->Buf[intval($i / 8)] & (1 << ($i % 8))) != 0) << $j;
		}

		$this->CurBit += $this->CodeSize;
		return $iRet;
	}
}
