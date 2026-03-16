<?php
namespace YoudsFramework;
use YoudsFramework\Request\DataHolder;
use YoudsFramework\Config\Cache;
use YoudsFramework\Filter\Chain;
use YoudsFramework\Util\AttributeHolder;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Exceptions\Exception;
use YoudsFramework\Exceptions\Configuration;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * A container used for each chain execution that holds necessary information,
 * such as the output type, the response etc.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage controller
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class ExecutionContainer extends AttributeHolder
{
	/**
	 * @var        Context The context instance.
	 */
	protected $context = null;

	/**
	 * @var        Chain The container's filter chain.
	 */
	protected $filterChain = null;

	/**
	 * @var        ValidationManager The validation manager instance.
	 */
	protected $validationManager = null;

	/**
	 * @var        string The request method for this container.
	 */
	protected $requestMethod = null;

	/**
	 * @var        DataHolder A request data holder with request info.
	 */
	protected $requestData = null; // TODO: check if this can actually be protected 
	                               // or whether it should be private (would break chaintests though)

	/**
	 * @var        DataHolder A pointer to the global request data.
	 */
	private $globalRequestData = null;

	/**
	 * @var        DataHolder A request data holder with arguments.
	 */
	protected $arguments = null;

	/**
	 * @var        Response A response instance holding the Action's output.
	 */
	protected $response = null;

	/**
	 * @var        OutputType The output type for this container.
	 */
	protected $outputType = null;

	/**
	 * @var        float The microtime at which this container was initialized.
	 */
	protected $microtime = null;

	/**
	 * @var        Action The Action instance that belongs to this container.
	 */
	protected $chainInstance = null;

	/**
	 * @var        Layout The Layout instance that belongs to this container.
	 */
	protected $layoutInstance = null;

	/**
	 * @var        string The name of the Action's Module.
	 */
	protected $contentName = null;

	/**
	 * @var        string The name of the Action.
	 */
	protected $chainName = null;

	/**
	 * @var        string Name of the content of the Layout returned by the Action.
	 */
	protected $layoutContentName = null;

	/**
	 * @var        string The name of the Layout returned by the Action.
	 */
	protected $layoutName = null;

	/**
	 * @var        ExecutionContainer The next container to execute.
	 */
	protected $next = null;

	/**
	 * Action names may contain any valid PHP token, as well as dots and slashes
	 * (for sub-chains).
	 */
	const SANE_ACTION_NAME = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\/.]*/';
	
	/**
	 * Layout names may contain any valid PHP token, as well as dots and slashes
	 * (for sub-chains).
	 */
	const SANE_VIEW_NAME   = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\/.]*/';
	
	/**
	 * Only valid PHP tokens are allowed in Core Content Directives.
	 */
	const SANE_MODULE_NAME = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
	
	/**
	 * Pre-serialization callback.
	 *
	 * Will set the name of the context instead of the instance, and the name of
	 * the output type instead of the instance. Both will be restored by __wakeup
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __sleep()
	{
		$this->contextName = $this->getContext()->getName();
		if(!empty($this->outputType)) {
			$this->outputTypeName = $this->outputType->getName();	
		}
		$arr = get_object_vars($this);
		unset($arr['context'], $arr['outputType'], $arr['requestData'], $arr['globalRequestData']);
		return array_keys($arr);
	}

	/**
	 * Post-unserialization callback.
	 *
	 * Will restore the context and output type instances based on their names set
	 * by __sleep.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __wakeup()
	{
		$this->context = Context::getInstance($this->contextName);
		
		if(!empty($this->outputTypeName)) {
			$this->outputType = $this->getContext()->getController()->getOutputType($this->outputTypeName);
		}
		
		try {
			$this->globalRequestData = $this->getContext()->getRequest()->getRequestData();
		} catch(Exception $e) {
			$this->globalRequestData = new DataHolder();
		}
		unset($this->contextName, $this->outputTypeName);
	}

	/**
	 * Initialize the container. This will create a response instance.
	 *
	 * @param      Context The current Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->microtime = microtime(true);

		$this->context = $context;

		$this->parameters = $parameters;

		$this->response = $this->getContext()->createInstanceFor('response');


    }

	/**
	 * Creates a new container instance with the same output type and request
	 * method as this one.
	 *
	 * @param      string                 The name of the content.
	 * @param      string                 The name of the chain.
	 * @param      DataHolder A DataHolder with additional
	 *                                    request arguments.
	 * @param      string                 Optional name of an initial output type
	 *                                    to set.
	 * @param      string                 Optional name of the request method to
	 *                                    be used in this container.
	 *
	 * @return     ExecutionContainer A new execution container instance,
	 *                                     fully initialized.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function createExecutionContainer($contentName = null, $chainName = null, $arguments = null, $outputType = null, $requestMethod = null)
	{
		
		if($outputType === null) {
			$outputType = $this->getOutputType()->getName();
		}
		if($requestMethod === null) {
			$requestMethod = $this->getRequestMethod();
		}
		
		$container = $this->getContext()->getController()->createExecutionContainer($contentName, $chainName, $arguments, $outputType, $requestMethod);
		
		// copy over parameters (could be is_slot, is_forward etc)
		$container->setParameters($this->getParameters());
		
		return $container;
	}

	/**
	 * Start execution.
	 *
	 * This will create an instance of the chain and merge in request parameters.
	 *
	 * This method returns a response. It is not necessarily the same response as
	 * the one of this container, but instead the one that contains the actual
	 * content that should be used for output etc, since the container's own
	 * response might be empty or invalid due to a "next" container that has been
	 * set and executed.
	 *
	 * @return     Response The "real" response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function execute()
	{
		
		$controller = $this->getContext()->getController();

		$controller->countExecution();

		$contentName = $this->getContentName();
		
		try {
			$chainInstance = $this->getChainInstance();
		} catch(DisabledModule $e) {
			$this->setNext($this->createSystemActionForwardContainer('content_disabled'));
			return $this->proceed();
		} catch(FileNotFound $e) {
			$this->setNext($this->createSystemActionForwardContainer('error_404'));
			return $this->proceed();
		} // do not catch ClassNotFound; class in the chain file is named incorrectly
		
		// copy and merge request data as required
		$this->initRequestData();
		
		$filterChain = $this->getChain();

		if(!$chainInstance->isSimple()) {
			// simple chains have no filters

			if(Config::get('core.available', false)) {
				// the application is available so we'll register
				// globally defined and content-specific chain filters, otherwise skip them

				// does this chain require security?
				if(Config::get('core.use_security', false)) {
					// register security filter
					$filterChain->register($controller->getFilter('security'), 'framework_security_filter');
				}

				// load filters
				$controller->loadFilters($filterChain, 'chain');
				$controller->loadFilters($filterChain, 'chain', $contentName);
			}
		}

		// register the execution filter
		$filterChain->register($controller->getFilter('execution'), 'framework_execution_filter');
		//echo '---after $filterChain-----' . PHP_EOL;

		// process the filter chain
		$filterChain->execute($this);
		//echo '---after $filterChain->execute()-----' . PHP_EOL;
		
		//echo '---before $this->proceed-----' . PHP_EOL;
		
		return $this->proceed();
	}
	
	/**
	 * Copies and merges the global request data.
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        0.1
	 */
	protected function initRequestData()
	{
		if($this->getChainInstance()->isSimple()) {
			if($this->arguments !== null) {
				
				// clone it so mutating it has no effect on the "outside world"
				$this->requestData = clone $this->arguments;
			} else {
				
				// first get the class
				$rdhc = $this->getContext()->getRequest()->getParameter('request_data_holder_class');
				
				// check for namespace requirement
				if (substr($rdhc, 0, 7) != 'Request')
					$rdhc = 'Request\\' . $rdhc;
				$rdhc = 'YoudsFramework\\' . $rdhc;

				// assign request data class
				$this->requestData = new $rdhc();
			}
		} else {
			// mmmh I smell awesomeness... clone the RD JIT, yay, that's the spirit
			$this->requestData = clone $this->globalRequestData;

			if($this->arguments !== null && $this->requestData instanceof DataHolder) {
				$this->requestData->merge($this->arguments);
			}
		}
	}
	
	/**
	 * Create a system forward container
	 *
	 * Calling this method will set the attributes:
	 *  - requested_content
	 *  - requested_chain
	 *  - (optional) exception
	 * in the appropriate namespace on the created container as well as the global
	 * request (for legacy reasons)
	 *
	 *
	 * @param      string          The type of forward to create (error_404, 
	 *                             content_disabled, secure, login, unavailable).
	 * @param      Exception  Optional exception thrown by the controller
	 *                             while resolving the content/action.
	 *
	 * @return     ExecutionContainer The forward container.
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	public function createSystemActionForwardContainer($type, ?Exception $e = null)
	{
		
		if(!in_array($type, array('error_404', 'content_disabled', 'websockets', 'secure', 'login', 'unavailable'))) {
			throw new Exception(sprintf('Unknown system forward type "%1$s"', $type));
		}
		
		// track the requested content so we have access to the data in the error 404 page
		$forwardInfoData = array(
			'requested_content' => $this->getContentName(),
			'requested_chain' => $this->getChainName(),
			'exception'        => $e,
		);
		$forwardInfoNamespace = 'org.framework.controller.forwards.' . $type;
		
		$contentName = Config::get('chains.' . $type . '_content');
		$chainName = Config::get('chains.' . $type . '_chain');

		
		if(false === $this->getContext()->getController()->checkActionFile($contentName, $chainName)) {
			// cannot find unavailable content/action
			$error = 'Invalid configuration settings: chains.%3$s_content "%1$s", chains.%3$s_chain "%2$s"';
			$error = sprintf($error, $contentName, $chainName, $type);
			
			throw new Configuration($error);
		}
		
		$forwardContainer = $this->createExecutionContainer($contentName, $chainName);
		
		$forwardContainer->setAttributes($forwardInfoData, $forwardInfoNamespace);
		
		// legacy
		$this->getContext()->getRequest()->setAttributes($forwardInfoData, $forwardInfoNamespace);
		
		return $forwardContainer;
	}
	
	/**
	 * Proceed to the "next" container by running it and returning its response,
	 * or return our response if there is no "next" container.
	 *
	 * @return     Response The "real" response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	protected function proceed()
	{
		if($this->next !== null) {
			return $this->next->execute();
		} else {
			return $this->getResponse();
		}
	}

	/**
	 * Get the Context.
	 *
	 * @return     Context The Context.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the ValidationManager
	 *
	 * @return     ValidationManager The container's ValidationManager
	 *                                    implementation instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getValidationManager()
	{
		if($this->validationManager === null) {
			$this->validationManager = $this->getContext()->createInstanceFor('validation_manager');
		}
		
		return $this->validationManager;
	}

	/**
	 * Get the container's filter chain.
	 *
	 * @return     Chain The container's filter chain.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getChain()
	{
		if($this->filterChain === null) {
			$this->filterChain = $this->getContext()->createInstanceFor('filter_chain');
			$this->filterChain->setType(Chain::TYPE_ACTION);
		}
		
		return $this->filterChain;
	}
	
	/**
	 * Execute the Action.
	 *
	 * @return     mixed The processed Layout information returned by the Action.
	 *
	 * @author     David Zülke <david.zuelke@bitxtender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function runAction()
	{
		$layoutName = null;

		$controller = $this->getContext()->getController();
		$request = $this->getContext()->getRequest();
		$validationManager = $this->getValidationManager();

		// get the current chain instance
		$chainInstance = $this->getChainInstance();

		// get the current chain information
		$contentName = $this->getContentName();
		$chainName = $this->getChainName();

		// get the (already formatted) request method
		$method = $this->getRequestMethod();
        $outputType = $this->getOutputType()->getName();

		$requestData = $this->getRequestData();

		$useGenericMethods = false;
        if ($outputType !== 'html')
            $executeMethod = 'execute' . ucfirst($outputType);
        else
            $executeMethod = 'execute' . $method;

		if(!is_callable(array($chainInstance, $executeMethod))) {
			$executeMethod = 'execute' . $method;
			if(!is_callable(array($chainInstance, $executeMethod))) {
				$executeMethod = 'execute';
			}
			$useGenericMethods = true;
		}

		if($chainInstance->isSimple() || ($useGenericMethods && !is_callable(array($chainInstance, $executeMethod)))) {
			// this chain will skip validation/execution for this method
			// get the default layout
			$key = $request->toggleLock();
			try {
				$layoutName = $chainInstance->getDefaultLayoutName();
			} catch(Exception $e) {
				// we caught an exception... unlock the request and rethrow!
				$request->toggleLock($key);
				throw $e;
			}
			$request->toggleLock($key);
			
			// run the validation manager - it's going to take care of cleaning up the request data, and retain "conditional" mode behavior etc.
			// but only if the chain is not simple; otherwise, the (safe) arguments in the request data holder will all be removed
			if(!$chainInstance->isSimple()) {
				$validationManager->execute($requestData);
			}
		} else {
			if($this->performValidation()) {
				// execute the chain
				// prevent access to Request::getParameters()
				$key = $request->toggleLock();
				try {
					//dump('----$executeMethod (' . $executeMethod . ')-------------');
					switch ($executeMethod):
						case 'execute':
						case 'executeRead':
						case 'executeWrite':
						case 'executeHtml':
                        case 'executeHook':
                        case 'executeText':

                            $layoutName = $chainInstance->$executeMethod($requestData);
						break;
						default:
                            if (method_exists($chainInstance, 'startup'))
                                $chainInstance->startup($requestData);
							$layoutName = $chainInstance->$executeMethod($requestData);
					endswitch;
					
				} catch(Exception $e) {
					// we caught an exception... unlock the request and rethrow!
					$request->toggleLock($key);
					throw $e;
				}
				$request->toggleLock($key);
			} else {
				//dump('---handleError------');

				// validation failed
				$handleErrorMethod = 'handle' . $method . 'Error';
				if(!is_callable(array($chainInstance, $handleErrorMethod))) {
					$handleErrorMethod = 'handleError';
				}
				$key = $request->toggleLock();
				try {
					$layoutName = $chainInstance->$handleErrorMethod($requestData);
				} catch(Exception $e) {
					// we caught an exception... unlock the request and rethrow!
					$request->toggleLock($key);
					throw $e;
				}
				$request->toggleLock($key);
			}
		}
		if(is_array($layoutName)) {
			// we're going to use an entirely different chain for this layout
			$layoutModule = $layoutName[0];
			$layoutName   = $layoutName[1];
		} elseif($layoutName !== Layout::NONE) {
			// use a layout related to this chain
            $layoutName = Toolkit::evaluateModuleDirective(
				$contentName,
				'framework.layout.name',
				array(
					'chainName' => $chainName,
					'layoutName' => $layoutName,
				)
			);


            $layoutModule = $contentName;
		} else {
			$layoutName = Layout::NONE;
			$layoutModule = Layout::NONE;
		}

		return array($layoutModule, $layoutName === Layout::NONE ? Layout::NONE : Toolkit::canonicalName($layoutName));
	}
	
	/**
	 * Performs validation for this execution container.
	 * 
	 * @return     bool true if the data validated successfully, false otherwise.
	 * 
	 * @author     David Zülke <david.zuelke@bitxtender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	public function performValidation()
	{
		$validationManager = $this->getValidationManager();

		// get the current chain instance
		$chainInstance = $this->getChainInstance();
		// get the (already formatted) request method
		$method = $this->getRequestMethod();

		$requestData = $this->getRequestData();
		
		// set default validated status
		$validated = true;

		$this->registerValidators();

		// process validators
		$validated = $validationManager->execute($requestData);

		$validateMethod = 'validate' . $method;
		if(!is_callable(array($chainInstance, $validateMethod))) {
			$validateMethod = 'validate';
		}

		// process manual validation
		return $chainInstance->$validateMethod($requestData) && $validated;
	}

	/**
	 * Register validators for this execution container.
	 * 
	 * @author     David Zülke <david.zuelke@bitxtender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function registerValidators()
	{
		$validationManager = $this->getValidationManager();

		// get the current chain instance
		$chainInstance = $this->getChainInstance();
	
		// get the current chain information
		$contentName = $this->getContentName();
		$chainName = $this->getChainName();
		
		// get the (already formatted) request method
		$method = $this->getRequestMethod();

		// lets start with the default chain files
		// get the current chain validation configuration
		$contentName = Toolkit::canonicalNameReverse($contentName);
		$chainName = Toolkit::canonicalNameReverse($chainName);
		$validationConfig = Toolkit::evaluateModuleDirective(
			$contentName,
			'framework.validate.chain.path',
			array(
				'contentName' => $contentName,
				'chainName' => $chainName,
			)
		);
 		// allow chain XML files to be located in the same directory as the chain file
		$validationConfig = str_replace($chainName . '.xml', substr($chainName, strrpos($chainName, '/')?strrpos($chainName, '/') + 1:0) . '.xml', $validationConfig);

		if(is_readable($validationConfig)) {
			
			// load validation configuration
			// do NOT use require_once(
			require(Cache::checkConfig($validationConfig, $this->getContext()->getName()));
		}

		// now require the default content files
		// get the current chain validation configuration
		$validationConfig = Toolkit::evaluateModuleDirective(
			$contentName,
			'framework.validate.defaults.chain.path',
			array(
				'contentName' => $contentName,
				'chainName' => $chainName,
			)
		);
				
		if(is_readable($validationConfig)) {
			
			// load validation configuration
			// do NOT use require_once(
			require(Cache::checkConfig($validationConfig, $this->getContext()->getName()));
		}

        // now require the default content files
        // get the current chain validation configuration
        $validationConfig = Toolkit::evaluateModuleDirective(
            $contentName,
            'framework.validate.content.chain.path',
            array(
                'contentName' => $contentName,
                'chainName' => $chainName,
            )
        );

        if(is_readable($validationConfig)) {

            // load validation configuration
            // do NOT use require_once(
            require(Cache::checkConfig($validationConfig, $this->getContext()->getName()));
        }
		
		// manually load validators
		$registerValidatorsMethod = 'register' . $method . 'Validators';
		if(!is_callable(array($chainInstance, $registerValidatorsMethod))) {			
			$registerValidatorsMethod = 'registerValidators';
		}
		
		$chainInstance->$registerValidatorsMethod();
	}
	
	/**
	 * Retrieve this container's request method name.
	 *
	 * @return     string The request method name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getRequestMethod()
	{
		return $this->requestMethod;
	}

	/**
	 * Set this container's request method name.
	 *
	 * @param      string The request method name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setRequestMethod($requestMethod)
	{
		$this->requestMethod = $requestMethod;
	}

	/**
	 * Retrieve this container's request data holder instance.
	 *
	 * @return     DataHolder The request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function getRequestData()
	{
		return $this->requestData;
	}

	/**
	 * Set this container's global request data holder reference.
	 *
	 * @param      DataHolder The request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function setRequestData($rd)
	{
		$this->globalRequestData = $rd;
	}

	/**
	 * Get this container's request data holder instance for additional arguments.
	 *
	 * @return     DataHolder The additional arguments.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * Set this container's request data holder instance for additional arguments.
	 *
	 * @return     DataHolder The request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setArguments($arguments)
	{
		$this->arguments = $arguments;
	}

	/**
	 * Retrieve this container's response instance.
	 *
	 * @return     Response The Response instance for this chain.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Set a new response.
	 *
	 * @param      Response A new Response instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setResponse(Response $response)
	{
		$this->response = $response;
		// do not set the output type on the response here!
	}

	/**
	 * Retrieve the output type of this container.
	 *
	 * @return     OutputType The output type object.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getOutputType()
	{
		return $this->outputType;
	}

	/**
	 * Set a different output type for this container.
	 *
	 * @param      OutputType An output type object.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setOutputType(OutputType $outputType)
	{
		$this->outputType = $outputType;
		if($this->response) {
			$this->response->setOutputType($outputType);
		}
	}

	/**
	 * Retrieve this container's microtime.
	 *
	 * @return     string A string representing the microtime this container was
	 *                    initialized.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getMicrotime()
	{
		return $this->microtime;
	}

	/**
	 * Retrieve this container's chain instance.
	 *
	 * @return     Action An chain implementation instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
     */
	public function getChainInstance()
	{
		
		$controller = $this->getContext()->getController();
		
		$contentName = $this->getContentName();
		$chainName = $this->getChainName();
		
		$this->chainInstance = $controller->createActionInstance($contentName, $chainName);
		
		// initialize the chain
		$this->chainInstance->initialize($this);

		return $this->chainInstance;
	}

	/**
	 * Retrieve this container's layout instance.
	 *
	 * @return     Layout A layout implementation instance.
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 */
	public function getLayoutInstance()
	{
		if($this->layoutInstance === null) {

			// get the layout instance
			$this->layoutInstance = $this->getContext()->getController()->createLayoutInstance($this->getLayoutContentName(), $this->getChainName(), $this->getLayoutName());

            // initialize the layout
			$this->layoutInstance->initialize($this);
		}
		
		return $this->layoutInstance;
	}

	/**
	 * Set this container's layout instance.
	 *
	 * @param      Layout A layout implementation instance.
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 */
	public function setLayoutInstance($layoutInstance)
	{
		return $this->layoutInstance = $layoutInstance;
	}

	/**
	 * Retrieve this container's Core Content Directive.
	 *
	 * @return     string A Core Content Directive.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getContentName()
	{
		return $this->contentName;
	}

	/**
	 * Retrieve this container's chain name.
	 *
	 * @return     string An chain name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getChainName()
	{
		return $this->chainName;
	}

	/**
	 * Retrieve this container's layout Core Content Directive. This is the name of the content of
	 * the Layout returned by the Action.
	 *
	 * @return     string A layout Core Content Directive.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getLayoutContentName()
	{
		return $this->layoutContentName;
	}

	/**
	 * Retrieve this container's layout name.
	 *
	 * @return     string A layout name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getLayoutName()
	{
		return $this->layoutName;
	}

	/**
	 * Set the Core Content Directive for this container.
	 *
	 * @param      string A Core Content Directive.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setContentName($contentName)
	{
		if(null === $contentName) {
			$this->contentName = null;
		} elseif(preg_match(self::SANE_MODULE_NAME, $contentName)) {
			$this->contentName = $contentName;
		} else {
			throw new Exception(sprintf('Invalid Core Content Directive "%1$s"', $contentName));
		}
	}

	/**
	 * Set the chain name for this container.
	 *
	 * @param      string An chain name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setChainName($chainName)
	{
		if(null === $chainName) {
			$this->chainName = null;
		} elseif(preg_match(self::SANE_ACTION_NAME, $chainName)) {
			$chainName = Toolkit::canonicalName($chainName);
			$this->chainName = $chainName;
		} else {
			throw new Exception(sprintf('Invalid chain name "%1$s"', $chainName));
		}
	}

	/**
	 * Set the layout Core Content Directive for this container.
	 *
	 * @param      string A layout Core Content Directive.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setLayoutContentName($layoutContentName)
	{
		if(null === $layoutContentName) {
			$this->layoutContentName = null;
		} elseif(preg_match(self::SANE_MODULE_NAME, $layoutContentName)) {
			$this->layoutContentName = $layoutContentName;
		} else {
			throw new Exception(sprintf('Invalid Layout Core Content Directive "%1$s"', $layoutContentName));
		}
	}

	/**
	 * Set the Core Content Directive for this container.
	 *
	 * @param      string A layout name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setLayoutName($layoutName)
	{
		if(null === $layoutName) {
			$this->layoutName = null;
		} elseif(preg_match(self::SANE_VIEW_NAME, $layoutName)) {
			$layoutName = Toolkit::canonicalName($layoutName);
			$this->layoutName = $layoutName;
		} else {
			throw new Exception(sprintf('Invalid layout name "%1$s"', $layoutName));
		}
	}

	 /**
	 * Check if a "next" container has been set.
	 *
	 * @return     bool True, if a container for eventual execution has been set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasNext()
	{
		return $this->next !== null;
	}

	/**
	 * Get the "next" container.
	 *
	 * @return     ExecutionContainer The "next" container, of null if unset.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * Set the container that should be executed once this one finished running.
	 *
	 * @param      ExecutionContainer An execution container instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setNext(ExecutionContainer $container)
	{
		$this->next = $container;
	}

	/**
	 * Remove a possibly set "next" container.
	 *
	 * @return     ExecutionContainer The removed "next" container, or null
	 *                                     if none had been set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clearNext()
	{
		$retVal = $this->next;
		$this->next = null;
		return $retVal;
	}
}

?>
