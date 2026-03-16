<?php

namespace YoudsFramework;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Provides support for session storage using a PDO database abstrchain
 * layer.
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
 * # data_as_lob  - [true]      - If true, data is stored as a LOB
 *                                       other wise as a string.
 *                                       (Note: with Oracle LOBs are always
 *                                        used)
 * # date_format  - [U]         - The format string passed to date() to
 *                                       format timestamps. Defaults to "U",
 *                                       which means a Unix Timestamp again.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage storage
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class PdoSessionStorage extends SessionStorage
{
	/**
	 * @var        PDO A Database Connection.
	 */
	protected $connection;

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
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function sessionClose()
	{
		if($this->connection) {
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
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function sessionDestroy($id)
	{
		if(!$this->connection) {
			return false;
		}
		
		// get table/column
		$db_table  = $this->getParameter('db_table');
		$db_id_col = $this->getParameter('db_id_col', 'sess_id');

		// delete the record associated with this id
		$sql = sprintf('DELETE FROM %s WHERE %s = ?', $db_table, $db_id_col);

		try {
			$stmt = $this->connection->prepare($sql);
			$result = $stmt->execute(array($id));
			if(!$result) {
				$errorInfo = $stmt->errorInfo();
				$e = new Exceptions\PDO($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
			return true;
		} catch(Exceptions\PDO $e) {
			$error = sprintf('Exceptions\PDO was thrown when trying to manipulate session data. Message: "%s"', $e->getMessage());
			throw new Database($error, 0, $e);
		}
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
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function sessionGC($lifetime)
	{
		if(!$this->connection) {
			return false;
		}
		
		// determine deletable session time
		$time = time() - $lifetime;
		$time = date($this->getParameter('date_format', 'U'), $time);

		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		// delete the records that are expired
		$sql = sprintf('DELETE FROM %s WHERE %s < :time', $db_table, $db_time_col);

		try {
			$stmt = $this->connection->prepare($sql);
			if(is_numeric($time)) {
				$time = (int)$time;
				$stmt->bindValue(':time', $time, PDO::PARAM_INT);
			} else {
				$stmt->bindValue(':time', $time, PDO::PARAM_STR);
			}
			$result = $stmt->execute();
			
			if(!$result) {
				$errorInfo = $stmt->errorInfo();
				$e = new Exceptions\PDO($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
			
			return true;
		} catch(Exceptions\PDO $e) {
			$error = sprintf('Exceptions\PDO was thrown when trying to manipulate session data. Message: "%s"', $e->getMessage());
			throw new Database($error, 0, $e);
		}
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
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function sessionOpen($path, $name)
	{
		// what database are we using?
		$database = $this->getParameter('database', null);

		$this->connection = $this->getContext()->getDatabaseConnection($database);
		if($this->connection === null || !$this->connection instanceof PDO) {
			$error = 'Database connection "' . $database . '" could not be found or is not a PDO database connection.';
			throw new Database($error);
		}

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
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function sessionRead($id)
	{
		if(!$this->connection) {
			return false;
		}
		
		// get table/columns
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		try {
			$sql = sprintf('SELECT %s FROM %s WHERE %s = ?', $db_data_col, $db_table, $db_id_col);

			$stmt = $this->connection->prepare($sql);
			$result = $stmt->execute(array($id));
			
			if(!$result) {
				$errorInfo = $stmt->errorInfo();
				$e = new Exceptions\PDO($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
			
			if($result = $stmt->fetch(PDO::FETCH_NUM)) {
				$result = $result[0];
				// pdo is returning the LOB as stream, so check if we had a lob (this seems to differ from db to db)
				if(is_resource($result)) {
					$result = stream_get_contents($result);
				}
				return $result;
			}

			return '';
		} catch(Exceptions\PDO $e) {
			$error = sprintf('Exceptions\PDO was thrown when trying to manipulate session data. Message: "%s"', $e->getMessage());
			throw new Database($error, 0, $e);
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
	 * @throws     Exceptions\Database If session data cannot be
	 *                                           written.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function sessionWrite($id, $data)
	{
		if(!$this->connection) {
			return false;
		}
		
		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		$isOracle = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) == 'oracle';
		$useLob = $this->getParameter('data_as_lob', true);
		$columnType = ($isOracle || $useLob) ? PDO::PARAM_LOB : PDO::PARAM_STR;

		if($isOracle) {
			$sp = fopen('php://memory', 'r+');
			fwrite($sp, $data);
			rewind($sp);
		} else {
			$sp = $data;
		}

		$ts = date($this->getParameter('date_format', 'U'));
		if(is_numeric($ts)) {
			$ts = (int)$ts;
		}

		try {
			// pretend the session does not exist and attempt to create it first
			$sql = sprintf('INSERT INTO %s (%s, %s, %s) VALUES (:id, :data, :time)', $db_table, $db_id_col, $db_data_col, $db_time_col);

			$stmt = $this->connection->prepare($sql);
			$stmt->bindParam(':id', $id);
			$stmt->bindParam(':data', $sp, $columnType);
			if(is_int($ts)) {
				$stmt->bindValue(':time', $ts, PDO::PARAM_INT);
			} else {
				$stmt->bindValue(':time', $ts, PDO::PARAM_STR);
			}
			$this->connection->beginTranschain();
			if(!$stmt->execute()) {
				$errorInfo = $stmt->errorInfo();
				$e = new Exceptions\PDO($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
			if(!$this->connection->commit()) {
				$errorInfo = $stmt->errorInfo();
				$e = new Exceptions\PDO($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
		} catch(Exceptions\PDO $e) {
			// something went wrong; probably a key collision, which means this session already exists
			$this->connection->rollback();

			if($isOracle) {
				$sql = sprintf('UPDATE %s SET %s = EMPTY_BLOB(), %s = :time WHERE %s = :id RETURNING %s INTO :data', $db_table, $db_data_col, $db_time_col, $db_id_col, $db_data_col);
			} else {
				$sql = sprintf('UPDATE %s SET %s = :data, %s = :time WHERE %s = :id', $db_table, $db_data_col, $db_time_col, $db_id_col);
			}

			$stmt = $this->connection->prepare($sql);
			$stmt->bindParam(':data', $sp, $columnType);
			if(is_int($ts)) {
				$stmt->bindValue(':time', $ts, PDO::PARAM_INT);
			} else {
				$stmt->bindValue(':time', $ts, PDO::PARAM_STR);
			}
			$stmt->bindParam(':id', $id);
			$this->connection->beginTranschain();
			if(!$stmt->execute()) {
				$errorInfo = $stmt->errorInfo();
				$e = new Exceptions\PDO($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
			if(!$this->connection->commit()) {
				$errorInfo = $stmt->errorInfo();
				$e = new Exceptions\PDO($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
		} catch(Exceptions\PDO $e) {
			$this->connection->rollback();
			$error = sprintf('Exceptions\PDO was thrown when trying to manipulate session data. Message: "%s"', $e->getMessage());
			throw new Database($error, 0, $e);
		}
		
		return true;
	}
}

?>
