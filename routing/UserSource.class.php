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
 * UserSource allows you to provide an user source for the routing
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
class UserSource implements ISource
{
	/**
	 * @var        ISecurityUser An user instance.
	 */
	protected $user = null;

	/**
	 * Constructor.
	 *
	 * @param      ISecurityUser An user instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function __construct($user)
	{
		$this->user = $user;
	}

	/**
	 * Retrieves the value for a given entry from the source.
	 *
	 * @param      array An array with the name parts for the entry.
	 * 
	 * @return     mixed The value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getSource(array $parts)
	{
		if($parts[0] == 'authenticated') {
			return (int) $this->user->isAuthenticated();
		} elseif($parts[0] == 'credentials' && count($parts) > 1) {
			// throw the 'credentials' entry away and check with the parameters left
			array_shift($parts);
			return (int) $this->user->hasCredentials($parts);
		}

		return null;
	}
}

?>
