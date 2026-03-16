<?php
namespace YoudsFramework\Request;
use YoudsFramework\Util\ArrayPathDefinition;
use InvalidArgument;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * ParameterHolder provides a base class for managing parameters.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage util
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class ParameterHolder
{
	/**
	 * @var        array An array of parameters
	 */
	protected $parameters = array();

	/**
	 * Constructor. Accepts an array of initial parameters as an argument.
	 *
	 * @param      array An array of parameters to be set right away.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __construct( $parameters = array())
	{
		$this->parameters = $parameters;
	}

	/**
	 * Clear all parameters associated with this request.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function clearParameters()
	{
		$this->parameters = array();
	}

	/**
	 * Retrieve a parameter.
	 *
	 * @param      string A parameter name.
	 * @param      mixed  A default parameter value.
	 *
	 * @return     mixed A parameter value, if the parameter exists, otherwise
	 *                   null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function &getParameter($name, $default = null)
	{
		if(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters)) {
			return $this->parameters[$name];
		}
		try {
			return ArrayPathDefinition::getValue($name, $this->parameters, $default);
		} catch(Exceptions\InvalidArgument $e) {
			return $default;
		}
	}

	/**
	 * Retrieve an array of parameter names.
	 *
	 * @return     array An indexed array of parameter names.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getParameterNames()
	{
		return array_keys($this->parameters);
	}

	/**
	 * Retrieve an array of flattened parameter names. This means when a parameter
	 * is an array you wont get the name of the parameter in the result but 
	 * instead all child keys appended to the name (like foo[0],foo[1][0], ...)
	 *
	 * @return     array An indexed array of parameter names.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getFlatParameterNames()
	{
		return ArrayPathDefinition::getFlatKeyNames($this->parameters);
	}

	/**
	 * Retrieve an array of parameters.
	 *
	 * @return     array An associative array of parameters.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function &getParameters()
	{
		return $this->parameters;
	}

	/**
	 * Indicates whether or not a parameter exists.
	 *
	 * @param      string A parameter name.
	 *
	 * @return     bool true, if the parameter exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function hasParameter($name)
	{
		if(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters)) {
			return true;
		}
		try {
			return ArrayPathDefinition::hasValue($name, $this->parameters);
		} catch(Exceptions\InvalidArgument $e) {
			return false;
		}
	}

	/**
	 * Remove a parameter.
	 *
	 * @param      string A parameter name.
	 *
	 * @return     string A parameter value, if the parameter was removed,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function &removeParameter($name)
	{
		if(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters)) {
			$retVal =& $this->parameters[$name];
			unset($this->parameters[$name]);
			return $retVal;
		}
		
		$retVal = null;
		try {
			$retVal =& ArrayPathDefinition::unsetValue($name, $this->parameters);
		} catch(Exceptions\InvalidArgument $e) {
		}
		return $retVal;
	}

	/**
	 * Set a parameter.
	 *
	 * If a parameter with the name already exists the value will be overridden.
	 *
	 * @param      string A parameter name.
	 * @param      mixed  A parameter value.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	/**
	 * Append a parameter.
	 *
	 * If this parameter is already set, convert it to an array and append the
	 * new value.  If not, set the new value like normal.
	 *
	 * @param      string A parameter name.
	 * @param      mixed  A parameter value.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function appendParameter($name, $value)
	{
		if(!isset($this->parameters[$name]) || !is_array($this->parameters[$name])) {
			settype($this->parameters[$name], 'array');
		}
		$this->parameters[$name][] = $value;
	}

	/**
	 * Set a parameter by reference.
	 *
	 * If a parameter with the name already exists the value will be
	 * overridden.
	 *
	 * @param      string A parameter name.
	 * @param      mixed  A reference to a parameter value.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function setParameterByRef($name, &$value)
	{
		$this->parameters[$name] =& $value;
	}

	/**
	 * Append a parameter by reference.
	 *
	 * If this parameter is already set, convert it to an array and append the
	 * reference to the new value.  If not, set the new value like normal.
	 *
	 * @param      string A parameter name.
	 * @param      mixed  A reference to a parameter value.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function appendParameterByRef($name, &$value)
	{
		if(!isset($this->parameters[$name]) || !is_array($this->parameters[$name])) {
			settype($this->parameters[$name], 'array');
		}
		$this->parameters[$name][] =& $value;
	}

	/**
	 * Set an array of parameters.
	 *
	 * If an existing parameter name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param      array An associative array of parameters and their associated
	 *                   values.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function setParameters(array $parameters)
	{
		// array_merge would reindex numeric keys, so we use the + operator
		// mind the operand order: keys that exist in the left one aren't overridden
		$this->parameters = $parameters + $this->parameters;
	}

	/**
	 * Set an array of parameters by reference.
	 *
	 * If an existing parameter name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param      array An associative array of parameters and references to their
	 *                   associated values.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function setParametersByRef(array &$parameters)
	{
		foreach($parameters as $key => &$value) {
			$this->parameters[$key] =& $value;
		}
	}

}

?>
