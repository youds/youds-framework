<?php
namespace YoudsFramework\Config;
use YoudsFramework\Exceptions\Configuration;
use YoudsFramework\Exceptions\Parse;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * DatabaseHandler allows you to setup database connections in a
 * configuration file that will be created for you automatically upon first
 * request.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class TestingHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/testing';
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      XmlDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     Exceptions\Parse If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 */
	public function execute(XmlDomDocument $document)
	{
		
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'testing');
		
		$suites = array();
		$databases = array();
		$default = null;
		
		foreach($document->getConfigurationElements() as $configuration) {
			
			// loop over <setting> elements; there can be many of them
			foreach($configuration->get('settings') as $setting) {
				$localPrefix = '';
				
				
				// let's see if this buddy has a <settings> parent with valuable information
				if($setting->parentNode->localName == 'settings') {
					if($setting->parentNode->hasAttribute('prefix')) {
						$localPrefix = $setting->parentNode->getAttribute('prefix');
					}
				}
				
				$settingName = $localPrefix . $setting->getAttribute('name');
				
				if($setting->hasParameters()) {
					$data['testing.' . $settingName] = $setting->getParameters();
				} else {
					$data['testing.' . $settingName] = $setting->getLiteralValue();
				}
			}
			
			// suites
			foreach($configuration->get('suites') as $suite) {
				
				if (!$configuration->getChild('suites')->getAttribute('default'))
					throw new Parse('Suites definition must include a default attribute.');
				if (!$suite->getAttribute('name'))
					throw new Parse('Suite definition must include a name attribute.');
								
				// default suite
				$suites['testing.suites.default'] = $configuration->getChild('suites')->getAttribute('default');
					
				$suiteName = $suite->getAttribute('name');
				$a = 0;
				foreach ($suite->get('include') as $include):
					$a++;
					$suites['testing.suites.' . $suiteName . '.' . $a] = $include->getLiteralValue();
					
				endforeach;
			}
			
			// hooks
			foreach($configuration->get('hooks') as $hook):
				
				// validate
				if (!$configuration->getChild('hooks')->getAttribute('default'))
					throw new Parse('Hooks definition must include a default attribute.');
				if (!$hook->getAttribute('name'))
					throw new Parse('Hook definition must include a name attribute.');
				if (!$hook->has('chain') && !$hook->has('file'))
					throw new Parse('Hook definition must include either a chain or a file.');
				if ($hook->has('chain') && $hook->has('file'))
					throw new Parse('Hook definition must include only a chain or a file, both provided.');	
								
				// default hook
				$hooks['testing.hooks.default'] = $configuration->getChild('hooks')->getAttribute('default');
				
				// hook name
				$hookName = $hook->getAttribute('name');
				
				// hook chain
				foreach ($hook->get('chain') as $chain):
					$hooks['testing.hooks.' . $hookName . '.type'] = 'chain';
					$hooks['testing.hooks.' . $hookName . '.chain.content'] = $chain->get('content')[0]->getLiteralValue();
					$hooks['testing.hooks.' . $hookName . '.chain.name'] = $chain->get('name')[0]->getLiteralValue();
				endforeach;
				
				// hook file
				foreach ($hook->get('file') as $file):
					$hooks['testing.hooks.' . $hookName . '.type'] = 'file';
					$hooks['testing.hooks.' . $hookName . '.file'] = $file->getLiteralValue();
				endforeach;
			endforeach;
			

			//$data[] = sprintf('$this->defaultDatabaseName = %s;', var_export($default, true));
			$code[] = 'Config::fromArray(' . var_export($data, true) . ');';
			$code[] = 'Config::fromArray(' . var_export($suites, true) . ');';			
			$code[] = 'Config::fromArray(' . var_export($hooks, true) . ');';
				
			return $this->generate($code, $document->documentURI);
		}
	}
}

?>
