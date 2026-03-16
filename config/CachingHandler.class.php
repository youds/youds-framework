<?php
namespace YoudsFramework\Config;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * CachingHandler compiles the per-chain configuration files placed
 * in the "cache" subfolder of a content directory.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class CachingHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/caching';
	
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
	 */
	public function execute(XmlDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'caching');
		
		$cachings = array();
		
		foreach($document->getConfigurationElements() as $cfg) {
			if(!$cfg->has('cachings')) {
				continue;
			}
			
			foreach($cfg->get('cachings') as $caching) {
				$groups = array();
				if($caching->has('groups')) {
					foreach($caching->get('groups') as $group) {
						$groups[] = array('name' => $group->getValue(), 'source' => $group->getAttribute('source', 'string'), 'namespace' => $group->getAttribute('namespace')) ;
					}
				}
				
				$chainAttributes = array();
				if($caching->has('chain_attributes')) {
					foreach($caching->get('chain_attributes') as $chainAttribute) {
						$chainAttributes[] = $chainAttribute->getValue();
					}
				}
				
				$layouts = null;
				if($caching->has('layouts')) {
					$layouts = array();
					foreach($caching->get('layouts') as $layout) {
						if($layout->hasAttribute('content')) {
							$layouts[] = array('content' => $layout->getAttribute('content'), 'layout' => $layout->getValue());
						} else {
							$layouts[] = Toolkit::literalize($layout->getValue());
						}
					}
				}
				
				$outputTypes = array();
				if($caching->has('output_types')) {
					foreach($caching->get('output_types') as $outputType) {
						$layers = null;
						if($outputType->has('layers')) {
							$layers = array();
							foreach($outputType->get('layers') as $layer) {
								$include = Toolkit::literalize($layer->getAttribute('include', 'true'));
								if(($layer->has('slots') && !$layer->hasAttribute('include')) || !$include) {
									$slots = array();
									if($layer->has('slots')) {
										foreach($layer->get('slots') as $slot) {
											$slots[] = $slot->getValue();
										}
									}
									$layers[$layer->getAttribute('name')] = $slots;
								} else {
									$layers[$layer->getAttribute('name')] = true;
								}
							}
						}
						
						$templateVariables = array();
						if($outputType->has('template_variables')) {
							foreach($outputType->get('template_variables') as $templateVariable) {
								$templateVariables[] = $templateVariable->getValue();
							}
						}
						
						$requestAttributes = array();
						if($outputType->has('request_attributes')) {
							foreach($outputType->get('request_attributes') as $requestAttribute) {
								$requestAttributes[] = array('name' => $requestAttribute->getValue(), 'namespace' => $requestAttribute->getAttribute('namespace'));
							}
						}
						
						$requestAttributeNamespaces = array();
						if($outputType->has('request_attribute_namespaces')) {
							foreach($outputType->get('request_attribute_namespaces') as $requestAttributeNamespace) {
								$requestAttributeNamespaces[] = $requestAttributeNamespace->getValue();
							}
						}
						
						$otnames = array_map('trim', explode(' ', $outputType->getAttribute('name', '*')));
						foreach($otnames as $otname) {
							$outputTypes[$otname] = array(
								'layers' => $layers,
								'template_variables' => $templateVariables,
								'request_attributes' => $requestAttributes,
								'request_attribute_namespaces' => $requestAttributeNamespaces,
							);
						}
					}
				}
				
				$methods = array_map('trim', explode(' ', $caching->getAttribute('method', '*')));
				foreach($methods as $method) {
					if(!Toolkit::literalize($caching->getAttribute('enabled', true))) {
						unset($cachings[$method]);
					} else {
						$values = array(
							'lifetime' => $caching->getAttribute('lifetime'),
							'groups' => $groups,
							'layouts' => $layouts,
							'chain_attributes' => $chainAttributes,
							'output_types' => $outputTypes,
						);
						$cachings[$method] = $values;
					}
				}
			}
		}
		
		$code = array(
			'$configs = ' . var_export($cachings, true) . ';',
			'if(isset($configs[$index = $container->getRequestMethod()]) || isset($configs[$index = "*"])) {',
			'	$isCacheable = true;',
			'	$config = $configs[$index];',
			'	if(is_array($config["layouts"])) {',
			'		foreach($config["layouts"] as &$layout) {',
			'			if(!is_array($layout)) {',
			'				if($layout === null) {',
			'					$layout = array(',
			'						"content" => null,',
			'						"name" => null',
			'					);',
			'				} else {',
			'					$layout = array(',
			'						"content" => $contentName,',
			'						"name" => Toolkit::evaluateModuleDirective(',
			'							$contentName,',
			'							"framework.layout.name",',
			'							array(',
			'								"chainName" => $chainName,',
			'								"layoutName" => $layout,',
			'							)',
			'						)',
			'					);',
			'				}',
			'			}',
			'		}',
			'	}',
			'}',
		);
		
		return $this->generate($code, $document->documentURI);
	}
}

?>
