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
 * Logger provides an easy way to manage multiple log destinations and 
 * write to them all simultaneously.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage logging
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Logger implements ILogger
{
	/**
	 * @var        array An array of LoggerAppenders.
	 */
	protected $appenders = array();

	/**
	 * @var        int Logging level.
	 */
	protected $level = Logger::WARN;

	/**
	 * Log a message.
	 *
	 * @param      Message A Message instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function log(Message $message)
	{
		// get message level
		$msgLevel = $message->getLevel();

		if($this->level & $msgLevel) {
			foreach($this->appenders as $appender) {
				$appender->write($message);
			}
		}
	}

	/**
	 * Set an appender.
	 *
	 * If an appender with the name already exists, an exception will be thrown.
	 *
	 * @param      string              An appender name.
	 * @param      LoggerAppender An Appender instance.
	 *
	 * @throws     Exceptions\Logging If an appender with the name 
	 *                                          already exists.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function setAppender($name, Appender $appender)
	{
		if(!isset($this->appenders[$name])) {
			$this->appenders[$name] = $appender;
			return;
		}

	}

	/**
	 * Returns a list of appenders for this logger.
	 *
	 * @return     array An associative array of appender names and instances.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getAppenders()
	{
		return $this->appenders;
	}

	/**
	 * Set the level.
	 *
	 * @param      int A log level.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function setLevel($level)
	{
		$this->level = $level;
	}

	/**
	 * Get the level.
	 *
	 * @author     Peter Limbach <peter.limbach@gmail.com>
	 */
	public function getLevel()
	{
		return $this->level;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function shutdown()
	{
	}
}

?>
