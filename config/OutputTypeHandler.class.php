<?php
namespace YoudsFramework\Config;
use YoudsFramework\Exceptions\Configuration;
use YoudsFramework\Util\Toolkit;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * OutputTypeHandler handles output type configuration files.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class OutputTypeHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/output_types';
	
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
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'output_types');
		
		// remember the config file path
		$config = $document->documentURI;
		
		$data = array();
		$defaultOt = null;
		foreach($document->getConfigurationElements() as $cfg) {
			if(!$cfg->has('output_types')) {
				continue;
			}
						
			$otnames = array();
			foreach($cfg->get('output_types') as $outputType) {
				$otname = $outputType->getAttribute('name');
				if(in_array($otname, $otnames)) {
					throw new Configuration('Duplicate Output Type "' . $otname . '" in ' . $config);
				}
				$otnames[] = $otname;
			}
			
			if(!$cfg->getChild('output_types')->hasAttribute('default')) {
				throw new Configuration('No default Output Type specified in ' . $config);
			}

			foreach($cfg->get('output_types') as $outputType) {
				$outputTypeName = $outputType->getAttribute('name');
				$data[$outputTypeName] = isset($data[$outputTypeName]) ? $data[$outputTypeName] : array('parameters' => array(), 'default_renderer' => null, 'renderers' => array(), 'layouts' => array(), 'default_layout' => null, 'exception_template' => null);
				if($outputType->has('renderers')) {
					foreach($outputType->get('renderers') as $renderer) {
						$rendererName = $renderer->getAttribute('name');
						$data[$outputTypeName]['renderers'][$rendererName] = array(
							'class' => $renderer->getAttribute('class'),
							'instance' => null,
							'parameters' => $renderer->getParameters(array()),
						);
					}
					$data[$outputTypeName]['default_renderer'] = $outputType->getChild('renderers')->getAttribute('default');
				}
				
				if($outputType->has('layouts')) {
					foreach($outputType->get('layouts') as $layout) {
						$layers = array();
						
						if($layout->has('layers')) {
							foreach($layout->get('layers') as $layer) {
								$slots = array();
								
								if($layer->has('slots')) {
									foreach($layer->get('slots') as $slot) {
										$slots[$slot->getAttribute('name')] = array(
											'content' => $slot->getAttribute('content'),
											'chain' => $slot->getAttribute('chain'),
											'output_type' => $slot->getAttribute('output_type'),
											'request_method' => $slot->getAttribute('method'),
											'parameters' => $slot->getParameters(array()),
										);
									}
								}
								
								$layers[$layer->getAttribute('name')] = array(
									'class' => $layer->getAttribute('class', $this->getParameter('default_layer_class', 'FileTemplateLayer')),
									'parameters' => $layer->getParameters(array()),
									'renderer' => $layer->getAttribute('renderer'),
									'slots' => $slots,
								);
							}
						}
						
						$data[$outputTypeName]['layouts'][$layout->getAttribute('name')] = array(
							'layers' => $layers,
							'parameters' => $layout->getParameters(array()),
						);
					}
					$data[$outputTypeName]['default_layout'] = $outputType->getChild('layouts')->getAttribute('default');
				}
				if($outputType->hasAttribute('exception_template')) {
					$data[$outputTypeName]['exception_template'] = Toolkit::expandDirectives($outputType->getAttribute('exception_template'));
					if(!is_readable($data[$outputTypeName]['exception_template'])) {
						throw new Configuration('Exception template "' . $data[$outputTypeName]['exception_template'] . '" does not exist or is unreadable');
					}
				}
				$data[$outputTypeName]['parameters'] = $outputType->getParameters($data[$outputTypeName]['parameters']);
			}
			$defaultOt = $cfg->getChild('output_types')->getAttribute('default');
		}
		
		
		// TODO: next conditional is because testing framework breaks because of duplicate output_types calls with no data in the second call
		if (count($data) == 0):	
			$data = array(
				'text' => array(
					'parameters' => array(),
					'default_renderer' => NULL,
					'renderers' => array(),
					'layouts' => array(),
					'default_layout' => NULL,
					'exception_template' => NULL
				)
			);
			
			$defaultOt = 'text';
		endif;	
		if(!isset($data[$defaultOt])) {
			$error = 'Configuration file "%s" specifies undefined default output type "%s".';
			$error = sprintf($error, $document->documentURI, $defaultOt);
			throw new Configuration($error);
		}
	
		$code = array();
		foreach($data as $outputTypeName => $outputType) {
			$code[] = '$ot = new OutputType();';
			$code[] = sprintf(
				'$ot->initialize($this->context, %s, %s, %s, %s, %s, %s, %s);',
				var_export($outputType['parameters'], true),
				var_export($outputTypeName, true),
				var_export($outputType['renderers'], true),
				var_export($outputType['default_renderer'], true),
				var_export($outputType['layouts'], true),
				var_export($outputType['default_layout'], true),
				var_export($outputType['exception_template'], true)
			);
			$code[] = sprintf('$this->outputTypes[%s] = $ot;', var_export($outputTypeName, true));
		}
		$code[] = sprintf('$this->defaultOutputType = %s;', var_export($defaultOt, true));
	
		return $this->generate($code, $config);

	}
}

?>
