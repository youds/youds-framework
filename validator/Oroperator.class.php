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
 * OROperatorValidator succeeds if at least one sub-validators succeeded
 *
 * Parameters:
 *   'skip_errors' do not submit errors of child validators to validator manager
 *   'break'       break the execution of child validators after first success
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
class Oroperator extends Operator
{
	/**
	 * Executes the child validators.
	 * 
	 * @return     bool True if at least one child validator succeeded.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	protected function validate()
	{
		$return = false;
		
		foreach($this->children as $child) {
			$result = $child->execute($this->validationParameters);
			$this->result = max($this->result, $result);

			if($result == Validator::SUCCESS) {
				// if one child validator succeeds, the whole operator succeeds
				$return = true;
				$this->result = $result;
				if($this->getParameter('break')) {
					break;
				}
			} elseif($result == Validator::CRITICAL) {
				break;
			}
		}
		
		if(!$return) {
			$this->throwError();
		}

		return $return;
	}	
}

?>
