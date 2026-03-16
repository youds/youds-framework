<?php
namespace YoudsFramework\Config;
use YoudsFramework\Exceptions\Parse;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * DatabaseHandler allows you to setup database connections in a
 * configuration file that will be created for you automatically upon first
 * request.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class DatabaseHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/databases';
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      XmlDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     Exceptions\Parse If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 */
	public function execute(XmlDomDocument $document)
	{	

		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'databases');
		
		$databases = array();
		$default = null;
		foreach($document->getConfigurationElements() as $configuration) {
			if(!$configuration->hasChildren('databases')) {
				continue;
			}
			
			$databasesElement = $configuration->getChild('databases');
			
			// make sure we have a default database exists
			if(!$databasesElement->hasAttribute('default') && $default === null) {
				// missing default database
				$error = 'Configuration file "%s" must specify a default database configuration';
				$error = sprintf($error, $document->documentURI);

				throw new Parse($error);
			}
			if($databasesElement->hasAttribute('default')) {
				$default = $databasesElement->getAttribute('default');
			}

			// let's do our fancy work
			foreach($configuration->get('databases') as $database) {
				$name = $database->getAttribute('name');

				if(!isset($databases[$name])) {
					$databases[$name] = array('parameters' => array());

					if(!$database->hasAttribute('class')) {
						$error = 'Configuration file "%s" specifies database "%s" with missing class key';
						$error = sprintf($error, $document->documentURI, $name);

						throw new Parse($error);
					}
				}

				$databases[$name]['class'] = $database->hasAttribute('class') ? $database->getAttribute('class') : $databases[$name]['class'];

				$databases[$name]['parameters'] = $database->getParameters($databases[$name]['parameters']);
			}
		}

		if(!$databases) {
			
			// we have no connections
			$error = 'Configuration file "%s" does not contain any database connections.';
			$error = sprintf($error, $document->documentURI);
			throw new Configuration($error);
		}

		$data = array();
		
		if($databases) {

			foreach($databases as $name => $db) {

				switch ($db['class']):
					case 'Database\LaravelOrm':
				
					$params = array(
						'driver'    => $db['parameters']['driver'],
						'host'      => $db['parameters']['host'],
						'database'  => $db['parameters']['database'],
						'username'  => $db['parameters']['user'],
						'password'  => $db['parameters']['password'],
						'charset'   => 'utf8mb4',
						'collation' => 'utf8mb4_general_ci',
						'prefix'    => '',
					);
					if (isset($db['parameters']['socket']))
						$params['unix_socket'] = $db['parameters']['socket'];
					
					$params = var_export($params, true);
				
					$data[] = <<<HEREDOC
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container as Container;				
use Illuminate\Support\Facades\Facade;

\$app = new Container();

// create a new capsule inscance
\$capsule = new Capsule();

\$conn = \$capsule->addConnection($params);

// Make this Capsule instance available globally via static methods... (optional)
\$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
\$capsule->bootEloquent();

\$app->singleton('db.schema', function (\$app) {

	\$conn = \$app['db']->getConnection()->setSchemaGrammar(new \Illuminate\Database\Schema\Grammars\MySqlGrammar());
	return new \Illuminate\Database\Schema\Builder(\$conn);
});

// Register the database manager in the container 
\$app->singleton('db', function(\$app) use (\$capsule) {
	return \$capsule;
});

/**
 * Set \$app as FacadeApplication handler
 */
Facade::setFacadeApplication(\$app);

HEREDOC;
				default:
			
				endswitch;
			
			}

			if(!isset($databases[$default])) {
				$error = 'Configuration file "%s" specifies undefined default database "%s".';
				$error = sprintf($error, $document->documentURI, $default);
				throw new Configuration($error);
			}
			
		}

		foreach($databases as $name => $db) {
			
			// append new data
			$data[] = sprintf('$database = new %s();', $db['class']);
			$data[] = sprintf('$this->databases[%s] = $database;', var_export($name, true));
			$data[] = sprintf('$database->initialize($this, %s);', var_export($db['parameters'], true));
		}

		if(!isset($databases[$default])) {
			$error = 'Configuration file "%s" specifies undefined default database "%s".';
			$error = sprintf($error, $document->documentURI, $default);
			throw new Configuration($error);
		}

		$data[] = sprintf('$this->defaultDatabaseName = %s;', var_export($default, true));
		
		return $this->generate($data, $document->documentURI);
	}
}

?>
