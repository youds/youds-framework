<?php
namespace YoudsFramework\Request;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Interface for DataHolders that allow access to Parameters.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage request
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
interface IParameters
{
	public function hasParameter($name);
	
	public function isParameterValueEmpty($name);
	
	public function &getParameter($name, $default = null);
	
	public function &getParameters();
	
	public function getParameterNames();
	
	public function getFlatParameterNames();
	
	public function setParameter($name, $value);
	
	public function setParameters(array $parameters);
	
	public function &removeParameter($name);
	
	public function clearParameters();
	
	public function mergeParameters($other);
}

?>
