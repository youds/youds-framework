<?php
namespace Defaults\Core\Chains\Auth\Account;
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
	public function getDefaultLayoutName() {
		return 'Success';
	}
	
	public function isSecure() {
		return true;
	}

	public function executeWrite ($rd) {
				
		// setup our user
		$this->setAttribute('user', $this->getUser()->getAttribute('user'));
		
		return 'Success';
	}
	public function execute ($rd) {

		// setup our user
		$this->setAttribute('user', $this->getUser()->getAttribute('user'));

		return 'Success';
	}
	
}

?>
