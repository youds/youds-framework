<?php

namespace YoudsFramework;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      	   |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Config acts as global registry of Youds Framework related configuration settings
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Config
{
	/**
	 * @var        array
	 */
	private static $config = array();

	/**
	 * @var        array
	 */
	private static $readonlies = array();

	/**
	 * Get a configuration value.
	 *
	 * @param      string The name of the configuration directive.
	 *
	 * @return     mixed The value of the directive, or null if not set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public static function get($name, $default = null)
	{
	
		// wilcard matching
		if (strstr($name, '*')):
			$matched = array();
            //dump(substr_count($name, '*'));
			switch (substr_count($name, '*')):
				case 1:
                    $token = substr($name, 0, strpos($name, '*'));
                    if (strlen($token) > 0)
                        $tokens[] = $token;
					$token = substr($name, strpos($name, '*') + 1);
                    if (strlen($token) > 0)
                        $tokens[] = $token;
					break;
				case 2:
					$_token1 = substr($name, 0, strpos($name, '*'));
					$_token2 = substr($name, strlen($_token1) + 1, strpos($name, '*', strlen($_token1)) - 1);
					$_token3 = substr($name, strlen($_token1) + strlen($_token2) + 2);
                    if (strlen($_token1) > 0)
                        $tokens[] = $_token1;
                    if (strlen($_token2) > 0)
                        $tokens[] = $_token2;
                    if (strlen($_token3) > 0)
                        $tokens[] = $_token3;
					break;
				default:
					throw new Exception('Only 2 wildcards can be used in Config::get');
			endswitch;
			
			foreach ($tokens as $token):
				foreach (self::$config as $_name => $_config):

                    // check for starts with
                    if ((str_starts_with($name, '*') && str_ends_with($_name, $token)) || str_starts_with($_name, $token))
                        $matched[$_name] = self::$config[$_name];

                    // now check for ends with
                    if ((str_ends_with($name, '*') && str_starts_with($_name, $token)) || str_ends_with($_name, $token))
                        $matched[$_name] = self::$config[$_name];

                    // check for contains
                    if (str_starts_with($name, '*') && str_ends_with($name, '*') && str_contains($_name, $token))
                        $matched[$_name] = self::$config[$_name];

                    // now check for equal
					if ($_name == $token)
						$matched[$_name] = self::$config[$_name];
				endforeach;
			endforeach;
			
			return $matched;
		endif;
			
		// continue as normal 
		if(isset(self::$config[$name]) || array_key_exists($name, self::$config)) {
			return self::$config[$name];
		} else {
			return $default;
		}
	}

	/**
	 * Check if a configuration directive has been set.
	 *
	 * @param      string The name of the configuration directive.
	 *
	 * @return     bool Whether the directive was set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function has($name)
	{
		return isset(self::$config[$name]) || array_key_exists($name, self::$config);
	}

	/**
	 * Check if a configuration directive has been set as read-only.
	 *
	 * @param      string The name of the configuration directive.
	 *
	 * @return     bool Whether the directive is read-only.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function isReadonly($name)
	{
		return isset(self::$readonlies[$name]);
	}

	/**
	 * Set a configuration value.
	 *
	 * @param      string The name of the configuration directive.
	 * @param      mixed  The configuration value.
	 * @param      bool   Whether or not an existing value should be overwritten.
	 * @param      bool   Whether or not this value should be read-only once set.
	 *
	 * @return     bool   Whether or not the configuration directive has been set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function set($name, $value, $overwrite = true, $readonly = false)
	{
		$retVal = false;
		if(($overwrite || !(isset(self::$config[$name]) || array_key_exists($name, self::$config))) && !isset(self::$readonlies[$name])) {
			self::$config[$name] = $value;
			if($readonly) {
				self::$readonlies[$name] = $value;
			}
			$retVal = true;
		}
		return $retVal;
	}

	/**
	 * Remove a configuration value.
	 *
	 * @param      string The name of the configuration directive.
	 *
	 * @return     bool true, if removed successfully, false otherwise.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function remove($name)
	{
		$retVal = false;
		if((isset(self::$config[$name]) || array_key_exists($name, self::$config)) && !isset(self::$readonlies[$name])) {
			unset(self::$config[$name]);
			$retVal = true;
		}
		return $retVal;
	}

	/**
	 * Import a list of configuration directives.
	 *
	 * @param      array An array of configuration directives.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function fromArray(array $data)
	{
		// array_merge would reindex numeric keys, so we use the + operator
		// mind the operand order: keys that exist in the left one aren't overridden
		self::$config = self::$readonlies + $data + self::$config;
	}

	/**
	 * Get all configuration directives and values.
	 *
	 * @return     array An associative array of configuration values.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function toArray()
	{
		return self::$config;
	}

	/**
	 * Clear the configuration.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function clear()
	{
		$restore = array_intersect_assoc(self::$readonlies, self::$config);
		self::$config = $restore;
	}
}
?>
