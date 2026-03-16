<?php
namespace YoudsFramework\Util;
use YoudsFramework\Config;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and licence information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Autoloader is an autoloader implementation with support for namespaces,
 * conforming to the PSR-0 standard. It also allows a plain mapping of class
 * names to file paths.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage util
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Autoloader {
	
	/**
	 * @var        array An assoc array of classes and file paths for autoloading.
	 */
	public static $classes = array();
	
	/**
	 * @var        array An assoc array of namespaces and paths for autoloading.
	 */
	public static $namespaces = array();

	/**
	 * Add classes to the autoloader.
	 *
	 * @param      array An array containing class names as keys and paths to the
	 *                   corresponding PHP files as values.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public static function addClasses(array $map)
	{
		self::$classes = array_merge(self::$classes, $map);
	}

	/**
	 * Add namespaces to the autoloader.
	 *
	 * @param      array An array containing namespace prefixes as keys and paths
	 *                   to the corresponding directories containing files as
	 *                   values. Namespace prefixes must not contain a trailing
	 *                   backslash.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public static function addNamespaces(array $map)
	{
		self::$namespaces = array_merge(self::$namespaces, $map);
	}

	/**
	 * Handles autoloading of classes
	 *
	 * @param      string A class name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function loadClass($class)
	{
		if(isset(self::$classes[$class])) {
						
			// class exists, let's include it
			require_once(self::$classes[$class]);
			return true;
		}

		$expandedClass = explode('\\', $class);

		if (isset($expandedClass[2]) && substr($expandedClass[2], -9) == 'Exceptions')
			$file = sprintf('%s/exceptions/%s', Config::get('core.src_dir'), $expandedClass[1]);
		if (isset($expandedClass[1])):
            switch ($expandedClass[1]):

				case 'Context':
					$file = sprintf('%s/core/%s', Config::get('core.src_dir'), $expandedClass[1]);
				break;
				case 'OutputType':
					$file = sprintf('%s/controller/%s', Config::get('core.src_dir'), $expandedClass[1]);
				break;
				case 'AttributeHolder':
					$file = sprintf('%s/util/%s', Config::get('core.src_dir'), $expandedClass[1]);
				break;
				case 'SessionStorage':
				case 'Storage':
					$file = sprintf('%s/storage/%s', Config::get('core.src_dir'), $expandedClass[1]);
				break;
				case 'Models':
					$file = sprintf('%s/Models/%s', Config::get('core.core_dir'), str_replace('\\', '/', str_replace('Core\\Models', '', $class)));
				break;
				case 'Common':
					if (isset($expandedClass[2]) && $expandedClass[2] == 'Base'):
						if (isset($expandedClass[4]) && $expandedClass[4] == 'Default')
							$file = sprintf('%s/core/common/base/default/%s/%s', Config::get('core.defaults_dir'), strtolower($expandedClass[3]), $expandedClass[3]);
						else
							$file = sprintf('%s/common/%s/ProjectBase%s', Config::get('core.core_dir'), strtolower($expandedClass[2]), (isset($expandedClass[3])?$expandedClass[3]:''));
					endif;
					break;
				case 'Action':
				case 'Layout':
					$file = sprintf('%s/%s/%s', Config::get('core.src_dir'), strtolower($expandedClass[1]), $expandedClass[2] ?? '');

                break;
				case 'Chains':
                    $file = sprintf('%s/%s', Config::get('core.core_dir'), str_replace('\\', '/', substr($class, strlen($expandedClass[1]) - 1)));
					break;
				case 'Validator':
                    if (!isset($expandedClass[2]))
						$file = sprintf('%s/validator/%s', Config::get('core.src_dir'), $expandedClass[1]);
					else
						$file = sprintf('%s/validator/%s', Config::get('core.src_dir'), $expandedClass[2]);

					// pre-check to see if it exists
					if (!is_file($file . '.class.php')):
						$commonValidator = sprintf('%s/validators/%s', Config::get('core.common_dir'), $expandedClass[2]);

						if (is_file($commonValidator . '.class.php')):
							$file = $commonValidator;
						else:
							$defaultsFile = sprintf('%s/core/common/validators/%s', Config::get('core.defaults_dir'), $expandedClass[2]);
							if (is_file($defaultsFile . '.class.php')):
								$file = $defaultsFile;
							endif;
						endif;
					endif;
					break;
				case 'User':
					if (!isset($expandedClass[2]))
						$file = sprintf('%s/user/%s', Config::get('core.src_dir'), $expandedClass[1]);
					else
						$file = sprintf('%s/user/%s', Config::get('core.src_dir'), $expandedClass[2]);

					// in case of matching naming conventions
					if (!is_file($file . '.class.php') && !is_file($file . '.interface.php') && !is_file($file . '.abstract.php') && !is_file($file . '.php'))
						$file = sprintf('%s/users/%s', Config::get('core.common_dir'), $expandedClass[2]);
					break;
				case 'ExecutionContainer':
					$file = sprintf('%s/controller/%s', Config::get('core.src_dir'), $expandedClass[1]);
					break;
				case 'Config':
				case 'Controller':
				case 'Core':
				case 'Database':
				case 'Date':
				case 'Exceptions':
				case 'Filter':
				case 'Generator':
				case 'Integrations':
				case 'Logging':
				case 'Model':
				case 'Renderer':
				case 'Request':
				case 'Response':
				case 'Routing':
				case 'Testing':
				case 'Translation':
				case 'Util':
				case 'WebSockets':
					if (isset($expandedClass[2])):
						switch ($expandedClass[2]):
							case 'Common':
								$file = sprintf('%s/defaults/%s/%s/%s/DefaultBase%s', $expandedClass[0] == 'Core' ? Config::get('core.core_dir') : Config::get('core.src_dir') , strtolower($expandedClass[1]), strtolower($expandedClass[2]), strtolower($expandedClass[3]), $expandedClass[4]);;
								break;
							case 'Environment':
								$file = sprintf('%s/%s/%s/%s', Config::get('core.src_dir'), strtolower($expandedClass[1]), strtolower($expandedClass[2]), $expandedClass[3]);
							
								break;
							case 'Models':
								$file = sprintf('%s/%s', Config::get('core.defaults_model_dir'), str_replace('\\', '/', str_replace('Defaults\\Core\\Models\\', '', $class)));
								break;
							default:
								$file = sprintf('%s/%s/%s', Config::get('core.src_dir'), strtolower($expandedClass[1]), $expandedClass[2]);
						endswitch;
					else:
						
						$file = sprintf('%s/%s/%s', Config::get('core.src_dir'), strtolower($expandedClass[1]), $expandedClass[1]);
					endif;
					break;
			endswitch;
		endif;

		// include if found
		if (isset($file) && !class_exists($class)):
			$ext = array(
				'.class.php',
				'.abstract.php',
				'.interface.php',
				'.php'
			);
			$found = false;
			foreach ($ext as $end):
				if (is_readable($file . $end)):
					include_once($file . $end);
					$found = true;
					continue;
				endif;
			endforeach;

			if (!$found):
				foreach (glob($file . '*.php') as $filename):
				    include_once($filename);
				endforeach;
			endif;
		endif;
		
		// nothing yet; let's see if it's in one of our namespace map paths
		$lastBackslash = strrpos($class, '\\');
		
		if($lastBackslash === false) {
			return false;
		}
		
		// split input into namespace and class name
		$namespace = substr($class, 0, $lastBackslash);
		$class = substr($class, $lastBackslash + 1);
		
				
		foreach(self::$namespaces as $prefix => $path) {
			if(strpos($namespace . '\\', $prefix . '\\') === 0) { // make sure we terminate the prefix, or else a prefix like "Doc" would load "Doctrine"
				$file = str_replace('\\', DIRECTORY_SEPARATOR, substr($namespace, strlen($prefix))) // strip the prefix from the namespace and replace backslashes
				      . DIRECTORY_SEPARATOR
				      . str_replace('_', DIRECTORY_SEPARATOR, $class) // replace underscores in the class name in conformance with PSR-0
				      . '.php';
				// unconditionally load the file, but only use an include_once() just in case the file isn't there
				include($path . $file);
				return true;
			}
		}
		
		// If the class doesn't exist in autoload.xml there's not a lot we can do.
		// Hopefully, another registered autoloader will be able to help :)
		return false;
	}
}

?>
