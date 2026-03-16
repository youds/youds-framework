<?php
namespace YoudsFramework\Config;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Exceptions\Configuration;

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
class SettingHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/settings';
	
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function execute(XmlDomDocument $document)
	{
		
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'settings');
		
		// init our data array
		$data = array();
		
		$prefix = 'core.';
		foreach($document->getConfigurationElements() as $cfg) {
						
			// let's do our fancy work
			if($cfg->has('system_chains')) {
				foreach($cfg->get('system_chains') as $chain) {
					$name = $chain->getAttribute('name');
					$data[sprintf('chains.%s_content', $name)] = $chain->getChild('content')->getValue();
					$data[sprintf('chains.%s_chain', $name)] = $chain->getChild('chain')->getValue();
				}
			}
			
			// loop over <setting> elements; there can be many of them
			foreach($cfg->get('settings') as $setting) {
				$localPrefix = $prefix;
				
				// let's see if this buddy has a <settings> parent with valuable information
				if($setting->parentNode->localName == 'settings') {
					if($setting->parentNode->hasAttribute('prefix')) {
						$localPrefix = $setting->parentNode->getAttribute('prefix');
					}
				}
				
				$settingName = $localPrefix . $setting->getAttribute('name');
				if($setting->hasParameters()) {
					$data[$settingName] = $setting->getParameters();
				} else {
					$data[$settingName] = $setting->getLiteralValue();
				}
			}
			if($cfg->has('exception_templates')) {
				foreach($cfg->get('exception_templates') as $exception_template) {
					$tpl = Toolkit::expandDirectives($exception_template->getValue());
					if(!is_readable($tpl)) {
						throw new Configuration('Exception template "' . $tpl . '" does not exist or is unreadable');
					}
					if($exception_template->hasAttribute('context')) {
						foreach(array_map('trim', explode(' ', $exception_template->getAttribute('context'))) as $ctx) {
							$data['exception.templates.' . $ctx] = $tpl;
						}
					} else {
						$data['exception.default_template'] = Toolkit::expandDirectives($tpl);
					}
				}
			}
			
			
		}

		$code = 'Config::fromArray(' . var_export($data, true) . ');';

		return $this->generate($code, $document->documentURI);
	}
}

?>
