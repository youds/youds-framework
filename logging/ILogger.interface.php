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
 * ILogger is the interface for all Logger implementations
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
interface ILogger
{
	/**
	 * Fatal level.
	 *
	 */
	const FATAL = 1;

	/**
	 * Error level.
	 *
	 */
	const ERROR = 2;

	/**
	 * Warning level.
	 *
	 */
	const WARN = 4;

	/**
	 * Information level.
	 *
	 */
	const INFO = 8;

	/**
	 * Debug level.
	 *
	 */
	const DEBUG = 16;

	/**
	 * All levels. (2^32-1)
	 *
	 */
	const ALL = 4294967295;

	/**
	 * Log a message.
	 *
	 * @param      Message A Message instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function log(Message $message);

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
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setAppender($name, Appender $appender);

	/**
	 * Returns a list of appenders for this logger.
	 *
	 * @return     array An associative array of appender names and instances.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getAppenders();

	/**
	 * Set the level.
	 *
	 * @param      int A log level.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setLevel($level);

	/**
	 * Get the level.
	 *
	 * @author     Peter Limbach <peter.limbach@gmail.com>
	 */
	public function getLevel();

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function shutdown();
}

?>
