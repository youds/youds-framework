<?php
namespace Defaults\Core\Chains\Auth\VerifyRegister;
use Defaults\Core\Common\Base\Action as Project;
use YoudsFramework\Config;
use Defaults\Core\Models\Orm\Auth\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use YoudsFramework\Exceptions\Configuration;

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

        // check for logged in user
		if ($this->getUser()->isAuthenticated())
			return 'Authenticated';
		
		// first grab the user
		$user = $this->getUser()->getAttribute('user');
		
		// get integrations
		$it = $this->getIntegrations();
			
		// recaptcha
		$googleApi = $it->recaptcha($rd->getParameter('g-recaptcha-response'));

		// check for valid captcha
		if (is_object($googleApi) && $googleApi->success == true):

			// process verify if data available
			if ($rd->getParameter('code') != NULL):

				// now the code
				$code = $rd->getParameter('code');

				// verify code
				if ($code == $user['verifyCode']):

					// create database entry
					$userModel = new User();
					$userModel->email = $user['email'];
					$userModel->password = sha1($user['password']);
					$userModel->verified = true;
					$userModel->verifiedMethod = 'email';
					$userModel->verifiedTime = date('Y-m-d H:i:s');
					$userModel->save();

					// log user in
					$this->getUser()->setAuthenticated(true);
					$this->getUser()->clearCredentials();
					$this->getUser()->grantRoles(array('user'));

					// mark as verified in session
					$user['verified'] = true;
					$user['verifiedTime'] = date('Y-m-d H:i:s');
					$user['id'] = $userModel->id;

					$this->getUser()->setAttribute('user', $user);
					$this->setAttribute('user', $user);

					return 'Success';
				else:
					return 'Input';
				endif;
			else:
				return 'Input';
			endif;
		elseif (strlen($rd->getParameter('code')) < 16):

            // grab the user
			$user = $this->getUser()->getAttribute('user');

			// next set the code
			if (!isset($user['verifyCode']) || $user['verifyCode'] == NULL)
				$user['verifyCode'] = md5(uniqid() . rand(99999999, 9999999999));

			// message
			$siteName  = Config::get('core.name');
			$siteUrl = $this->getRouting()->getBaseHref();
			$siteCodeUrl = str_replace($this->getRouting()->getBasePath(), '', $siteUrl) . $this->getRouting()->gen('users.register.verify.email', array('code' => $user['verifyCode']));

            $message = sprintf(<<<MAIL
Hi!

Thank you for registering with $siteName.

Your Verification Code is: %s 

Click here to proceed: $siteCodeUrl

$siteName
$siteUrl

MAIL, $user['verifyCode'], $user['verifyCode']);
            $mail = new PHPMailer(true);

            try {
				$host = Config::get('email.support.host');
				$port = Config::get('email.support.port');
				$username = Config::get('email.support.username');
				$password = Config::get('email.support.password');
				$sender = Config::get('email.support.sender');
				$from = Config::get('email.support.from') ?? $sender;
				$encryption = Config::get('email.support.encryption') ?? PHPMailer::ENCRYPTION_STARTTLS;

				if (!$host || !$port || !$username || !$password || !$sender)
					throw new Configuration('Mail configuration incomplete, email not set in `integrations.xml`. Account `support` required.');

                $mail->isSMTP();
                $mail->Host = $host;
                $mail->SMTPAuth   = true;  // Enable SMTP authentication
                $mail->Username   = $username; // Your email/username
                $mail->Password   = $password;  // Your SMTP password
                $mail->SMTPSecure = $encryption; // Encryption: STARTTLS or SSL
                $mail->Port       = $port;

                $mail->setFrom($sender, $from);
                $mail->addAddress($user['email']);
                $mail->Subject = sprintf('%s verification code', $siteName);
                $mail->Body = $message;

                $mail->send();
            } catch (Exception $e) {
                echo "Error: {$mail->ErrorInfo}";
            }

			// store data (with code) for verification
			$this->getUser()->setAttribute('user', $user);

            return 'Input';
		endif;

		$this->setAttribute('code', $rd->getParameter('code'));
		return 'Input';
	}
	
	
	
	public function handleError($rd) {
		
		return 'Input';
	}
}

?>
