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
 * FilterHandler allows you to register filters with the system.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class FilterHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/filters';
	
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
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function execute(XmlDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'filters');
		
		$config = $document->documentURI;
		
		$filters = array();
		
		foreach($document->getConfigurationElements() as $cfg) {
			if($cfg->has('filters')) {
				foreach($cfg->get('filters') as $filter) {
					$name = $filter->getAttribute('name', Toolkit::uniqid());
					
					if(!isset($filters[$name])) {
						$filters[$name] = array('params' => array(), 'enabled' => Toolkit::literalize($filter->getAttribute('enabled', true)));
					} else {
						$filters[$name]['enabled'] = Toolkit::literalize($filter->getAttribute('enabled', $filters[$name]['enabled']));
					}
					
					if($filter->hasAttribute('class')) {
						$filters[$name]['class'] = $filter->getAttribute('class');
					}
					
					$filters[$name]['params'] = $filter->getParameters($filters[$name]['params']);
				}
			}
		}
		
		$data = array();

		foreach($filters as $name => $filter) {
			if(stripos($name, 'yf') === 0) {
				throw new Configuration('Filter names must not start with "yf".');
			}
			if(!isset($filter['class'])) {
				throw new Configuration('No class name specified for filter "' . $name . '" in ' . $config);
			}
			if($filter['enabled']) {
				$rc = new \ReflectionClass('YoudsFramework\\' . $filter['class']);
				$if = 'Filter\\I' . ucfirst(strtolower(substr(basename($config), 0, strpos(basename($config), '_filters'))));
				if(!$rc->implementsInterface('YoudsFramework\\' . $if)) {
					throw new Factory('Filter "' . $name . '" does not implement interface "' . $if . '"');
				}
				$data[] = '$filter = new ' . $filter['class'] . '();';
				$data[] = '$filter->initialize($this->context, ' . var_export($filter['params'], true) . ');';
				$data[] = '$filters[' . var_export($name, true) . '] = $filter;';
			}
		}

		return $this->generate($data, $config);
	}
}

?>
