<?php
namespace Defaults\Core\Chains\Auth\Login;
use Defaults\Core\Common\Base\Action as Project;
use Core\Models\Orm\User;

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
	
	public function executeUsers ($rd) {


		// check for logged in user
		if ($this->getUser()->isAuthenticated())
			return 'Authenticated';

		if ($rd->getParameter('email') && $rd->getParameter('password')):

			// lookup user
			$user = User::where('email', $rd->getParameter('email'))->where('password', sha1($rd->getParameter('password')))->first();

			if ($user != NULL):

				// if not verified send to verification page
				if (!$user->verified)
					return 'VerifyEmail';

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

				$this->setAttribute('error', true);

				return 'Input';
			endif;
		endif;

		return 'Input';
	}
	
	
	public function execute ($rd) {
		
		// check for logged in user
		if ($this->getUser()->isAuthenticated())
			return 'Authenticated';

		return 'Input';
	}
	
	public function handleError($rd) {

		$this->setAttribute('error', true);

		return 'Input';
	}
}

?>
