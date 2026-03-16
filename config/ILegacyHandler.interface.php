<?php
namespace YoudsFramework\Config;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * ILegacyHandler is the interface that all old-style config handlers
 * which deal with ValueHolders and parse configs themselves implement.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
interface ILegacyHandler {
	/**
	 * Initialize this Handler.
	 *
	 * @param      string The path to a validation file for this config handler.
	 * @param      string The parser class to use.
	 * @param      array An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing the
	 *                                                 Handler
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function initialize($validationFile = null, $parser = null, $parameters = array());
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string Name of the executing context (if any).
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     Exceptions\Unreadable If a requested configuration
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     Exceptions\Parse If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function execute($config, $context = null);
}

?>
