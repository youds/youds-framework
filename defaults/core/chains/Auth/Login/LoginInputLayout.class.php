<?php
namespace Defaults\Core\Chains\Auth\Login;
use Defaults\Core\Common\Base\Layout as Project;

class Input extends Project
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
		if ($this->isAjax())
			$this->setupHtml($rd, 'inner');
		else
			$this->setupHtml($rd, 'outer');

		$this->setAttribute('title', 'Login');
		
		if ($this->getAttribute('continue') == 'game')
			return $this->runChain('Users', 'Account');
			
	}

}

?>
