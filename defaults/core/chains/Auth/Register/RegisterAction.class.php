<?php
namespace Defaults\Core\Chains\Auth\Register;
use Defaults\Core\Common\Base\Action as Project;

class Action extends Project
{
	
/**
	 * Returns the default layout if the chain does not serve the request
	 * method used.
	 *
	 * @return     mixed <ul>
	 *                     <li>A string containing the layout name associated
	 *                     with this chain; or</li>
	 *                     <li>An array with two indices: the parent content
	 *                     of the layout to be executed and the layout to be
	 *                     executed.</li>
	 *                   </ul>
	 */
	public function getDefaultLayoutName()
	{
		return 'Success';
	}
	
	public function executeWrite ($rd) {

        // check for logged in user
		if ($this->getContext()->getUser()->isAuthenticated())
			return 'Authenticated';
		
		// save user input
		$this->getContext()->getUser()->setAttribute('user', $rd->getParameters());

		// forward layout to verify
		return 'Verify';
		
	}
	public function execute ($rd) {
		
		// check if logged in 
		if ($this->getContext()->getUser()->isAuthenticated())
			return 'Authenticated';

		return 'Input';
	}
	
	public function handleError($rd) {
		
		// check if logged in
		if ($this->getContext()->getUser()->isAuthenticated())
			return 'Authenticated';

		return 'Input';
	}
}

?>
