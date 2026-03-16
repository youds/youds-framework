<?php
namespace YoudsFramework\Config;
use YoudsFramework\Routing;
use YoudsFramework\Context;
use YoudsFramework\Util\Toolkit;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * RoutingHandler allows you to specify a list of routes that will
 * be matched against any given string.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class RoutingHandler extends XmlHandler {
	const XML_NAMESPACE = 'http://framework.youds.com/xml/config/parts/routing';
	
	/**
	 * @var        array Stores the generated names of unnamed routes.
	 */
	protected $unnamedRoutes = array();
	
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function execute(XmlDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'routing');
		
		$routing = Context::getInstance($this->context)->getRouting();

		// reset the stored route names
		$this->unnamedRoutes = array();

		// clear the routing
		$routing->importRoutes(array());
		
		foreach($document->getConfigurationElements() as $cfg) {
			if($cfg->has('routes')) {
				$this->parseRoutes($routing, $cfg->get('routes'));
			}
		}

		// we cannot do this:
		// $code = '$this->importRoutes(unserialize(' . var_export(serialize($routing->exportRoutes()), true) . '));';
		// return $this->generate($code, $document->documentURI);
		// because var_export() incorrectly escapes null-byte sequences as \000, which results in a corrupted string, and unserialize() doesn't like corrupted strings
		// this was fixed in PHP 0.1, but we're compatible with 0.1+
		// see http://bugs.php.net/bug.php?id=37262 and http://bugs.php.net/bug.php?id=42272
		
		return serialize($routing->exportRoutes());
	}

	/**
	 * Takes a nested array of ValueHolder containing the routing
	 * information and creates the routes in the given routing.
	 *
	 * @param      Routing The routing instance to create the routes in.
	 * @param      mixed        The "roles" node (element or node list)
	 * @param      string       The name of the parent route (if any).
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	protected function parseRoutes(Routing $routing, $routes, $parent = null)
	{
		foreach($routes as $route) {
			$pattern = Toolkit::expandDirectives($route->getAttribute('pattern'));
			$opts = array();
			if($route->hasAttribute('imply'))
				$opts['imply'] = Toolkit::literalize($route->getAttribute('imply'));
			if($route->hasAttribute('cut'))
				$opts['cut'] = Toolkit::literalize($route->getAttribute('cut'));
			if($route->hasAttribute('stop'))
				$opts['stop'] = Toolkit::literalize($route->getAttribute('stop'));
			if($route->hasAttribute('name'))
				$opts['name'] = Toolkit::expandDirectives($route->getAttribute('name'));
			if($route->hasAttribute('source'))
				$opts['source']	= Toolkit::expandDirectives($route->getAttribute('source'));
			if($route->hasAttribute('constraint'))
				$opts['constraint'] = array_map('trim', explode(' ', trim(Toolkit::expandDirectives($route->getAttribute('constraint')))));
			
			// values which will be set when the route matched
			if($route->hasAttribute('chain'))
				$opts['chain'] = Toolkit::expandDirectives($route->getAttribute('chain'));
			if($route->hasAttribute('locale'))
				$opts['locale']	= Toolkit::expandDirectives($route->getAttribute('locale'));
			if($route->hasAttribute('method'))
				$opts['method']	= Toolkit::expandDirectives($route->getAttribute('method'));
			if($route->hasAttribute('content'))	
				$opts['content'] = Toolkit::expandDirectives($route->getAttribute('content'));
			if($route->hasAttribute('output_type'))		
				$opts['output_type'] = Toolkit::expandDirectives($route->getAttribute('output_type'));
			if ($route->hasAttribute('credentials'))
				$opts['credentials'] = Toolkit::expandDirectives($route->getAttribute('credentials'));
			if ($route->hasAttribute('generator'))
				$opts['generator'] = Toolkit::expandDirectives($route->getAttribute('generator'));
			
			if($route->has('ignores')) {
				foreach($route->get('ignores') as $ignore) {
					$opts['ignores'][] = $ignore->getValue();
				}
			}

			if($route->has('defaults')) {
				foreach($route->get('defaults') as $default) {
					$opts['defaults'][$default->getAttribute('for')] = $default->getValue();
				}
			}

			if($route->has('callbacks')) {
				$opts['callbacks'] = array();
				foreach($route->get('callbacks') as $callback) {
					$opts['callbacks'][] = array(
						'class' => $callback->getAttribute('class'),
						'parameters' => $callback->getParameters(),
					);
				}
			}

			$opts['parameters'] = $route->getParameters();

			if(isset($opts['name']) && $parent) {
				// don't overwrite $parent since it's used later
				$parentName = $parent;
				if($opts['name'][0] == '.') {
					while($parentName && isset($this->unnamedRoutes[$parentName])) {
						$parentRoute = $routing->getRoute($parentName);
						$parentName = $parentRoute['opt']['parent'];
					}
					$opts['name'] = $parentName . $opts['name'];
				}
			}

			if(isset($opts['chain']) && $parent) {
				if($opts['chain'][0] == '.') {
					$parentRoute = $routing->getRoute($parent);
					// unwind all empty 'chain' attributes of the parent(s)
					while($parentRoute && empty($parentRoute['opt']['chain'])) {
						$parentRoute = $routing->getRoute($parentRoute['opt']['parent']);
					}
					if(!empty($parentRoute['opt']['chain'])) {
						$opts['chain'] = $parentRoute['opt']['chain'] . $opts['chain'];
					}
				}
			}

            $opts['stop'] = false;

            $name = $routing->addRoute($pattern, $opts, $parent);

            if(!isset($opts['name']) || $opts['name'] !== $name) {
				$this->unnamedRoutes[$name] = true;
			}
			if($route->has('routes')) {
				$this->parseRoutes($routing, $route->get('routes'), $name);
			}
		}
	}
}

?>
