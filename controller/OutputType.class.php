<?php
namespace YoudsFramework;
use YoudsFramework\Request\ParameterHolder;
use YoudsFramework\Exceptions\Exception;
// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * This class holds information about an Output Type.
 * 
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage controller
 * 
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class OutputType extends ParameterHolder
{
	/**
	 * @var        Context The context instance.
	 */
	protected $context = null;
	
	/**
	 * @var        string The name of the Output Type.
	 */
	protected $name = '';
	
	/**
	 * @var        array An array of Renderers (settings and instances).
	 */
	protected $renderers = array();
	
	/**
	 * @var        string The name of the default Renderer, if set.
	 */
	protected $defaultRenderer = null;
	
	/**
	 * @var        array An array of configured layouts.
	 */
	protected $layouts = array();
	
	/**
	 * @var        string The name of the default layout, if set.
	 */
	protected $defaultLayout = null;
	
	/**
	 * @var        string The name of the exception template for this output type.
	 */
	protected $exceptionTemplate = null;
	
	/**
	 * Initialize the Output Type.
	 *
	 * @param      Context The current Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters, $name, array $renderers, $defaultRenderer, array $layouts, $defaultLayout, $exceptionTemplate = null)
	{
		$this->context = $context;
		
		$this->parameters = $parameters;
		
		$this->name = $name;
		
		/* Decipher Renderers */
		foreach ($renderers as $renderer => $values) {
			switch (Config::get('core.renderer')) {
				case 'twig':
					$renderers[$renderer]['class'] = 'Twig';
					break;
				case 'smarty':
					$renderers[$renderer]['class'] = 'Smarty';
					break;
				case 'plain':
				default:
					$renderers[$renderer]['class'] = 'Plain';
					break;
			}
		}
		
		$this->renderers = $renderers;
		
		$this->defaultRenderer = $defaultRenderer;
		
		$this->layouts = $layouts;
		
		$this->defaultLayout = $defaultLayout;
		
		$this->exceptionTemplate = $exceptionTemplate;
	}
	
	/**
	 * Get the name of the Output Type.
	 *
	 * @return     string The name of the Output Type.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the name of the Output Type.
	 *
	 * @return     void
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
	
	/**
	 * @see        OutputType::getName()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function __toString()
	{
		return $this->getName();
	}
	
	/**
	 * Checks whether or not any renderers are defined for this Output Type.
	 *
	 * @return     bool True, if renderers are defined, false otherwise.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasRenderers()
	{
		return (count($this->renderers) > 0);
	}
	
	/**
	 * Get a renderer instance.
	 *
	 * @param      string The optional name of the Renderer to fetch.
	 *
	 * @return     Renderer A Renderer instance or null if none defined.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getRenderer($name = null)
	{
		if(count($this->renderers) == 0) {
			return null;
		} elseif($name === null || $name == '') {
			$name = $this->defaultRenderer;
		}
		if(isset($this->renderers[$name])) {
			if(!isset($this->renderers[$name]['instance'])) {
				$class = 'YoudsFramework\Renderer\\' . $this->renderers[$name]['class'];
				$renderer = new $class();
				$renderer->initialize($this->context, $this->renderers[$name]['parameters']);
				if(isset($this->renderers[$name]['extension'])) {
					$renderer->setExtension($this->renderers[$name]['extension']);
				}
				if($renderer instanceof IReusableRenderer) {
					$this->renderers[$name]['instance'] = $renderer;
				}
				return $renderer;
			} else {
				return $this->renderers[$name]['instance'];
			}
		} else {
			throw new Exception('Unknown renderer "' . $name . '"');
		}
	}
	
	/**
	 * Get the name of the default layout.
	 *
	 * @return     string The name of the default layout, or null if none defined.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getDefaultLayoutName()
	{
		return $this->defaultLayout;
	}
	
	/**
	 * Get a layout.
	 *
	 * @param      The optional name of the layout to fetch.
	 *
	 * @return     array An array of layout information.
	 *
	 * @throws     Exception If the layout doesn't exist.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getLayout($name = null)
	{
		if($name === null) {
			$name = $this->defaultLayout;
		}

		if(isset($this->layouts[$name])) {
			return $this->layouts[$name];
		} else {
			throw new Exception('Unknown layout "' . $name . '"');
		}
	}
	
	/**
	 * Get the exception template filename for this renderer.
	 *
	 * @return     string A path to the exception template, or null if undefined.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getTemplate()
	{
		return $this->exceptionTemplate;
	}
}

?>
