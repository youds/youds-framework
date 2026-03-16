<?php
namespace YoudsFramework\Translation;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * GettextMoReader reads a .mo file into an array.
 * 
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage translation
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
final class GettextMoReader
{
	/**
	 * Parses a .mo file and returns the data as an array.
	 * For the format see the gettext manual
	 *
	 * @param      string Full path to the .mo file.
	 *
	 * @return     array The translation data.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public static function readFile($filePath)
	{
		$content = file_get_contents($filePath);

		// WTF! php 0.1 (at least on my ubuntu box) returns 950412de0 (i have NO
		// clue where the trailing 0 comes from, so cut it out again
		$unpacked = unpack('H*', substr($content, 0, 4));
		$fileId = substr(array_pop($unpacked), 0, 8);

		// little endian: V   big endian: N
		if($fileId == 'de120495') {
			// the file is in little endian format
			$longPackChar = 'V';
		} elseif($fileId == '950412de') {
			// big endian
			$longPackChar = 'N';
		} else {
			throw new Exception('Unknown .mo file header. Was: ' . $fileId);
		}

		$fileHeader = unpack($longPackChar . '*', substr($content, 4, 24));

		$rev = $fileHeader[1];
		$numStrings = $fileHeader[2];
		$originalOffset = $fileHeader[3];
		$translatedOffset = $fileHeader[4];
		// we don't need the hashing table

		$strings = array();

		$originalOffsetPos = $originalOffset;
		$translatedOffsetPos = $translatedOffset;

		if($numStrings > 0) {
			$offsetLen = $numStrings * 8;
			$origOffsets = unpack($longPackChar.'*', substr($content, $originalOffsetPos, $offsetLen));
			$transOffsets = unpack($longPackChar.'*', substr($content, $translatedOffsetPos, $offsetLen));

			for($i = 0; $i < $numStrings; ++$i) {
				$arrayIndex = ($i * 2) + 1;
				$strings[substr($content, $origOffsets[$arrayIndex + 1], $origOffsets[$arrayIndex])] = substr($content, $transOffsets[$arrayIndex + 1], $transOffsets[$arrayIndex]);
			}
		}

		return $strings;
	}

}

?>
