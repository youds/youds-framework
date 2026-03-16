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
 * XmlHandler is the base config handler that deals with \DOMDocuments
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class XmlHandler extends BaseHandler implements IXmlHandler {
	
	/**
	 * @var        Context The context to work with (if available).
	 */
	protected $context = null;
	
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
	public function initialize(?Context $context = null, $parameters = array())
	{
		$this->context = $context;
		$this->setParameters($parameters);
	}
}

?>
