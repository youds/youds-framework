<?php
namespace Defaults\Core\Chains\Auth\VerifyLogin;
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
	
	public function executeWrite ($rd) {
		
		// get integrations
		$it = $this->getContext()->getIntegrations();
		
		// recaptcha
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,'https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
		            sprintf('secret=%s&response=%s', 
						'6LcmUTIfAAAAADckgJ5esVbvlZgOWczaCrfmH6be', 
						$rd->getParameter('g-recaptcha-response')
					)
				);

		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$googleApi = json_decode(curl_exec($ch));

		curl_close ($ch);
		
		// check for valid captcha
		if ($googleApi->success == true):
	
				// first grab the user
				$user = $this->getContext()->getUser()->getAttribute('user');
			
				// create database entry
				$userModel = User::find($user['id']);
				$userModel->lastLogin = date('Y-m-d H:i:s');
				$userModel->save();
				
				$this->getContext()->getUser()->setAuthenticated(true);
				$this->getContext()->getUser()->setAttribute('user', $user);
				
				return 'Success';
		else:
			return 'Input';
		endif;
	}
	public function execute ($rd) {

		return 'Input';
	}
	
	public function handleError($rd) {
		
		return 'Input';
	}
}

?>
