<?php
namespace Defaults\Core\Chains\Migrations\Run;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as DB;
use PHPUnit\TextUI\Command as PhpUnitCommand;
use Defaults\Core\Common\Base\Action as Project;
use YoudsFramework\Exceptions\Configuration;
use YoudsFramework\Exceptions\Exception;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Config;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.						 |
// | Copyright (c) 2022 the Youds Framework Project.						   |
// |																		   |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code.						  |
// +---------------------------------------------------------------------------+

/**
 * This file operates on pre-determined methods; including execute, executeWrite executeConsole, 
 * executeJson and so on. For instance, when handling POST requests you would be expected to have 
 * an executeWrite method; or else the request won’t execute. Alternatively, an execute method 
 * would match all read requests.
 *
 * There are other methods that might either be generic or specific to a request method. These 
 * are: registerValidators() and register*Validators(), validate() and validate*(), handleError() and handle*Error()
 * 
 * For help and assistance please use the board at http://framework.youds.com/board
 */
class Action extends Project
{
	public function isSimple() {
		return false;
	}

	public function execute($rd)
	{

        if (Config::get('core.use_database') !== true)
            throw new Exception('Database is not enabled. Please enable it in the settings.xml config file.');

		// get the database connection
		$db = $this->getContext()->getDatabaseManager()->getDatabase();

		// now the source and dest variables
		if ($rd->getParameter('defaults')):
			$schema = Toolkit::expandDirectives(Config::get('core.defaults_dir') . '/App/Migrations/database/migrations/*.php');
			$models = Toolkit::expandDirectives(Config::get('core.defaults_dir') . '/App/Migrations/Core/Models/Orm/*/*.php');
			$dest = Config::get('core.defaults_dir');
		else:
			$schema = Toolkit::expandDirectives($db->getParameter('migrations.schema_dir'));
			$models = Toolkit::expandDirectives($db->getParameter('migrations.model_dir'));
			$dest = Config::get('core.model_dir');
		endif;
		
		// validation
		if ($schema == NULL || !glob($schema)):
			throw new Configuration(sprintf('migrations:run expects "migrations.schema_dir" to be a valid folder set in "databases.xml", found "%s"', $schema));
		endif;
		if ($models == NULL || !glob($models)):
			throw new Configuration(sprintf('migrations:run expects "migrations.model_dir" to be a valid folder set in "databases.xml", found "%s"', $models));
		endif;
		if ($dest == NULL || !is_dir($dest)):
			throw new Configuration('migrations:run expects "' . ($rd->getParameter('defaults')?sprintf('%s/Core/Models/Orm', Config::get('core.defaults_dir')):'core.model_dir') . '" to be set and pointing to a valid folder location');
		endif;
 		foreach (glob($models) as $filename):
			$filename = dirname($filename);

			foreach (
				$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($filename, \RecursiveDirectoryIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::SELF_FIRST) as $item) {

				if ($item->isDir() && !is_dir($dest)) {
					mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
				} else if (!$item->isDir()) {

					// decipher file
					$internalPath = str_replace('app/migrations/', '', (string)$item);
					$internalPath = str_replace('Core/Models/', '', $internalPath);
					if ($rd->getParameter('defaults'))
						$file = $dest . (str_replace(Config::get('core.defaults_dir'), '',  $internalPath));
					else
						$file = $dest . (str_replace(Config::get('core.root_dir'), '',  $internalPath));


					// check for existence of "permanent" model
					$permanentFileFrom  = str_replace('/Abstract', '/', str_replace('AbstractModels/', '/', (string) $item));
					$permanentFile = str_replace('/Abstract', '/', str_replace('AbstractModels/', '/', $file));

					// permanent file
					if (!is_file($permanentFile)):
						if (!is_dir(dirname($permanentFile)))
							mkdir(dirname($permanentFile), 0755);
						copy($permanentFileFrom, $permanentFile);

						// check for defaults, if so, then amend with "Defaults\"
						if ($rd->getParameter('defaults')):
							file_put_contents($permanentFile, str_replace('namespace ', 'namespace Defaults\\', file_get_contents($permanentFile)));
							file_put_contents($permanentFile, str_replace('extends ', 'extends \Defaults', file_get_contents($permanentFile)));
						endif;
					endif;

					// create or recreate the file if it exists (honours our layout)
					if (strstr($file, 'AbstractModels')):
						if (is_file($item)):
							self::delTree($item);
						endif;

						if (!is_dir(dirname($file)))
							mkdir(dirname($file), 0755);
						copy($item, $file);
						if ($rd->getParameter('defaults'))
							file_put_contents($file, str_replace('namespace ', 'namespace Defaults\\', file_get_contents($file)));
					endif;
				}
			}
		endforeach;

		// we are using native PDO due to lack of Laravel-esque solution
		if (!$db->getParameter('socket'))
			$dsn = sprintf('%s:host=%s;dbname=%s;charset=UTF8mb4;', $db->getParameter('driver'), $db->getParameter('host') . ($db->getParameter('port')?sprintf(';port=%s', $db->getParameter('port')):NULL), $db->getParameter('database'));
		else
			$dsn = sprintf('%s:unix_socket=%s;dbname=%s;charset=UTF8mb4;', $db->getParameter('driver'), $db->getParameter('socket'), $db->getParameter('database'));

		// delete old database
		$pdo = new \PDO($dsn, $db->getParameter('user'), $db->getParameter('password'));
		$retVal1 = $pdo->exec('drop database if exists ' . $db->getParameter('database'));
		$retVal2 = $pdo->exec('create database ' . $db->getParameter('database'));

		// loop the migrations
		foreach (glob($schema) as $schemaFilename):
			include_once($schemaFilename);

			$class = 'SkipperMigrations' . str_replace('_', '', substr($schemaFilename, strpos($schemaFilename, 'skipper_migrations_') + 19, -4));

			// recreate the schema
			$migration = new $class();
			$migration->up();
		endforeach;

		/* Automation */
		if ($rd->getParameter('content') && $rd->getParameter('chain') && $rd->getParameter('content') !== 'migrations' && $rd->getParameter('chain') !== 'run'):
			$chainOutput = $this->runChain($rd->getParameter('content'), $rd->getParameter('chain'));

			printf('--Chain Output-----%s%s%s', PHP_EOL, $chainOutput, PHP_EOL . PHP_EOL);
		endif;
		
		return 'Success';
	}
	
	public static function delTree($dir) {

		$files = array_diff(glob($dir . '/AbstractModels/Abstract*.php'), array('.','..'));

		foreach ($files as $file) {

		  (is_dir($dir . '/' . $file)) ? self::delTree($dir . '/' . $file) : unlink($dir . '/' . $file);

		}
		
		if (is_dir($dir) && (count(scandir($dir)) == 2))
			return rmdir($dir);
		else
			return true;

	}

//	public function executeWrite($rd)
//	{
//		return 'Success';
//	}
	public function handleError($rd)
	{
		return 'Error';
	}
}

?>
