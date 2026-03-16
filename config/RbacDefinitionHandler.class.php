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
 * RbacDefinitionHandler handles RBAC role and permission definition files
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class RbacDefinitionHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/roles';
	
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
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'roles');
		
		$data = array();

		foreach($document->getConfigurationElements() as $cfg) {
			if(!$cfg->has('roles')) {
				continue;
			}
			
			$this->parseRoles($cfg->get('roles'), null, $data);
		}

		$code = "return " . var_export($data, true) . ";";
		
		return $this->generate($code, $document->documentURI);
	}
	
	/**
	 * Parse a 'roles' node.
	 *
	 * @param      mixed  The "roles" node (element or node list)
	 * @param      string The name of the parent role, or null.
	 * @param      array  A reference to the output data array.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	protected function parseRoles($roles, $parent, &$data)
	{
		foreach($roles as $role) {
			$name = $role->getAttribute('name');
			$entry = array();
			$entry['parent'] = $parent;
			$entry['permissions'] = array();
			if($role->has('permissions')) {
				foreach($role->get('permissions') as $permission) {
					$entry['permissions'][] = $permission->getValue();
				}
			}
			if($role->has('roles')) {
				$this->parseRoles($role->get('roles'), $name, $data);
			}
			$data[$name] = $entry;
		}
	}
}

?>
