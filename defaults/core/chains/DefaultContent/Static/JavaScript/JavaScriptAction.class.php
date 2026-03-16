<?php
namespace Defaults\Core\Chains\DefaultContent\Static\JavaScript;
use Defaults\Core\Common\Base\Action as Project;

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
		$user = $this->getUser()->getAttribute('user');
		
		if ($this->getUser()->isAuthenticated()):
			$this->setAttributes($this->getUser()->getAttribute('user'));
		
			$user = $this->getUser()->getAttribute('user');
			$this->setAttribute('user', $user);
			$salt = Config::get('core.websockets'); // salt
            if (isset($user['name'])):
			    $this->setAttribute('token', md5($user['name'] . $salt));
                $this->setAttribute('private', md5(User::where('name', $user['name'])->first()->id . $salt));
            endif;
		endif;
		
		return 'Success';
	}

//	public function executeWrite($rd)
//	{
//		return 'Success';
//	}

}

?>