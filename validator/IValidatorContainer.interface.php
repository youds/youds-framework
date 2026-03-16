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
 * IValidatorContainer is an interface for classes which contains several
 * child validators
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
interface IValidatorContainer
{
	/**
	 * Adds a new validator to the list of children.
	 * 
	 * @param      Validator new child
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function addChild(Validator $validator);

	/**
	 * Adds a intermediate result of an validator for the given argument
	 *
	 * @param      ValidationArgument The argument
	 * @param      int                     The arguments result.
	 * @param      Validator          The validator (if the error was caused
	 *                                     inside a validator).
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function addArgumentResult(ValidationArgument $argument, $result, $validator = null);

	/**
	 * Adds an incident to the validation result. 
	 *
	 * @param      ValidationIncident The incident.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function addIncident(ValidationIncident $incident);

	/**
	 * Returns a named child validator.
	 *
	 * @param      Validator The child validator.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getChild($name);

	/**
	 * Returns all child validators.
	 *
	 * @return     array An array of Validator instances.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getChilds();

	/**
	 * Fetches the dependency manager
	 * 
	 * @return     DependencyManager The dependency manager to be used
	 *                                    by child validators.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 */
	public function getDependencyManager();

}
?>
