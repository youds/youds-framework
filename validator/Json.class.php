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
 * JsonValidator verifies if a parameter contains a value that is valid
 * JSON and optionally exports the decoded value.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage validator
 *
 * @author     Thomas Bachem <mail@thomasbachem.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Json extends Validator {
	protected $jsonErrors = array(
		'depth',
		'state_mismatch',
		'ctrl_char',
		'syntax',
		'utf8',
		'recursion',
		'inf_or_nan',
		'unsupported_type',
	);
	
	/**
	 * Validates the input.
	 * 
	 * @return     bool The input is valid JSON.
	 * 
	 * @author     Thomas Bachem <mail@thomasbachem.com>
	 */
	protected function validate()
	{
		$json = $this->getData($this->getArgument());
		
		$ret = json_decode($json, $this->getParameter('assoc', true));
		
		if($json !== '' && $ret === null) {
			$jsonError = json_last_error();
			foreach($this->jsonErrors as $errorName) {
				$constName = 'JSON_ERROR_' . strtoupper($errorName);
				if(defined($constName) && constant($constName) === $jsonError) {
					$this->throwError($errorName);
					return false;
				}
			}
			
			$this->throwError();
			return false;
		} else {
			$this->export($ret);
			return true;
		}
	}
}

?>
