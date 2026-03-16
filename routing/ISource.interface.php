<?php
namespace YoudsFramework\Routing;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * ISource allows you to provide sources for the routing
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage routing
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
interface ISource
{
	/**
	 * Retrieves the value for a given entry from the source.
	 *
	 * @param      array An array with the name parts for the entry.
	 * 
	 * @return     mixed The value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getSource(array $parts);
}

?>
