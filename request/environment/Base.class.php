<?php
namespace YoudsFramework\Request\Environment;
use YoudsFramework\Request\DataHolder;
use YoudsFramework\Context;
use YoudsFramework\Util\AttributeHolder;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Exceptions\Exception;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Request provides methods for manipulating client request information
 * such as attributes, errors and parameters. It is also possible to manipulate
 * the request method originally sent by the user.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage request
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class Base extends AttributeHolder
{
	/**
	 * @var        array An associative array of attributes
	 */
	protected $attributes = array();

	/**
	 * @var        array An associative array of errors
	 */
	protected $errors     = array();

	/**
	 * @var        string The request method name
	 */
	protected $method     = null;

	/**
	 * @var        Context An Context instance.
	 */
	protected $context    = null;

	/**
	 * @var        DataHolder The request data holder instance.
	 */
	private $requestData = null;

	/**
	 * @var        string The key used to lock the request, or null if no lock set
	 */
	private $key = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context An Context instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve this requests method.
	 *
	 * @return     string The request method name
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Constructor.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __construct()
	{
		$this->setParameters(array(
			'use_content_chain_parameters' => false,
			'content_accessor' => 'content',
			'chain_accessor' => 'chain',
			'request_data_holder_class' => 'DataHolder',
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
		$this->context = $context;
		
		if(isset($parameters['default_namespace'])) {
			$this->defaultNamespace = $parameters['default_namespace'];
			unset($parameters['default_namespace']);
		}
		$this->setParameters($parameters);
	}

	/**
	 * Set the request method.
	 *
	 * @param      string The request method name.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setMethod($method)
	{
		$this->method = $method;
	}

	/**
	 * Set the data holder instance of this request.
	 *
	 * @param      DataHolder The request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	final protected function setRequestData($rd)
	{
		if(!$this->isLocked()) {
			$this->requestData = $rd;
		}
	}

	/**
	 * Get the data holder instance of this request.
	 *
	 * @return     DataHolder The request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	final public function getRequestData()
	{
		if($this->isLocked()) {
			throw new Exception("Access to request data is locked during Action and Layout execution and while templates are rendered. Please use the local request data holder passed to your Action's or Layout's execute*() method to access request data.");
		}
		return $this->requestData;
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function startup()
	{
		if($this->getParameter('unset_input', true)) {
			// remove raw post data
			// can still be read from php://input, but we can't prevent that
			unset($GLOBALS['HTTP_RAW_POST_DATA']);
			
			// nuke argc and argc if necessary
			$rla = ini_get('register_long_arrays');
			if($rla) {
				trigger_error('Support for php.ini directive "register_long_arrays" is deprecated and may be dropped in a later version of Youds Framework. The setting is deprecated in PHP 5.3 and may be removed in PHP 5.4. Please refer to the PHP manual for details.', E_USER_DEPRECATED);
			}
			
			if(isset($_SERVER['argc'])) {
				$_SERVER['argc'] = 0;
				if(isset($GLOBALS['argc'])) {
					$GLOBALS['argc'] = 0;
				}
				if($rla) {
					$GLOBALS['HTTP_SERVER_VARS']['argc'] = 0;
				}
			}
			if(isset($_SERVER['argv'])) {
				$_SERVER['argv'] = array();
				if(isset($GLOBALS['argv'])) {
					$GLOBALS['argv'] = array();
				}
				if($rla) {
					$GLOBALS['HTTP_SERVER_VARS']['argv'] = array();
				}
			}
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function shutdown()
	{
	}
	
	/**
	 * Get a value by trying to find the given key in $_SERVER first, then in
	 * $_ENV. If nothing was found, return the key, or the given default value.
	 *
	 * @param      mixed  The key (or an array of keys) of the value to fetch.
	 * @param      mixed  A default return value, or null if the key should be
	 *                    returned (static return values can be defined this way).
	 *
	 * @author     David Zülke
	 */
	public static function getSourceValue($keys, $default = null)
	{
		$keys = (array)$keys;
		// walk over all possible keys
		foreach($keys as $key) {
			if(isset($_SERVER[$key])) {
				return $_SERVER[$key];
			} elseif(isset($_ENV[$key])) {
				return $_ENV[$key];
			}
		}
		if($default !== null) {
			return $default;
		}
		// nothing found so far. remember that the keys list is an array
		if($keys) {
			return end($keys);
		}
	}

	/**
	 * Whether or not the Request is locked.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function isLocked()
	{
		return $this->key !== null;
	}

	/**
	 * Lock or unlock the Request so request data can(not) be fetched anymore.
	 *
	 * @param      string The key to unlock, if the lock should be removed, or
	 *                    null if the lock should be set.
	 *
	 * @return     mixed The key, if a lock was set, or a boolean value indicating
	 *                   whether or not the unlocking was successful.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function toggleLock($key = null)
	{
		if(!$this->isLocked() && $key === null) {
			return $this->key = Toolkit::uniqid();
		} elseif($this->isLocked()) {
			if($this->key === $key) {
				$this->key = null;
				return true;
			}
			return false;
		}
	}
}

?>
