<?php
namespace YoudsFramework\Integrations;
use YoudsFramework\Request\ParameterHolder;


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
class Transit extends ParameterHolder
{
	
	/**
	 * Send an SMS
	 *
	 * @return void
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function sms ($countryCode, $mobile, $message)
	{
		
		// setup basic data
		$messageId = uniqid();
		$carrier['url'] = 'https://' . Config::get('transit.infobip.url') . '/sms/2/text/advanced';
		$carrier['key'] = Config::get('transit.infobip.key');
		$from = Config::get('transit.infobip.sms.from');		
		
		// going to
		$destination = array(
			'messageId' => $messageId,
			'to' => str_replace('+', '', $countryCode) . $mobile
	   );
	   
	   // with message
	   $message = array(
		   'from' => $from,
		   'destinations' => array($destination),
		   'text' => str_replace('\\', '', $message)
		);
		
		// post data
		$postData = array('messages' => array($message));
		$postDataJson = json_encode($postData);
		
		// begin sending process
		$ch = curl_init();
		$header = array(
			'Content-Type:application/json', 
			'Accept:application/json', 
			'Authorization: App ' . $carrier['key'], 
			'Accept: application/json'
		);
		
		curl_setopt($ch, CURLOPT_URL, $carrier['url']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		// execute and retrieve
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$responseBody = json_decode($response);

		curl_close($ch);
		
		return $responseBody;
	}
	/**
	 * Send WhatsApp Message
	 *
	 * @return response from server
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function whatsapp ($countryCode, $mobile, $template, $lang, $placeholders = array(), $buttons = array())
	{	
		
		
		// setup basic data
		$messageId = uniqid();
		$carrier['url'] = sprintf('https://%s/whatsapp/1/message/template', Config::get('transit.infobip.url'));
		$carrier['key'] = Config::get('transit.infobip.key');
		$from = Config::get('transit.infobip.whatsapp.from');	
				
		// buttons
		foreach($buttons as $button):
			if (is_array($button))
				$_buttons = $button;
			else
				$_buttons[] = array('type' => 'URL', 'parameter' => $button);
		endforeach;
			
		$config = array(
			'messages' => array(
				array(
					'from' => Config::get('transit.infobip.whatsapp.sender'),
					'to' => $countryCode . $mobile,
					'content' => array(
						'templateName' => $template,
						'templateData' => [
                            'body' => [
                                'placeholders' => $placeholders
                            ],
							'buttons' => 
								$_buttons
							,
                        ],
                       
                        'language' => $lang,
					)
				)
			)
		);
		
		// do request
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
		    CURLOPT_URL => $carrier['url'],
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_ENCODING => '',
		    CURLOPT_MAXREDIRS => 10,
		    CURLOPT_TIMEOUT => 0,
		    CURLOPT_FOLLOWLOCATION => true,
		    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		    CURLOPT_CUSTOMREQUEST => 'POST',
		    CURLOPT_POSTFIELDS => json_encode($config),
		    CURLOPT_HTTPHEADER => array(
		        sprintf('Authorization: App %s', $carrier['key']),
		        'Content-Type: application/json',
		        'Accept: application/json'
		    ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		return true;
		
	}

	
	
}

?>