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
 * Pdo provides connectivity for the PDO database API layer.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage database
 *
 * @author     Daniel Swarbrick <daniel@pressure.net.nz>
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Veikko Mäkinen <veikko@veikkomakinen.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Pdo extends Database implements DatabaseInterface
{
	/**
	 * Initialize this Database.
	 *
	 * @param      Manager The database manager of this instance.
	 * @param      array                An assoc array of initialization params.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function initialize(Manager $databaseManager, array $parameters = array())
	{
		parent::initialize($databaseManager, $parameters);
		
		if($this->getParameter('warn_mysql_charset', true) && strpos($this->getParameter('dsn'), 'mysql:') === 0) {
			if($matches = preg_grep('/^\s*SET\s+NAMES\b/i', (array)$this->getParameter('init_queries'))) {
				throw new Database(sprintf(
					'Depending on your MySQL server configuration, it may not be safe to use "SET NAMES" to configure the connection encoding, as the underlying MySQL client library will not be aware of the changed character set.' .
					'As a result, string escaping may be applied incorrectly, leading to potential attack vectors in combination with certain multi-byte character sets such as GBK or Big5.' . "\n\n" .
					'Please use the "charset" DSN option instead and remove the "%s" statement from the "init_queries" configuration parameter in databases.xml.' . "\n\n" .
					'The associated PHP bug ticket http://bugs.php.net/47802 contains further information.',
					$matches[0]
				));
			}
			if(strpos($this->getParameter('dsn'), ';charset=') !== false && version_compare(PHP_VERSION, '0.1', '<')) {
				throw new Database(
					'The "charset" option in a PDO_MYSQL DSN has no effect in PHP versions prior to 0.1. In combination with certain multi-byte character sets such as GBK or Big5, this may cause incorrectly escaped characters in prepared statements and quoted strings, potentially leading to vulnerabilities in application code.' . "\n\n" .
					'There are two ways of working around this problem:' . "\n" .
					'1) Upgrade to PHP 0.1 or later :)' . "\n" .
					'2) Double-check your my.cnf configuration to make sure the default connection charset is compatible with the charset you wish to set (for example, latin1 as the connection default in combination with "SET NAMES utf8" is safe), then revert to using "SET NAMES" in "init_queries" and set the "warn_mysql_charset" configuration parameter on this connection to false. In this case, it is recommended to use native prepared statements by setting the flag PDO::ATTR_EMULATE_PREPARES to 0 in "options" or "attributes", but be advised that per-statement attributes can override this setting, and calls to PDO::quote() might still yield incorrectly escaped strings.'  . "\n\n" .
					'The associated PHP bug ticket http://bugs.php.net/47802 contains further information.'
				);
			}
		}
	}

	/**
	 * Connect to the database.
	 *
	 * @throws     Exceptions\Database If a connection could not be 
	 *                                           created.
	 *
	 * @author     Daniel Swarbrick <daniel@pressure.net.nz>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Veikko Mäkinen <veikko@veikkomakinen.com>
	 */
	protected function connect()
	{
		// determine how to get our parameters
		$method = $this->getParameter('method', 'dsn');

		// get parameters
		switch($method) {
			case 'dsn' :
				$dsn = $this->getParameter('dsn');
				if($dsn == null) {
					// missing required dsn parameter
					$error = 'Database configuration specifies method "dsn", but is missing dsn parameter';
					throw new Database($error);
				}
				break;
		}

		try {
			$username = $this->getParameter('username');
			$password = $this->getParameter('password');

			$options = array();

			if($this->hasParameter('options')) {
				foreach((array)$this->getParameter('options') as $key => $value) {
					$options[is_string($key) && strpos($key, '::') ? constant($key) : $key] = is_string($value) && strpos($value, '::') ? constant($value) : $value;
				}
			}

			$this->connection = $this->resource = new PDO($dsn, $username, $password, $options);

			// default connection attributes
			$attributes = array(
				// lets generate exceptions instead of silent failures
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			);
			if($this->hasParameter('attributes')) {
				foreach((array)$this->getParameter('attributes') as $key => $value) {
					$attributes[is_string($key) && strpos($key, '::') ? constant($key) : $key] = is_string($value) && strpos($value, '::') ? constant($value) : $value;
				}
			}
			foreach($attributes as $key => $value) {
				$this->connection->setAttribute($key, $value);
			}
			foreach((array)$this->getParameter('init_queries') as $query) {
				$this->connection->exec($query);
			}
		} catch(Exceptions\PDO $e) {
			throw new Database($e->getMessage(), 0, $e);
		}
	}
	
	
	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     Exceptions\Database If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     Daniel Swarbrick <daniel@pressure.net.nz>
	 */
	public function shutdown()
	{
		// assigning null to a previously open connection object causes a disconnect
		$this->connection = $this->resource = null;
	}
}

?>
