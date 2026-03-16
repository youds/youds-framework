<?php

namespace YoudsFramework\Database;
use YoudsFramework\Database;
use YoudsFramework\Database\Database as DatabaseInterface;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage database
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class LaravelOrm extends Database implements DatabaseInterface
{

	/**
	 * Initialize this Database.
	 *
	 * @param      Manager The database manager of this instance.
	 * @param      array                An assoc array of initialization params.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing this Database.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function initialize(Manager $databaseManager, array $parameters = array())
	{
		
		$capsule = new \Illuminate\Database\Capsule\Manager;
		
		$this->setParameters($parameters);
				
		$capsule->addConnection([
		    'driver'    => $this->getParameter('driver'),
			'host'		=> $this->getParameter('host'),
		    'unix_socket'  => $this->getParameter('socket'),
		    'database'  => $this->getParameter('database'),
		    'username'  => $this->getParameter('user'),
		    'password'  => $this->getParameter('password'),
		    'charset'   => $this->getParameter('charset'),
		    'collation' => $this->getParameter('collation'),
		    'prefix'    => '',
		]);
				
		// Make this Capsule instance available globally via static methods... (optional)
		$capsule->setAsGlobal();
		
		// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
		$capsule->bootEloquent();

	}
	
	
	/**
	 * Connect to the database.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function connect()
	{
		
	}
	
	
	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws Exceptions\Database If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function shutdown()
	{
		if($this->connection !== null) {
			$this->connection = null;
			$this->resource = null;
		}
	}
}

?>