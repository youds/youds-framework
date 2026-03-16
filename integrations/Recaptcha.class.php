<?php
namespace YoudsFramework\Integrations;
use YoudsFramework\Request\ParameterHolder;
use YoudsFramework\Config;


// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * IntegrationTransit is a gateway providing call, sms, tg and slack 
 * functionality to your code
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage generator
 *
 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Recaptcha extends ParameterHolder
{
	/**
	 * Retrieve recaptcha response
	 *
	 * @return void
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function recaptcha ($code = NULL)
	{

		if (!$code):
		
			// grab the config
			$siteKey = Config::get(sprintf('recaptcha.sitekey'));

			// return code
			return sprintf('<!-- reCaptcha via Youds Framework --><script src="https://www.google.com/recaptcha/api.js" async="true" defer="true"></script><div class="g-recaptcha" data-sitekey="%s"></div>', $siteKey);
		else:
		
			// get config
			$secretKey = Config::get(sprintf('recaptcha.secretkey'));

			// check config present 
			if (strlen($secretKey) <= 0)
				throw new Exception('Could not find secret key for recaptcha');
		
			// request
			$ch = curl_init();
		
			curl_setopt($ch, CURLOPT_URL,'https://www.google.com/recaptcha/api/siteverify');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
			            sprintf('secret=%s&response=%s', 
							$secretKey, 
							$code
						)
					);

			// Receive server response ...
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$googleApi = json_decode(curl_exec($ch));

			curl_close ($ch);

			// return
			return $googleApi;
		endif;
	}
	
}

?>