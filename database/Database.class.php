<?php
namespace YoudsFramework;
use YoudsFramework\Request\ParameterHolder;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Database is a base abstrchain class that allows you to setup any type
 * of database connection via a configuration file.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage database
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David ZÃ¼lke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class Database extends ParameterHolder
{
	/**
	 * @var        Manager An Manager instance.
	 */
	protected $databaseManager = null;
	
	/**
	 * @var        mixed A database connection.
	 */
	protected $connection = null;

	/**
	 * @var        string The name of the database.
	 */
	private $name = null;

	/**
	 * @var        mixed A database resource.
	 */
	protected $resource = null;

	/**
	 * Connect to the database.
	 *
	 * @throws     Exceptions\Database If a connection could not be 
	 *                                           created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	abstract protected function connect();
	
	/**
	 * Retrieve the Database Manager instance for this implementation.
	 *
	 * @return     Manager A Database Manager instance.
	 *
	 * @author     David ZÃ¼lke <dz@bitxtender.com>
	 */
	public function getManager()
	{
		return $this->databaseManager;
	}

	/**
	 * Retrieve the name of this database connection.
	 *
	 * @return     string The name of the database.
	 *
	 * @author     David ZÃ¼lke <dz@bitxtender.com>
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Retrieve the database connection associated with this Database
	 * implementation.
	 *
	 * When this is executed on a Database implementation that isn't an
	 * abstrchain layer, a copy of the resource will be returned.
	 *
	 * @return     mixed A database connection.
	 *
	 * @throws     Exceptions\Database If a connection could not be 
	 *                                           retrieved.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getConnection()
	{
		if($this->connection === null) {
			$this->connect();
		}

		return $this->connection;
	}

	/**
	 * Retrieve a raw database resource associated with this Database
	 * implementation.
	 *
	 * @return     mixed A database resource.
	 *
	 * @throws     Exceptions\Database If no resource could be retrieved
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getResource()
	{
		if($this->resource === null) {
			$this->connect();
		}

		return $this->resource;
	}
	
	/**
	 * Get Credentials of Database
	 *
	 * @return array
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getCredentials()
	{
		return $this->getParameters();
	}
	
	/**
	 * Initialize this Database.
	 *
	 * @param      Manager The database manager of this instance.
	 * @param      array                An assoc array of initialization params.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing this Database.
	 *
	 * @author     David ZÃ¼lke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function initialize(Database\Manager $databaseManager, array $parameters = array())
	{
		$this->databaseManager = $databaseManager;
		
		$this->setParameters($parameters);
		
		$this->name = $databaseManager->getDatabaseName($this);
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 * It is called during the startup() of the database manager.
	 *
	 * @author     David ZÃ¼lke <dz@bitxtender.com>
	 */
	public function startup()
	{
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     Exceptions\Database If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	abstract public function shutdown();
}

?>
