<?php
namespace YoudsFramework\Logging;
use YoudsFramework\Request\ParameterHolder;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Message, by default, holds a message and a priority level.
 * It is intended to be passed to a Logger.
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
class Message extends ParameterHolder
{
	/**
	 * Constructor.
	 *
	 * @param      string optional message
	 * @param      int    optional priority level
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function __construct($message = null, $level = Logger::INFO)
	{
		$this->setParameter('message', $message);
		$this->setParameter('level', $level);
	}

	/**
	 * toString method.
	 *
	 * @return     string The message.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function __toString()
	{
		return(is_array($this->getParameter('message')) ? implode("\n", $this->getParameter('message')) : (string) $this->getParameter('message'));
	}

	/**
	 * Set the message.
	 *
	 * @param      string The message to set.
	 *
	 * @return     Message
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function setMessage($message)
	{
		$this->setParameter('message', $message);
		return $this;
	}

	/**
	 * Append to the message.
	 *
	 * @param      string Message to append.
	 *
	 * @return     Message
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function appendMessage($message)
	{
		$this->appendParameter('message', $message);
		return $this;
	}

	/**
	 * Set the priority level.
	 *
	 * @param      int The priority level.
	 *
	 * @return     Message
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function setLevel($level)
	{
		$this->setParameter('level', $level);
		return $this;
	}

	/**
	 * Get the priority level.
	 *
	 * @return     int The priority level.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function getLevel()
	{
		return $this->getParameter('level');
	}

	/**
	 * Get the message.
	 *
	 * @return     mixed The message.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function getMessage()
	{
		return $this->getParameter('message');
	}
}

?>
