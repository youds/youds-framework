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
 * SetValidator only exports a value and always succeeds
 * 
 * Parameters:
 *   'value'  value that should be exported
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
class Set extends Validator {
	/**
	 * Exports the value and returns true.
	 * 
	 * @return     bool Always returns true.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	protected function validate()
	{
		$this->export($this->getParameter('value'));
		
		return true;
	}
}

?>
