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
 * StreamLoggerAppender appends Messages to a given stream.
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
class StreamLoggerAppender extends Appender
{
	/**
	 * @var        The resource of the stream this appender is writing to.
	 */
	protected $handle = null;

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
		parent::initialize($context, $parameters);

		if(!isset($parameters['destination'])) {
			throw new Exception('No destination given for appending');
		}
	}

	/**
	 * Retrieve the handle for this stream appender.
	 *
	 * @throws     Exceptions\Logging if stream cannot be opened for
	 *                                          appending.
	 *
	 * @return     resource The opened resource handle.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	protected function getHandle()
	{
		$destination = $this->getParameter('destination');
		if(is_null($this->handle)) {
			$this->handle = fopen($destination, $this->getParameter('mode', 'a'));
			if(!$this->handle) {
				throw new Logging('Cannot open stream "' . $destination . '".');
			}
		}
		return $this->handle;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * If open, close the stream handle.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function shutdown()
	{
		if(!is_null($this->handle)) {
			fclose($this->handle);
		}
	}

	/**
	 * Write log data to this appender.
	 *
	 * @param      Message Log data to be written.
	 *
	 * @throws     Exceptions\Logging if no Layout is set or the stream
	 *                                          cannot be written.
	 *
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	public function write(Message $message)
	{
		if(($layout = $this->getLayout()) === null) {
			throw new Logging('No Layout set');
		}

		$str = sprintf("%s\n", $this->getLayout()->format($message));
		if(fwrite($this->getHandle(), $str) === false) {
			throw new Logging('Cannot write to stream "' . $this->getParameter('destination') . '".');
		}
	}
}

?>
