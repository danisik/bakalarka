<?php

namespace Mpdf\Utils;

class UtfString
{

	/**
	 * Converts all the &#nnn; and &#xhhh; in a string to Unicode
	 *
	 * @since mPDF 5.7
	 * @param string $str
	 * @param bool $lo
	 *
	 * @return string
	 */
	public static function strcode2utf($str, $lo = true)
	{
		$str = preg_replace_callback('/\&\#(\d+)\;/m', function ($matches) use ($lo) {
			return static::code2utf($matches[1], $lo ? 1 : 0);
		}, $str);
		$str = preg_replace_callback('/\&\#x([0-9a-fA-F]+)\;/m', function ($matches) use ($lo) {
			return static::codeHex2utf($matches[1], $lo ? 1 : 0);
		}, $str);

		return $str;
	}

	/**
	 * @param int $num
	 * @param bool $lo
	 *
	 * @return string
	 */
	public static function code2utf($num, $lo = true)
	{
        $chr = '';
		// Returns the utf string corresponding to the unicode value
		if ($num < 128) {
			if ($lo) {
				$chr = chr($num);
                return $chr;
			}
            $chr = '&#' . $num . ';'; 
			return $chr;
		}
		if ($num < 2048) {
			$chr = chr(($num >> 6) + 192) . chr(($num & 63) + 128); 
            return $chr;
		}
		if ($num < 65536) {
			$chr = chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128); 
            return $chr;
		}
		if ($num < 2097152) {		
			$chr = chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128); 
            return $chr;            
		}

        $chr = '?';
        
		return $chr;
	}

	public static function codeHex2utf($hex, $lo = true)
	{
		$num = hexdec($hex);
		if (($num < 128) && !$lo) {
			return '&#x' . $hex . ';';
		}

		return static::code2utf($num, $lo);
	}

}
