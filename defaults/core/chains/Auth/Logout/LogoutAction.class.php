<?php
namespace Defaults\Core\Chains\Auth\Logout;
use Defaults\Core\Common\Base\Action as Project;
use YoudsFramework\Request\DataHolder;
use YoudsFramework\Config;


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
	
	public function execute ($rd) {
		$this->getContext()->getUser()->setAuthenticated(false);
		
		return 'Success';
	}

}

?>
