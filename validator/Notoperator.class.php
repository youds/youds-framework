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
 * NOTOperatorValidator succeeds if the sub-validator failed
 *
 * Parameters:
 *   'skip_errors' do not submit errors of child validators to validator manager
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @author     Ross Lawley <ross.lawley@gmail.com>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Notoperator extends OperatorValidator
{
	/**
	 * Checks if operator has more then one child validator.
	 * 
	 * @throws     Exceptions\Validator If the operator has more then
	 *                                            one child validator
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	protected function checkValidSetup()
	{
		if(count($this->children) != 1) {
			throw new Validator('NOT allows only 1 child validator');
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
		// prevent reporting of any child validators
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
		// prevent reporting of any child validators
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
		// prevent reporting of any child validators
	}

	/**
	 * Validates the operator by returning the inverse result of the child 
	 * validator
	 * 
	 * @return     bool True if the child validator failed.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	protected function validate()
	{
		$children = $this->children;
		$child = array_shift($children);
		$result = $child->execute($this->validationParameters);
		if($result == Validator::CRITICAL || $result == Validator::SUCCESS) {
			$this->result = max(Validator::ERROR, $result);
			$this->throwError(null, $child->getFullArgumentNames());
			return false;
		} else {
			// lets mark the fields of the child validator all as successful
			$affectedFields = $child->getFullArgumentNames();
			foreach($affectedFields as $field) {
				parent::addArgumentResult(new ValidationArgument($field, $this->getParameter('source')), Validator::SUCCESS, $this);
			}
			return true;
		}
	}	
}

?>
