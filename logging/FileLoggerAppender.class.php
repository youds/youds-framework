<?php
namespace YoudsFramework\Logging;
use YoudsFramework\Context;
use YoudsFramework\Exceptions\Logging;
// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * FileLoggerAppender appends Messages to a given file.
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
class FileLoggerAppender extends StreamLoggerAppender
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
		// for < 0.1 BC
		if(isset($parameters['file'])) {
			$parameters['destination'] = $parameters['file'];
			unset($parameters['file']);
		}
		
		parent::initialize($context, $parameters);

	}

	/**
	 * Retrieve the file handle for this FileAppender.
	 *
	 * @throws     Exceptions\Logging if file cannot be opened for
	 *                                          appending.
	 *
	 * @return     resource The open file handle.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 */
	protected function getHandle()
	{
		$destination = $this->getParameter('destination');
		if(is_null($this->handle) && (!is_writable(dirname($destination)) || (file_exists($destination) && !is_writable($destination)))) {
			throw new Logging('Cannot open file "' . $destination . '", please check permissions on file or directory.');
		}
		
		return parent::getHandle();
	}
}

?>
