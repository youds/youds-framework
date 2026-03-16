<?php
namespace YoudsFramework;
use YoudsFramework\Config\Cache;
use YoudsFramework\Request\ParameterHolder;
use YoudsFramework\Filter\Chain;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Exceptions\Exception;
use YoudsFramework\Exceptions\Controller as ControllerException;;
use YoudsFramework\Exceptions\FileNotFound;
use YoudsFramework\Exceptions\ClassNotFound;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Controller directs application flow.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage controller
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Controller extends ParameterHolder
{
	/**
	 * @var        int The number of execution containers run so far.
	 */
	protected $numExecutions = 0;
	
	/**
	 * @var        Context An Context instance.
	 */
	protected $context = null;
	
	/**
	 * @var        Response The global response.
	 */
	protected $response = null;
	
	/**
	 * @var        Chain The global filter chain.
	 */
	protected $filterChain = null;
	
	/**
	 * @var        array An array of filter instances for reuse.
	 */
	protected $filters = array(
		'global' => array(),
		'chain' => array(
			'*' => null
		),
		'dispatch' => null,
		'execution' => null,
		'security' => null
	);
	
	/**
	 * @var        string The default Output Type.
	 */
	protected $defaultOutputType = null;
	
	/**
	 * @var        array An array of registered Output Types.
	 */
	protected $outputTypes = array();
	
	/**
	 * @var        array Ref to the request data object from the request.
	 */
	private $requestData = null;
	
	/**
	 * Increment the execution counter.
	 * Will throw an exceptions if the maximum amount of runs is exceeded.
	 *
	 * @throws     Exceptions\Controller If too many execution runs were made.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function countExecution()
	{
		$maxExecutions = $this->getParameter('max_executions');
		
		if(++$this->numExecutions > $maxExecutions && $maxExecutions > 0) {
			throw new ControllerException('Too many execution runs have been detected for this Context.');
		}
	}
	
	/**
	 * Create and initialize new execution container instance.
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
		
		// create a new execution container
		$container = $this->getContext()->createInstanceFor('execution_container');
		$container->setContentName($contentName);
		$container->setChainName($chainName);
		$container->setRequestData($this->requestData);		
		
		if($arguments !== null) {
			$container->setArguments($arguments);
		}
		
		$container->setOutputType($this->getContext()->getController()->getOutputType($outputType));
		if($requestMethod === null) {
			$requestMethod = $this->getContext()->getRequest()->getMethod();
		}
		
		$container->setRequestMethod($requestMethod);
		return $container;
	}
	
	/**
	 * Initialize a content and load its autoload, content config etc.
	 *
	 * @param      string The name of the content to initialize.
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @author	   Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function initializeContent($contentName)
	{
		$contentName = Toolkit::canonicalName($contentName); // fix / in content name
		$lowerContentName = strtolower($contentName); // for config output
		
		if(null === Config::get('content.' . $lowerContentName . '.enabled')) {
			
			// set some defaults first
			Config::fromArray(array(
				'content.' . $lowerContentName . '.framework.chain.path' => '%core.chains_dir%/${contentName}/${chainName}/${chainNameFile}Action.class.php',
				'content.' . $lowerContentName . '.framework.default.chain.path' => '%core.defaults_dir%/core/chains/${contentName}/${chainName}/${chainNameFile}Action.class.php',
				'content.' . $lowerContentName . '.framework.cache.path' => '%core.app_dir%/cache/${chainName}.xml',
				'content.' . $lowerContentName . '.framework.template.directory' => '%core.chains_dir%/${content}/${chainName}',
				'content.' . $lowerContentName . '.framework.validate.chain.path' => '%core.core_dir%/chains/${contentName}/${chainName}/${chainName}.xml',
                'content.' . $lowerContentName . '.framework.validate.content.chain.path' => '%core.core_dir%/chains/Chains.xml',
                'content.' . $lowerContentName . '.framework.validate.defaults.chain.path' => '%core.defaults_dir%/core/chains/${contentName}/${chainName}/${chainName}.xml',
                'content.' . $lowerContentName . '.framework.layout.path' => '%core.chains_dir%/${contentName}/${chainName}/${layoutName}Layout.class.php',
				'content.' . $lowerContentName . '.framework.default.layout.path' => '%core.defaults_dir%/core/chains/${contentName}/${chainName}/${layoutName}Layout.class.php',
				'content.' . $lowerContentName . '.framework.layout.name' => '${chainName}${layoutName}',
			)); // TODO: using contentName instead of making contentPath available in config
		}
		if(Config::get('content.' . $lowerContentName . '.disabled')) {
			throw new DisabledModule(sprintf('The module "%1$s" is disabled.', $contentName));
		}

	}
	
	/**
	 * Dispatch a request
	 *
	 * @param      DataHolder  An optional request data holder object
	 *                                     with additional request data.
	 * @param      ExecutionContainer An optional execution container that,
	 *                                     if given, will be executed right away,
	 *                                     skipping routing execution.
	 *
	 * @return     Response The response produced during this dispatch call.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function dispatch($arguments = null, ?ExecutionContainer $container = null)
	{
		try {
			
			$rq = $this->context->getRequest();
			$rd = $rq->getRequestData();
			
			if($container === null) {
				// match routes and assign returned initial execution container
				$container = $this->context->getRouting()->execute();
			}
			
			if($container instanceof ExecutionContainer) {
				// merge in any arguments given. they need to have precedence over what the routing found
				if($arguments !== null) {
					$rd->merge($arguments);
				}
				
				// next, we have to see if the routing did anything useful, i.e. whether or not it was enabled.
				$contentName = $container->getContentName();
				$chainName = $container->getChainName();

				if(!$contentName) {
					// no content has been specified; that means the routing did not run, as it would otherwise have the 404 chain's Core Content Directive
					
					// lets see if our request data has values for content and chain
					$ma = $rq->getParameter('content_accessor');
					$aa = $rq->getParameter('chain_accessor');
					
					if($rd->hasParameter($ma) && $rd->hasParameter($aa)) {
						// yup. grab those
						$contentName = $rd->getParameter($ma);
						$chainName =  $rd->getParameter($aa);
					} else {
						// nope. then its time for the default chain
						$contentName = Config::get('chains.default_content');
						$chainName = Config::get('chains.default_chain');
					}
					
					// so by now we hopefully have something reasonable for content and chain names - let's set them on the container
					$container->setContentName($contentName);
					$container->setChainName($chainName);
				}

				if(!Config::get('core.available', false)) {
					$container = $container->createSystemActionForwardContainer('unavailable');
				}
				
				// create a new filter chain
				$filterChain = $this->getFilterChain();
				
				$this->loadFilters($filterChain, 'global');
				
				// register the dispatch filter
				$filterChain->register($this->filters['dispatch'], 'framework_dispatch_filter');
				
				// go, go, go!
				$filterChain->execute($container);
				
				$response = $container->getResponse();
			} elseif($container instanceof Response) {
				
				// the routing returned a response!
				$response = $container;
				
				// set $container to null so Exception::render() won't think it is a container if an exception happens later!
				$container = null;
			} else {
				throw new Exception('Routing::execute() returned neither ExecutionContainer nor Response object.');
			}
			$response->merge($this->response);
			
			if($this->getParameter('send_response')) {
				$response->send();
			}

			return $response;
			
		} catch(Exception $e) {
			Exception::render($e, $this->context, $container);
		}
	}
	
	/**
	 * Get the global response instance.
	 *
	 * @return     Response The global response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getGlobalResponse()
	{
		return $this->response;
	}
	
	
	/**
	 * Indicates whether or not a content has a specific chain file.
	 * 
	 * Please note that this is only a cursory check and does not 
	 * check whether the file actually contains the proper class
	 * The returned $file is used to determine chain location
	 *
	 * @param      string A Core Content Directive.
	 * @param      string An chain name.
	 *
	 * @return     mixed  the path to the chain file if the chain file 
	 *                    exists and is readable, false in any other case
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @author	   Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function checkActionFile($contentName, $chainName)
	{

        // fix . /
        $contentName = Toolkit::canonicalName($contentName);
        $chainName = Toolkit::canonicalName($chainName);

        $this->initializeContent($contentName);


		// check for normal chain
		$file = Toolkit::evaluateModuleDirective(
			$contentName,
			'framework.chain.path',
			array(
				'contentName' => str_replace('\\', '/', $contentName),
				'chainName' => str_replace('\\', '/', $chainName),
			)
		);


		if(is_readable($file) && substr($chainName, 0, 1) !== '/') {
			return $file;
		} else {
			
			// now check for hidden content
			$file = Toolkit::evaluateModuleDirective(
				$contentName,
				'framework.default.chain.path',
				array(
					'contentName' => $contentName,
					'chainName' => $chainName,
				)
			);
			
			if(is_readable($file) && substr($chainName, 0, 1) !== '/') {
				return $file;
			}
		}
		
		return false;
	}
	
	/**
	 * Retrieve an Action implementation instance.
	 *
	 * @param      string A Core Content Directive.
	 * @param      string An chain name.
	 *
	 * @return     Action An Action implementation instance
	 *
	 * @throws     Exception if the chain could not be found.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 */
	public function createActionInstance($contentName, $chainName)
	{
		// content/chain name
        $contentName = Toolkit::canonicalName($contentName);
        $chainName = Toolkit::canonicalName($chainName);
        $classes = explode('\\', $chainName);
        $fullChain = 'Core\Chains\\' . ucfirst($contentName) . '\\';
        foreach ($classes as $singleClass):
            $fullChain .= ucfirst($singleClass) . '\\';
        endforeach;
        $class = $fullChain . 'Action';
        $file = $this->checkActionFile($contentName, $chainName);
        if (str_contains($file, '/defaults/'))
            $class = 'Defaults\\' . $class;

        // vanity vars
        $contentName = ucfirst($contentName);
        $chainName = str_replace('\\', '.', ucfirst($chainName));

        // init
		$this->initializeContent($contentName);
		if(!class_exists($class)) {

            if(false !== $file) {
				require_once($file);
			} else {
				throw new FileNotFound(sprintf('Could not find file for chain "%s" in content "%s".', $chainName, $contentName));
			}
			if(!class_exists($class, false)) {
				throw new ClassNotFound(sprintf('Failed to instantiate chain "%s" in content "%s" because file "%s" does not contain class "%s".', $chainName, $contentName, $file, $class));
			}
		} 
		//echo '###########################################' . PHP_EOL . '##### ' . $class . '####' . PHP_EOL . '###########################################' . PHP_EOL . PHP_EOL;

        return new $class();
	}

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
	 * Indicates whether or not a content has a specific layout file.
	 * 
	 * Please note that this is only a cursory check and does not 
	 * check whether the file actually contains the proper class
	 *
	 * @param      string A Core Content Directive.
	 * @param      string A layout name.
	 *
	 * @return     mixed  the path to the layout file if the layout file 
	 *                    exists and is readable, false in any other case
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	public function checkLayoutFile($contentName, $chainName, $layoutName)
	{
		$this->initializeContent($contentName);

		$chainName = Toolkit::canonicalName($chainName);
		$layoutName = Toolkit::canonicalName($layoutName);
		
		$file = Toolkit::evaluateModuleDirective(
			$contentName,
			'framework.layout.path',
			array(
				'contentName' => $contentName,
				'chainName' => $chainName,
				'layoutName' => $chainName . $layoutName,
			)
		);
		if(is_readable($file) && substr($layoutName, 0, 1) !== '/') {
			return $file;
		} else {
			
			$file = Toolkit::evaluateModuleDirective(
				$contentName,
				'framework.default.layout.path',
				array(
					'contentName' => ucwords($contentName),
					'chainName' => ucwords($chainName),
					'layoutName' => ucwords($chainName) . ucwords($layoutName),
				)
			);
			if(is_readable($file) && substr($layoutName, 0, 1) !== '/') {
				return $file;
			}
		}
		
		return false;
	}
	
	/**
	 * Retrieve a Layout implementation instance.
	 *
	 * @param      string A Core Content Directive.
	 * @param      string A layout name.
	 *
	 * @return     Layout A Layout implementation instance,
	 *
	 * @throws     Exception if the layout could not be found.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function createLayoutInstance($contentName, $chainName, $layoutName)
	{

		try {
            $contentName = Toolkit::canonicalName(str_replace($layoutName, '', $contentName));
            $this->initializeContent($contentName);
		} catch(DisabledModule $e) {
			// layouts from disabled content should be usable by definition
			// swallow
		}

        // layout and chain name
        $layoutName = Toolkit::canonicalName(str_replace($chainName, '', $layoutName));
        $chainName = Toolkit::canonicalName(str_replace($layoutName, '', $chainName));
        $classes = explode('\\', $chainName);
        $fullChain = 'Core\Chains\\' . ucfirst($contentName) . '\\';

        foreach ($classes as $singleClass):
            $fullChain .= ucfirst($singleClass) . '\\';
        endforeach;
        $class = $fullChain . ucfirst($layoutName);
        $file = $this->checkLayoutFile($contentName, $chainName, $layoutName);
        if (str_contains($file, '/defaults/'))
            $class = 'Defaults\\' . $class;

        // vanity vars
        $contentName = ucfirst($contentName);
        $chainName = ucfirst($chainName);

        if(!class_exists($class)) {
			if(false !== $file) {
				require_once($file);
			} else {
				throw new FileNotFound(sprintf('Could not find file for layout "%s" in content "%s" for chain "%s".', $layoutName, $contentName, $chainName));
			}
			
			if(!class_exists($class, false)) {
				throw new ClassNotFound(sprintf('Failed to instantiate layout "%s" in module "%s" because file "%s" does not contain class "%s".', $layoutName, $contentName, $file, $class));
			}
		} 
		
		return new $class();
	}

	/**
	 * Constructor.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setParameters(array(
			'max_executions' => 20,
			'send_response' => true,
		));
	}
	
	/**
	 * Initialize this controller.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     	David Zülke <dz@bitxtender.com>
	 * @author 		Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->context = $context;
		
		$this->setParameters($parameters);
		
		$this->response = $this->context->createInstanceFor('response');
		
		// compile our xml
		// note: this is to support the defaults config
		$dom = new \DOMDocument();
		$dom->loadXML(file_get_contents(Config::get('core.config_dir') . '/output_types.xml'));
		$cfg = $this->mergeFiles($dom, Config::get('core.src_dir') . '/defaults/config/output_types.xml');
		
		require_once(Cache::checkConfig($cfg, $this->context->getName()));
		
		
		if(Config::get('core.use_security', false)) {
			$this->filters['security'] = $this->context->createInstanceFor('security_filter');
		}
		
		$this->filters['dispatch'] = $this->context->createInstanceFor('dispatch_filter');
		
		$this->filters['execution'] = $this->context->createInstanceFor('execution_filter');
		
	}
	
	/**
	 * Merge XML files
	 *
	 * @return string	Path to merged file
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	protected function mergeFiles (\DOMDocument $target, $fileName)
	{
	    $source = new \DOMDocument();
	    $source->load($fileName);
		$append = $target->getElementsByTagName('output_types')->item(0);
		
	    foreach ($source->getElementsByTagName("output_type") as $row) {
	        $import = $target->importNode($row, true);
	       if  ($target->getElementsByTagName('output_types')->item(0)) $target->getElementsByTagName('output_types')->item(0)->appendChild($import);
	    }
		
		$a = 0;
	    foreach ($source->getElementsByTagName("configuration") as $row) {
			if ($a >= 1):
				$import = $target->importNode($row, true);
				$target->getElementsByTagName('configurations')->item(0)->appendChild($import);
				$a++;
			endif;
	    }
		
		$out = Config::get('core.cache_dir') . '/config/output_types.xml_' . md5(time());
		
		$target->save($out);
		
		return $out;
		
	}
	
	/**
	 * Get a filter.
	 *
	 * @param      string The name of the filter list section.
	 *
	 * @return     Filter A filter instance, or null.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getFilter($which)
	{
		return (isset($this->filters[$which]) ? $this->filters[$which] : null);
	}
	
	/**
	 * Get the global filter chain.
	 *
	 * @return     Chain The global filter chain.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getFilterChain()
	{
		if($this->filterChain === null) {
			$this->filterChain = $this->getContext()->createInstanceFor('filter_chain');
		
			$this->filterChain->setType(Chain::TYPE_GLOBAL);
		}
		
		return $this->filterChain;
	}
	
	/**
	 * Load filters.
	 *
	 * @param      Chain A Chain instance.
	 * @param      string           "global" or "chain".
	 * @param      string           A Core Content Directive, or "*" for the generic config.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function loadFilters(Chain $filterChain, $which = 'global', $content = null)
	{
		if($content === null) {
			$content = '*';
		}
		
		if(($which != 'global' && !isset($this->filters[$which][$content])) || $which == 'global' && $this->filters[$which] == null) {
			if($which == 'global') {
				$this->filters[$which] = array();
				$filters =& $this->filters[$which];
			} else {
				$this->filters[$which][$content] = array();
				$filters =& $this->filters[$which][$content];
			}
			$config = ($content == '*' ? Config::get('core.config_dir') : Config::get('core.chains_dir') . '/' . $content . '/config') . '/' . $which . '_filters.xml';
			if(is_readable($config)) {
				require_once(Cache::checkConfig($config, $this->context->getName()));
			}
		} else {
			if ($which == 'global') {
				$filters =& $this->filters[$which];
			} else {
				$filters =& $this->filters[$which][$content];
			}
		}
		
		foreach($filters as $name => $filter) {
			$filterChain->register($filter, $name);
		}
	}

	/**
	 * Indicates whether or not a content has a specific model.
	 *
	 * @param      string A Core Content Directive.
	 * @param      string A model name.
	 *
	 * @return     bool true, if the model exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function modelExists($contentName, $modelName)
	{
		$modelName = Toolkit::canonicalName($modelName);
		$file = Config::get('core.core_dir') . '/models/' . $modelName .	'Model.class.php';
		return is_readable($file);
	}

	/**
	 * Indicates whether or not a content exists.
	 *
	 * @param      string A Core Content Directive.
	 *
	 * @return     bool true, if the content exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function contentExists($contentName)
	{
		$file = Config::get('core.chains_dir') . '/' . $contentName . '/config/' . strtolower($contentName) . '.xml';
		return is_readable($file);
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
		// grab a pointer to the request data
		$this->requestData = $this->context->getRequest()->getRequestData();
	}

	/**
	 * Execute the shutdown procedure for this controller.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function shutdown()
	{
	}

	/**
	 * Indicates whether or not a content has a specific chain.
	 *
	 * @param      string A Core Content Directive.
	 * @param      string A layout name.
	 *
	 * @return     bool true, if the chain exists, otherwise false.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function chainExists($contentName, $chainName)
	{
		return $this->checkActionFile($contentName, $chainName) !== false;
	}

	/**
	 * Indicates whether or not a content has a specific layout.
	 *
	 * @param      string A Core Content Directive.
	 * @param      string A layout name.
	 *
	 * @return     bool true, if the layout exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function layoutExists($contentName, $layoutName)
	{
		return $this->checkLayoutFile($contentName, $layoutName) !== false;
	}
	
	/**
	 * Retrieve an Output Type object
	 *
	 * @param      string The optional output type name.
	 *
	 * @return     OutputType An Output Type object.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getOutputType($name = null)
	{
		if($name == NULL) {
			$name = $this->defaultOutputType;
		}

		if(isset($this->outputTypes[$name])) {
			return $this->outputTypes[$name];
		} else {
			throw new Exception('Output Type "' . $name . '" has not been configured.');
		}
	}
	
	/**
	 * Reset controller executions to 0 
	 *
	 * @return void
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function resetExecutions ()
	{
		$this->numExecutions = 0;
	}
}

?>
