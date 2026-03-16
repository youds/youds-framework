<?php
namespace YoudsFramework;
use YoudsFramework\Config\Cache;
use YoudsFramework\Request\DataHolder;
use YoudsFramework\Request\Console;
use YoudsFramework\Request\Web;
use YoudsFramework\WebSockets\Manager as WebSocketsManager;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Action allows you to separate application and business logic from your
 * presentation. By providing a core set of methods used by the framework,
 * automation in the form of security and validation can occur.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage chain
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class Action
{
	/**
	 * @var        ExecutionContainer This chain's execution container.
	 */
	protected $container = null;

	/**
	 * @var        Context An Context instance.
	 */
	protected $context = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the execution container for this chain.
	 *
	 * @return     ExecutionContainer This chain's execution container.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function getContainer()
	{
		return $this->container;
	}

	/**
	 * Retrieve the credential required to access this chain.
	 *
	 * @return     mixed Data that indicates the level of security for this
	 *                   chain.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getCredentials()
	{
		return null;
	}

	/**
	 * Execute any post-validation error application logic.
	 *
	 * @param      Web The chain's request data holder.
	 *
	 * @return     mixed A string containing the Layout Name associated with this
	 *                   chain.
	 *                   Or an array with the following indices:
	 *                   - The parent content of the layout that will be executed.
	 *                   - The layout that will be executed.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function handleError(Console|Web $rd)
	{
		return 'Error';
	}

	/**
	 * Initialize this chain.
	 *
	 * @param      ExecutionContainer This Action's execution container.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(ExecutionContainer $container)
	{
		$this->container = $container;

		$this->context = $container->getContext();


    }

	/**
	 * Indicates that this chain requires security.
	 *
	 * @return     bool true, if this chain requires security, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function isSecure()
	{
		return false;
	}

	/**
	 * Whether or not this chain is "simple", i.e. doesn't use validation etc.
	 *
	 * @return     bool true, if this chain should act in simple mode, or false.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function isSimple()
	{
		return false;
	}

	/**
	 * Manually register validators for this chain.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function registerValidators()
	{
	}

	/**
	 * Manually validate files and parameters.
	 *
	 * @param      Web The chain's request data holder.
	 *
	 * @return     bool true, if validation completed successfully, otherwise
	 *                  false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function validate($rd)
	{
		return true;
	}

	/**
	 * Get the default Layout name if this Action doesn't serve the Request method.
	 *
	 * @return     mixed A string containing the Layout Name associated with this
	 *                   chain.
	 *                   Or an array with the following indices:
	 *                   - The parent content of the layout that will be executed.
	 *                   - The layout that will be executed.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getDefaultLayoutName()
	{
		return 'Input';
	}

	/**
	 * @see        AttributeHolder::clearAttributes()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clearAttributes()
	{
		$this->container->clearAttributes();
	}

	/**
	 * @see        AttributeHolder::getAttribute()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &getAttribute($name, $default = null)
	{
		return $this->container->getAttribute($name, null, $default);
	}

	/**
	 * @see        AttributeHolder::getAttributeNames()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getAttributeNames()
	{
		return $this->container->getAttributeNames();
	}

	/**
	 * @see        AttributeHolder::getAttributes()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &getAttributes()
	{
		return $this->container->getAttributes();
	}

	/**
	 * @see        AttributeHolder::hasAttribute()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasAttribute($name)
	{
		return $this->container->hasAttribute($name);
	}

	/**
	 * @see        AttributeHolder::removeAttribute()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &removeAttribute($name)
	{
		return $this->container->removeAttribute($name);
	}

	/**
	 * @see        AttributeHolder::setAttribute()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setAttribute($name, $value)
	{
		$this->container->setAttribute($name, $value);
	}
	

	/**
	 * @see        AttributeHolder::appendAttribute()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function appendAttribute($name, $value)
	{
		$this->container->appendAttribute($name, $value);
	}

	/**
	 * @see        AttributeHolder::setAttributeByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setAttributeByRef($name, &$value)
	{
		$this->container->setAttributeByRef($name, $value);
	}

	/**
	 * @see        AttributeHolder::appendAttributeByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function appendAttributeByRef($name, &$value)
	{
		$this->container->appendAttributeByRef($name, $value);
	}

	/**
	 * @see        AttributeHolder::setAttributes()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setAttributes(array $attributes)
	{
		$this->container->setAttributes($attributes);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setAttributesByRef(array &$attributes)
	{
		$this->container->setAttributesByRef($attributes);
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
		return $this->getContext()->getController();
	}

	/**
	 * Retrieve a database connection from the database manager.
	 *
	 * This is a shortcut to manually getting a connection from an existing
	 * database implementation instance.
	 *
	 * If the core.use_database setting is off, this will return null.
	 *
	 * @param      name A database name.
	 *
	 * @return     mixed A database connection.
	 *
	 * @throws     Exceptions\Database If the requested database name 
	 *                                           does not exist.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getDatabaseConnection($name = null)
	{
		$this->getContext()->getDatabaseConnection($name);
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
		return $this->getContext()->getDatabaseManager();
	}
	
	/**
	 * Retrieve the database manager.
	 *
	 * @return     DatabaseManager The current DatabaseManager instance.
	 *
	 * @author    Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getDatabase($database = 'default')
	{
		
		return $this->getContext()->getDatabaseManager()->getDatabase(
			$database == 'default' ? $this->getContext()->getDatabaseManager()->getDefaultDatabaseName() : 
			$this->getContext()->getDatabaseManager()->getDatabase($database)
		);
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
		return $this->getContext()->getLoggerManager();
	}
	
	
	/**
	 * Retrieve the name of this Context.
	 *
	 * @return     string A context name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getName()
	{
		return $this->getContext()->getName();
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
		return $this->getContext()->getRequest();
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
		return $this->getContext()->getRouting();
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
		return $this->getContext()->getStorage();
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
		return $this->getContext()->getTranslationManager();
	}

	/**
	 * Retrieve the user.
	 *
	 * @return     User The current User implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getUser()
	{
		return $this->getContext()->getUser();
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
		return $this->getContext()->getIntegrations();
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
		return $this->getContext()->getGenerator();
	}
	
	
	/**
	 * Retrieve the validators.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getValidationManager ()
	{
		return $this->getContainer()->getValidationManager();
	}
	
	/**
	 * Return matched route as an array with values:
	 *  - route name (string)
	 *  - route url (string)
	 *  - other route options (array)
	 *
	 * @return array 
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getMatchedRoute()
	{
		return $this->getContext()->getRequest()->getAttribute('matched_routes', 'org.framework.routing');
	}
	
	/**
	 * Get active WebSockets Server state
	 *
	 * @return Ratchet server instance
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getWebSockets()
	{
		return new WebSocketsManager();
	}

	/**
	 * Forward to another action, elegantly
	 *
	 * @return string
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function runChain ($content, $chain, $arguments = null, $outputType = null, $requestMethod = null) :string
	{
		
		// clear execution container
		$this->getContext()->getController()->setParameter('max_executions', 0);
		
		// arguments
		$rdhc = 'YoudsFramework\\' . $this->context->getRequest()->getParameter('request_data_holder_class');
		$arguments = new $rdhc(array(DataHolder::SOURCE_PARAMETERS => (array) $arguments));

		// fetch container
		$container = $this->getContext()->getController()->createExecutionContainer($content, $chain, $arguments, $outputType, $requestMethod);

		return $container->execute()->getContent();
	}
	
}

?>
