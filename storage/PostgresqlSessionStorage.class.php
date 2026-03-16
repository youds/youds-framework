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
 * Provides support for session storage using a PostgreSQL brand database.
 *
 * Required parameters:
 *
 * # db_table - [none] - The database table in which session data will be
 *                              stored.
 *
 * Optional parameters:
 *
 * # database     - [default]   - The database connection to use
 *                                       (see databases.xml).
 * # db_id_col    - [sess_id]   - The database column in which the
 *                                       session id will be stored.
 * # db_data_col  - [sess_data] - The database column in which the
 *                                       session data will be stored.
 * # db_time_col  - [sess_time] - The database column in which the
 *                                       session timestamp will be stored.
 * # date_format  - [U]         - The format string passed to date() to
 *                                       format timestamps. Defaults to "U",
 *                                       which means a Unix Timestamp again.
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
class PostgresqlSessionStorage extends SessionStorage
{
	/**
	 * @var        resource A postgresql database resource.
	 */
	protected $resource = null;

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
		// initialize the parent
		parent::initialize($context, $parameters);

		if(!$this->hasParameter('db_table')) {
			// missing required 'db_table' parameter
			$error = 'Factory configuration file is missing required "db_table" parameter for the Storage category';
			throw new Initialization($error);
		}

		// use this object as the session handler
		session_set_save_handler(
			array($this, 'sessionOpen'),
			array($this, 'sessionClose'),
			array($this, 'sessionRead'),
			array($this, 'sessionWrite'),
			array($this, 'sessionDestroy'),
			array($this, 'sessionGC')
		);
	}

	/**
	 * Close a session.
	 *
	 * @return     bool true, if the session was closed, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function sessionClose()
	{
		if($this->resource) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Destroy a session.
	 *
	 * @param      string A session ID.
	 *
	 * @return     bool true, if the session was destroyed, otherwise an
	 *                  exception is thrown.
	 *
	 * @throws     Exceptions\Database If the session cannot be 
	 *                                           destroyed.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function sessionDestroy($id)
	{
		if(!$this->resource) {
			return false;
		}
		
		// get table/column
		$db_table  = $this->getParameter('db_table');
		$db_id_col = $this->getParameter('db_id_col', 'sess_id');

		// cleanup the session id, just in case
		$id = addslashes($id);

		// delete the record associated with this id
		$sql = sprintf("DELETE FROM %s WHERE %s = '%s'", $db_table, $db_id_col, $id);

		if(@pg_query($this->resource, $sql)) {
			return true;
		}

		// failed to destroy session
		$error = 'PostgreSQLSessionStorage cannot destroy session id "%s", error reported by server: "%s"';
		$error = sprintf($error, $id, pg_last_error($this->resource));
		throw new Database($error);
	}

	/**
	 * Cleanup old sessions.
	 *
	 * @param      int The lifetime of a session.
	 *
	 * @return     bool true, if old sessions have been cleaned, otherwise an
	 *                  exception is thrown.
	 *
	 * @throws     Exceptions\Database If old sessions cannot be 
	 *                                           cleaned.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function sessionGC($lifetime)
	{
		if(!$this->resource) {
			return false;
		}
		
		// determine deletable session time
		$time = time() - $lifetime;

		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		$ts = date($this->getParameter('date_format', 'U'), $time);
		if(is_numeric($ts)) {
			$ts = (int)$ts;
		} else {
			$ts = "'" . addslashes($ts) . "'";
		}
		
		// delete the records that are expired
		$sql = sprintf("DELETE FROM %s WHERE %s < %s", $db_table, $db_time_col, $ts);

		if(@pg_query($this->resource, $sql)) {
			return true;
		}

		// failed to cleanup old sessions
		$error = 'PostgreSQLSessionStorage cannot delete old sessions, error reported by server: "%s"';
		$error = sprintf($error, pg_last_error($this->resource));
		throw new Database($error);
	}

	/**
	 * Open a session.
	 *
	 * @param      string The path is ignored.
	 * @param      string The name is ignored.
	 *
	 * @return     bool true, if the session was opened, otherwise an exception
	 *                  is thrown.
	 *
	 * @throws     Exceptions\Database If a connection with the database
	 *                                           does not exist or cannot be
	 *                                           created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function sessionOpen($path, $name)
	{
		// what database are we using?
		$database = $this->getParameter('database', null);

		// get the database resource
		$this->resource = $this->getContext()->getDatabaseManager()->getDatabase($database)->getResource();

		return true;
	}

	/**
	 * Read a session.
	 *
	 * @param      string A session ID.
	 *
	 * @return     bool true, if the session was read, otherwise an exception is
	 *                  thrown.
	 *
	 * @throws     Exceptions\Database If the session cannot be read.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function sessionRead($id)
	{
		if(!$this->resource) {
			return false;
		}
		
		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		// cleanup the session id, just in case
		$id = addslashes($id);

		// retrieve the record associated with this id
		$sql = sprintf("SELECT %s FROM %s WHERE %s = '%s'", $db_data_col, $db_table, $db_id_col, $id);

		$result = @pg_query($this->resource, $sql);

		if($result != false && @pg_num_rows($result) == 1) {
			// found the session
			$data = pg_fetch_row($result);
			return $data[0];
		} else {
			return '';
		}
	}

	/**
	 * Write session data.
	 *
	 * @param      string A session ID.
	 * @param      string A serialized chunk of session data.
	 *
	 * @return     bool true, if the session was written, otherwise an exception
	 *                  is thrown.
	 *
	 * @throws     Exceptions\Database If the session data cannot be 
	 *                                           written.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function sessionWrite($id, &$data)
	{
		if(!$this->resource) {
			return false;
		}
		
		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		// cleanup the session id and data, just in case
		$id   = addslashes($id);
		$data = addslashes($data);

		$ts = date($this->getParameter('date_format', 'U'));
		if(is_numeric($ts)) {
			$ts = (int)$ts;
		} else {
			$ts = "'" . addslashes($ts) . "'";
		}

		// delete the record associated with this id
		$sql = sprintf(
			"UPDATE %s SET %s = '%s', %s = %s WHERE %s = '%s'",
			$db_table,
			$db_data_col,
			$data,
			$db_time_col,
			$ts,
			$db_id_col,
			$id
		);

		$result = @pg_query($this->resource, $sql);
		if($result !== false && pg_affected_rows($result)) {
			return true;
		} elseif($result !== false) {
			// session does not exist, create it
			$sql = sprintf(
				"INSERT INTO %s (%s, %s, %s) VALUES ('%s', '%s', %s)",
				$db_table,
				$db_id_col,
				$db_data_col,
				$db_time_col,
				$id,
				$data,
				$ts
			);

			if(@pg_query($this->resource, $sql)) {
				return true;
			}

			// can't create record
			$error = 'PostgreSQLSessionStorage cannot create new record for id "%s", error reported by server: "%s"';
			$error = sprintf($error, $id, pg_last_error($this->resource));
			throw new Database($error);
		}
		
		// failed to write session data
		$error = 'PostgreSQLSessionStorage cannot write session data for id "%s", error reported by server: "%s"';
		$error = sprintf($error, $id, pg_last_error($this->resource));
		throw new Database($error);
	}
}

?>
