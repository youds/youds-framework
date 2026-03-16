<?php
namespace YoudsFramework\Database;
use YoudsFramework\Database;
use YoudsFramework\Config;
use YoudsFramework\Config\Cache;
use YoudsFramework\Context;


// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Manager allows you to setup your database connectivity before 
 * the request is handled. This eliminates the need for a filter to manage 
 * database connections.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage database
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Manager
{
	/**
	 * @var        string The name of the default database.
	 */
	protected $defaultDatabaseName = null;
	
	/**
	 * @var        array An array of Databases.
	 */
	protected $databases = array();

	/**
	 * @var        Context An Context instance.
	 */
	protected $context = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Return array of credentials
	 *
	 * @return array 	Array of database details
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getCredentials()
	{
		foreach ($this->databases as $name => $database):
			$retVal[$name] = $database->getCredentials();
		endforeach;
		
		return $retVal;
	}
	
	/**
	 * Retrieve the database connection associated with this Database
	 * implementation.
	 *
	 * @param      string A database name.
	 *
	 * @return     mixed A Database instance.
	 *
	 * @throws     Exceptions\Database If the requested database name
	 *                                           does not exist.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getDatabase($name = null)
	{
		if($name === null) {
			$name = $this->defaultDatabaseName;
		}
		
		if(isset($this->databases[$name])) {
			return $this->databases[$name];
		}

		// nonexistent database name
		$error = 'Database "%s" does not exist';
		$error = sprintf($error, $name);
		throw new Database($error);
	}
	
	/**
	 * Get All Databases 
	 *
	 * @return Array Array of databases
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getDatabases() 
	{
		return $this->databases;
	}
	
	/**
	 * Retrieve the name of the given database instance.
	 *
	 * @param      Database The database to fetch the name of.
	 *
	 * @return     string The name of the database, or false if it was not found.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getDatabaseName(Database $database)
	{
		return array_search($database, $this->databases, true);
	}

	/**
	 * Returns the name of the default database.
	 *
	 * @return     string The name of the default database.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getDefaultDatabaseName()
	{
		return $this->defaultDatabaseName;
	}

	/**
	 * Initialize this Manager.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing this 
	 *                                                 Manager.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->context = $context;

		// load database configuration
		require_once(Cache::checkConfig(Config::get('core.config_dir') . '/databases.xml'));
				
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function startup()
	{
		foreach($this->databases as $database) {
			$database->startup();
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     Exceptions\Database If an error occurs while shutting
	 *                                           down this Manager.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function shutdown()
	{
		// loop through databases and shutdown connections
		foreach($this->databases as $database) {
			$database->shutdown();
		}
	}
}

?>
