<?php
namespace YoudsFramework\Validator;
use YoudsFramework\Validator;
use YoudsFramework\Util\VirtualArrayPath;



// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * DependencyManager handles the dependencies in the validation process
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage validator
 *
 * @author     Uwe Mesecke <uwe@mesecke.net>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class DependencyManager
{
	/**
	 * @var array already provided tokens.
	 */
	protected $depData = array();
	
	/**
	 * Clears the dependency cache.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function clear()
	{
		$this->depData = array();
	}
	
	/**
	 * Checks whether a list of dependencies is met.
	 * 
	 * @param      array  The list of dependencies that have to meet.
	 * @param      VirtualArrayPath The base path to which all tokens are 
	 *                                   appended.
	 * 
	 * @return     bool all dependencies are met
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function checkDependencies(array $tokens, VirtualArrayPath $base)
	{
		$currentParts = $base->getParts();
		foreach($tokens as $token) {
			if($currentParts && strpos($token, '%') !== false) { 
				// the depends attribute contains sprintf syntax 
				$token = vsprintf($token, $currentParts); 
			}
			
			$path = new VirtualArrayPath($token);
			if(!$path->getValue($this->depData)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Puts a list of tokens into the dependency cache.
	 * 
	 * @param      array  The list of new tokens.
	 * @param      VirtualArrayPath The base path to which all tokens are 
	 *                                   appended.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function addDependTokens(array $tokens, VirtualArrayPath $base)
	{
		$currentParts = $base->getParts();
		foreach($tokens as $token) {
			if($currentParts && strpos($token, '%') !== false) { 
				// the depends attribute contains sprintf syntax 
				$token = vsprintf($token, $currentParts); 
			}
			
			$path = new VirtualArrayPath($token);
			$path->setValue($this->depData, true);
		}
	}
	
	/**
	 * Populate key references in an argument base string if necessary.
	 * Fills only empty bracket positions with an sprintf() offset placeholder.
	 * Example: foo[][bar][] as input will return foo[%2$s][bar][%4$s] as output.
	 * This is used in validate.xsl to convert pre-1.1 provides/depends behavior.
	 *
	 * @param      string The argument base string.
	 *
	 * @return     string The argument base string with empty brackets filled with
	 *                    correct sprintf() position specifiers.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public static function populateArgumentBaseKeyRefs($string)
	{
		$index = 1;
		return preg_replace_callback(
			'#\[([^\]]*)\]#',
			function($matches) use(&$index) {
				$index++; // always increment so static key parts are "skipped" properly
				return $matches[1] !== '' ? $matches[0] : '[%'.$index.'$s]'; // leave parts other than "[]" intact, else inject numeric accessor
			},
			$string
		);
	}
	
	/*
	 * Returns the list of provided tokens from the dependency cache.
	 *
	 * @return     array Provided tokens from the dependency cache.
	 *
	 * @author     Steffen Gransow <agavi@mivesto.de>
	 */
	public function getDependTokens()
	{
		return $this->depData;
	}
}

?>
