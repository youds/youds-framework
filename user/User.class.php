<?php
namespace YoudsFramework;
use YoudsFramework\Util\AttributeHolder;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * User wraps a client session and provides accessor methods for user
 * attributes. It also makes storing and retrieving multiple page form data
 * rather easy by allowing user attributes to be stored in namespaces, which
 * help organize data.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage user
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class User extends AttributeHolder
{
	/**
	 * @var        Context An Context instance.
	 */
	protected $context = null;

	/**
	 * @var        string Storage namespace where user attributes are put.
	 */
	protected $storageNamespace = 'org.framework.user.general';

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context An Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the Storage namespace
	 *
	 * @return     string The Storage namespace
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getStorageNamespace()
	{
		return $this->storageNamespace;
	}

	/**
	 * Initialize this User.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing this User.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->context = $context;

		if(isset($parameters['default_namespace'])) {
			$this->defaultNamespace = $parameters['default_namespace'];
		}
		
		if(isset($parameters['storage_namespace'])) {
			$this->storageNamespace = $parameters['storage_namespace'];
		}

		$this->setParameters($parameters);
		
		// read data from storage		
		$this->attributes = $context->getStorage()->read($this->storageNamespace);

		if($this->attributes == null) {
			// initialize our attributes array
			$this->attributes = array();
		}
	}

	/**
	 * Startup the user.
	 *
	 * You'd usually try to auth from a cookie here etc.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function startup()
	{
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function shutdown()
	{
		// write attributes to the storage
		$this->getContext()->getStorage()->write($this->storageNamespace, $this->attributes);
	}
}

?>
