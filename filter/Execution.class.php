<?php
namespace YoudsFramework\Filter;
use YoudsFramework\Filter;
use YoudsFramework\Config\Cache;
use YoudsFramework\Exceptions\Exception;
use YoudsFramework\Layout;
use YoudsFramework\ExecutionContainer;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Util\ArrayPathDefinition;
use YoudsFramework\Request\DataHolder;
use ReflectionClass;
use ReflectionMethod;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * ExecutionFilter is the last filter registered for each filter chain.
 * This filter does all chain and layout execution.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage filter
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Execution extends Filter implements IAction
{
	/*
	 * The directory inside %core.cache_dir% where cached stuff is stored.
	 */
	const CACHE_SUBDIR = 'content';

	/*
	 * The name of the file that holds the cached chain data.
	 * Minuses because these are not allowed in an output type name.
	 */
	const ACTION_CACHE_ID = '4-8-15-16-23-42';

	/*
	 * Constants for the cache callback event types.
	 */
	const CACHE_CALLBACK_ACTION_NOT_CACHED = 0;
	const CACHE_CALLBACK_ACTION_CACHE_GONE = 1;
	const CACHE_CALLBACK_VIEW_NOT_CACHEABLE = 2;
	const CACHE_CALLBACK_VIEW_NOT_CACHED = 3;
	const CACHE_CALLBACK_OUTPUT_TYPE_NOT_CACHEABLE = 4;
	const CACHE_CALLBACK_VIEW_CACHE_GONE = 5;
	const CACHE_CALLBACK_ACTION_CACHE_USELESS = 6;
	const CACHE_CALLBACK_VIEW_CACHE_WRITTEN = 7;
	const CACHE_CALLBACK_ACTION_CACHE_WRITTEN = 8;
	
	/**
	 * Method that's called when a cacheable Action/Layout with a stale cache is
	 * about to be run.
	 * Can be used to prevent stampede situations where many requests to an chain
	 * with an out-of-date cache are run in parallel, slowing down everything.
	 * For instance, you could set a flag into memcached with the groups of the
	 * chain that's currently run, and in checkCache check for those and return
	 * an old, stale cache until the flag is gone.
	 *
	 * @param      int                     The type of the event that occurred.
	 *                                     See CACHE_CALLBACK_* constants.
	 * @param      array                   The groups.
	 * @param      array                   The caching configuration.
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function startedCacheCreationCallback($eventType, array $groups, array $config, ExecutionContainer $container)
	{
	}
	
	/**
	 * Method that's called when an Action/Layout that was assumed to be cacheable
	 * turned out not to be (because the Layout or Output Type isn't).
	 *
	 * @see        ExecutionFilter::startedCacheCreationCallback()
	 *
	 * @param      int                     The type of the event that occurred.
	 *                                     See CACHE_CALLBACK_* constants.
	 * @param      array                   The groups.
	 * @param      array                   The caching configuration.
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function abortedCacheCreationCallback($eventType, array $groups, array $config, ExecutionContainer $container)
	{
	}
	
	/**
	 * Method that's called when a cacheable Action/Layout with a stale cache has
	 * finished execution and all caches are written.
	 *
	 * @see        ExecutionFilter::startedCacheCreationCallback()
	 *
	 * @param      int                     The type of the event that occurred.
	 *                                     See CACHE_CALLBACK_* constants.
	 * @param      array                   The groups.
	 * @param      array                   The caching configuration.
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function finishedCacheCreationCallback($eventType, array $groups, array $config, ExecutionContainer $container)
	{
	}
	
	/**
	 * Check if a cache exists and is up-to-date
	 *
	 * @param      array  An array of cache groups
	 * @param      string The lifetime of the cache as a strtotime relative string
	 *                    without the leading plus sign.
	 *
	 * @return     bool true, if the cache is up to date, otherwise false
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function checkCache(array $groups, $lifetime = null)
	{
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		$filename = Config::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache';
		$isReadable = is_readable($filename);
		if($lifetime === null || !$isReadable) {
			return $isReadable;
		} else {
			$expiry = strtotime('+' . $lifetime, filemtime($filename));
			if($expiry !== false) {
				return $isReadable && ($expiry >= time());
			} else {
				return false;
			}
		}
	}

	/**
	 * Read the contents of a cache
	 *
	 * @param      array An array of cache groups
	 *
	 * @return     array The cache data
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function readCache(array $groups)
	{
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		$filename = Config::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache';
		$data = @file_get_contents($filename);
		if($data !== false) {
			return unserialize($data);
		} else {
			throw new Exception(sprintf('Failed to read cache file "%s"', $filename));
		}
	}

	/**
	 * Write cache content
	 *
	 * @param      array  An array of cache groups
	 * @param      array  The cache data
	 * @param      string The lifetime of the cache as a strtotime relative string
	 *                    without the leading plus sign.
	 *
	 * @return     bool The result of the write operation
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function writeCache(array $groups, $data, $lifetime = null)
	{
		// lifetime is not used in this implementation!
		
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		@mkdir(Config::get('core.cache_dir') . DIRECTORY_SEPARATOR  . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR , array_slice($groups, 0, -1)), 0777, true);
		return file_put_contents(Config::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache', serialize($data), LOCK_EX);
	}

	/**
	 * Flushes the cache for a group
	 *
	 * @param      array An array of cache groups
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public static function clearCache(array $groups = array())
	{
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		$path = self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups);
		if(is_file(Config::get('core.cache_dir') . DIRECTORY_SEPARATOR . $path . '.cefcache')) {
			Toolkit::clearCache($path . '.cefcache');
		} else {
			Toolkit::clearCache($path);
		}
	}

	/**
	 * Builds an array of cache groups using the configuration and a container.
	 *
	 * @param      array                   The group array from the configuration.
	 * @param      ExecutionContainer The execution container.
	 *
	 * @return     array An array of groups.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function determineGroups(array $groups, ExecutionContainer $container)
	{
		$retVal = array();

		foreach($groups as $group) {
			$group += array('name' => null, 'source' => null, 'namespace' => null);
			$val = $this->getVariable($group['name'], $group['source'], $group['namespace'], $container);
			
			if(is_object($val) && is_callable(array($val, '__toString'))) {
				$val = $val->__toString();
			} elseif(is_object($val)) {
				$val = spl_object_hash($val);
			}
			
			if($val === null || $val === false || $val === '') {
				$val = '0';
			}
			
			if(!is_scalar($val)) {
				throw new Uncacheable('Group value is not a scalar, cannot construct a meaningful string representation.');
			}
			
			$retVal[] = $val;
		}

		$retVal[] = $container->getContentName() . '_' . $container->getChainName();

		return $retVal;
	}

	/**
	 * Read a variable from the given source and, optionally, namespace.
	 *
	 * @param      string The variable name.
	 * @param      string The optional variable source.
	 * @param      string The optional namespace in the source.
	 * @param      ExecutionContainer The container to use, if necessary.
	 *
	 * @return     mixed The variable.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getVariable($name, $source = 'string', $namespace = null, ?ExecutionContainer $container = null)
	{
		$val = $name;
		
		switch($source) {
			case 'callback':
				$val = $container->getChainInstance()->$name();
				break;
			case 'configuration_directive':
				$val = Config::get($name);
				break;
			case 'constant':
				$val = constant($name);
				break;
			case 'container_parameter':
				$val = $container->getParameter($name);
				break;
			case 'global_request_data':
				$val = $this->context->getRequest()->getRequestData()->get($namespace ? $namespace : DataHolder::SOURCE_PARAMETERS, $name);
				break;
			case 'locale':
				$val = $this->context->getTranslationManager()->getCurrentLocaleIdentifier();
				break;
			case 'request_attribute':
				$val = $this->context->getRequest()->getAttribute($name, $namespace);
				break;
			case 'request_data':
				$val = $container->getRequestData()->get($namespace ? $namespace : DataHolder::SOURCE_PARAMETERS, $name);
				break;
			case 'request_parameter':
				$val = $this->context->getRequest()->getRequestData()->getParameter($name);
				break;
			case 'user_attribute':
				$val = $this->context->getUser()->getAttribute($name, $namespace);
				break;
			case 'user_authenticated':
				if(($user = $this->context->getUser()) instanceof ISecurityUser) {
					$val = $user->isAuthenticated();
				}
				break;
			case 'user_credential':
				if(($user = $this->context->getUser()) instanceof ISecurityUser) {
					$val = $user->hasCredentials($name);
				}
				break;
			case 'user_parameter':
				$val = $this->context->getUser()->getParameter($name);
				break;
		}
		
		return $val;
	}

	/**
	 * Execute this filter.
	 *
	 * @param      Chain        The filter chain.
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @throws     Exceptions\Initialization If an error occurs during
	 *                                                 Layout initialization.
	 * @throws     Exceptions\Layout           If an error occurs while
	 *                                                 executing the Layout.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function execute(Chain $filterChain, ExecutionContainer $container)
	{
		// $lm = $this->context->getLoggerManager();

		// get the context, controller and validator manager
		$controller = $this->context->getController();

		// get the current chain information
		$chainName = $container->getChainName();	
		$contentName = $container->getContentName();

		// the chain instance
		$chainInstance = $container->getChainInstance();

		$request = $this->context->getRequest();

		$isCacheable = false;
		$cachingDotXml = Toolkit::evaluateModuleDirective(
			$contentName,
			'framework.cache.path',
			array(
				'contentName' => $contentName,
				'chainName' => $chainName,
			)
		);
		
		if($this->getParameter('enable_caching', true) && is_readable($cachingDotXml)) {
			// $lm->log('Caching enabled, configuration file found, loading...');
			// no _once please! TODO: why?
            include(Cache::checkConfig($cachingDotXml, $this->context->getName()));
		}

		$isActionCached = false;

		if($isCacheable) {
			try {
				$groups = $this->determineGroups($config['groups'], $container);
				$chainGroups = array_merge($groups, array(self::ACTION_CACHE_ID));
			} catch(Exceptions\Uncacheable $e) {
				// a group callback threw an exception. that means we're not allowed t cache
				$isCacheable = false;
			}
			if($isCacheable) {
				// this is not wrapped in the try/catch block above as it might throw an exception itself
				$isActionCached = $this->checkCache(array_merge($groups, array(self::ACTION_CACHE_ID)), $config['lifetime']);
			
				if(!$isActionCached) {
					// cacheable, but chain is not cached. notify our callback so it can prevent the stampede that follows
					$this->startedCacheCreationCallback(self::CACHE_CALLBACK_ACTION_NOT_CACHED, $chainGroups, $config, $container);
				}
			}
		} else {
			// $lm->log('Action is not cacheable!');
		}

		if($isActionCached) {

			// $lm->log('Action is cached, loading...');
			// cache/dir/4-8-15-16-23-42 contains the chain cache
			try {
				$chainCache = $this->readCache($chainGroups);

				// and restore chain attributes
				$chainInstance->setAttributes($chainCache['chain_attributes']);
			} catch(Exception $e) {

				// cacheable, but chain is not cached. notify our callback so it can prevent the stampede that follows
				$this->startedCacheCreationCallback(self::CACHE_CALLBACK_ACTION_CACHE_GONE, $chainGroups, $config, $container);
				$isActionCached = false;
			}
		}
		
		$isLayoutCached = false;
		$rememberTheLayout = null;
		
		while(true) {
			if(!$isActionCached) {
				$chainCache = array();
			
				// $lm->log('Action not cached, executing...');
				// execute the Action and get the Layout to execute
				list($chainCache['layout_content'], $chainCache['layout_name']) = $container->runAction();
				
				// check if we've just run the chain again after a previous cache read revealed that the layout is not cached for this output type and we need to go back to square one due to the lack of chain attribute caching configuration...
				// if yes: is the layout content/name that we got just now different from what was in the cache?
				if(isset($rememberTheLayout) && $chainCache != $rememberTheLayout) {
					// yup. clear it!
					$ourClass = get_class($this);
					$ourClass::clearCache($groups);
				}
				
				// check if the returned layout is cacheable
				if($isCacheable && is_array($config['layouts']) && !in_array(array('content' => $chainCache['layout_content'], 'name' => $chainCache['layout_name']), $config['layouts'], true)) {
					$isCacheable = false;
					$this->abortedCacheCreationCallback(self::CACHE_CALLBACK_VIEW_NOT_CACHEABLE, $chainGroups, $config, $container);
					
					// so that layout is not cacheable? okay then:
					// check if we've just run the chain again after a previous cache read revealed that the layout is not cached for this output type and we need to go back to square one due to the lack of chain attribute caching configuration...
					// 'cause then we need to flush all those existing caches - obviously, that data is stale now, as we learned, since we are not allowed to cache anymore for the layout that was returned now
					if(isset($rememberTheLayout)) {
						// yup. clear it!
						$ourClass = get_class($this);
						$ourClass::clearCache($groups);
					}
					// $lm->log('Returned Layout is not cleared for caching, setting cacheable status to false.');
				} else {
					// $lm->log('Returned Layout is cleared for caching, proceeding...');
				}

				$chainAttributes = $chainInstance->getAttributes();
			}

			// clear the response
			$response = $container->getResponse();
			$response->clear();

			// clear any forward set, it's ze layout's job
			$container->clearNext();
			
			if($chainCache['layout_name'] !== Layout::NONE) {
				$container->setLayoutContentName($chainCache['layout_content']);

				$container->setLayoutName($chainCache['layout_name']);

				$key = $request->toggleLock();
				try {
					$layoutInstance = $container->getLayoutInstance();
				} catch(Exception $e) {
					// we caught an exception... unlock the request and rethrow!
					$request->toggleLock($key);
					throw $e;
				}
				$request->toggleLock($key);

				$outputType = $container->getOutputType()->getName();

				if($isCacheable) {
					if(isset($config['output_types'][$otConfig = $outputType]) || isset($config['output_types'][$otConfig = '*'])) {
						$otConfig = $config['output_types'][$otConfig];
						
						$layoutGroups = array_merge($groups, array($outputType));

						if($isActionCached) {
							$isLayoutCached = $this->checkCache($layoutGroups, $config['lifetime']);
							if(!$isLayoutCached) {
								// cacheable, but layout is not cached. notify our callback so it can prevent the stampede that follows
								$this->startedCacheCreationCallback(self::CACHE_CALLBACK_VIEW_NOT_CACHED, $layoutGroups, $config, $container);
							}
						}
					} else {
						$this->abortedCacheCreationCallback(self::CACHE_CALLBACK_OUTPUT_TYPE_NOT_CACHEABLE, $chainGroups, $config, $container);
						$isCacheable = false;
					}
				}

				if($isLayoutCached) {
					// $lm->log('Layout is cached, loading...');
					try {
						$layoutCache = $this->readCache($layoutGroups);
					} catch(Exception $e) {
						$this->startedCacheCreationCallback(self::CACHE_CALLBACK_VIEW_CACHE_GONE, $layoutGroups, $config, $container);
						$isLayoutCached = false;
					}
				}
				if(!$isLayoutCached) {
					// layout not cached
					// has the cache config a list of chain attributes?
					if($isActionCached && !$config['chain_attributes']) {
						// no. that means we must run the chain again!
						$isActionCached = false;
						
						if($isCacheable) {
							// notify our callback so it can remove the lock that's on the layout
							// but only if we're still marked as cacheable (if not, then that means the OT is not cacheable, so there wouldn't be a $layoutGroups)
							$this->abortedCacheCreationCallback(self::CACHE_CALLBACK_ACTION_CACHE_USELESS, $layoutGroups, $config, $container);
						}
						// notify our callback so it can prevent the stampede that follows
						$this->startedCacheCreationCallback(self::CACHE_CALLBACK_ACTION_CACHE_USELESS, $chainGroups, $config, $container);
						
						// but remember the layout info, just in case it differs if we run the chain again now
						$rememberTheLayout = array(
							'layout_content' => $chainCache['layout_content'],
							'layout_name' => $chainCache['layout_name'],
						);
						continue;
					}
				
					$layoutCache = array();
					$layoutCache['next'] = $this->executeLayout($container);
				}

				if($layoutCache['next'] instanceof ExecutionContainer) {
					// $lm->log('Forwarding request, skipping rendering...');
					$container->setNext($layoutCache['next']);
				} else {
					$output = array();
					$nextOutput = null;
				
					if($isLayoutCached) {
						$layers = $layoutCache['layers'];
						$response = $layoutCache['response'];
						$container->setResponse($response);

						foreach($layoutCache['template_variables'] as $name => $value) {
							$layoutInstance->setAttribute($name, $value);
						}

						foreach($layoutCache['request_attributes'] as $requestAttribute) {
							$request->setAttribute($requestAttribute['name'], $requestAttribute['value'], $requestAttribute['namespace']);
						}
					
						foreach($layoutCache['request_attribute_namespaces'] as $ranName => $ranValues) {
							$request->setAttributes($ranValues, $ranName);
						}

						$nextOutput = $response->getContent();
					} else {
						if($layoutCache['next'] !== null) {
							// response content was returned from layout execute()
							$response->setContent($nextOutput = $layoutCache['next']);
							$layoutCache['next'] = null;
						}

						$layers = $layoutInstance->getLayers();

						if($isCacheable) {
							$layoutCache['template_variables'] = array();
							foreach($otConfig['template_variables'] as $varName) {
								$layoutCache['template_variables'][$varName] = $layoutInstance->getAttribute($varName);
							}

							$layoutCache['response'] = clone $response;

							$layoutCache['layers'] = array();

							$layoutCache['slots'] = array();

							$lastCacheableLayer = -1;
							if(is_array($otConfig['layers'])) {
								if(count($otConfig['layers'])) {
									for($i = count($layers)-1; $i >= 0; $i--) {
										$layer = $layers[$i];
										$layerName = $layer->getName();
										if(isset($otConfig['layers'][$layerName])) {
											if(is_array($otConfig['layers'][$layerName])) {
												$lastCacheableLayer = $i - 1;
											} else {
												$lastCacheableLayer = $i;
											}
										}
									}
								}
							} else {
								$lastCacheableLayer = count($layers) - 1;
							}

							for($i = $lastCacheableLayer + 1; $i < count($layers); $i++) {
								// $lm->log('Adding non-cacheable layer "' . $layers[$i]->getName() . '" to list');
								$layoutCache['layers'][] = clone $layers[$i];
							}
						}
					}

					$attributes =& $layoutInstance->getAttributes();

					// whether or not we should assign the previous' layer's output to the $slots array
					$assignInnerToSlots = $this->getParameter('assign_inner_to_slots', false);
					
					// $lm->log('Starting rendering...');
					for($i = 0; $i < count($layers); $i++) {
						$layer = $layers[$i];
						$layerName = $layer->getName();
						// $lm->log('Running layer "' . $layerName . '"...');
						foreach($layer->getSlots() as $slotName => $slotContainer) {
							if($isLayoutCached && isset($layoutCache['slots'][$layerName][$slotName])) {
								// $lm->log('Loading cached slot "' . $slotName . '"...');
								$slotResponse = $layoutCache['slots'][$layerName][$slotName];
							} else {
								// $lm->log('Running slot "' . $slotName . '"...');
								$slotResponse = $slotContainer->execute();
								if($isCacheable && !$isLayoutCached && isset($otConfig['layers'][$layerName]) && is_array($otConfig['layers'][$layerName]) && in_array($slotName, $otConfig['layers'][$layerName])) {
									// $lm->log('Adding response of slot "' . $slotName . '" to cache...');
									$layoutCache['slots'][$layerName][$slotName] = $slotResponse;
								}
							}
							// set the presentation data as a template attribute
							ArrayPathDefinition::setValue($slotName, $output, $slotResponse->getContent());
							// and merge the other slot's response (this used to be conditional and done only when the content was not null)
							// $lm->log('Merging in response from slot "' . $slotName . '"...');
							$response->merge($slotResponse);
						}
						$moreAssigns = array(
							'container' => $container,
							'inner' => $nextOutput,
							'request_data' => $container->getRequestData(),
							'response' => $response,
							'validation_manager' => $container->getValidationManager(),
							'layout' => $layoutInstance,
						);
						// lock the request. can't be done outside the loop for the whole run, see #628
						$key = $request->toggleLock();
						try {
							$nextOutput = $layer->getRenderer()->render($layer, $attributes, $output, $moreAssigns);
						} catch(Exception $e) {
							// we caught an exception... unlock the request and rethrow!
							$request->toggleLock($key);
							throw $e;
						}
						// and unlock the request again
						$request->toggleLock($key);

						$response->setContent($nextOutput);

						if($isCacheable && !$isLayoutCached && $i === $lastCacheableLayer) {
							$layoutCache['response'] = clone $response;
						}

						$output = array();
						if($assignInnerToSlots) {
							$output[$layer->getName()] = $nextOutput;
						}
					}
				}

				if($isCacheable && !$isLayoutCached) {
					// we're writing the layout cache first. this is just in case we get into a situation with really bad timing on the leap of a second
					$layoutCache['request_attributes'] = array();
					foreach($otConfig['request_attributes'] as $requestAttribute) {
						$layoutCache['request_attributes'][] = $requestAttribute + array('value' => $request->getAttribute($requestAttribute['name'], $requestAttribute['namespace']));
					}
					$layoutCache['request_attribute_namespaces'] = array();
					foreach($otConfig['request_attribute_namespaces'] as $requestAttributeNamespace) {
						$layoutCache['request_attribute_namespaces'][$requestAttributeNamespace] = $request->getAttributes($requestAttributeNamespace);
					}

					$this->writeCache($layoutGroups, $layoutCache, $config['lifetime']);

					// notify callback that the execution has finished and caches have been written
					$this->finishedCacheCreationCallback(self::CACHE_CALLBACK_VIEW_CACHE_WRITTEN, $layoutGroups, $config, $container);
					// $lm->log('Writing Layout cache...');
				}
			}
		
			// chain cache writing must occur here, so chains that return Layout::NONE also get their cache written
			if($isCacheable && !$isActionCached) {
				$chainCache['chain_attributes'] = array();
				foreach($config['chain_attributes'] as $attributeName) {
					$chainCache['chain_attributes'][$attributeName] = $chainAttributes[$attributeName];
				}

				// $lm->log('Writing Action cache...');

				$this->writeCache($chainGroups, $chainCache, $config['lifetime']);
			
				// notify callback that the execution has finished and caches have been written
				$this->finishedCacheCreationCallback(self::CACHE_CALLBACK_ACTION_CACHE_WRITTEN, $chainGroups, $config, $container);
			}
			
			// we're done here. bai.
			break;
		}
		
	}

	/**
	 * Execute the Action
	 *
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @return     mixed The processed Layout information returned by the Action.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * 
	 * @deprecated since 0.1, use YoudsFramework\ExecutionContainer::runAction()
	 */
	protected function runAction(ExecutionContainer $container)
	{
		return $container->runAction();
	}
	
	/**
	 * execute this containers layout instance
	 * 
	 * @return     mixed the layout's result
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function executeLayout(ExecutionContainer $container)
	{
		$outputType = $container->getOutputType()->getName();
		$request = $this->context->getRequest();
		$layoutInstance = $container->getLayoutInstance();
		// $lm->log('Layout is not cached, executing...');
		// layout initialization completed successfully
        $executeMethod = 'execute' . ucfirst($outputType);

        if(!is_callable(array($layoutInstance, $executeMethod))) {
			$executeMethod = 'execute';
		}

        $key = $request->toggleLock();
		try {
			switch ($executeMethod):
				case 'execute':
				case 'executeRead':
				case 'executeWrite':
				case 'executeHtml':
                case 'executeHook':
                case 'executeText':
                    $layoutResult = $layoutInstance->$executeMethod($container->getRequestData() ?? new DataHolder());
				break;
				default:
					$layoutResult = $layoutInstance->$executeMethod($container->getRequestData() ?? new DataHolder());
			endswitch;
		} catch(Exception $e) {
			// we caught an exception... unlock the request and rethrow!
			$request->toggleLock($key);
			throw $e;
		}
		$request->toggleLock($key);
		return $layoutResult;
	}
	
}

?>
