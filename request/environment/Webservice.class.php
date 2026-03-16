<?php
namespace YoudsFramework\Request\Environment;
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
 * Webservice is the base class for Web Service requests
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage request
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class Webservice extends Base
{
	/**
	 * @var        string The Input Data.
	 */
	protected $input = '';
	
	/**
	 * @var        string The method called by the web service request.
	 */
	protected $invokedMethod = '';
	
	/**
	 * Constructor.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setParameters(array(
			'request_data_holder_class' => 'Request\Webservice',
		));
	}
	
	/**
	 * Initialize this Request.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing this Request.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		// empty $_POST just to be sure
		$_POST = array();
		
		// grab the POST body
		$this->input = file_get_contents('php://input');
		
		parent::initialize($context, $parameters);
	}
	
	/**
	 * Get the input data, usually the request from the POST body.
	 *
	 * @return     string The input data.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getInput()
	{
		return $this->input;
	}
	
	/**
	 * Set the input data. Useful for debugging purposes.
	 *
	 * @param      string The input data.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setInput($input)
	{
		$this->input = $input;
	}
	
	/**
	 * Set the name of the method called by the web service request.
	 *
	 * @return     string A method name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setInvokedMethod($method)
	{
		$this->invokedMethod = $method;
		
		// let the routing update its input
		$this->context->getRouting()->updateInput();
	}
	
	/**
	 * Get the name of the method called by the web service request.
	 *
	 * @return     string A method name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getInvokedMethod()
	{
		return $this->invokedMethod;
	}
}

?>
