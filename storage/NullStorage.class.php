<?php

namespace YoudsFramework;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * NullStorage doesn't store what it is given and always returns null on
 * reads. Perfect if you want to use a User, but no sessions.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage storage
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class NullStorage extends Storage
{
	/**
	 * Read data from this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 *
	 * @return     void Always null.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function read($key)
	{
		return null;
	}

	/**
	 * Remove data from this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 *
	 * @return     null Always null.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function remove($key)
	{
		return null;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function shutdown()
	{
	}

	/**
	 * Write data to this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 * @param      mixed  Data associated with your key.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function write($key, $data)
	{
	}
}

?>
