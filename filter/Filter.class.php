<?php

namespace YoudsFramework;

use YoudsFramework\ExecutionContainer;
use YoudsFramework\Request\ParameterHolder;
use YoudsFramework\Filter\IFilter;
use YoudsFramework\Filter\Chain;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Filter provides a way for you to intercept incoming requests or outgoing
 * responses.
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
abstract class Filter extends ParameterHolder implements IFilter
{
	/**
	 * @var        Context An Context instance.
	 */
	protected $context = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Initialize this Filter.
	 *
	 * @param      Context The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing this Filter.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->context = $context;

		$this->setParameters($parameters);
	}
	
	/**
	 * The default "execute" method, which just calls continues in the chain.
	 *
	 * @param      Chain        A Chain instance.
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function execute(Chain $filterChain, ExecutionContainer $container)
	{
		$filterChain->execute($container);
	}
}

?>
