<?php
namespace YoudsFramework\Config;
use YoudsFramework\Config;
use YoudsFramework\Util\Autoloader;
use YoudsFramework\User\Security;
use ReflectionClass;
use YoudsFramework\Filter\Dispatch;
use YoudsFramework\Exceptions\Configuration;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * FactoryHandler allows you to specify which factory implementation 
 * the system will use.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class FactoryHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/factories';
	
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 */
	public function execute(XmlDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'factories');
		
		$config = $document->documentURI;
		$data = array();
		
		// The order of this initialization code is fixed, to not change
		// name => required?
		$factories = array(
			'execution_container' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
				),
			),
			
			'validation_manager' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
				),
			),
			
			'dispatch_filter' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
					'Filter\IGlobal',
				),
			),
			
			'execution_filter' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
					'Filter\IAction',
				),
			),
			
			'security_filter' => array(
				'required' => Config::get('core.use_security', false),
				'var' => null,
				'must_implement' => array(
					'Filter\IAction',
					'Filter\ISecurity',
				),
			),
			
			'filter_chain' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
				),
			),
			
			'response' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
				),
			),
			
			'database_manager' => array(
				'required' => Config::get('core.use_database', false),
				'var' => 'databaseManager',
				'must_implement' => array(
				),
			),
			
			'database_manager', // startup()
			
			'logger_manager' => array(
				'required' => Config::get('core.use_logging', false),
				'var' => 'loggerManager',
				'must_implement' => array(
				),
			),
			
			'logger_manager', // startup()
			
			'translation_manager' => array(
				'required' => Config::get('core.use_translation', false),
				'var' => 'translationManager',
				'must_implement' => array(
				),
			),
			
			'request' => array(
				'required' => true,
				'var' => 'request',
				'must_implement' => array(
				),
			),
			
			'routing' => array(
				'required' => true,
				'var' => 'routing',
				'must_implement' => array(
				),
			),
			
			'controller' => array(
				'required' => true,
				'var' => 'controller',
				'must_implement' => array(
				),
			),
			
			'storage' => array(
				'required' => true,
				'var' => 'storage',
				'must_implement' => array(
				),
			),
			
			'storage', // startup()
			
			'user' => array(
				'required' => true,
				'var' => 'user',
				'must_implement' => (
					Config::get('core.use_security')
					? array(
						'User\ISecurity',
					)
					: array(
					)
				),
			),
			
			'translation_manager', // startup()
			
			'user', // startup()
			
			'routing', // startup()
			
			'request', // startup()
			
			'controller', // startup()
			
			'generator' => array(
				'required' => true,
				'var' => 'gt',
				'must_implement' => array(
				)
			)
			
		);
		
		foreach($document->getConfigurationElements() as $configuration) {
			foreach($factories as $factory => $info) {
				if(is_array($info) && $info['required'] && $configuration->hasChild($factory)) {
					$element = $configuration->getChild($factory);
					
					$data[$factory] = isset($data[$factory]) ? $data[$factory] : array('class' => null, 'params' => array());
					$data[$factory]['class'] = $element->getAttribute('class', $data[$factory]['class']);
					$data[$factory]['params'] = $element->getParameters($data[$factory]['params']);
				}
			}
		}
		
		$code = array();
		$shutdownSequence = array();
		foreach($factories as $factory => $info) {
			if(is_array($info)) {
				if(!$info['required']) {
					continue;
				}
				if(!isset($data[$factory]) || $data[$factory]['class'] === null) {
					$error = 'Configuration file "%s" has missing or incomplete entry "%s"';
					$error = sprintf($error, $config, $factory);
					throw new Configuration($error);
				}
				try {
					if (!class_exists($data[$factory]['class'])):
						
						foreach (glob(Config::get('core.src_dir') . '/*\/' . $data[$factory]['class'] . '.*.php') as $filename):
							include_once($filename);
						endforeach;
						foreach (glob(Config::get('core.common_dir') . '/callbacks/*/' . $data[$factory]['class'] . '*.php') as $filename):							
							include_once($filename);
						endforeach;
					endif;
					
					$className = str_replace('\\\\', '\\', 'YoudsFramework\\' . $data[$factory]['class']);
					Autoloader::loadClass($className);

					$rc = new ReflectionClass($className);
				} catch(Exceptions\Reflection $e) {
					$error = 'Configuration file "%s" specifies unknown class "%s" for entry "%s"';
					$error = sprintf($error, $config, $data[$factory]['class'], $factory);
					throw new Configuration($error, 0,  $e);
				}
				foreach($info['must_implement'] as $interface) {
					$interface = 'YoudsFramework\\' . $interface;
					if(!$rc->implementsInterface($interface)) {
						$error = 'Class "%s" for entry "%s" does not implement interface "%s" in configuration file "%s"';
						$error = sprintf($error, $data[$factory]['class'], $factory, $interface, $config);
						throw new Configuration($error);
					}
				}
				
				if($info['var'] !== null) {
					// we have to make an instance
					$code[] = sprintf(
						'$this->%1$s = new %2$s();' . "\n" . '$this->%1$s->initialize($this, %3$s);',
						$info['var'],
						$data[$factory]['class'],
						var_export($data[$factory]['params'], true)
					);
				} else {
					// it's a factory info
					$code[] = sprintf(
						'$this->factories[%1$s] = %2$s;',
						var_export($factory, true),
						var_export(array(
							'class' => $data[$factory]['class'],
							'parameters' => $data[$factory]['params'],
						), true)
					);
				}
			} else {
				if($factories[$info]['required']) {
					$code[] = sprintf('$this->%s->startup();', $factories[$info]['var']);
					array_unshift($shutdownSequence, sprintf('$this->%s', $factories[$info]['var']));
				}
			}
		}
		
		$code[] = sprintf('$this->shutdownSequence = array(%s);', implode(",\n", $shutdownSequence));
		
		return $this->generate($code, $config);
	}
}

?>
