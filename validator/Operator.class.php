<?php
namespace YoudsFramework\Validator;
use YoudsFramework\Validator;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * OperatorValidator
 * 
 * Operators group a couple if validators...
 * 
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Uwe Mesecke <uwe@mesecke.net>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class Operator extends Validator implements IValidatorContainer
{
	/**
	 * @var        array The child validators.
	 */
	protected $children = array();

	/**
	 * @var        int The highest error severity in the container.
	 */
	protected $result = Validator::SUCCESS;
	

	/**
	 * Method for checking the validity of child validators.
	 * 
	 * Some operators (XOR and NOT) need a specific quantity of child
	 * validators so they implement an algorithm that checks of the setup
	 * is valid. This method is run first when execute() is invoked and
	 * should throw an exception if the setup is invalid.
	 * 
	 * @throws     Exceptions\Validator If the  quantity of child 
	 *                                           validators is invalid
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	protected function checkValidSetup()
	{
	}
	
	/**
	 * Shutdown method, for shutting down the model etc.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function shutdown()
	{
		foreach($this->children as $child) {
			$child->shutdown();
		}
	}
	

	/**
	 * Adds a validation result for a given field.
	 *
	 * @param      Validator The validator.
	 * @param      string The name of the field which has been validated.
	 * @param      int    The result of the validation.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 *
	 * @deprecated 0.1
	 */
	public function addFieldResult($validator, $fieldname, $result)
	{
		if($this->parentContainer !== null) {
			return $this->parentContainer->addFieldResult($validator, $fieldname, $result);
		}
	}

	/**
	 * Adds a intermediate result of an validator for the given argument
	 *
	 * @param      ValidationArgument The argument
	 * @param      int                     The arguments result.
	 * @param      Validator          The validator (if the error was caused
	 *                                     inside a validator).
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function addArgumentResult(ValidationArgument $argument, $result, $validator = null)
	{
		if($this->parentContainer !== null) {
			return $this->parentContainer->addArgumentResult($argument, $result, $validator);
		}
	}

	/**
	 * Adds an incident to the validation result. 
	 *
	 * @param      ValidationIncident The incident.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function addIncident(ValidationIncident $incident)
	{
		if($this->parentContainer !== null) {
			return $this->parentContainer->addIncident($incident);
		}
	}

	/**
	 * Adds new child validator.
	 * 
	 * @param      Validator The new child validator.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function addChild(Validator $validator)
	{
		$name = $validator->getName();
		if(isset($this->children[$name])) {
			throw new InvalidArgument('A validator with the name "' . $name . '" already exists');
		}

		$this->children[$name] = $validator;
		$validator->setParentContainer($this);
	}

	/**
	 * Returns a named child validator.
	 *
	 * @param      Validator The child validator.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getChild($name)
	{
		if(!isset($this->children[$name])) {
			throw new InvalidArgument('A validator with the name "' . $name . '" does not exist');
		}

		return $this->children[$name];
	}

	/**
	 * Returns all child validators.
	 *
	 * @return     array An array of Validator instances.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getChilds()
	{
		return $this->children;
	}

	/**
	 * Registers an array of validators.
	 * 
	 * @param      array The array of validators.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function registerValidators(array $validators)
	{
		foreach($validators as $validator) {
			$this->addChild($validator);
		}
	}
	
	/**
	 * Gets parent's dependency manager.
	 * 
	 * @return     DependencyManager The parent's dependency manager.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function getDependencyManager()
	{
		return $this->parentContainer->getDependencyManager();
	}

	/**
	 * Returns the result from the error manager.
	 * 
	 * @return     int The result of the validation process.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * Executes the validator.
	 * 
	 * Executes the operators validate()-Method after checking the quantity
	 * of child validators with checkValidSetup().
	 * 
	 * @param      ParameterHolder The parameters which should be validated.
	 *
	 * @return     int The result of validation (SUCCESS, NONE, NOTICE, ERROR, CRITICAL).
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function execute($parameters)
	{
		// check if we have a valid setup of validators
		$this->checkValidSetup();
		
		$result = parent::execute($parameters);
		if($result != Validator::SUCCESS && !$this->getParameter('skip_errors') && $this->result == Validator::CRITICAL) {
			/*
			 * one of the child validators resulted with CRITICAL
			 * we change our operator's result to CRITICAL, too so the
			 * surrounding validator container is aware of the critical
			 * result and can abort further validation... 
			 */
			$result = Validator::CRITICAL;
		}
		
		return $result;
	}
}

?>
