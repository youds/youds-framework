<?php
namespace Defaults\Core\Chains\Testing\Run;
use Defaults\Core\Common\Base\Action as Project;
use YoudsFramework\Config\Cache;
use YoudsFramework\Testing\PhpUnitCli;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Config;
use YoudsFramework\Exceptions\Exception;


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

		// load cache
		$testing = Cache::load(Config::get('core.config_dir') . '/testing.xml');

		// run migrations?
		if ($rd->getParameter('database')):
			$this->runChain('Migrations', 'Run', array('content' => Config::get('chains.automation_content'), 'chain' => Config::get('chains.automation_chain')));
		endif;

		// require the testing platform
		require(Config::get('core.src_dir') . '/testing.php');
		if (is_file(Config::get('core.root_dir') . '/repodeps.php'))
			include_once(Config::get('core.root_dir') . '/repodeps.php');
			
		// allow cli to configure the base directory
		if ($rd->getParameter('defaults')):
			Config::set('testing.directory', Config::get('core.defaults_testing_dir'));
			Config::set(sprintf('testing.suites.%s.defaults', Config::get('testing.suites.default') ?? 'default'), Config::get('core.defaults_testing_dir') . '/*/*/*.php');
		elseif ($rd->getParameter('base')):
			Config::set('testing.directory', Config::get('core.testing_dir') . '/' . $rd->getParameter('base'));
        else:
            Config::set('testing.directory', Config::get('core.testing_dir'));
		endif;

        // allow cli to choose the hook
		if ($rd->getParameter('hooks') && !is_bool($rd->getParameter('hooks')))
			$hook = $rd->getParameter('hooks');
		else
			$hook = Config::get('testing.hooks.default');
		
		$hookType = Config::get('testing.hooks.' . $hook . '.type');
		switch ($hookType):
			case 'chain':
		
				// assign variables
				$content = Config::get('testing.hooks.' . $hook . '.chain.content');
				$name = Config::get('testing.hooks.' . $hook . '.chain.name');
			
				// call chain				
				$chainOutput = $this->runChain($content, $name);
				
				printf('--Chain Output-----%s%s%s', PHP_EOL, $chainOutput, PHP_EOL . PHP_EOL);
				
				break;
		
			case 'file':
				$file = Config::get('testing.hooks.' . $hook . '.file');
				if (is_file($file)):
					$file = Toolkit::expandDirectives($file);
				else:
					throw new Exception(sprintf('Unable to include file "%s" from "%s" hook as it does not exist', $file, $hook));
				endif;
				include($file);
			break;
		endswitch;
		
		// suites

		if ($rd->getParameter('suites') && $rd->getParameter('suites') != 'enabled'):
			Config::set('testing.suites.default', $rd->getParameter('suites'));
			dump(Config::get('testing.suites.default'));
		endif;

		// tests
		$phpunit = new PhpUnitCli();
		$phpunit->dispatch([]);
		
		return 'Success';
	}


//	public function executeWrite($rd)
//	{
//		return 'Success';
//	}

}

?>
