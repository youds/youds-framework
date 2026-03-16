<?php
namespace YoudsFramework\Logging;
use YoudsFramework\Context;
use YoudsFramework\Config;


// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * RotatingFileLoggerAppender extends FileLoggerAppender by enabling 
 * per-day log files and removing unwanted old log files.
 *
 * Required parameters:
 *
 * # dir    - [none]              - Log directory
 *
 * Optional parameters:
 *
 * # cycle  - [7]                 - Number of log files to keep.
 * # prefix - [core.project_name-]    - Log filename prefix.
 * # suffix - [.log]              - Log filename suffix.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage logging
 *
 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class RotatingFileLoggerAppender extends FileLoggerAppender
{

	
	/**
	 * Initialize the object.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$cycle = 7;
		if (Config::get('core.name'))
			$prefix = str_replace(' ', '_', Config::get('core.name')) . '-';
		else
			$prefix = 'yf-';
		$suffix = '.log';

		if(!isset($parameters['dir'])) {
			throw new Logging('No directory defined for rotating logging.');
		}

		$dir = $parameters['dir'];

		if(isset($parameters['cycle'])) {
			$cycle = (int)$parameters['cycle'];
		}
		
		if($cycle < 1) {
			throw new Logging('Logging rotation cycle cannot be smaller than 1');
		}

		if(isset($parameters['prefix'])) {
			$prefix = $parameters['prefix'];
		}

		if(isset($parameters['suffix'])) {
			$suffix = $parameters['suffix'];
		}

		$logfile = $dir . $prefix . date('Y-m-d') . $suffix;

		if(!file_exists($logfile)) {

			// todays log file didn't exist so we need to create it
			// and at the same time we'll remove all unwanted history files

			$remove = glob($dir . $prefix . '*-*-*' . $suffix);
			if($remove === false) {
				// who cares, it's just log files
				$remove = array();
			}
			
			foreach(array_slice($remove, 0, -$cycle + 1) as $filename) {
				unlink($filename);
			}
		}

		//it's all up to the parent after this
		$parameters['file'] = $logfile;
		parent::initialize($context, $parameters);
	}
}

?>
