<?php
namespace YoudsFramework\Config;
use YoudsFramework\Util\Toolkit;


// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * HandlersHandler allows you to specify configuration handlers
 * for the application or on a content level.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class HandlersHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/config_handlers';
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      XmlDomDocument The document to handle.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     Exceptions\Unreadable If a requested configuration
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     Exceptions\Parse If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function execute(XmlDomDocument $document)
	{

		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'config_handlers');
		
		// init our data arrays
		$handlers = array();

		foreach($document->getConfigurationElements() as $configuration) {
			
			if(!$configuration->has('handlers')) {
				continue;
			}
			
			// let's do our fancy work
			foreach($configuration->get('handlers') as $handler) {
				
				$pattern = $handler->getAttribute('pattern');
				
				$category = Toolkit::normalizePath(Toolkit::expandDirectives($pattern));
				
				$class = $handler->getAttribute('class');

				$transformations = array(
					XmlParser::STAGE_SINGLE => array(),
					XmlParser::STAGE_COMPILATION => array(),
				);
				
				if($handler->has('transformations')) {
					foreach($handler->get('transformations') as $transformation) {
						$path = Toolkit::literalize($transformation->getValue());

						$for = $transformation->getAttribute('for', XmlParser::STAGE_SINGLE);
						$transformations[$for][] = $path;
					}
				}

				$validations = array(
					XmlParser::STAGE_SINGLE => array(
						XmlParser::STEP_TRANSFORMATIONS_BEFORE => array(
							XmlParser::VALIDATION_TYPE_RELAXNG => array(
							),
							XmlParser::VALIDATION_TYPE_SCHEMATRON => array(
							),
							XmlParser::VALIDATION_TYPE_XMLSCHEMA => array(
							),
						),
						XmlParser::STEP_TRANSFORMATIONS_AFTER => array(
							XmlParser::VALIDATION_TYPE_RELAXNG => array(
							),
							XmlParser::VALIDATION_TYPE_SCHEMATRON => array(
							),
							XmlParser::VALIDATION_TYPE_XMLSCHEMA => array(
							),
						),
					),
					XmlParser::STAGE_COMPILATION => array(
						XmlParser::STEP_TRANSFORMATIONS_BEFORE => array(
							XmlParser::VALIDATION_TYPE_RELAXNG => array(
							),
							XmlParser::VALIDATION_TYPE_SCHEMATRON => array(
							),
							XmlParser::VALIDATION_TYPE_XMLSCHEMA => array(
							),
						),
						XmlParser::STEP_TRANSFORMATIONS_AFTER => array(
							XmlParser::VALIDATION_TYPE_RELAXNG => array(
							),
							XmlParser::VALIDATION_TYPE_SCHEMATRON => array(
							),
							XmlParser::VALIDATION_TYPE_XMLSCHEMA => array(
							),
						),
					),
				);
				
				if($handler->has('validations')) {
					foreach($handler->get('validations') as $validation) {
						$path = Toolkit::literalize($validation->getValue());
						$type = null;
						if(!$validation->hasAttribute('type')) {
							$type = $this->guessValidationType($path);
						} else {
							$type = $validation->getAttribute('type');
						}
						$for = $validation->getAttribute('for', XmlParser::STAGE_SINGLE);
						$step = $validation->getAttribute('step', XmlParser::STEP_TRANSFORMATIONS_AFTER);
						$validations[$for][$step][$type][] = $path;
					}
				}
				
				
				$handlers[$category] = isset($handlers[$category])
					? $handlers[$category]
					: array(
						'parameters' => array(),
						);
				$handlers[$category] = array(
					'class' => $class,
					'parameters' => $handler->getParameters($handlers[$category]['parameters']),
					'transformations' => $transformations,
					'validations' => $validations,
				);
				
			}
		}
		$data = array(
			'return ' . var_export($handlers, true),
		);

		return $this->generate($data, $document->documentURI);
	}
	
	/**
	 * Convenience method to quickly guess the type of a validation file using its
	 * file extension.
	 *
	 * @param      string The path to the file.
	 *
	 * @return     string An XmlParser::VALIDATION_TYPE_* const value.
	 *
	 * @throws     Exception If the type could not be determined.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	protected function guessValidationType($path)
	{
		switch(pathinfo($path, PATHINFO_EXTENSION)) {
			case 'rng':
				return XmlParser::VALIDATION_TYPE_RELAXNG;
			case 'rnc':
				return XmlParser::VALIDATION_TYPE_RELAXNG;
			case 'sch':
				return XmlParser::VALIDATION_TYPE_SCHEMATRON;
			case 'xsd':
				return XmlParser::VALIDATION_TYPE_XMLSCHEMA;
			default:
				throw new Exception(sprintf('Could not determine validation type for file "%s"', $path));
		}
	}
}

?>
