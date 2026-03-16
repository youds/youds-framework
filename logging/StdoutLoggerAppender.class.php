<?php
namespace YoudsFramework\Logging;
use YoudsFramework\Context;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * StdoutLoggerAppender appends an Message to stdout.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage logging
 *
 * @author     Bob Zoller <bob@agavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class StdoutLoggerAppender extends StreamLoggerAppender
{
	/**
	 * Initialize the object.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$parameters['destination'] = 'php://stdout';
		// 'a' doesn't work on Linux
		// http://bugs.php.net/bug.php?id=45303
		$parameters['mode'] = 'w';
		
		parent::initialize($context, $parameters);
	}
}

?>
