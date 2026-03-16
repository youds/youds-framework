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
 * XOROperatorValidator succeeds if only one of two sub-validators 
 * succeeded
 *
 * Parameters:
 *   'skip_errors'  don't submit errors of child validators to validator manager
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
class Xoroperator extends OperatorValidator
{
	/**
	 * Checks if this operator has a exactly 2 child validators.
	 * 
	 * @throws     Exceptions\Validator If the operator doesn't have 
	 *                                            exactly 2 child validators.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	protected function checkValidSetup()
	{
		if(count($this->children) != 2) {
			throw new Validator('XOR allows only exact 2 child validators');
		}
	}

	/**
	 * Validates the operator by returning the by XORing the results of the child
	 * validators.
	 * 
	 * @return     bool True if exactly one child validator succeeded.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	protected function validate()
	{
		$children = $this->children;
		
		$child1 = array_shift($children);
		$result1 = $child1->execute($this->validationParameters);
		if($result1 == Validator::CRITICAL) {
			$this->result = $result1;
			$this->throwError();
			return false;
		}
		
		$child2 = array_shift($children);
		$result2 = $child2->execute($this->validationParameters);
		if($result2 == Validator::CRITICAL) {
			$this->result = $result2;
			$this->throwError();
			return false;
		}
		
		$this->result = max($result1, $result2);
		
		if(($result1 == Validator::SUCCESS) xor ($result2 == Validator::SUCCESS)) {
			return true;
		} else {
			$this->throwError();
			return false;
		}
	}	
}

?>
