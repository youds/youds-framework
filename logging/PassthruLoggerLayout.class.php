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
 * PassthruLoggerLayout is an LoggerLayout that will return the entire
 * Message or parts of it, depending on the configuration.
 * 
 * Parameter "mode" controls the four possible modes of operation:
 *   'to_string' - return Message::__toString() (default)
 *   'full'      - return the full Message object
 *   'message'   - return Message::getMessage()
 *   'parameter' - return only one parameter of the object. By default, this is
 *                 "message"; can be changed using parameter "parameter".
 * Parameter "parameter" controls which parameter of the Message
 * object is used when "mode" is "parameter". Defaults to "message".
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
class PassthruLoggerLayout extends Logger
{
	/**
	 * Format a message.
	 *
	 * @param      Message An Message instance.
	 *
	 * @return     string A formatted message.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function format(Message $message)
	{
		switch($this->getParameter('mode', 'to_string')) {
			case 'full':
				return $message;
			case 'message':
				return $message->getMessage();
			case 'parameter':
				return $message->getParameter($this->getParameter('parameter', 'message'));
			default:
				return $message->__toString();
		}
	}
}

?>
