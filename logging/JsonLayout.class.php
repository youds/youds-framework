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
 * JsonLoggerLayout is an LoggerLayout that will return a JSON
 * representation of the Message or parts of it, depending on the
 * configuration.
 * 
 * Parameter "mode" controls the four possible modes of operation:
 *   'parameters' - serialize all parameters of the message
 *   'full'       - serialize the entire Message object
 *   'message'    - serialize the value of Message::getMessage()
 *   'parameter'  - serialize only one parameter of the object. By default, this
 *                  is "message"; can be changed using parameter "parameter".
 * Parameter "parameter" controls which parameter of the Message
 * object is used when "mode" is "parameter". Defaults to "message".
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage logging
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class JsonLayout extends Logger
{
	/**
	 * Format a message.
	 *
	 * @param      Message An Message instance.
	 *
	 * @return     string The Message object as a JSON-encoded string.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function format(Message $message)
	{
		switch($this->getParameter('mode', 'parameters')) {
			case 'full':
				$value = $message;
				break;
			case 'message':
				$value = $message->getMessage();
				break;
			case 'parameter':
				$value = $message->getParameter($this->getParameter('parameter', 'message'));
				break;
			default:
				$value = $message->getParameters();
		}
		
		return json_encode($value);
	}
}

?>
