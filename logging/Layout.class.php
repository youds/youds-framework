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
 * LoggerLayout allows you to specify a message layout for log messages.
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
abstract class Logger extends ParameterHolder
{
	/**
	 * @var        Context An Context instance.
	 */
	protected $context = null;

	/**
	 * @var        string A message layout.
	 */
	protected $layout = null;

	/**
	 * Initialize the Layout.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->context = $context;
		$this->parameters = $parameters;
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
	 * Format a message.
	 *
	 * @param      Message A Message instance.
	 *
	 * @return     string A formatted message.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	abstract function format(Message $message);

	/**
	 * Retrieve the message layout.
	 *
	 * @return     string A message layout.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Set the message layout.
	 *
	 * @param      string A message layout.
	 *
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;
	}
}

?>
