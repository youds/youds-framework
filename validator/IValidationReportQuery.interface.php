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
 * IValidationReportQuery allows queries against the validation run report.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage validator
 *
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
interface IValidationReportQuery
{
	/**
	 * Returns a new IValidationReportQuery which returns only the incidents
	 * for the given argument (and the other existing filter rules).
	 * 
	 * @param      ValidationArgument|string|array The argument instance, or
	 *                                                  a parameter name, or an
	 *                                                  array of these elements.
	 * 
	 * @return     IValidationReportQuery
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function byArgument($argument);
	
	/**
	 * Returns a new IValidationReportQuery which contains only the incidents
	 * for the given validator (and the other existing filter rules).
	 * 
	 * @param      string|array The name of the validator, or an array of names.
	 * 
	 * @return     IValidationReportQuery
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function byValidator($name);
	
	/**
	 * Returns a new IValidationReportQuery which contains only the incidents
	 * for the given error name (and the other existing filter rules).
	 * 
	 * @param      string|array The name of the error, or an array of names.
	 * 
	 * @return     IValidationReportQuery
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function byErrorName($name);
	
	/**
	 * Returns a new IValidationReportQuery which contains only the incidents
	 * of the given severity or higher (and the other existing filter rules).
	 * 
	 * @param      int The minimum severity.
	 * 
	 * @return     IValidationReportQuery
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function byMinSeverity($minSeverity);
	
	/**
	 * Returns a new IValidationReportQuery which contains only the incidents
	 * of the given severity or lower (and the other existing filter rules).
	 * 
	 * @param      int The maximum severity.
	 * 
	 * @return     IValidationReportQuery
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function byMaxSeverity($maxSeverity);
	
	/**
	 * Retrieves all incidents which match the currently defined filter rules.
	 * 
	 * @return     array An array of ValidationIncident objects.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getIncidents();
	
	/**
	 * Retrieves all ValidationError objects which match the currently
	 * defined filter rules.
	 * 
	 * @return     array An array of ValidationError objects.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getErrors();
	
	/**
	 * Retrieves all error messages which match the currently defined filter
	 * rules.
	 * 
	 * @return     array An array of message strings.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getErrorMessages();
	
	/**
	 * Retrieves all ValidationArgument objects which match the currently
	 * defined filter rules.
	 * 
	 * @return     array An array of ValidationArgument objects.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getArguments();
	
	/**
	 * Check if there are any incidents matching the currently defined filter
	 * rules.
	 * 
	 * @return     bool Whether or not any incidents exist for the currently
	 *                  defined filter rules.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function has();
	
	/**
	 * Get the number of incidents matching the currently defined filter rules.
	 * 
	 * @return     int The number of incidents matching the currently defined
	 *                 filter rules.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function count();
	
	/**
	 * Retrieves the highest validation result code of the collection composed of
	 * the currently defined filter rules.
	 *
	 * @return     int An Validator::* severity constant, or null if there is
	 *                 no result for this filter combination. Please remember to
	 *                 do a strict === comparison if you are comparing against
	 *                 Validator::SUCCESS.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getResult();
}

?>
