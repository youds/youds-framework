<?php
namespace Defaults\Core\Chains\Testing\Chain;
use YoudsFramework\Core\Common\Base\Action as Project;
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
        echo 'Running Chain';

        $content = $rd->getParameter('content');
        $chain = $rd->getParameter('chain');
        $input = $rd->getParameter('input');

        // Convert [key1 => value1, key2 => value2] format to PHP array
        if ($input && preg_match('/^\[(.*)\]$/', $input, $outer_matches)) {
            $pairs = explode(',', $outer_matches[1]);
            $input = [];
            foreach ($pairs as $pair) {
                if (preg_match('/^\s*(.*?)\s*=>\s*(.*?)\s*$/', $pair, $matches)) {
                    $input[trim($matches[1])] = trim($matches[2]);
                }
            }
        }

        $chainOutput = $this->runChain($content, $chain, $input);

		return 'Success';
	}
	
	public static function delTree($dir) {

		$files = array_diff(glob($dir . '/AbstractModels/Abstract*.php'), array('.','..'));

		foreach ($files as $file) {
		  (is_dir($dir . '/' . $file)) ? self::delTree($dir . '/' . $file) : (is_file($dir . '/' . $file)?unlink($dir . '/' . $file): false);
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

}

?>
