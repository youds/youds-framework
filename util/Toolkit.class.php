<?php
namespace YoudsFramework\Util;
use YoudsFramework\Config;
use YoudsFramework\Exceptions\Exception;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Toolkit provides basic utility methods.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage util
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
final class Toolkit
{
	/**
	 * Determine if a filesystem path is absolute.
	 *
	 * @param      path A filesystem path.
	 *
	 * @return     bool true, if the path is absolute, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public static function isPathAbsolute($path)
	{
		if(strpos($path, "file://") === 0) {
			$path = substr($path, 7);
		}
		
		if($path[0] == '/' || substr($path, 0, 2) == '\\\\' ||
			(
				strlen($path) >= 3 && ctype_alpha($path[0]) &&
				$path[1] == ':' &&
				($path[2] == '\\' || $path[2] == '/')
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Normalizes a path to contain only '/' as path delimiter.
	 *
	 * @param      string The path to normalize.
	 *
	 * @return     string The unified bool The mkdir return value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public static function normalizePath($path)
	{
		return str_replace('\\', '/', $path);
	}

	/**
	 * Creates a directory without sucking at permissions.
	 * PHP mkdir() doesn't do what you tell it to, it takes umask into account.
	 *
	 * @param      string   The path name.
	 * @param      int      The mode. Really. Defaults to 0775.
	 * @param      bool     Recursive or not.
	 * @param      resource A Context.
	 *
	 * @return     bool The mkdir return value.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 */
	public static function mkdir($path, $mode = 0775, $recursive = false, $context = null)
	{
		$path = rtrim($path, '/\\');

		$retVal = is_dir($path) && is_writeable($path);
		if(!$retVal) {
			if($context !== null) {
				$retVal = @mkdir($path, $mode, $recursive, $context);
			} else {
                if (!file_exists($path) && !is_dir($path))
                    $retVal = @mkdir($path, $mode, $recursive);
            }
            if ($retVal && is_dir($path)) {
                @chmod($path, $mode);
            }
		}
		return $retVal;
	}

