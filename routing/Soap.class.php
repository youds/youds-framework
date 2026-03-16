<?php
namespace YoudsFramework\Routing;
use YoudsFramework\Context;

use YoudsFramework\Config\Cache;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Soap handles the routing for SOAP web service requests.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage routing
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Soap extends Webservice
{
	/**
	 * Initialize the routing instance.
	 *
	 * @param      Context A Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		// must always be on
		// don't do this after parent::initialize() as Webservice::initialize() checks the value already
		$parameters['enabled'] = true;
		
		parent::initialize($context, $parameters);
	}
	
	/**
	 * Returns the local filesystem path to the WSDL file built from routing.xml.
	 *
	 * @return     string A fully qualified filesystem path.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getWsdlPath()
	{
		$path = $this->getParameter('wsdl', Config::get('core.src_dir') . '/routing/soap/wsdl.xml');
		
		return Cache::checkConfig($path, $this->context->getName());
	}
}

?>
