<?php
namespace YoudsFramework\Logging;
use YoudsFramework\Request\ParameterHolder;
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
 * LoggerAppender allows you to specify a destination for log data and 
 * provide a custom layout for it, through which all log messages will be 
 * formatted.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage logging
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Bob Zoller <bob@agavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class Appender extends ParameterHolder
{
	/**
	 * @var        Context An Context instance.
	 */
	protected $context = null;

	/**
	 * @var        LoggerLayout An LoggerLayout instance.
	 */
	protected $layout = null;

	/**
	 * Initialize the object.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->context = $context;
		
		$this->setParameters($parameters);
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context An Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the layout.
	 *
	 * @return     LoggerLayout A Layout instance, if it has been set, 
	 *                               otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Set the layout.
	 *
	 * @param      LoggerLayout A Layout instance.
	 *
	 * @return     LoggerAppender
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function setLayout(Logger $layout)
	{
		$this->layout = $layout;
		return $this;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	abstract function shutdown();

	/**
	 * Write log data to this appender.
	 *
	 * @param      Message Log data to be written.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	abstract function write(Message $message);
}

?>
