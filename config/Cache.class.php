<?php
namespace YoudsFramework\Config;
use YoudsFramework\Config;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Context;
use YoudsFramework\Exceptions\Configuration;
use YoudsFramework\Exceptions\Unreadable;
use YoudsFramework\Exceptions\Cache as CacheException;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Cache allows you to customize the format of a configuration
 * file to make it easy-to-use, yet still provide a PHP formatted result
 * for direct inclusion into your content.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Cache
{
	const CACHE_SUBDIR = 'config';

	/**
	 * @var        array An array of config handler instructions.
	 */
	protected static $handlers = null;

	/**
	 * @var        array A string=>bool array containing config handler files and
	 *                   their loaded status.
	 */
	protected static $handlerFiles = array();

	/**
	 * @var        bool Whether there is an entry in self::$handlerFiles that
	 *                  needs processing.
	 */
	protected static $handlersDirty = true;
	
	/**
	 * @var        bool Whether the config handler files have been required.
	 */
	protected static $filesIncluded = false;

	/**
	 * Load a configuration handler.
	 *
	 * @param      string The path of the originally requested configuration file.
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An absolute filesystem path to the cache file that
	 *                    will be written.
	 * @param      string The context which we're currently running.
	 * @param      array  Optional config handler info array.
	 *
	 * @throws     Exceptions\Configuration If a requested configuration
	 *                                                file does not have an
	 *                                                associated config handler.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected static function callHandler($name, $config, $cache, $context, ?array $handlerInfo = null)
	{

		self::setupHandlers();
		
		if(null === $handlerInfo) {
			// we need to load the handlers first
			$handlerInfo = self::getHandlerInfo($name);
		}
		
		if($handlerInfo === null) {
			
			// we do not have a registered handler for this file
			$error = 'Configuration file "%s" does not have a registered handler';
			$error = sprintf($error, $name);
			throw new Configuration($error);
		}
		
		$data = self::executeHandler($config, $context, $handlerInfo);
		
		self::writeCacheFile($config, $cache, $data, false);
		
		
	}

	/**
	 * Set up all config handler definitions.
	 * 
	 * Checks whether the handlers have been loaded or the dirtyHandlers flat is
	 * set, and loads any handler that has not been loaded.
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        0.1
	 */
	protected static function setupHandlers()
	{
		self::loadHandlers();
		
		if(self::$handlersDirty) {
			
			// set handlersdirty to false, prevent an infinite loop
			self::$handlersDirty = false;
			
			// load additional config handlers
			foreach(self::$handlerFiles as $filename => &$loaded) {
				if(!$loaded) {
					self::loadHandlersFile($filename);
					$loaded = true;
				}
			}
		}
	}
	
	/**
	 * Fetch the handler information for the given filename.
	 * 
	 * @param        string The name of the config file (partial path).
	 * 
	 * @return       array  The handler info.
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        0.1
	 */
	protected static function getHandlerInfo($name)
	{
		// grab the base name of the originally requested config path
		$basename = basename($name);

		$handlerInfo = null;

		if(isset(self::$handlers[$name])) {
			// we have a handler associated with the full configuration path
			$handlerInfo = self::$handlers[$name];
		} elseif(isset(self::$handlers[$basename])) {
			// we have a handler associated with the configuration base name
			$handlerInfo = self::$handlers[$basename];
		} else {
			// let's see if we have any wildcard handlers registered that match
			// this basename
			foreach(self::$handlers as $key => $value)	{
				// replace wildcard chars in the configuration and create the pattern
				$pattern = sprintf('#%s#', str_replace('\*', '.*?', preg_quote($key, '#')));

				if(preg_match($pattern, $name)) {
					$handlerInfo = $value;
					break;
				}
			}
		}
		
		return $handlerInfo;
	}
	
	/**
	 * Execute the config handler for the given file.
	 * 
	 * @param        string The path to the config file (full path).
	 * @param        string The context which we're currently running.
	 * @param        array  The config handler info.
	 * 
	 * @return       string The compiled data.
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        0.1
	 */
	protected static function executeHandler($config, $context, array $handlerInfo)
	{
		
		// retrieve cache data
		if ($handlerInfo['class'] == 'YoudsFramework\Config')
			$handler = new $handlerInfo['class'];
		elseif(substr($handlerInfo['class'], 0, 7) == 'Config\\')
			$handler = 'YoudsFramework\\' . $handlerInfo['class'];
		else
			$handler = 'YoudsFramework\Config\\' . $handlerInfo['class'];

		$handler = new $handler();


		if($handler instanceof IXmlHandler) {
			
			// a new-style config handler
			// it does not parse the config itself; instead, it is given a complete and merged \DOM document
			$doc = XmlParser::run($config, Config::get('core.environment'), $context, $handlerInfo['transformations'], $handlerInfo['validations']);

			if($context !== null) {
				$context = Context::getInstance($context);
			}

			$handler->initialize($context, $handlerInfo['parameters']);
			 
			try {
				$data = $handler->execute($doc);
			} catch(Exception $e) {
				throw new $e(sprintf("Compilation of configuration file '%s' failed for the following reason(s):\n\n%s", $config, $e->getMessage()), 0, $e);
			}
		} else {
			$validationFile = null;
			if(isset($handlerInfo['validations'][XmlParser::STAGE_SINGLE][XmlParser::STEP_TRANSFORMATIONS_AFTER][XmlParser::VALIDATION_TYPE_XMLSCHEMA][0])) {
				$validationFile = $handlerInfo['validations'][XmlParser::STAGE_SINGLE][XmlParser::STEP_TRANSFORMATIONS_AFTER][XmlParser::VALIDATION_TYPE_XMLSCHEMA][0];
			}
			$handler->initialize($validationFile, null, $handlerInfo['parameters']);
			$data = $handler->execute($config, $context);
		}
		
		return $data;
	}
	
	/**
	 * Check to see if a configuration file has been modified and if so
	 * recompile the cache file associated with it.
	 *
	 * If the configuration file path is relative, the path itself is relative
	 * to the Youds Framework "core.app_dir" application setting.
	 *
	 * @param      string A filesystem path to a configuration file.
	 * @param      string An optional context name for which the config should be
	 *                    read.
	 *
	 * @return     string An absolute filesystem path to the cache filename
	 *                    associated with this specified configuration file.
	 *
	 * @throws     Exceptions\Unreadable If a requested configuration
	 *                                             file does not exist.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public static function checkConfig($config, $context = null)
	{
		
		$config = Toolkit::normalizePath($config);
		
		// the full filename path to the config, which might not be what we were given.
		$filename = Toolkit::isPathAbsolute($config) ? $config : Toolkit::normalizePath(Config::get('core.storage_dir')) . '/' . $config;
		if(!is_readable($filename)) {
			throw new Unreadable('Configuration file "' . $filename . '" does not exist or is unreadable.');
		}

		// the cache filename we'll be using
		$cache = self::getCacheName($config, $context);
		

		if(self::isModified($filename, $cache)) {
			
			// configuration file has changed so we need to reparse it
			self::callHandler($config, $filename, $cache, $context);
		}

		return $cache;
	}

	/**
	 * Check if the cached version of a file is up to date.
	 *
	 * @param      string The source file.
	 * @param      string The name of the cached version.
	 *
	 * @return     bool Whether or not the cached file must be updated.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function isModified($filename, $cachename)
	{
		return (!is_readable($cachename) || filemtime($filename) > filemtime($cachename));
	}

	/**
	 * Clear all configuration cache files.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public static function clear()
	{
		Toolkit::clearCache(self::CACHE_SUBDIR);
	}

	/**
	 * Convert a normal filename into a cache filename.
	 *
	 * @param      string A normal filename.
	 * @param      string A context name.
	 *
	 * @return     string An absolute filesystem path to a cache filename.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public static function getCacheName($config, $context = null)
	{
		$environment = Config::get('core.environment');

		if(strlen($config) > 3 && ctype_alpha($config[0]) && $config[1] == ':' && ($config[2] == '\\' || $config[2] == '/')) {
			// file is a windows absolute path, strip off the drive letter
			$config = substr($config, 3);
		}

		// replace unfriendly filename characters with an underscore and postfix the name with a php extension
		// see http://trac.agavi.org/wiki/RFCs/Ticket932 for an explanation how cache names are constructed

		$cacheName = sprintf(
			'%1$s_%2$s.php',
			preg_replace(
				'/[^\w\-_.]/i', 
				'_', 
				sprintf(
					'%1$s_%2$s_%3$s', 
					basename($config), 
					$environment,
					$context
				)
			),
			sha1(
				sprintf(
					'%1$s_%2$s_%3$s',
					$config,
					$environment,
					$context
				)
			)
		);
		
		return Config::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . $cacheName;
	}

	/**
	 * Import a configuration file.
	 *
	 * If the configuration file path is relative, the path itself is relative
	 * to the Youds Framework "core.app_dir" application setting.
	 *
	 * @param      string A filesystem path to a configuration file.
	 * @param      string A context name.
	 * @param      bool   Only allow this configuration file to be included once
	 *                    per request?
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public static function load($config, $context = null, $once = true)
	{
		$cache = self::checkConfig($config, $context);

		if($once) {
			include_once($cache);
		} else {
			include($cache);
		}
	}

	/**
	 * Load all configuration application and content level handlers.
	 *
	 * @throws     Exceptions\Configuration If a configuration related
	 *                                                error occurs.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	protected static function loadHandlers()
	{
		if(self::$handlers !== null) {
			return;
		} else {
			self::$handlers = array();
		}
		
		// some checks first
		if(!defined('LIBXML_DOTTED_VERSION') || (!Config::get('core.ignore_broken_libxml', false) && !version_compare(LIBXML_DOTTED_VERSION, '0.1', 'gt'))) {
			throw new Exception("A libxml version greater than 0.1 is highly recommended. With version 0.1 and possibly later releases, validation of XML configuration files will not work and Form Population Filter will eventually fail randomly on some documents due to *severe bugs* in older libxml releases (0.1 was released in November 2004, so it is really getting time to update).\n\nIf you still would like to try your luck, disable this message by doing\nConfig::set('core.ignore_broken_libxml', true);\nand\nConfig::set('core.skip_config_validation', true);\nbefore calling\n::bootstrap();\nin index.php (config.php is not the right place for this).\n\nBut be advised that you *will* run into segfaults and other sad situations eventually, so what you should really do is upgrade your libxml install.");
		}
		
		$srcDir = Config::get('core.src_dir');
		
		// :NOTE: fgilcher, 2008-12-03
		// we need this method reentry safe for unit testing
		// sorry for the testing code in the class, but I don't have
		// any other idea to solve the issue
		if(!self::$filesIncluded) {
			// since we only need the parser and handlers when the config is not cached
			// it is sufficient to include them at this stage
			require_once($srcDir . '/config/ILegacyHandler.interface.php');
			require_once($srcDir . '/config/IXmlHandler.interface.php');
			require_once($srcDir . '/config/BaseHandler.class.php');
			require_once($srcDir . '/config/Handler.class.php');
			require_once($srcDir . '/config/XmlHandler.class.php');
			require_once($srcDir . '/config/AutoloadHandler.class.php');
			require_once($srcDir . '/config/HandlersHandler.class.php');
			require_once($srcDir . '/config/ValueHolder.class.php');
			require_once($srcDir . '/config/Parser.class.php');
			require_once($srcDir . '/config/XmlParser.class.php');
			require_once($srcDir . '/config/ModuleHandler.class.php');
			
			// extended \DOM* classes
			require_once($srcDir . '/config/util/dom/XmlDomAttr.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomCharacterData.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomComment.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomDocument.class.php');
			
			require_once($srcDir . '/config/util/dom/XmlDomDocumentFragment.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomDocumentType.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomElement.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomEntity.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomEntityReference.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomNode.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomNotation.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomProcessingInstruction.class.php');
			require_once($srcDir . '/config/util/dom/XmlDomText.class.php');
			// schematron processor
			require_once($srcDir . '/util/SchematronProcessor.class.php');
			// extended XSL* classes
			if(!Config::get('core.skip_config_transformations', false)) {
				if(!extension_loaded('xsl')) {
					throw new Configuration("You do not have the XSL extension for PHP (ext/xsl) installed or enabled. The extension is used by Youds Framework to perform XSL transformations in the configuration system to guarantee forwards compatibility of applications.\n\nIf you do not want to or can not install ext/xsl, you may disable all transformations by setting\nConfig::set('core.skip_config_transformations', true);\nbefore calling\n::bootstrap();\nin index.php (Keep in mind that disabling transformations means you *have* to use the latest configuration file formats and namespace versions. Also, certain additional configuration file validations implemented via Schematron will not be performed.");
				}
				require_once($srcDir . '/util/XsltProcessor.class.php');
			}
			self::$filesIncluded = true;
		}
		
		// manually create our config_handlers.xml handler
		self::$handlers['config_handlers.xml'] = array(
			'class' => 'Config\HandlersHandler',
			'parameters' => array(
			),
			'transformations' => array(
				XmlParser::STAGE_SINGLE => array(
					// 0.11 -> 1.0
					$srcDir . '/config/xsl/config_handlers.xsl',
					// 1.0 -> 1.0 with ReturnArrayHandler <transformation> for Youds Framework 1.1
					$srcDir . '/config/xsl/config_handlers.xsl',
				),
				XmlParser::STAGE_COMPILATION => array(
				),
			),
			'validations' => array(
				XmlParser::STAGE_SINGLE => array(
					XmlParser::STEP_TRANSFORMATIONS_BEFORE => array(
					),
					XmlParser::STEP_TRANSFORMATIONS_AFTER => array(
						XmlParser::VALIDATION_TYPE_XMLSCHEMA => array(
							$srcDir . '/config/xsd/config_handlers.xsd',
						),
						XmlParser::VALIDATION_TYPE_SCHEMATRON => array(
							$srcDir . '/config/sch/config_handlers.sch',
						),
					),
				),
				XmlParser::STAGE_COMPILATION => array(
					XmlParser::STEP_TRANSFORMATIONS_BEFORE => array(),
					XmlParser::STEP_TRANSFORMATIONS_AFTER => array()
				),
			),
		);

		$cfg = Config::get('core.config_dir') . '/config_handlers.xml';
		if(!is_readable($cfg)) {
			$cfg = Config::get('core.framework_config_dir') . '/config_handlers.xml';
		}

		// application configuration handlers
		self::loadHandlersFile($cfg);
		
	}
	
	/**
	 * Load the config handlers from the given config file.
	 * Existing handlers will not be overwritten.
	 * 
	 * @param      string The path to a config_handlers.xml file.
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected static function loadHandlersFile($cfg)
	{
		$array = (array) include_once(Cache::checkConfig($cfg));
		if (!is_array($array))
			throw new Exception('Array for ' . $cfg . ' not an array');
		self::$handlers = (array)self::$handlers + $array;
	}

	/**
	 * Schedules a config handlers file to be loaded.
	 * 
	 * @param      string The path to a config_handlers.xml file.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public static function addHandlersFile($filename)
	{
		if(!isset(self::$handlerFiles[$filename])) {
			if(!is_readable($filename)) {
				throw new Unreadable('Configuration file "' . $filename . '" does not exist or is unreadable.');
			}
			
			self::$handlerFiles[$filename] = false;
			self::$handlersDirty = true;
		}
	}

	/**
	 * Write a cache file.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An absolute filesystem path to the cache file that
	 *                    will be written.
	 * @param      string Data to be written to the cache file.
	 * @param      bool   Should we append the data?
	 *
	 * @throws     Exceptions\Cache If the cache file cannot be written.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
     */
	public static function writeCacheFile($config, $cache, $data, $append = false)
	{
		
		$perms = fileperms(Config::get('core.cache_dir')) ^ 0x4000;

		$cacheDir = Config::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR;

		Toolkit::mkdir($cacheDir, $perms);

		if($append && is_readable($cache)) {
			$data = file_get_contents($cache) . $data;
		}

		$tmpName = tempnam($cacheDir, basename($cache));
		if(@file_put_contents($tmpName, $data) !== false) {
			// that worked, but that doesn't mean we're safe yet
			// first, we cannot know if the destination directory really was writeable, as tempnam() falls back to the system temp dir
			// second, with php < 0.1 on win32 renaming to an already existing file doesn't work, but copy does
			// so we simply assume that when rename() fails that we are on win32 and try to use copy() followed by unlink()
			// if that also fails, we know something's odd
			if(@rename($tmpName, $cache) || (@copy($tmpName, $cache) && unlink($tmpName))) {
				// alright, it did work after all. chmod() and bail out.
				if (@file_exists($cache) && @is_writeable($cache))
					@chmod($cache, $perms);

				return;
			}
		}
		
		// still here?
		// that means we could not write the cache file
		$error = 'Failed to write cache file "%s" generated from ' . 'configuration file "%s".';
		$error .= "\n\n";
		$error .= 'Please make sure you have set correct write permissions for directory "%s".';
		$error = sprintf($error, $cache, $config, Config::get('core.cache_dir'));
		throw new CacheException($error);
	}

	/**
	 * Parses a config file with the Parser for the extension of the given
	 * file.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      bool   Whether the config parser class should be autoloaded if
	 *                    the class doesn't exist.
	 * @param      string A path to a validation file for this config file.
	 * @param      string A class name which specifies an parser to be used.
	 *
	 * @return     ValueHolder An abstract representation of the
	 *                                    config file.
	 *
	 * @throws     Exceptions\Configuration If the parser for the
	 *             extension couldn't be found.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 *
	 * @deprecated New-style config handlers don't call this method anymore. To be
	 *             removed in Youds Framework 1.1
	 */
	public static function parseConfig($config, $autoloadParser = true, $validationFile = null, $parserClass = null)
	{
		
		$parser = new Parser();

		return $parser->parse($config, $validationFile);
	}
}

?>
