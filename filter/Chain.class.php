<?php
namespace YoudsFramework\Filter;
use YoudsFramework\Context;
use YoudsFramework\ExecutionContainer;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Chain manages registered filters for a specific context.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage filter
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Chain
{
	/**
	 * @constant   string Filter chain type identifier "chain".
	 */
	const TYPE_ACTION = 'chain';
	
	/**
	 * @constant   string Filter chain type identifier "global".
	 */
	const TYPE_GLOBAL = 'global';
	
	/**
	 * @var        array An array to keep track of filter execution.
	 */
	protected static $filterLog;
	
	/**
	 * @var        string The unique key to access the list of filters and their
	 *                    execution count for this filter chain's Context.
	 */
	protected $filterLogKey = '';
	
	/**
	 * @var        array The elements in this chain.
	 */
	protected $chain = array();
	
	/**
	 * @var        ExecutionContainer The execution container that is handed to filters.
	 */
	protected $context = null;

	/**
	 * @var        string The type of filter chain.
	 * @see        Chain::TYPE_ACTION
	 * @see        Chain::TYPE_GLOBAL
	 */
	protected $type = self::TYPE_ACTION;
	
	/**
	 * Initialize this Filter Chain.
	 *
	 * @param      Response the Response instance for this Chain.
	 * @param      array An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->context = $context;
		$this->filterLogKey = $context->getName();
	}
	
	/**
	 * Set the type of this filter chain.
	 *
	 * @see        Chain::TYPE_ACTION
	 * @see        Chain::TYPE_GLOBAL
	 *
	 * @param      string The type identifier.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function setType($type)
	{
		$this->type = $type;
	}
	
	/**
	 * Get the type of this filter chain.
	 *
	 * @see        Chain::TYPE_ACTION
	 * @see        Chain::TYPE_GLOBAL
	 *
	 * @return     string The type identifier.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Execute the next filter in this chain.
	 *
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function execute(ExecutionContainer $container)
	{
		if($filter = current($this->chain)) {
			// advance the pointer immediately; the next filter will call this again
			next($this->chain);
			$count = ++self::$filterLog[$this->filterLogKey][$fc = get_class($filter)];
			if($count == 1 && method_exists($filter, 'executeOnce')) {
				trigger_error(sprintf('Filter "%s" is implementing the deprecated method IFilter::executeOnce(); support may be removed for this method in later versions of Youds Framework.', $fc), E_USER_DEPRECATED);
				$filter->executeOnce($this, $container);
			} else {
				$filter->execute($this, $container);
			}
		}
	}

	/**
	 * Get a named filter instance from this chain.
	 *
	 * @param      string The name of the filter in this chain.
	 *
	 * @return     IFilter The filter instance, or null if no such filter.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getFilter($name)
	{
		if(isset($this->chain[$name])) {
			return $this->chain[$name];
		}
	}

	/**
	 * Register a filter with this chain.
	 *
	 * @param      IFilter A Filter implementation instance.
	 * @param      string       The filter name.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function register(IFilter $filter, $name)
	{
		$this->chain[$name] = $filter;
		$filterClass = get_class($filter);
		if(!isset(self::$filterLog[$this->filterLogKey][$filterClass])) {
			self::$filterLog[$this->filterLogKey][$filterClass] = 0;
		}
	}
}

?>
