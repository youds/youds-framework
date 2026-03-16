<?php
namespace Defaults\Core\Chains\DefaultContent\WebSockets\LiveContent;
use DataHolder;
use YoudsFramework\Action\Base as YoudsFrameworkAction;


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
class Action extends YoudsFrameworkAction
{
	
	/**
	 * This method is included to return the next stage of the execution chain, ie. the name 
	 * of the Layout name. This method should *not* contain any logic!
	 *
	 * @return	 mixed - A string containing the layout name associated with this chain
	 *				   - An array with two indices:
	 *						 0. The parent content of the layout that will be executed.
	 *						 1. The layout that will be executed.
	 *
	 */
	public function getDefaultLayoutName()
	{
		// this method should *not* contain any logic
		return 'Success';
	}
	
	public function execute($rd)
	{
		
		// get WebSockets Server
		$ws = $this->getWebSockets()->getServer();
		
		// output
		echo sprintf('---Server Started (Live Content) %s--------' . PHP_EOL, date('d-m-Y \a\t h:i:s'));
		
		// handle new connection 
		$ws->connected = function ($clients, $conn) {
			echo sprintf('Client Connected (%d) - Total Connections %d' . PHP_EOL, $conn->resourceId, $clients->count());
		};

		// handle disconnect
		$ws->disconnected = function ($clients, $conn) {
			echo sprintf('Client Disconnected (%s)' . PHP_EOL, $conn->resourceId);
		};

		// erroneous function
		$ws->error = function ($clients, $conn, $e) { 
			echo $e->getMessage();
		};

		// new message function
		$ws->message = function ($clients, $from, $message) { 
			
			preg_match('/Authentication:\s"([\w\d]{32})"\sUsername:\s"([\w\d]+)"\sAction: "([\w\d\:]+)"\sData: ([\w\d\W]+)\sValue:\s(.+)?/', $message, $matches);
					
			if (isset($matches[1]))
				$token = $matches[1];
			if (isset($matches[2]))
				$name = $matches[2];
			if (isset($matches[3]))
				$action = $matches[3];
			if (isset($matches[4]))
				$data = json_decode($matches[4]);
			if (isset($matches[5]))
				$value = json_decode($matches[5]);
		
			if (isset($token) && isset($name) && ($token == md5(Config::get('core.token') . $name)) && isset($action) && isset($data) && isset($value)):
				
				// authorise the username
				$this->getUser()->setAuthenticated(true);
				//$this->getUser()->clearCredentials();
				//$this->getUser()->grantRoles(array('player'));
				
				// set username values
				if (class_exists('User')):
					$user = User::where('name', $name);
					$this->getUser()->setAttribute('user', $user);
				else:
					$this->getUser()->setAttribute('user', array('name' => $name));
				endif;
				
				switch ($action):
					case 'auth':
						if (isset($token)):
							$this->sessions[$token][$from->resourceId] = $from;
						endif;
						break;
					default:
			
						// seperate name for chain..
						$direction = substr($action, strpos($action, '::') + 2);
						$module = substr($action, 0, strpos($action, ':'));
						$chain = substr($action, strpos($action, ':') + 1, strpos($action, '::') - 1 - strlen($module));
						
						// combine values
						if (!is_array($value) && !is_object($value))
							$data = array_merge((array) $data, array($value));
						else
							$data = array_merge((array) $data, (array) $value);
						
						// perform chain execution				
						$result = $this->forward($module, $chain, $data, 'text', 'write');
						
						foreach ($this->sessions[$token] as $session):
							if ($session !== $from)
								$session->send($action . ' ' . $result);
						endforeach;
						break;
				endswitch;
			endif;
				
			
		};

		// run the server
		$this->getWebSockets()->startServer($ws, 8043);
		
		return 'Success';
	}

//	public function executeWrite($rd)
//	{
//		return 'Success';
//	}

}

?>
