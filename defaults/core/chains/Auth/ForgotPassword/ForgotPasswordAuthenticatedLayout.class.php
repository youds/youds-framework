<?php
namespace Defaults\Core\Chains\Auth\ForgotPassword;
use Defaults\Core\Common\Base\Layout as Project;
use YoudsFramework\Request\DataHolder;
use YoudsFramework\Config;


class Authenticated extends Project
{
	

	/**
	 * Handles the Html output type.
	 *
	 * @parameter  RequestDataHolder the (validated) request data
	 *
	 * @return     mixed <ul>
	 *                     <li>An ExecutionContainer to forward the execution to or</li>
	 *                     <li>Any other type will be set as the response content.</li>
	 *                   </ul>
	 */
	public function executeUsers($rd)
	{

		return $this->runChain('Users', 'Account');

	}

}

?>
