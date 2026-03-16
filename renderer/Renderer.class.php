<?php
namespace YoudsFramework;
use YoudsFramework\Request\ParameterHolder;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * A renderer produces the output as defined by a Layout
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage renderer
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class Renderer extends ParameterHolder
{
	/**
	 * @var        Context An Context instance.
	 */
	protected $context = null;
	
	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '';
	
	/**
	 * @var        string The name of the array that contains the template vars.
	 */
	protected $varName = 'template';
	
	/**
	 * @var        string The name of the array that contains the slots output.
	 */
	protected $slotsVarName = 'slots';
	
	/**
	 * @var        bool Whether or not the template vars should be extracted.
	 */
	protected $extractVars = false;
	
	/**
	 * @var        array An array of objects to be exported for use in templates.
	 */
	protected $assigns = array();
	
	/**
	 * @var        array An array of names for the "more" assigns.
	 */
	protected $moreAssignNames = array();
	
	/**
	 * Pre-serialization callback.
	 *
	 * Will set the name of the context and exclude the instance from serializing.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __sleep()
	{
		$this->contextName = $this->context->getName();
		$arr = get_object_vars($this);
		unset($arr['context']);
		return array_keys($arr);
	}
	
	/**
	 * Post-unserialization callback.
	 *
	 * Will restore the context based on the names set by __sleep.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __wakeup()
	{
		$this->context = Context::getInstance($this->contextName);
		unset($this->contextName);
	}
	
	/**
	 * Initialize this Renderer.
	 *
	 * @param      Context The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->context = $context;
		
		$this->setParameters($parameters);
		
		$this->varName = $this->getParameter('var_name', $this->varName);
		$this->slotsVarName = $this->getParameter('slots_var_name', $this->slotsVarName);
		$this->extractVars = $this->getParameter('extract_vars', $this->extractVars);
		
		$this->defaultExtension = $this->getParameter('default_extension', $this->defaultExtension);
		
		if(!$this->extractVars && $this->varName == $this->slotsVarName) {
			throw new Exception('Template and Slots container variable names cannot be identical.');
		}
		
		foreach($this->getParameter('assigns', array()) as $item => $var) {
			$getter = 'get' . str_replace('_', '', $item);
			if(is_callable(array($this->context, $getter))) {
				if($var === null) {
					// the name is null, which means this one should not be assigned
					// we do this in here, not for the moreAssignNames, since those are checked later in the renderer
					continue;
				}
				$this->assigns[$var] = $getter;
			} else {
				$this->moreAssignNames[$item] = $var;
			}
		}
	}
	
	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function getContext()
	{
		return $this->context;
	}
	
	/**
	 * Get the template file extension
	 *
	 * @return     string The extension, including a leading dot.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getDefaultExtension()
	{
		return $this->defaultExtension;
	}
	
	/**
	 * Build an array of "more" assigns.
	 *
	 * @param      array The values to be assigned.
	 * @param      array Assigns name map.
	 *
	 * @return     array The data.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	protected static function &buildMoreAssigns(&$moreAssigns, $moreAssignNames)
	{
		$retVal = array();
		
		foreach($moreAssigns as $name => &$value) {
			if(isset($moreAssignNames[$name])) {
				$name = $moreAssignNames[$name];
			} elseif(array_key_exists($name, $moreAssignNames)) {
				// the name is null, which means this one should not be assigned
				continue;
			}
			$retVal[$name] =& $value;
		}
		
		return $retVal;
	}
	
	/**
	 * Render the presentation and return the result.
	 *
	 * @param      TemplateLayer The template layer to render.
	 * @param      array              The template variables.
	 * @param      array              The slots.
	 * @param      array              Associative array of additional assigns.
	 *
	 * @return     string A rendered result.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	abstract public function render(TemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array());
}

?>
