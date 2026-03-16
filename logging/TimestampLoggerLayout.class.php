<?php
namespace YoudsFramework\Logging;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * TimestampLoggerLayout prepends the current date and time to the message.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage logging
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class TimestampLoggerLayout extends Logger
{
	
	public function initialize() {
		
	}
	
	/**
	 * Format a message.
	 *
	 * @param      Message An Message instance.
	 *
	 * @return     string A formatted message.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function format (Message $message) :string
	{
		return $message->__toString();
	}
}

?>
