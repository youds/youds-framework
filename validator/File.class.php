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
 * FileValidator verifies the size and extension of a file
 * 
 * @see        BaseFile
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
class File extends BaseFile
{
	/**
	 * Validates the input
	 * 
	 * @return     bool The file is valid according to given parameters.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	protected function validate()
	{
		return parent::validate();
	}
}

?>
