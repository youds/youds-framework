<?php
namespace YoudsFramework\Generator;
use YoudsFramework\Config;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Generator provides access to generator facilities.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage generator
 *
 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class UnicodeEmoji extends Generator
{
	
	/**
	 * Get Unicode Emojis
	 *
	 * @return array Array of Unicode Emojis
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	function fetch ()
	{
		$lines = file(Config::get('core.src_dir') . '/generator/unicode/emoji-list.txt'); 
		
		foreach ($lines as $line):
			
			/* First, Define Context */
			if (strstr($line, '# group:'))
				$group = trim(substr($line, 9));
			if (strstr($line, '# subgroup:'))
				$subgroup = ucwords(str_replace('-', ' ', trim(substr($line, 12))));
			
			if (strstr($line, ';')):
				preg_match('/^(1F\d\w\w)\s+\;[\w\d\s\-]+\#[.\s\W]+\sE\d{1,}\.\d{1,}\s([\w\d\s]+)/', $line, $matches);
				if (isset($matches[1]) && isset($matches[2]))
					$arr[$group][$subgroup][ucwords(str_replace('-', ' ', trim($matches[2])))] = trim($matches[1]);
			endif;	
			
		endforeach;
		
		return $arr;
	}
}

?>