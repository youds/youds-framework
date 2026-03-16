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
 * FoundValidator verifies a parameter is set
 * 
 * The content of the input value is not verified in any manner, it is only
 * checked if the input value exists. (see isset() in PHP)
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Found extends Validator {
	/**
	 * We need to return true here when this validator is required, because 
	 * otherwise the is*ValueEmpty check would make empty but set fields not 
	 * reach the validate method.
	 *
	 * @see        Validator::checkAllArgumentsSet
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	protected function checkAllArgumentsSet($throwError = true)
	{
		if($this->getParameter('required', true)) {
			return true;
		} else {
			return parent::checkAllArgumentsSet($throwError);
		}
	}

	/**
	 * Validates the input.
	 * 
	 * @return     bool The value is set.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	protected function validate()
	{
		$params = $this->validationParameters->getAll($this->getParameter('source'));

		foreach($this->getArguments() as $argument) {
			if(!$this->curBase->hasValueByChildPath($argument, $params)) {
				$this->throwError();
				return false;
			}
		}

		return true;
	}
}

?>
