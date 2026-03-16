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
 * IsNotEmptyValidator verifies a parameter is not empty
 * 
 * The content of the input value is not verified in any manner, it is only
 * checked if the input value exists and is not empty. It lets the data holder
 * implementation decide what is regarded as empty.
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
class IsNotEmpty extends Validator {
	/**
	 * Validates the input.
	 * 
	 * @return     bool The value is set.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	protected function validate()
	{
		// we don't need to do any checking here because validate will only be
		// called when all values it needs were non empty.
		return true;
	}
}

?>
