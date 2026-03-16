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
 * ArraylengthValidator verifies the length (count()) constraints for an array
 *
 * Parameters:
 *   'min'       The array should contain at least 'min' elements
 *   'max'       The array should contain at most 'max' elements
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage validator
 *
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $id$
 */
class Arraylength extends Validator {
	/**
	 * Returns whether all arguments are set in the validation input parameters.
	 * Set means anything but empty string.
	 * Different to Validator::checkAllArgumentsSet() in that it will not
	 * rely on the isValueEmpty() information from the respective request data
	 * holder class, but instead pull the value and check if it is an array.
	 *
	 * @param      bool Whether an error should be thrown for each missing 
	 *                  argument if this validator is required.
	 *
	 * @return     bool Whether the arguments are set.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	protected function checkAllArgumentsSet($throwError = true)
	{
		// copied from Validator::checkAllArgumentsSet()
		$isRequired = $this->getParameter('required', true);
		$paramType = $this->getParameter('source');
		$result = true;

		foreach($this->getArguments() as $argument) {
			$pName = $this->curBase->pushRetNew($argument)->__toString();
			// can't do this:
			// if($this->validationParameters->isValueEmpty($paramType, $pName)) {
			// as for example Web::isFileValueEmpty() returns false if the element is not an instance of UploadedFile
			// as this may happen in the future with other parameter types etc, it's safer to manually check if the value exists and is an array
			if(!$this->validationParameters->has($paramType, $pName) || !is_array($this->validationParameters->get($paramType, $pName))) {
				if($throwError && $isRequired) {
					$this->throwError('required', $pName);
				}
				$result = false;
			}
		}
		return $result;
	}
	
	/**
	 * Validates the input.
	 * 
	 * @return     bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	protected function validate()
	{
		$data = $this->getData($this->getArgument());
		if(!is_array($data)) {
			// we can only count() arrays
			$this->throwError();
			return false;
		}
		
		$count = count($data);
		
		if($this->hasParameter('min') && $count < $this->getParameter('min')) {
			$this->throwError('min');
			return false;
		}
		
		if($this->hasParameter('max') && $count > $this->getParameter('max')) {
			$this->throwError('max');
			return false;
		}
		
		return true;
	}
}

?>