	/**
	 * Returns the base for two strings (the part at the beginning of both which
	 * is equal)
	 *
	 * @param      string The base string.
	 * @param      string The string which should be compared to the base string.
	 * @param      int    The number of characters which are equal.
	 *
	 * @return     string The equal part at the beginning of both strings.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public static function stringBase($baseString, $compString, &$equalAmount = 0)
	{
		$equalAmount = 0;
		$base = '';
		$maxEqualAmount = min(strlen($baseString), strlen($compString));
		for($i = 0; ($i < $maxEqualAmount) && $baseString[$i] == $compString[$i]; ++$i) {
			$base .= $baseString[$i];
			$equalAmount = $i + 1;
		}
		return $base;
	}

	/**
	 * Deletes a specified path in the cache dir recursively. If a folder is given
	 * the contents of this folder and all sub-folders get erased, but not the
	 * folder itself.
	 *
	 * @param      string The path to remove
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function clearCache($path = '')
	{
		if(!Config::get('core.cache_dir')) {
			throw new Exception('Holy disk wipe, Batman! It seems that the value of "core.cache_dir" is empty, and because Youds Framework considers you its most dearest of friends, it chose not to erase your entire file system. Skynet or other evil machines may not be so forgiving however, so please fix whatever code you wrote that caused this :)');
		}
		
		$ignores = array('.', '..', '.svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.gitignore', '.gitkeep');
		$path = str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', DIRECTORY_SEPARATOR, $path));
		$path = realpath(Config::get('core.cache_dir') . DIRECTORY_SEPARATOR . $path);
		if($path === false) {
			return false;
		}
		if(is_file($path)) {
			@unlink($path);
		} else {
			try {
				foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::CHILD_FIRST) as $iterator) {
					// omg, thanks spl for always using forward slashes ... even on windows
					$pathname = str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', DIRECTORY_SEPARATOR, $iterator->getPathname()));
					$continue = false;
					if(in_array($iterator->getFilename(), $ignores)) {
						$continue = true;
					} else {
						foreach($ignores as $ignore) {
							if(strpos($pathname, DIRECTORY_SEPARATOR . $ignore . DIRECTORY_SEPARATOR) !== false) {
								$continue = true;
								break;
							} elseif(strrpos($pathname, DIRECTORY_SEPARATOR . $ignore) == (strlen($pathname) - strlen(DIRECTORY_SEPARATOR . $ignore))) {
								// if we hit the directory itself it wont include a trailing /
								$continue = true;
								break;
							}
						}
					}
					if($continue) {
						continue;
					}
					if($iterator->isDir() && is_dir($pathname) && is_writable($pathname)) {
						@self::deletePath($pathname);
					} elseif($iterator->isFile() && is_file($pathname)) {
						@unlink($pathname);
					}
				}
			} catch(Exception $e) {
				// ignore all exceptions in case the path didn't exist anymore
			}
		}
	}

	/**
	 * Remove all files and directories recursively from $path
	 *
	 * @param string  The path to delete
	 *
	 * @return     boolean True if successful
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public static function deletePath ($path) {

		// Check if the provided path is valid
		if (!is_dir($path)) {
			return false;
		}

		// Iterate through the directory contents
		$files = scandir($path);
		$items = array_diff(is_array($files)?$files:[], array('.', '..'));
		foreach ($items as $item) {
			$itemPath = $path . DIRECTORY_SEPARATOR . $item;

			if (is_dir($itemPath)) {
				self::deletePath($itemPath);
			} else {
				@unlink($itemPath);
			}
		}

		// Remove the directory itself after its contents are deleted
		$files = scandir($path);
		if (count(array_diff(is_array($files)?$files:[], array('.', '..'))) === 0)
            if (@is_dir($path))
			    return @rmdir($path);
		else
			return false;
	}

	/**
	 * Returns the method from the given definition list matching the given
	 * parameters.
	 *
	 * @param      array  The definitions of the functions.
	 * @param      array  The parameters which were passed to the function.
	 *
	 * @return     string The name of the function which matched.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public static function overloadHelper(array $definitions, array $parameters)
	{
		$countedDefs = array();
		foreach($definitions as $def) {
			$countedDefs[count($def['parameters'])][] = $def;
		}

		$paramCount = count($parameters);
		if(!isset($countedDefs[$paramCount])) {
			throw new Exception('overloadhelper couldn\'t find a matching method with the parameter count ' . $paramCount);
		}
		if(count($countedDefs[$paramCount]) > 1) {
			$matchCount = 0;
			$matchIndex = null;
			foreach($countedDefs[$paramCount] as $key => $paramDef) {
				$success = true;
				for($i = 0; $i < $paramCount; ++$i) {
					if(substr(gettype($parameters[$i]), 0, strlen($paramDef['parameters'][$i])) != $paramDef['parameters'][$i]) {
						$success = false;
						break;
					}
				}
				if($success) {
					++$matchCount;
					$matchIndex = $key;
				}
			}
			if($matchCount == 0) {
				throw new Exception('overloadhelper couldn\'t find a matching method');
			} elseif($matchCount > 1) {
				throw new Exception('overloadhelper found ' . $matchCount . ' matching methods');
			}
			return $countedDefs[$paramCount][$key]['name'];
		} else {
			return $countedDefs[$paramCount][0]['name'];
		}
	}

	/**
	 * Expand variables in a string.
	 *
	 * Variables can be in the form $foo, ${foo} or {$foo}.
	 *
	 * @param      string The format string.
	 * @param      array  The variables to use.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function expandVariables($string, array $arguments = array())
	{

		// replacing the other two forms is faster than using three different search values in the str_replace
		// also, if we had three search patterns, ${foo} with an argument {foo} would be replaced...
		$string = preg_replace(
			'/((\{\$)|\$)([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(?(2)\}|)/',
			'${$3}',
			$string
		);

		$search = array();
		foreach($arguments as $key => $value) {
			$search[] = '${' . $key . '}';
		}
		return str_replace($search, $arguments, $string);
	}
	
	/**
	 * Literalize a string value.
	 *
	 * @param      string The value to literalize.
	 *
	 * @return     string A literalized value.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function literalize($value)
	{
		if(!is_string($value)) {
			return $value;
		}
		
		// trim!
		$value = trim($value);
		if($value == '') {
			return null;
		}
		
		// lowercase our value for comparison
		$lvalue = strtolower($value);
		
		if($lvalue == 'on' || $lvalue == 'yes' || $lvalue == 'true') {
			// replace values 'on' and 'yes' with a boolean true value
			return true;
		} elseif($lvalue == 'off' || $lvalue == 'no' || $lvalue == 'false') {
			// replace values 'off' and 'no' with a boolean false value
			return false;
		} elseif(!is_numeric($value)) {
			return self::expandDirectives($value);
		}
		
		// numeric value, remains a string on purpose (for BC)
		return $value;
	}
	
	/**
	 * Replace configuration directive identifiers in a string.
	 *
	 * @param      string The value on which to run the replacement procedure.
	 *
	 * @return     string The new value.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function expandDirectives($value)
	{
		do {
			$oldvalue = $value;
			$value = preg_replace_callback(
				'/\%([\w\.]+?)\%/',
				function($matches) { return Config::get($matches[1], '%' . $matches[1] . '%'); },
				$value
			);
		} while($oldvalue != $value);

		return $value;
	}
	
	/**
	 * This function takes the numerator and divides it through the denominator while
	 * storing the remainder and returning the quotient.
	 *
	 * @param      float The numerator.
	 * @param      int   The denominator.
	 * @param      int   The remainder.
	 *
	 * @return     int   The floored quotient.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public static function floorDivide($numerator, $denominator, &$remainder)
	{
		if((int)$denominator != $denominator) {
			throw new Exception('Toolkit::floorDivive works only for int denominators');
		}
		$quotient = floor($numerator / $denominator);
		$remainder = (int) ($numerator - ($quotient * $denominator));

		return $quotient;
	}
	
	/**
	 * Determines whether a port declaration is necessary in a URL authority.
	 *
	 * @param      string The scheme (protocol identifier).
	 * @param      int    The port.
	 *
	 * @return     bool True, if port must be included, otherwise false.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function isPortNecessary($scheme, $port)
	{
		static $protocolList = array(
			'ftp' => 21,
			'ssh' => 22,
			'telnet' => 23,
			'gopher' => 70,
			'http' => 80,
			'nttp' => 119,
			'https' => 443,
			'mms' => 1755,
		);
		if(isset($protocolList[$scheme = strtolower($scheme)]) && $protocolList[$scheme] === $port) {
			return false;
		}
		return true;
	}
	
	/**
	 * Tries to grab a value from the given array using the given list of keys.
	 *
	 * @param      array The array to search in.
	 * @param      array The list of keys.
	 * @param      mixed A default return value, defaults to null.
	 *
	 * @return     mixed The found value, or the default value if nothing found.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function getValueByKeyList(array $array, array $keys, $default = null)
	{
		foreach($keys as $key) {
			if(isset($array[$key])) {
				return $array[$key];
			}
		}
		return $default;
	}

	/**
	 * Checks if a value is not an array
	 *
	 * @param      mixed The value to check
	 *
	 * @return     bool The result.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public static function isNotArray($value)
	{
		return !is_array($value);
	}
	
	/**
	 * Generate a proper unique ID.
	 *
	 * Uses PHP's uniqid(), but forces use of additional entropy. Without, it's
	 * just the microtime in hex, and much slower than with entropy on Linux.
	 *
	 * @param      string An optional prefix
	 * @return     string A properly unique ID
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function uniqid($prefix = '')
	{
		return uniqid($prefix, true);
	}
	
	/**
	 * Returns the canonical name for a dot-separated layout/action/model name.
	 * This method is idempotent.
	 *
	 * @param      string The layout/action/model name.
	 *
	 * @return     string The canonical name.
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public static function canonicalName($name)
	{
		return str_replace('.', '\\', str_replace('_', '\\', str_replace('/', '\\', $name)));
	}


    /**
     * Returns the reverse canonical name for a dot-separated layout/action/model name.
     * This method is idempotent.
     *
     * @param      string The layout/action/model name.
     *
     * @return     string The canonical name.
     *
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
     */
    public static function canonicalNameReverse($name)
    {
        return str_replace('\\', '/', self::canonicalName($name));
    }

