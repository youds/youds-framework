<?php
namespace YoudsFramework\Config;
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
 * IXmlHandler is the interface that config handlers may implement to
 * indicate that they wish to process a \DOMDocument directly.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
interface IXmlHandler {
	/**
	 * Initialize this Handler.
	 *
	 * @param      Context The context to work with (if available).
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing the
	 *                                                 Handler
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(?Context $context = null, $parameters = array());
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      XmlDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     Exceptions\Parse If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function execute(XmlDomDocument $document);
}

?>
