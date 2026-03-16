<?php
namespace YoudsFramework;
use YoudsFramework\Request\ParameterHolder;
use YoudsFramework\Integrations\Recaptcha;
use YoudsFramework\Integrations\Transit;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Integration provides 2fa, OAuth, Transit and Payment Gateway
 * services. Put simply, with this class you can verify the user identity, send them
 * SMS or WhatsApp messages and also take payment too.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage integrations
 *
 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Integrations extends ParameterHolder
{
	/* Google reCaptcha */
	public $recaptcha;
	
	/* Transit */
	public $transit;
	
	/**
	 * Send an SMS
	 *
	 * @return string Response of message sent
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function sms ($countryCode, $mobile, $message)
	{
		return $this->getTransit()->sms($countryCode, $mobile, $message);
	}
	
	/**
	 * Send a WhatsApp message
	 *
	 * @return string Response of message sent
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function whatsapp ($countryCode, $mobile, $template, $lang, $placeholders, $buttons)
	{
		return $this->getTransit()->whatsapp($countryCode, $mobile, $template, $lang, $placeholders, $buttons);
	}
	
	/**
	 * Verify Captcha
	 *
	 * @return string Response of message sent
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function recaptcha ($code = NULL)
	{
		return $this->getRecaptcha()->recaptcha($code);
	}
	
	/**
	 * DataForSEO
	 *
	 * @return string Data For SEO
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function seo ($action, $config)
	{
		return $this->getSeo()->seo($action, $config);
	}
	
	 
	/**
	 * Retrieve the transit class
	 *
	 * @return class IntegrationsTransit class
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	private function getTransit ()
	{
		$this->transit = new Transit();
		
		return $this->transit;
	} 
	
	
	/**
	 * Send an SMS
	 *
	 * @return string Message from the server
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	private function getSms ($countryCode, $mobile, $message)
	{		
		return $this->getTransit()->sms($countryCode, $mobile, $message);
	}
	
	/**
	 * Send an WhatsApp Message
	 *
	 * @return string Message from the server
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	private function getWhatsApp ($countryCode, $mobile, $message)
	{		
		return $this->getTransit()->whatsapp($countryCode, $mobile, $message);
	}
	
	/**
	 * Retrieve the recaptcha class
	 *
	 * @return class Recaptcha class
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	private function getRecaptcha ()
	{
		$this->recaptcha = new Recaptcha();
		
		return $this->recaptcha;
	}
	
	/**
	 * Retrieve the SEO class
	 *
	 * @return class IntegrationsSeo class
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	private function getSeo ()
	{
		$this->seo = new IntegrationsSeo();
		
		return $this->seo;
	}
	
}

?>