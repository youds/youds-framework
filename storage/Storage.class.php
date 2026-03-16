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
 * Storage allows you to customize the way Youds Framework stores its persistent 
 * data.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage storage
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class Storage extends ParameterHolder
{
	/**
	 * @var        Context An Context instance.
	 */
	protected $context = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context An Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Initialize this Storage.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing this Storage.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->context = $context;

		$this->setParameters($parameters);
	}

	/**
	 * Executes code necessary to startup the storage (a session, for example).
	 * This code cannot be run in initialize(), because initialization has to
	 * finish completely, for all instances, before a session can be created.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function startup()
	{
	}

	/**
	 * Read data from this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 *
	 * @return     mixed Data associated with the key.
	 *
	 * @throws     Exceptions\Storage If an error occurs while reading
	 *                                          data from this storage.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	abstract function read($key);

	/**
	 * Remove data from this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 *
	 * @return     mixed Data associated with the key.
	 *
	 * @throws     Exceptions\Storage If an error occurs while removing
	 *                                          data from this storage.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	abstract function remove($key);

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     Exceptions\Storage If an error occurs while shutting
	 *                                          down this storage.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	abstract function shutdown();

	/**
	 * Write data to this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 * @param      mixed  Data associated with your key.
	 *
	 * @throws     Exceptions\Storage If an error occurs while writing
	 *                                          to this storage.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	abstract function write($key, $data);
}

?>
