<?php
namespace YoudsFramework;
use YoudsFramework\Config\Cache;
use YoudsFramework\Exceptions\Exception;
use YoudsFramework\Exceptions\DisabledModule;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Exceptions\Autoload;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Context provides information about the current application context, 
 * such as the content and chain names and the content directory. 
 * It also serves as a gateway to the core pieces of the framework, allowing
 * objects with access to the context, to access other useful objects such as
 * the current controller, request, user, database manager etc.
 *
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Mike Vincent <mike@agavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Context
{
	/**
	 * @var        string The name of the Context.
	 */
	protected $name = '';
	
	/**
	 * @var        Controller An Controller instance.
	 */
	protected $controller = null;
	
	/**
	 * @var        array An array of class names for frequently used factories.
	 */
	protected $factories = array(
		'dispatch_filter' => null,
		'execution_container' => null,
		'execution_filter' => null,
		'filter_chain' => null,
		'response' => null,
		'security_filter' => null,
		'validation_manager' => null,
		'application' => null
	);
	
	/**
	 * @var        DatabaseManager An DatabaseManager instance.
	 */
	protected $databaseManager = null;
	
	/**
	 * @var        LoggerManager An LoggerManager instance.
	 */
	protected $loggerManager = null;
	
	/**
	 * @var        Request An Request instance.
	 */
	protected $request = null;
	
	/**
	 * @var        Routing An Routing instance.
	 */
	protected $routing = null;
	
	/**
	 * @var        Storage An Storage instance.
	 */
	protected $storage = null;
	
	/**
	 * @var        TranslationManager An TranslationManager instance.
	 */
	protected $translationManager = null;
	
	/**
	 * @var        Generator An Generator instance.
	 */
	protected $gt = null;
	
	/**
	 * @var        Integrations An Integrations instance.
	 */
	protected $integrations = null;
	
	/**
	 * @var        Integration An Integration instance.
	 */
	protected $it = null;
	
	/**
	 * @var        User An User instance.
	 */
	protected $user = null;
	
	
	/**
	 * @var        array The array used for the shutdown sequence.
	 */
	protected $shutdownSequence = array();
	
	/**
	 * @var        array An array of Context instances.
	 */
	protected static $instances = array();
	
	/**
	 * @var        array An array of SingletonModel instances.
	 */
	protected $singletonModelInstances = array();
	
	/**
	 * Generator instance
	 *
	 * @var object Generator
	 */
	protected $generator = null;

	/**
	 * Clone method, overridden to prevent cloning, there can be only one.
	 *
	 * @author     Mike Vincent <mike@agavi.org>
	 */
	public function __clone()
	{
		trigger_error('Cloning the Context instance is not allowed.', E_USER_ERROR);
	}

	/**
	 * Constructor method, intentionally made protected so the context cannot be
	 * created directly.
	 *
	 * @param      string The name of this context.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 */
	protected function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * __toString overload, returns the name of the Context.
	 *
	 * @return     string The context name.
	 *
	 * @see        Context::getName()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __toString()
	{
		return $this->getName();
	}
	
	/**
	 * Get information on a frequently used class.
	 *
	 * @param      string The factory identifier.
	 *
	 * @return     array An associative array (keys 'class' and 'parameters').
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getFactoryInfo($for)
	{
		if(isset($this->factories[$for])) {
			return $this->factories[$for];
		}
	}
	
	/**
	 * Set information on a frequently used class.
	 *
	 * @param      string The factory identifier.
	 * @param      array An associative array (keys 'class' and 'parameters').
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitxtender.com>
	 */
	public function setFactoryInfo($for, array $info)
	{
		$this->factories[$for] = $info;
	}

	/**
	 * Factory for frequently used classes from factories.xml
	 *
	 * @param      string The factory identifier.
	 *
	 * @return     mixed An instance, already initialized with parameters.
	 *
	 * @throws     Exception If no such identifier exists.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function createInstanceFor($for)
	{
		$info = $this->getFactoryInfo($for);
		if(null === $info) {
			throw new Exception(sprintf('No factory info for "%s"', $for));
		}

		$className = 'YoudsFramework\\' . $info['class'];
		$class = new $className();
		$class->initialize($this, $info['parameters']);
		return $class;
	}

	/**
	 * Retrieve the controller.
	 *
	 * @return     Controller The current Controller implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Retrieve a database connection from the database manager.
	 *
	 * This is a shortcut to manually getting a connection from an existing
	 * database implementation instance.
	 *
	 * If the core.use_database setting is off, this will return null.
	 *
	 * @param      name An database name.
	 *
	 * @return     mixed An database connection.
	 *
	 * @throws     Exceptions\Database If the requested database name 
	 *                                           does not exist.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getDatabaseConnection($name = null)
	{
		if($this->databaseManager !== null) {
			return $this->databaseManager->getDatabase($name)->getConnection();
		}
	}

	/**
	 * Retrieve the database manager.
	 *
	 * @return     DatabaseManager The current DatabaseManager instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getDatabaseManager()
	{
		return $this->databaseManager;
	}

	/**
	 * Retrieve the Context instance.
	 *
	 * If you don't supply a profile name this will try to return the context 
	 * specified in the <kbd>core.default_context</kbd> setting.
	 *
	 * @param      string An name corresponding to a section of the config
	 *
	 * @return     Context An context instance initialized with the 
	 *                          settings of the requested context name
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @author	   Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public static function getInstance($profile = null)
	{
		// save $profile in config
		if (is_string($profile))
			Config::set('core.profile', $profile);
		
		// perform acton
		try {
			if($profile === null) {
				$profile = Config::get('core.default_context');
				if($profile === null) {
					throw new Exception('You must supply a context name to Context::getInstance() or set the name of the default context to be used in the configuration directive "core.default_context".');
				}
			}
			$profile = strtolower($profile);
			if(!isset(self::$instances[$profile])) {
				$class = Config::get('core.context_implementation', get_called_class());
				self::$instances[$profile] = new $class($profile);
				self::$instances[$profile]->initialize();
			}
			return self::$instances[$profile];
		} catch(Exception $e) {
			Exception::render($e);
		}
	}
	
	/**
	 * Retrieve the LoggerManager
	 *
	 * @return     LoggerManager The current LoggerManager implementation 
	 *                                instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getLoggerManager()
	{
		return $this->loggerManager;
	}

	/**
	 * (re)Initialize the Context instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 */
	public function initialize()
	{
		try {
			include_once(Cache::checkConfig(Config::get('core.config_dir') . '/factories.xml', $this->name));
		} catch(Exception $e) {
			Exception::render($e, $this);
		}

        register_shutdown_function(array($this, 'shutdown'));
	}
	
	/**
	 * Shut down this Context and all related factories.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function shutdown()
	{
		foreach($this->shutdownSequence as $object) {
			$object->shutdown();
		}
	}

	
	/**
	 * Retrieve the name of this Context.
	 *
	 * @return     string An context name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Retrieve the request.
	 *
	 * @return     Request The current Request implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Retrieve the routing.
	 *
	 * @return     Routing The current Routing implementation instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getRouting()
	{
		return $this->routing;
	}

	/**
	 * Retrieve the storage.
	 *
	 * @return     Storage The current Storage implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getStorage()
	{
		return $this->storage;
	}

	/**
	 * Retrieve the translation manager.
	 *
	 * @return     TranslationManager The current TranslationManager
	 *                                     implementation instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getTranslationManager()
	{
		return $this->translationManager;
	}

	/**
	 * Retrieve the user.
	 *
	 * @return     User The current User implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getUser()
	{
		
		// append session data from attribute "user" to user object directly
		if ($this->user->isAuthenticated() && is_array($this->user->getParameter('user'))):
			foreach ($this->user->getParameter('user') as $key => $param):
				$this->user->$key = $param;
			endforeach;
		endif;
		
		return $this->user;
	}
	
	/**
	 * Retrieve the integration toolset
	 *
	 * @return     Application The current Application implementation instance.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getIntegrations ()
	{
		$this->integrations = new Integrations();	
		return $this->integrations;
	}
	
	
	/**
	 * Retrieve the generator.
	 *
	 * @return     Generator The current Form Generator implementation instance.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getGenerator ()
	{
		$this->generator = new Generator\Generator();
		return $this->generator;
	}
	

}

?>
