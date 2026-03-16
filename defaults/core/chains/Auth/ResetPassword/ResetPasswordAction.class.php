<?php
namespace Defaults\Core\Chains\Auth\ResetPassword;
use Defaults\Core\Common\Base\Action as Project;
use YoudsFramework\Request\DataHolder;
use YoudsFramework\Config;
use Defaults\Core\Models\Orm\Auth\User;

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
		
		// fetch user based on code
		$user = User::where('forgotPassword', $rd->getParameter('code'))->first();
		
		if ($user):
			
			// password
			$user->password = sha1($rd->getParameter('password'));
			$user->forgotPassword = NULL;
			$user->forgotPasswordTime = NULL;
			$user->save();
		
			// log user in
			$this->getUser()->setAuthenticated(true);
			$this->getUser()->clearCredentials();
			$this->getUser()->grantRoles(array('user'));
		
			if ($user->admin == 1)
				$this->getUser()->grantRoles(array('admin'));
		
			// store in session
			$this->getUser()->setAttribute('user', array(
				'id' => $user->id,
				'email' => $user->email,
				'verified' => $user->verified,
			));
		
			// save last login time
			$user->lastLogin = date('Y-m-d H:i:s');
			$user->save();
		
			return 'Success';
		else:
			return 'Error';
		endif;
	}
	
	public function execute ($rd) {
		
		// fetch user based on code
		$user = User::where('forgotPassword', $rd->getParameter('code'))->first();

		// check code is not over 24 hours old
		if ($user != NULL && strtotime($user['forgotPasswordTime']) > (time() - (60 * 60 * 24))):
			
			// store code in session
			$this->getContext()->getUser()->setAttribute('code', $rd->getParameter('code'));
			return 'Input';
		else:
			return 'Error';
		endif;
		
	}
	
	public function handleError ($rd) {

		return 'Error';
	}
}

?>
