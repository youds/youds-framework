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
 * Soap is an implementation for handling SOAP Web Services using
 * PHP 5's SOAP extension.
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
class Soap extends Webservice
{
	/**
	 * Constructor.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setParameters(array(
			'request_data_holder_class' => 'Request\Soap',
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
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$rdhc = $this->getParameter('request_data_holder_class');
		$this->setRequestData(new $rdhc(array(
			constant("$rdhc::SOURCE_PARAMETERS") => array(),
			constant("$rdhc::SOURCE_HEADERS") => array(),
		)));
		
		$this->setMethod($this->getParameter('default_method', 'read'));
	}
}

?>
