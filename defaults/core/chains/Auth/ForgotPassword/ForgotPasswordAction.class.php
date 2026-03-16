<?php
namespace Defaults\Core\Chains\Auth\ForgotPassword;
use Defaults\Core\Common\Base\Action as Project;
use Defaults\Core\Models\Orm\Auth\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use YoudsFramework\Config;
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
	
	public function executeWrite ($rd) {

		$user = User::where('email', $rd->getParameter('email'))->first();

		if ($user != NULL):
			$siteName  = Config::get('core.name');
			$code = md5(uniqid() . rand(100000000, 9999999999));
			$siteUrl = $this->getRouting()->getBaseHref();
			$siteCodeUrl = str_replace($this->getRouting()->getBasePath(), '', $siteUrl)  . $this->getRouting()->gen('users.resetpassword', array('code' => $code));
            $message = sprintf(<<<MAIL
Hi!

We received a forgot password request for $siteName.

If you recognise this request, please go to: $siteCodeUrl

The code for this request is: %s 

$siteName
$siteUrl
MAIL, $code);
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
				$mail->Subject = sprintf('%s Forgot Password', $siteName);
				$mail->Body = $message;

                $mail->send();

            } catch (Exception $e) {
                echo "Error: {$mail->ErrorInfo}";
            }			
			// store code for retrieval
			$user->forgotPassword = $code;
			$user->forgotPasswordTime = date('Y-m-d H:i:s');
			$user->save();
		endif;
		return 'Success';
	}
	
	public function execute ($rd) {

		// check for logged in user
		if ($this->getUser()->isAuthenticated())
			return 'Authenticated';


		return 'Input';
	}

}

?>
