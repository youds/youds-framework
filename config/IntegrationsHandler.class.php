<?php
namespace YoudsFramework\Config;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * SettingHandler handles the settings.xml file
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class IntegrationsHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/integrations';
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      XmlDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     Exceptions\Unreadable If a requested configuration
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     Exceptions\Parse If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function execute(XmlDomDocument $document)
	{
		
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'integrations');
		
		// init our data array
		$data = array();
		foreach($document->getConfigurationElements() as $cfg) {
			
			// 2fa
			if($cfg->has('twofactor')) {
				
				// loop over providers
				foreach($cfg->get('twofactor') as $provider) {
					
					// loop over provider
					foreach ($provider as $value) {
						
						// retain important information
						$name = $value->getAttribute('name');
						$data[sprintf('twofactor.%s.publicKey', $name)] = $value->getChild('publicKey')->getValue();
						$data[sprintf('twofactor.%s.privateKey', $name)]= $value->getChild('privateKey')->getValue();
						
					}
				}
			}

			// recaptcha
			if($cfg->has('recaptcha')) {
				
				// fetch secret key
				foreach($cfg->get('recaptcha') as $config) {
					
					// save config
					$data['recaptcha.sitekey'] = $config->getChild('siteKey')->getValue();
					$data['recaptcha.secretkey'] = $config->getChild('secretKey')->getValue();
						
				}		
			}
			
			
			// oauth
			if($cfg->has('oauth')) {
				
				// loop over providers
				foreach($cfg->get('oauth') as $provider) {
					
					// loop over provider
					foreach ($provider as $value) {
						
						// retain important information
						$name = $value->getAttribute('name');
						$data[sprintf('oauth.%s.publicKey', $name)] = $value->getChild('publicKey')->getValue();
						$data[sprintf('oauth.%s.privateKey', $name)] = $value->getChild('privateKey')->getValue();
						
					}
				}
			}

			// email
			if($cfg->has('email')) {

				// loop over accounts
				$email = $cfg->get('email')[0];
				foreach ($email->get('account') as $account) {
					$pattern = sprintf('email.%s', $account->getAttribute('name')) . '.%s';
					$data[sprintf($pattern, 'sender')] = $account->getChild('sender')->getValue();
					$data[sprintf($pattern, 'from')] = $account->getChild('from')->getValue();
					$data[sprintf($pattern, 'host')] = $account->getChild('host')->getValue();
					$data[sprintf($pattern, 'username')] = $account->getChild('username')->getValue();
					$data[sprintf($pattern, 'password')] = $account->getChild('password')->getValue();
					$data[sprintf($pattern, 'port')] = $account->getChild('port')->getValue();
					$data[sprintf($pattern, 'encryption')] = $account->getChild('encryption')->getValue();
				}
			}

			// transit
			if($cfg->has('transit')) {
				
				// loop over providers
				foreach($cfg->get('transit') as $transit) {

					foreach($transit->get('provider') as $provider) {

						$providerName = $provider->getAttribute('name');
						foreach($provider->get('parameter') as $parameter) {
							$parameterName = $parameter->getAttribute('name');
							$data[sprintf('transit.%s.%s', $providerName, $parameterName)] = $parameter->getValue();
						}
						
						foreach($provider->get('channels') as $channel) {
							$channelName = $channel->getAttribute('name');
							foreach($channel->get('parameter') as $parameter) {
								$parameterName = $parameter->getAttribute('name');
								$data[sprintf('transit.%s.%s.%s', $providerName, $channelName, $parameterName)] = $parameter->getValue();
							}
						}
					}

				}
			}
			
			// seo
			if($cfg->has('seo')) {
				
				// fetch secret key
				foreach($cfg->get('seo') as $config) {
					
					// save config
					$data['seo.login'] = $config->getChild('login')->getValue();
					$data['seo.password'] = $config->getChild('password')->getValue();
						
				}		
			}
			
			// payments
			if($cfg->has('gateway')) {
				
				// loop over providers
				foreach($cfg->get('gateway') as $provider) {
					
					// loop over provider
					foreach ($provider as $value) {
						
						// retain important information
						$name = $value->getAttribute('name');
						$data[sprintf('gateway.%s.publicKey', $name)] = $value->getChild('publicKey')->getValue();
						$data[sprintf('gateway.%s.privateKey', $name)] = $value->getChild('privateKey')->getValue();
						
					}
				}
			}
		
		}
		
		// save to config
		$this->setParameters($data);

		// caching
		$code = 'Config::fromArray(' . var_export($data, true) . ');';
			
		return $this->generate($code, $document->documentURI);
	}
}

?>
