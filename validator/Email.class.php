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
 * EmailValidator verifies if a parameter contains a value that qualifies
 * as an email address.
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
class Email extends Validator {
	/**
	 * Validates the input.
	 * 
	 * @return     bool The input is a valid email address.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	protected function validate()
	{
		$data = $this->getData($this->getArgument());
		if(!is_scalar($data)) {
			// non scalar values would cause notices
			$this->throwError();
			return false;
		}
		
		$extraChars = preg_quote('!#$%&\'*+-/=?^_`{|}~', '/');
		if(!preg_match('/^[a-z0-9' . $extraChars . ']+(\.[a-z0-9' . $extraChars . ']+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,6}$/iD', $data)) {
			$this->throwError('invalid');
			return false;
		}
		
		return true;
	}
}

?>
