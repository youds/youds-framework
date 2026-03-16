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
 * ANDOperatorValidator only succeeds if all sub-validators succeeded
 * 
 * Parameters:
 *   'skip_errors' do not submit errors of child validators to validator manager
 *   'break'       break the execution of child validators after first failure
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
class Andoperator extends Operator
{
	/**
	 * Validates the operator by executing the child validators.
	 * 
	 * @return     bool True if all child validators resulted successful.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	protected function validate()
	{
		$return = true;
		
		foreach($this->children as $child) {
			$result = $child->execute($this->validationParameters);
			$this->result = max($result, $this->result);
			if($result > Validator::SUCCESS) {
				// if one validator fails, the whole operator fails
				$return = false;
				$this->throwError();
				if($this->getParameter('break') || $result == Validator::CRITICAL) {
					break;
				}
			}
		}
		
		return $return;
	}	
}

?>
