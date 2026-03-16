<?php
namespace YoudsFramework\Validator;
use YoudsFramework\Validator;
use YoudsFramework\Request\DataHolder;
use YoudsFramework\Request\ParameterHolder;
use YoudsFramework\Context;
use YoudsFramework\Util\VirtualArrayPath;
use YoudsFramework\Util\ArrayPathDefinition;
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
 * ValidationManager provides management for request parameters and their
 * associated validators.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage validator
 *
 * @author     Uwe Mesecke <uwe@mesecke.net>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Manager extends ParameterHolder implements IValidatorContainer
{
	/**
	 * @var        DependencyManager The dependency manager.
	 */
	protected $dependencyManager = null;

	/**
	 * @var        array An array of child validators.
	 */
	protected $children = array();

	/**
	 * @var        Context The context instance.
	 */
	protected $context = null;

	/**
	 * @var        ValidationReport The report container storing the validation results.
	 */
	protected $report = null;

	/**
	 * All request variables are always available.
	 */
	const MODE_RELAXED = 'relaxed';

	/**
	 * All request variables are available when no validation defined else only 
	 * validated request variables are available.
	 */
	const MODE_CONDITIONAL = 'conditional';

	/**
	 * Only validated request variables are available.
	 */
	const MODE_STRICT = 'strict';

	/**
	 * Initializes the validator manager.
	 *
	 * @param      Context The context instance.
	 * @param      array        The initialization parameters.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		if(isset($parameters['mode'])) {
			if(!in_array($parameters['mode'], array(self::MODE_RELAXED, self::MODE_CONDITIONAL, self::MODE_STRICT))) {
				throw new Configuration('Invalid validation mode "' . $parameters['mode'] . '" specified');
			}
		} else {
			$parameters['mode'] = self::MODE_STRICT;
		}

		$this->context = $context;
		$this->setParameters($parameters);

		$this->dependencyManager = new DependencyManager();
		$this->report = new ValidationReport();
		$this->children = array();
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public final function getContext()
	{
		return $this->context;
	}
	
	/**
	 * Retrieve the validation result report container of the last validation run.
	 *
	 * @return     ValidationReport The result report container.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getReport()
	{
		return $this->report;
	}

	/**
	 * Creates a new validator instance.
	 *
	 * @param      string The name of the class implementing the validator.
	 * @param      array The argument names.
	 * @param      array The error messages.
	 * @param      array The validator parameters.
	 * @param      IValidatorContainer The parent (will use the validation 
	 *                                      manager if null is given)
	 * @return     Validator
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function createValidator($class, array $arguments, array $errors = array(), $parameters = array(), ?IValidatorContainer $parent = null)
	{
		if($parent === null) {
			$parent = $this;
		}		
		$obj = new $class;
		$obj->initialize($this->getContext(), $parameters, $arguments, $errors);
		$parent->addChild($obj);

		return $obj;
	}

	/**
	 * Clears the validation manager for reuse
	 *
	 * clears the validator manager by resetting the dependency and error
	 * manager and removing all validators after calling their shutdown
	 * method so they can do a save shutdown.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function clear()
	{
		$this->dependencyManager->clear();

		$this->report = new ValidationReport();

		foreach($this->children as $child) {
			$child->shutdown();
		}
		$this->children = array();
	}

	/**
	 * Adds a new child validator.
	 *
	 * @param      Validator The new child validator.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function addChild(Validator $validator)
	{
		$name = $validator->getName();
		if(isset($this->children[$name])) {
			throw new Exception('A validator with the name "' . $name . '" already exists');
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
	 * Returns the dependency manager.
	 *
	 * @return     DependencyManager The dependency manager instance.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function getDependencyManager()
	{
		return $this->dependencyManager;
	}

	/**
	 * Gets the base path of the validator.
	 *
	 * @return     VirtualArrayPath The base path.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function getBase()
	{
		return new VirtualArrayPath($this->getParameter('base', ''));
	}

	/**
	 * Starts the validation process.
	 *
	 * @param      DataHolder The data which should be validated.
	 *
	 * @return     bool true, if validation succeeded.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function execute($parameters)
	{
		
		$success = true;
		$this->report = new ValidationReport();
		$result = Validator::SUCCESS;
		
		$req = $this->context->getRequest();
		
		$executedValidators = 0;
		foreach($this->children as $validator) {
			++$executedValidators;

			$validatorResult = $validator->execute($parameters);
			$result = max($result, $validatorResult);

			switch($validatorResult) {
				case Validator::SUCCESS:
					continue 2;
				case Validator::INFO:
					continue 2;
				case Validator::SILENT:
					continue 2;
				case Validator::NOTICE:
					continue 2;
				case Validator::ERROR:
					$success = false;
					continue 2;
				case Validator::CRITICAL:
					$success = false;
					break 2;
			}
		}
		$this->report->setResult($result);
		$this->report->setDependTokens($this->getDependencyManager()->getDependTokens());

		$ma = $req->getParameter('content_accessor');
		$aa = $req->getParameter('chain_accessor');
		$umap = $req->getParameter('use_content_chain_parameters');
		$mode = $this->getParameter('mode');

		if($executedValidators == 0 && $mode == self::MODE_STRICT) {
			
			// strict mode and no validators executed -> clear the parameters
			if($umap) {
				$maParam = $parameters->getParameter($ma);
				$aaParam = $parameters->getParameter($aa);
			}
			$parameters->clearAll();
			if($umap) {
				if($maParam) {
					$parameters->setParameter($ma, $maParam);
				}
				if($aaParam) {
					$parameters->setParameter($aa, $aaParam);
				}
			}
		}
		if($mode == self::MODE_STRICT || ($executedValidators > 0 && $mode == self::MODE_CONDITIONAL)) {
			
			// first, we explicitly unset failed arguments
			// the primary purpose of this is to make sure that arrays that failed validation themselves (e.g. due to array length validation, or due to use of operator validators with an argument base) are removed
			// that's of course only necessary if validation failed
			$failedArguments = $this->report->getFailedArguments();
			foreach($failedArguments as $argument) {
				$parameters->remove($argument->getSource(), $argument->getName());
			}
			
			// next, we remove all arguments from the request data that are not in the list of succeeded arguments
			// this will also remove any arguments that didn't have validation rules defined
			$succeededArguments = $this->report->getSucceededArguments();
			foreach($parameters->getSourceNames() as $source) {
				$sourceItems = $parameters->getAll($source);
				foreach(ArrayPathDefinition::getFlatKeyNames($sourceItems) as $name) {
					if(!isset($succeededArguments[$source . '/' . $name]) && (!$umap || ($source != DataHolder::SOURCE_PARAMETERS || ($name != $ma && $name != $aa)))) {
						$parameters->remove($source, $name);
					}
				}
			}
		}
		
		return $success;
	}

	/**
	 * Shuts the validation system down.
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
	 * Registers multiple validators.
	 *
	 * @param      array An array of validators.
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
	 * Adds an incident to the validation result. This will automatically adjust
	 * the field result table (which is required because one can still manually
	 * add errors either via Request::addError or by directly using this 
	 * method)
	 *
	 * @param      ValidationIncident The incident.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function addIncident(ValidationIncident $incident)
	{
		return $this->report->addIncident($incident);
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
		return $this->report->addArgumentResult($argument, $result, $validator);
	}}
?>
