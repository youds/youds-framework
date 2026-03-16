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
 * ModuleHandler reads content configuration files to determine the
 * status of a content.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class ModuleHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/chains';
	
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function execute(XmlDomDocument $document)
	{

		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'content');
		
		// remember the config file path
		$config = $document->documentURI;

		//$contentName = str_replace('/chains.xml', '', substr($config, strpos($config, 'content/') + 8));
		
		$enabled = false;
		$prefix = 'content.${contentName}.';
		$data = array();
		
		// loop over <configuration> elements
		foreach($document->getConfigurationElements() as $configuration) {
			$content = $configuration->getChild('content');
			if(!$content) {
				continue;
			}
			
			// enabled flag is treated separately
			$enabled = (bool) Toolkit::literalize($content->getAttribute('enabled'));
			
			// loop over <setting> elements; there can be many of them
			foreach($content->get('settings') as $setting) {
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
					$data[$settingName] = Toolkit::literalize($setting->getValue());
				}
			}
		}

		$code = array();
		$code[] = '$lcContentName = strtolower(\'' . $contentName . '\');';
		$code[] = 'Config::set(Toolkit::expandVariables(' . var_export($prefix . 'enabled', true) . ', array(\'contentName\' => $lcContentName)), ' . var_export($enabled, true) . ', true, true);';
		if(count($data)) {
			$code[] = '$contentConfig = ' . var_export($data, true) . ';';
			$code[] = '$contentConfigKeys = array_keys($contentConfig);';
			$code[] = 'foreach($contentConfigKeys as &$value) $value = Toolkit::expandVariables($value, array(\'contentName\' => $lcContentName));';
			$code[] = '$contentConfig = array_combine($contentConfigKeys, $contentConfig);';
			$code[] = 'Config::fromArray($contentConfig);';
		}
		
		
		return $this->generate($code, $config);
	}
}

?>
