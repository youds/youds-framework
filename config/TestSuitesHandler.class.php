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
 * TestSuitesHandler reads the testsuites configuration files to determine 
 * the available suites and their tests.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class TestSuitesHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/testing/suites';
	
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
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'suite');
		
		// remember the config file path
		$config = $document->documentURI;
		
		$data = array();
		// loop over <configuration> elements
		foreach($document->getConfigurationElements() as $configuration) {
			foreach($configuration->get('suites') as $current) {
				$includes = array();
				foreach($current->get('includes') as $include) {
					$includes[] = $include->textContent;
				}
				
				$excludes = array();
				foreach($current->get('excludes') as $exclude) {
					$excludes[] = $exclude->textContent;
				}
				
				$suite =  array(
					'class' => $current->getAttribute('class', 'TestSuite'),
					'base' => $current->getAttribute('base', 'tests/'),
					'includes' => $includes,
					'excludes' => $excludes
				);
				
				$suite['testfiles'] = array();
				foreach($current->get('testfiles') as $file) {
					$suite['testfiles'][] = $file->textContent;
				}
				
				$data[$current->getAttribute('name')] = $suite;
			}
		}
		$code = 'return '.var_export($data, true);
		return $this->generate($code, $config);
	}
}

?>