	/**
	 * Evaluates a given Config per-content directive using the given info.
	 *
	 * @param      string The name of the content
	 * @param      string The relevant name fragment of the directive
	 * @param      array  The variables to expand in the directive value.
	 *
	 * @return     string The final value
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author	   Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public static function evaluateModuleDirective($contentName, $directiveNameFragment, $variables = array())
	{

		$contentName = Toolkit::canonicalName($contentName);
		
		// handle /
        if (isset($variables['contentName']))
            $variables['contentName'] = Toolkit::canonicalNameReverse($variables['contentName']);
		if (isset($variables['chainName'])):
			$variables['chainName'] = Toolkit::canonicalNameReverse($variables['chainName']);
			$variables['chainNameFile'] = substr($variables['chainName'], strrpos($variables['chainName'], '/') ? strrpos($variables['chainName'], '/') + 1 : 0);
		endif;
		if (isset($variables['layoutName'])):
			$variables['layoutName'] = Toolkit::canonicalNameReverse($variables['layoutName']);
			$variables['layoutName'] = substr($variables['layoutName'], strrpos($variables['layoutName'], '/') ? strrpos($variables['layoutName'], '/') + 1 : 0);
		endif;

		$retVal = Toolkit::expandVariables(
			Toolkit::expandDirectives(
				Config::get(
					sprintf(
						'content.%s.%s',
						strtolower($contentName),
						$directiveNameFragment
					)
				)
			),
			$variables
		);

		// fix / in chain name for path
		if (isset($variables['chainName'])):
			$folder = $contentName . '/' . $variables['chainName'];
			$retVal = str_replace($folder, str_replace('_', '/', $folder), $retVal);
		endif;

		return $retVal;
		
	}
	
	/**
	 * Counterpart of PHP's parse_url().
	 * 
	 * @param      array $parts The parts of the URL as defined by parse_url()
	 * @return     string
	 * 
	 * @author     Thomas Bachem <mail@thomasbachem.com>
	 */
	public static function buildUrl(array $parts)
	{
		$url = '';
		if(isset($parts['host']) && strlen($parts['host'])) {
			if(isset($parts['scheme'])) {
				$url .= $parts['scheme'] . ':';
			}
			$url .= '//';
			if(isset($parts['user'])) {
				$url .= $parts['user'];
				if(isset($parts['pass'])) {
					$url .= ':' . $parts['pass'];
				}
				$url .= '@';
			}
			$url .= $parts['host'];
			if(isset($parts['port'])) {
				$url .= ':' . $parts['port'];
			}
		}
		$url .= '/';
		if(isset($parts['path']) && strlen($parts['path'])) {
			$url .= $parts['path'][0] === '/' ? substr($parts['path'], 1) : $parts['path'];
		}
		if(isset($parts['query']) && strlen($parts['query'])) {
			$url .= '?' . $parts['query'];
		}
		if(isset($parts['fragment']) && strlen($parts['fragment'])) {
			$url .= '#' . $parts['fragment'];
		}
		return $url;
	}
	
	/**
	 * Tokenize given string.
	 *
	 * @author    Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	static function tokenize ($string) {
		
		/* Validate */
		if (strlen($string) <= 0)
			throw new Exception('String not passed to Toolkit::tokenize ');
		
		
		/* Convert to URL format without any fancy chars */
		return strtolower($string[0]) . substr(preg_replace('/[^\w\d]/', '', strtolower($string)), 1); // camel case 
	}
	
	/**
	 * Generate a random password
	 *
	 * @author    Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	static function password ($length = 8) {
		if (!is_numeric($length))
			throw new Exception('Length must be integer in Toolkit::password');
		
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@$%^&*-_=+';
       	$pass = array(); 
       	$alphaLength = strlen($alphabet) - 1; 
       	for ($i = 0; $i < $length; $i++) {
       		$n = rand(0, $alphaLength);
           	$pass[] = $alphabet[$n];
       	}
       	$retVal = implode($pass); //turn the array into a string
	
		
		return $retVal;
	}
	
}
?>
