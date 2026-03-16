<?php
namespace YoudsFramework\Routing;
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
 * Webservice handles the routing for Web Service requests.
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
class Webservice extends Routing implements Routing
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
		parent::initialize($context, $parameters);
		
		if(!$this->isEnabled()) {
			return;
		}
		
		$this->updateInput();
	}
	
	/**
	 * Set the name of the called web service method as the routing input.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function updateInput()
	{
		$this->input = $this->context->getRequest()->getInvokedMethod();
	}
}

?>
